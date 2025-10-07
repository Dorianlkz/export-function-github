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
     * Flatten the array and control output for Excel
     * Column A = name / duration_days / field label / field key
     * Column B = empty
     * Column C = field value
     */
    protected function flatten(array $data, array &$rows)
    {
        // Output 'name' and 'duration_days' dynamically
        foreach (['name', 'duration_days'] as $key) {
            if (isset($data[$key])) {
                $rows[] = [
                    $key, // Column A: dynamic label
                    '', // Column B
                    $data[$key], // Column C: actual value
                ];
            }
        }

        // Add one empty row after each columns for spacing
        $rows[] = [
            '', // Column A
            '', // Column B
            '', // Column C
        ];

        // Then output 'fields'
        if (isset($data['fields']) && is_array($data['fields'])) {
            $fieldNumber = 1;
            foreach ($data['fields'] as $fieldItem) {
                // Field label row
                $rows[] = [
                    'field ' . $fieldNumber, // Column A
                    '', // Column B
                    '', // Column C
                ];

                // Flatten each field item: output key => value directly
                if (is_array($fieldItem)) {
                    foreach ($fieldItem as $fKey => $fValue) {
                        $rows[] = [
                            $fKey, // Column A
                            '', // Column B
                            $fValue, // Column C
                        ];
                    }
                }

                // Add one empty row after each field for spacing
                $rows[] = [
                    '', // Column A
                    '', // Column B
                    '', // Column C
                ];

                $fieldNumber++;
            }
        }
    }
}
