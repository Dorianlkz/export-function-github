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
     * Column A = key (with indentation)
     * Column B = parent ID (only at top of block for 'name' or 'duration_days')
     * Column C = value
     */
    protected function flatten(array $data, array &$rows, int $level = 0, ?string $parentId = null, bool $isTopLevel = true)
    {
        foreach ($data as $key => $value) {
            // Special handling for 'fields' array
            if ($key === 'fields' && is_array($value)) {
                $fieldNumber = 1;
                foreach ($value as $fieldItem) {
                    // Field label row (no indentation)
                    $row = [];
                    $row[] = 'field ' . $fieldNumber; // Column A
                    $row[] = ''; // Column B: parent ID
                    $row[] = ''; // Column C: value (children come below)
                    $rows[] = $row;

                    // Flatten fieldItem with level=0 to avoid indentation
                    if (is_array($fieldItem)) {
                        $this->flatten($fieldItem, $rows, 0, $parentId, true);
                    }

                    $fieldNumber++;
                }
                continue; // skip default processing for 'fields' key
            }

            // Normal processing for other keys
            $row = array_fill(0, $level, ''); // indentation for Column A
            $row[] = $key;

            // Parent ID only for top-level 'name'/'duration_days'
            if ($isTopLevel && in_array($key, ['name', 'duration_days'])) {
                $row[] = $parentId;
            } else {
                $row[] = '';
            }

            if (is_array($value)) {
                $rows[] = $row;

                $currentId = $parentId;
                if (isset($value['id'])) {
                    $currentId = (string) $value['id'];
                }

                if ($this->isAssoc($value)) {
                    $this->flatten($value, $rows, $level + 1, $currentId, true);
                } else {
                    foreach ($value as $item) {
                        if (is_array($item)) {
                            $this->flatten($item, $rows, $level + 1, $currentId, true);
                        }
                    }
                }
            } else {
                $row[] = $value;
                $rows[] = $row;

                if ($key === 'id') {
                    $parentId = (string) $value;
                }
            }

            $isTopLevel = false;
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
