<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\RichText\RichText;

class SingleSheetExport implements FromArray, WithTitle, WithEvents
{
    protected array $stageData;
    protected string $title;

    /** styled rows [ rowNumber => ['color'=>'HEX'] ] */
    protected array $styledRows = [];

    /** fixed number of columns (A..AN = 40 cols) */
    protected int $padCols = 40;

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
     * Insert a marker row and record its style info.
     */
    protected function addStyledRow(array &$rows, string $color = '999999'): void
    {
        $nbsp = html_entity_decode('&nbsp;', ENT_QUOTES, 'UTF-8');
        $marker = array_fill(0, $this->padCols, $nbsp);

        $rows[] = $marker;
        $rowNumber = count($rows);

        $this->styledRows[$rowNumber] = [
            'color' => strtoupper(ltrim($color, '#')),
        ];
    }

    /**
     * Insert a visible blank row.
     */
    protected function addBlankRow(array &$rows): void
    {
        $nbsp = html_entity_decode('&nbsp;', ENT_QUOTES, 'UTF-8');
        $rows[] = [$nbsp];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColLetter = Coordinate::stringFromColumnIndex($this->padCols);

                foreach ($this->styledRows as $row => $props) {
                    $color = $props['color'] ?? '999999';

                    // Apply fill from A..AN (40 cols)
                    $sheet
                        ->getStyle("A{$row}:{$lastColLetter}{$row}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setRGB($color);

                    // Clear NBSP markers
                    for ($col = 1; $col <= $this->padCols; $col++) {
                        $sheet->setCellValueByColumnAndRow($col, $row, '');
                    }
                }
            },
        ];
    }

    /**
     * Build rows.
     */
    protected function flatten(array $data, array &$rows)
    {
        if (!empty($data['fields']) && is_array($data['fields'])) {
            $fieldNumber = 1;

            foreach ($data['fields'] as $fieldItem) {
                // Spacer after each field
                $this->addBlankRow($rows);
                $this->addStyledRow($rows, '999999');
                if (!is_array($fieldItem)) {
                    continue;
                }

                // === Parent field row ===
                $label = $fieldItem['label'] ?? '(no label)';
                $rows[] = [$fieldNumber . '. ' . $label];
                $rows[] = ['Remark: ' . ($fieldItem['remark'] ?? '-')];
                $rows[] = ['Type: ' . ($fieldItem['type'] ?? '')];

                if (!empty($fieldItem['validations'])) {
                    foreach ($fieldItem['validations'] as $validation) {
                        $rows[] = ['Validation: ' . ($validation['type'] ?? '') . ' - ' . ($validation['message'] ?? '')];
                    }
                }

                // === If it's a QUERY_CHECKER, show options & actions ===
                if (($fieldItem['type'] ?? '') === 'QUERY_CHECKER') {
                    if (!empty($fieldItem['options']) && is_array($fieldItem['options'])) {
                        foreach ($fieldItem['options'] as $optIndex => $option) {
                            $this->addBlankRow($rows);
                            $rows[] = ['Option ' . ($optIndex + 1) . ': ' . ($option['label'] ?? '')];

                            // Actions inside option
                            if (!empty($option['actions']) && is_array($option['actions'])) {
                                foreach ($option['actions'] as $actIndex => $action) {
                                    $rows[] = ['Action ' . ($actIndex + 1) . ': ' . ($action['label'] ?? '')];
                                    $rows[] = ['Remark: ' . ($action['remark'] ?? '-')];
                                    $rows[] = ['Type: ' . ($action['type'] ?? '')];

                                    if (!empty($action['validations'])) {
                                        foreach ($action['validations'] as $aVal) {
                                            $rows[] = ['Validation: ' . ($aVal['type'] ?? '') . ' - ' . ($aVal['message'] ?? '')];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $fieldNumber++;
            }
        }
    }
}
