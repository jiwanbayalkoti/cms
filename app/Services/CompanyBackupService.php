<?php

namespace App\Services;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class CompanyBackupService
{
    public function rootPath(): string
    {
        return (string) config('backup.company_root', base_path('company_backups'));
    }

    public function companyDirectory(Company $company): string
    {
        return $this->rootPath() . DIRECTORY_SEPARATOR . Str::slug($company->name) . '-' . $company->id;
    }

    public function monthDirectory(Company $company, ?Carbon $at = null): string
    {
        $at ??= now();

        return $this->companyDirectory($company) . DIRECTORY_SEPARATOR . $at->format('Y-m');
    }

    /**
     * @return array{path:string,relative:string,filename:string,month:string,trigger:string,size:int}
     */
    public function runBackup(Company $company, array $dataPlan, string $trigger = 'manual'): array
    {
        $trigger = in_array($trigger, ['manual', 'scheduled'], true) ? $trigger : 'manual';
        $filename = 'backup-' . now()->format('Y-m-d-His') . '-' . $trigger . '.zip';
        $destination = $this->monthDirectory($company) . DIRECTORY_SEPARATOR . $filename;

        $path = $this->createZip($company, $dataPlan, $destination);

        return [
            'path' => $path,
            'relative' => $this->relativePath($path),
            'filename' => $filename,
            'month' => now()->format('Y-m'),
            'trigger' => $trigger,
            'size' => File::size($path),
        ];
    }

    public function listBackups(Company $company): array
    {
        $companyDir = $this->companyDirectory($company);
        if (!File::isDirectory($companyDir)) {
            return [];
        }

        $backups = [];

        foreach (File::directories($companyDir) as $monthDir) {
            $month = basename($monthDir);
            if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
                continue;
            }

            foreach (File::files($monthDir) as $file) {
                if (strtolower($file->getExtension()) !== 'zip') {
                    continue;
                }

                $filename = $file->getFilename();
                if (!$this->isValidBackupFilename($filename)) {
                    continue;
                }

                $backups[] = [
                    'month' => $month,
                    'filename' => $filename,
                    'size' => $file->getSize(),
                    'size_human' => $this->formatBytes($file->getSize()),
                    'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                    'trigger' => str_contains($filename, '-scheduled.zip') ? 'scheduled' : 'manual',
                    'relative' => $this->relativePath($file->getPathname()),
                ];
            }
        }

        usort($backups, static fn (array $a, array $b) => strcmp($b['created_at'], $a['created_at']));

