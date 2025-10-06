<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class SingleSheetExport implements FromArray, WithTitle
{
    protected $stageData;
    protected $title;

    public function __construct(array $stageData, string $title)
    {
        $this->stageData = $stageData;
        $this->title = $title;
    }

    // Put keys as first row, values as second row
    public function array(): array
    {
        $headers = [];
        $values = [];

        foreach ($this->stageData as $key => $value) {
            $headers[] = $key;

            if (is_array($value) || is_object($value)) {
                $value = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }

            $values[] = $value;
        }

        return [
            $headers, // First row = column titles
            $values, // Second row = data
        ];
    }

    public function title(): string
    {
        return $this->title;
    }
}
