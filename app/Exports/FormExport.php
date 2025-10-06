<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FormExport implements WithMultipleSheets
{
    protected $stages;

    public function __construct(array $stages)
    {
        $this->stages = $stages;
    }

    // Create a sheet for each stage
    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->stages as $stage) {
            $stageName = $stage['name'] ?? 'Unnamed Stage';
            $sheets[] = new SingleSheetExport($stage, $stageName);
        }

        return $sheets;
    }
}