        return $backups;
    }

    public function resolveStoredBackup(Company $company, string $month, string $filename): ?string
    {
        $month = basename($month);
        $filename = basename($filename);

        if (!preg_match('/^\d{4}-\d{2}$/', $month) || !$this->isValidBackupFilename($filename)) {
            return null;
        }

        $path = $this->companyDirectory($company) . DIRECTORY_SEPARATOR . $month . DIRECTORY_SEPARATOR . $filename;

        return File::isFile($path) ? $path : null;
    }

    public function createZip(Company $company, array $dataPlan, ?string $destinationPath = null): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new \RuntimeException('ZipArchive PHP extension is not available.');
        }

        $tempDir = storage_path('app/temp/backups/' . Str::uuid());
        $databaseDir = $tempDir . '/database';
        $filesDir = $tempDir . '/files';

        File::ensureDirectoryExists($databaseDir);
        File::ensureDirectoryExists($filesDir);

        $tableData = [];
        $tableCounts = [];

        $companyRow = DB::table('companies')->where('id', $company->id)->first();
        if ($companyRow) {
            $tableData['companies'] = [(array) $companyRow];
            $tableCounts['companies'] = 1;
        }

        foreach ($dataPlan as $table => $entry) {
            $rows = $this->exportTableRows($table, $entry);
            if ($rows === []) {
                continue;
            }

            $tableData[$table] = $rows;
            $tableCounts[$table] = count($rows);
        }

        $sql = $this->buildSqlDump($company, $tableData);
        File::put($databaseDir . '/dump.sql', $sql);

        File::ensureDirectoryExists($databaseDir . '/json');
        foreach ($tableData as $table => $rows) {
            $this->writeJson($databaseDir . '/json/' . $table . '.json', $this->sanitizeRows($table, $rows));
        }

        $storagePaths = $this->collectStoragePaths($company, $dataPlan);
        $filesAdded = $this->copyFilesToBackup($storagePaths, $filesDir);

        $manifest = [
            'version' => 2,
            'created_at' => now()->toIso8601String(),
            'company_id' => $company->id,
            'company_name' => $company->name,
            'backup_month' => now()->format('Y-m'),
            'tables' => $tableCounts,
            'files_count' => $filesAdded,
            'contents' => [
                'database/dump.sql' => 'Full SQL INSERT dump for this company (restoreable)',
                'database/json/' => 'JSON copy of table rows (passwords excluded)',
                'files/' => 'Uploaded files (logo, documents, letterhead, project files/photos)',
            ],
            'notes' => 'Complete company backup: SQL dump + files. SQL includes user password hashes for restore.',
        ];
        $this->writeJson($tempDir . '/manifest.json', $manifest);

        if ($destinationPath === null) {
            $destinationPath = storage_path('app/temp/backups/' . Str::slug($company->name) . '-' . now()->format('Y-m-d-His') . '.zip');
        }

        File::ensureDirectoryExists(dirname($destinationPath));

        $zip = new ZipArchive();
        if ($zip->open($destinationPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            File::deleteDirectory($tempDir);
            throw new \RuntimeException('Could not create backup archive.');
        }

        $this->addDirectoryToZip($zip, $tempDir, '');
        $zip->close();

        File::deleteDirectory($tempDir);

        return $destinationPath;
    }

    /**
     * @param  array<string, list<array<string, mixed>>>  $tableData
     */
    protected function buildSqlDump(Company $company, array $tableData): string
    {
        $database = (string) DB::getDatabaseName();
        $lines = [];
        $lines[] = '-- ============================================================';
        $lines[] = '-- Company SQL Backup';
        $lines[] = '-- Company: ' . $this->sqlComment($company->name) . ' (ID: ' . (int) $company->id . ')';
        $lines[] = '-- Database: ' . $this->sqlComment($database);
        $lines[] = '-- Created: ' . now()->toDateTimeString();
        $lines[] = '-- ============================================================';
        $lines[] = '';
        $lines[] = 'SET NAMES utf8mb4;';
        $lines[] = 'SET FOREIGN_KEY_CHECKS=0;';
        $lines[] = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";';
        $lines[] = 'SET time_zone = "+00:00";';
        $lines[] = '';

        foreach ($tableData as $table => $rows) {
            if ($rows === []) {
                continue;
            }

            $lines[] = '-- --------------------------------------------------------';
            $lines[] = '-- Table: `' . $table . '` (' . count($rows) . ' rows)';
            $lines[] = '-- --------------------------------------------------------';
            $lines[] = '';

            foreach (array_chunk($rows, 100) as $chunk) {
                $columns = array_keys($chunk[0]);
                $columnList = implode(', ', array_map(fn ($col) => '`' . str_replace('`', '``', $col) . '`', $columns));
                $valueGroups = [];

                foreach ($chunk as $row) {
                    $values = [];
                    foreach ($columns as $column) {
                        $values[] = $this->sqlValue($row[$column] ?? null);
                    }
                    $valueGroups[] = '(' . implode(', ', $values) . ')';
                }

                $lines[] = 'INSERT INTO `' . str_replace('`', '``', $table) . '` (' . $columnList . ') VALUES';
                $lines[] = implode(",\n", $valueGroups) . ';';
                $lines[] = '';
            }
        }

        $lines[] = 'SET FOREIGN_KEY_CHECKS=1;';
        $lines[] = '';
        $lines[] = '-- End of company backup';
        $lines[] = '';

        return implode("\n", $lines);
    }

    protected function sqlValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return "'" . $value->format('Y-m-d H:i:s') . "'";
        }

        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }

        $string = (string) $value;

        return "'" . str_replace(
            ["\\", "\0", "\n", "\r", "'", '"', "\x1a"],
            ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
            $string
        ) . "'";
    }

    protected function sqlComment(string $text): string
    {
        return str_replace(["\r", "\n"], ' ', $text);
    }

    protected function relativePath(string $absolutePath): string
    {
        $root = rtrim(str_replace('\\', '/', $this->rootPath()), '/');
        $path = str_replace('\\', '/', $absolutePath);

        if (str_starts_with($path, $root . '/')) {
            return substr($path, strlen($root) + 1);
        }

        return $path;
    }

    protected function isValidBackupFilename(string $filename): bool
    {
        return (bool) preg_match('/^backup-\d{4}-\d{2}-\d{2}-\d{6}-(manual|scheduled)\.zip$/', $filename);
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        if ($bytes < 1073741824) {
            return round($bytes / 1048576, 1) . ' MB';
        }

        return round($bytes / 1073741824, 2) . ' GB';
    }

    protected function exportTableRows(string $table, array $entry): array
    {
        $rows = [];

        if (!empty($entry['ids'])) {
            foreach (array_chunk($entry['ids'], 500) as $ids) {
                $query = DB::table($table)->whereIn('id', $ids);
                if ($table === 'users') {
                    $query->where('role', '!=', 'super_admin');
                }
                foreach ($query->get() as $row) {
                    $rows[] = (array) $row;
                }
            }

            return $rows;
        }

        foreach ($entry['criteria'] as $criterion) {
            foreach (array_chunk($criterion['values'], 500) as $values) {
                $query = DB::table($table)->whereIn($criterion['column'], $values);
                if ($table === 'users') {
                    $query->where('role', '!=', 'super_admin');
                }
                foreach ($query->get() as $row) {
                    $rows[] = (array) $row;
                }
            }
        }

        return $rows;
    }

    protected function sanitizeRows(string $table, array $rows): array
    {
        if ($table !== 'users') {
            return $rows;
        }

        return array_map(static function (array $row) {
            unset($row['password'], $row['remember_token']);

            return $row;
        }, $rows);
    }

    protected function collectStoragePaths(Company $company, array $dataPlan): array
    {
        $paths = [];
        $disk = Storage::disk('public');

        foreach (['logo', 'favicon'] as $column) {
            $path = $company->{$column};
            if (is_string($path) && $path !== '' && $disk->exists($path)) {
                $paths[$path] = $path;
            }
        }

        foreach ([
            "company_documents/{$company->id}",
            "company-letterhead-assets/{$company->id}",
            "company-letterheads/{$company->id}",
            "company-payment-proofs/{$company->id}",
        ] as $directory) {
            foreach ($disk->allFiles($directory) as $file) {
                $paths[$file] = $file;
            }
        }

        $projectRows = $dataPlan['projects']['ids'] ?? null;
        if (is_array($projectRows) && $projectRows !== []) {
            foreach (array_chunk($projectRows, 500) as $ids) {
                foreach (DB::table('projects')->whereIn('id', $ids)->get(['files', 'photos']) as $project) {
                    $this->collectJsonFilePaths($paths, $project->files ?? null);
                    $this->collectProjectPhotoPaths($paths, $project->photos ?? null);
                }
            }
        }

        if (isset($dataPlan['company_letterhead_exports'])) {
            foreach ($this->exportTableRows('company_letterhead_exports', $dataPlan['company_letterhead_exports']) as $row) {
                $path = $row['path'] ?? null;
                if (is_string($path) && $path !== '' && $disk->exists($path)) {
                    $paths[$path] = $path;
                }
            }
        }

        if (isset($dataPlan['company_documents'])) {
            foreach ($this->exportTableRows('company_documents', $dataPlan['company_documents']) as $row) {
                $path = $row['file_path'] ?? null;
                if (is_string($path) && $path !== '' && $disk->exists($path)) {
                    $paths[$path] = $path;
                }
            }
        }

        return array_values($paths);
    }

    protected function collectJsonFilePaths(array &$paths, mixed $value): void
    {
        if (!is_array($value)) {
            return;
        }

        $disk = Storage::disk('public');

        foreach ($value as $item) {
            if (!is_array($item)) {
                continue;
            }

            $path = $item['path'] ?? null;
            if (is_string($path) && $path !== '' && $disk->exists($path)) {
                $paths[$path] = $path;
            }
        }
    }

    protected function collectProjectPhotoPaths(array &$paths, mixed $value): void
    {
        if (!is_array($value)) {
            return;
        }

        $disk = Storage::disk('public');

        foreach ($value as $album) {
            if (!is_array($album)) {
                continue;
            }

            foreach ($album['photos'] ?? [] as $photo) {
                if (!is_array($photo)) {
                    continue;
                }

                $path = $photo['path'] ?? null;
                if (is_string($path) && $path !== '' && $disk->exists($path)) {
                    $paths[$path] = $path;
                }
            }
        }
    }

    protected function copyFilesToBackup(array $storagePaths, string $filesDir): int
    {
        $disk = Storage::disk('public');
        $added = 0;

        foreach ($storagePaths as $path) {
            if (!$disk->exists($path)) {
                continue;
            }

            $target = $filesDir . '/' . str_replace('\\', '/', $path);
            File::ensureDirectoryExists(dirname($target));
            File::copy($disk->path($path), $target);
            $added++;
        }

        return $added;
    }

    protected function addDirectoryToZip(ZipArchive $zip, string $directory, string $zipPrefix): void
    {
        $directory = rtrim($directory, DIRECTORY_SEPARATOR);
        $zipPrefix = trim(str_replace('\\', '/', $zipPrefix), '/');

        foreach (File::allFiles($directory) as $file) {
            $relative = ltrim(str_replace('\\', '/', Str::after($file->getPathname(), $directory . DIRECTORY_SEPARATOR)), '/');
            $zipPath = $zipPrefix === '' ? $relative : $zipPrefix . '/' . $relative;
            $zip->addFile($file->getPathname(), $zipPath);
        }
    }

    protected function writeJson(string $path, mixed $data): void
    {
        File::put($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
    }
}
