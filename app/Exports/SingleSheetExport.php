<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class SingleSheetExport implements FromArray, WithTitle
{
    protected array $stageData;
    protected string $title;

    public function __construct(array $stageData, string $title)
    {
        $this->stageData = $stageData;
        $this->title = $title;
    }

    public function array(): array
    {
        $rows = [];
        $this->flatten($this->stageData, $rows);
        return $rows;
    }

    public function title(): string
    {
        return $this->title;
    }

    /**
     * Recursively flatten the data
     * Column A = key
     * Column B = parent ID (only at the start of a new block)
     * Column C = value
     */
    protected function flatten(array $data, array &$rows, int $level = 0, ?string $parentId = null)
    {
        foreach ($data as $key => $value) {
            $row = array_fill(0, $level, ''); // indentation

            $row[] = $key;

            $currentId = null;
            if (is_array($value)) {
                // If associative array with 'id', treat as parent
                if (isset($value['id'])) {
                    $currentId = (string) $value['id'];
                    $row[] = $level === 0 ? $currentId : $parentId; // only top-level gets Column B
                } else {
                    $row[] = $parentId; // nested array, keep parentId
                }

                $rows[] = $row;

                if ($this->isAssoc($value)) {
                    $this->flatten($value, $rows, $level + 1, $currentId ?? $parentId);
                } else {
                    // Indexed array
                    foreach ($value as $item) {
                        if (is_array($item)) {
                            $this->flatten($item, $rows, $level + 1, $currentId ?? $parentId);
                        }
                    }
                }
            } else {
                $row[] = $parentId;
                $row[] = $value;
                $rows[] = $row;

                if ($key === 'id') {
                    $currentId = (string) $value;
                }
            }
        }
    }

    protected function isAssoc(array $arr): bool
    {
        if ([] === $arr) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
