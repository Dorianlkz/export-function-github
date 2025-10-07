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

    /** bold rows [ rowNumber => true ] */
    protected array $boldRows = [];

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

                // Apply background color rows
                foreach ($this->styledRows as $row => $props) {
                    $color = $props['color'] ?? '999999';

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

                // Apply bold styling
                foreach ($this->boldRows as $row => $_) {
                    $sheet
                        ->getStyle("A{$row}:{$lastColLetter}{$row}")
                        ->getFont()
                        ->setBold(true);
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
                $parentLabel = new RichText();
                $parentLabel
                    ->createTextRun($fieldNumber . '. ' . ($fieldItem['label'] ?? '(no label)'))
                    ->getFont()
                    ->setBold(true);
                $rows[] = [$parentLabel];

                // Remark
                $remarkText = new RichText();
                $remarkText->createTextRun('Remark: ')->getFont()->setBold(true);
                $remarkText->createText($fieldItem['remark'] ?? '-');
                $rows[] = [$remarkText];

                // Type
                $typeText = new RichText();
                $typeText->createTextRun('Type: ')->getFont()->setBold(true);
                $typeText->createText($fieldItem['type'] ?? '');
                $rows[] = [$typeText];

                // Validations
                if (!empty($fieldItem['validations'])) {
                    foreach ($fieldItem['validations'] as $validation) {
                        $valText = new RichText();
                        $valText->createTextRun('Validation: ')->getFont()->setBold(true);
                        $valText->createText(($validation['type'] ?? '') . ' - ' . ($validation['message'] ?? ''));
                        $rows[] = [$valText];
                    }
                }

                // === If it's a QUERY_CHECKER, show options & actions ===
                if (($fieldItem['type'] ?? '') === 'QUERY_CHECKER') {
                    if (!empty($fieldItem['options']) && is_array($fieldItem['options'])) {
                        foreach ($fieldItem['options'] as $optIndex => $option) {
                            $this->addBlankRow($rows);

                            // Option
                            $rows[] = ['Option ' . ($optIndex + 1) . ': ' . ($option['label'] ?? '')];

                            // Actions inside option
                            if (!empty($option['actions']) && is_array($option['actions'])) {
                                foreach ($option['actions'] as $actIndex => $action) {
                                    // Action (all bold)
                                    $actionText = new RichText();
                                    $run = $actionText->createTextRun('Action ' . ($actIndex + 1) . ': ' . ($action['label'] ?? ''));
                                    $run->getFont()->setBold(true);
                                    $rows[] = [$actionText];

                                    // Remark
                                    $aRemark = new RichText();
                                    $aRemark->createTextRun('Remark: ')->getFont()->setBold(true);
                                    $aRemark->createText($action['remark'] ?? '-');
                                    $rows[] = [$aRemark];

                                    // Type
                                    $aType = new RichText();
                                    $aType->createTextRun('Type: ')->getFont()->setBold(true);
                                    $aType->createText($action['type'] ?? '');
                                    $rows[] = [$aType];

                                    // Validations
                                    if (!empty($action['validations'])) {
                                        foreach ($action['validations'] as $aVal) {
                                            $aValText = new RichText();
                                            $aValText->createTextRun('Validation: ')->getFont()->setBold(true);
                                            $aValText->createText(($aVal['type'] ?? '') . ' - ' . ($aVal['message'] ?? ''));
                                            $rows[] = [$aValText];
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
