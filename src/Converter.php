<?php
namespace ArmoniaCsv;

use PhpOffice\PhpSpreadsheet\IOFactory;

class Converter
{
    /**
     * To Xlsx
     *
     * @author Armonia Tech <developer@armonia-tech.com>
     * @param string $source_file Full path of the source csv to be convert
     * @param string $source_encoding File encoding of the content in csv
     * @param bool $keep_source_file true to keep the source_file. false to remove the source_file after generated
     * @param bool $autoSizeColumn
     * @param string $font
     * @param int $font_size
     * @return string
     */
    public static function toXlsx(string $source_file, string $source_encoding = 'UTF-8', bool $keep_source_file = true, bool $autoSizeColumn = true, string $font = 'ＭＳ Ｐゴシック', int $font_size = 18)
    {
        $reader = IOFactory::createReader('Csv');

        // If the files uses an encoding other than UTF-8 or ASCII, then tell the reader
        if ($source_encoding != 'UTF-8') {
            $reader->setInputEncoding($source_encoding);
        }

        // Load spread sheet
        $source_sheet = $reader->load($source_file);

        $source_sheet->getDefaultStyle()->getFont()->setName($font);
        $source_sheet->getDefaultStyle()->getFont()->setSize($font_size);

        // Handle auto size column
        if ($autoSizeColumn === true) {
            foreach ($source_sheet->getActiveSheet()->getColumnIterator() as $column) {
                $source_sheet->getActiveSheet()->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
            }

            // Remark: after calculating the width, we turn off autoSize and
            // fit actual japanese character by multiplying ~1.7-2.0.
            // Note that This will make pure ascii column(s) much larger than usual
            $source_sheet->getActiveSheet()->calculateColumnWidths();
            foreach ($source_sheet->getActiveSheet()->getColumnIterator() as $column) {
                $column_dimension = $source_sheet->getActiveSheet()->getColumnDimension($column->getColumnIndex());
                $column_dimension->setAutoSize(false);
                $column_width = $column_dimension->getWidth();
                $column_dimension->setWidth($column_width * 1.9);
            }
        }

        // Append .xlsx extension. There are 2 cases here
        // 1. source_file ends with .csv, it will be replaced with .xlsx
        // 2. source_file ends with other extensions, .xlsx will be appened instead
        if (strtolower(substr($source_file, -4)) === '.csv') {
            $xlsx_file = substr($source_file, 0, -4) . '.xlsx';
        } else {
            $xlsx_file = $source_file . '.xlsx';
        }

        $xlsx_writer = IOFactory::createWriter($source_sheet, 'Xlsx');
        $xlsx_writer->save($xlsx_file);

        if (file_exists($xlsx_file) && $keep_source_file === false) {
            unlink($source_file);
        }

        return $xlsx_file;
    }
}
