<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CompanyDataScopeService
{
    public function buildPlan(int $companyId): array
    {
        $columns = $this->databaseColumnsByTable();
        $foreignKeys = $this->databaseForeignKeys();
        $plan = [];
        $queue = [];

        foreach ($columns as $table => $tableColumns) {
            if ($table === 'companies' || !in_array('company_id', $tableColumns, true)) {
                continue;
            }

            $ids = $this->pluckIdsByColumnValues($table, 'company_id', [$companyId], $columns);
            $count = $ids !== null
                ? count($ids)
                : $this->countRowsByColumnValues($table, 'company_id', [$companyId]);

            if ($count < 1) {
                continue;
            }

            $this->addPlanEntry($plan, $table, 'company_id', [$companyId], $ids, 1);
            if ($ids !== null && $ids !== []) {
                $queue[] = ['table' => $table, 'ids' => $ids, 'depth' => 1];
            }
        }

        while ($queue !== []) {
            $current = array_shift($queue);
            $parentTable = $current['table'];
            $parentIds = $current['ids'];
            $depth = $current['depth'];

            foreach ($foreignKeys as $foreignKey) {
                if ($foreignKey['parent_table'] !== $parentTable || $foreignKey['child_table'] === 'companies') {
                    continue;
                }

                $childTable = $foreignKey['child_table'];
                $childColumn = $foreignKey['child_column'];
                $childIds = $this->pluckIdsByColumnValues($childTable, $childColumn, $parentIds, $columns);
                $count = $childIds !== null
                    ? count($childIds)
                    : $this->countRowsByColumnValues($childTable, $childColumn, $parentIds);

                if ($count < 1) {
                    continue;
                }

                $newIds = $this->addPlanEntry($plan, $childTable, $childColumn, $parentIds, $childIds, $depth + 1);
                if ($newIds !== []) {
                    $queue[] = ['table' => $childTable, 'ids' => $newIds, 'depth' => $depth + 1];
                }
            }
        }

        return $plan;
    }

    protected function addPlanEntry(array &$plan, string $table, string $column, array $values, ?array $ids, int $depth): array
    {
        $plan[$table] ??= [
            'ids' => [],
            'criteria' => [],
            'depth' => 0,
        ];

        $plan[$table]['depth'] = max($plan[$table]['depth'], $depth);
        $plan[$table]['criteria'][] = [
            'column' => $column,
            'values' => array_values(array_unique($values)),
        ];

        if ($ids === null) {
            return [];
        }

        $newIds = array_values(array_diff($ids, $plan[$table]['ids']));
        $plan[$table]['ids'] = array_values(array_unique(array_merge($plan[$table]['ids'], $ids)));

        return $newIds;
    }

    protected function databaseColumnsByTable(): array
    {
        $rows = DB::select(
            "SELECT TABLE_NAME, COLUMN_NAME
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()"
        );

        $columns = [];
        foreach ($rows as $row) {
            $table = (string) ($row->TABLE_NAME ?? '');
            $column = (string) ($row->COLUMN_NAME ?? '');
            if ($table === '' || $column === '') {
                continue;
            }

            $columns[$table] ??= [];
            $columns[$table][] = $column;
        }

        return $columns;
    }

    protected function databaseForeignKeys(): array
    {
        $rows = DB::select(
            "SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME
             FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND REFERENCED_TABLE_NAME IS NOT NULL"
        );

        return collect($rows)
            ->map(fn ($row) => [
                'child_table' => (string) ($row->TABLE_NAME ?? ''),
                'child_column' => (string) ($row->COLUMN_NAME ?? ''),
                'parent_table' => (string) ($row->REFERENCED_TABLE_NAME ?? ''),
            ])
            ->filter(fn (array $row) => $row['child_table'] !== '' && $row['child_column'] !== '' && $row['parent_table'] !== '')
            ->values()
            ->all();
    }

    protected function pluckIdsByColumnValues(string $table, string $column, array $values, array $columns): ?array
    {
        if (!in_array('id', $columns[$table] ?? [], true)) {
            return null;
        }

        $ids = [];
        foreach (array_chunk(array_values(array_unique($values)), 500) as $chunk) {
            $ids = array_merge($ids, DB::table($table)->whereIn($column, $chunk)->pluck('id')->all());
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    protected function countRowsByColumnValues(string $table, string $column, array $values): int
    {
        $count = 0;
        foreach (array_chunk(array_values(array_unique($values)), 500) as $chunk) {
            $count += DB::table($table)->whereIn($column, $chunk)->count();
        }

        return $count;
    }
}
