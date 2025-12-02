<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Validate and store uploaded file
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param array $allowedMimes
     * @param int $maxSizeKB
     * @return string
     * @throws \Exception
     */
    public function uploadFile(
        UploadedFile $file,
        string $directory = 'uploads',
        array $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'],
        int $maxSizeKB = 5120
    ): string {
        // Validate file size
        if ($file->getSize() > ($maxSizeKB * 1024)) {
            throw new \Exception("File size exceeds maximum allowed size of {$maxSizeKB}KB");
        }

        // Get actual MIME type from file content
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $actualMimeType = finfo_file($finfo, $file->getRealPath());
        finfo_close($finfo);

        // Validate MIME type
        if (!in_array($actualMimeType, $allowedMimes)) {
            throw new \Exception("Invalid file type. Allowed types: " . implode(', ', $allowedMimes));
        }

        // Sanitize filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $filename = $filename . '_' . time() . '.' . $extension;

        // Store file
        $path = $file->storeAs($directory, $filename, 'public');

        return $path;
    }

    /**
     * Validate and store multiple files
     *
     * @param array $files
     * @param string $directory
     * @param array $allowedMimes
     * @param int $maxSizeKB
     * @param int $maxFiles
     * @return array
     * @throws \Exception
     */
    public function uploadMultipleFiles(
        array $files,
        string $directory = 'uploads',
        array $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'],
        int $maxSizeKB = 5120,
        int $maxFiles = 10
    ): array {
        if (count($files) > $maxFiles) {
            throw new \Exception("Maximum {$maxFiles} files allowed");
        }

        $uploadedPaths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploadedPaths[] = $this->uploadFile($file, $directory, $allowedMimes, $maxSizeKB);
            }
        }

        return $uploadedPaths;
    }

    /**
     * Delete file from storage
     *
     * @param string $path
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
    }
}

