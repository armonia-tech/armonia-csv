<?php
namespace ArmoniaCsv;

use ArmoniaCsv\Validator;

class Csv
{
    const UTF_8 = 'UTF-8';
    const UTF8_BYTE_ORDER_MARK = "\xEF\xBB\xBF";

    private static $config              = [];
    private static $supportedMBEncoding = [
        "UTF-8",
        "SJIS-WIN",
        "SHIFT_JIS",
        "EUC",
        "CP932"
    ];

    /**
     * set csv folder name
     *
     * @author Armonia Tech <developer@armonia-tech.com>
     * @param string $directoryPath
     * @return void
     */
    public static function setupCsvFolder(string $directoryPath)
    {
        self::$config['directory_path'] = $directoryPath;
        self::createConfigFoler();
    }

    /**
     * Render csv file
     *
     * @author Armonia Tech <developer@armonia-tech.com>
     * @param string $filePath
     * @param string $formatName
     * @param bool $hasHeader optional
     * @param int $skipDataLine optional default false
     * @return array
     */
    public static function renderCsv(string $filePath, string $formatName, bool $hasHeader = true, int $skipDataLine = 0, bool $skipEmptyRow = false)
    {
        if (file_exists($filePath)) {
            $fileContent  = file_get_contents($filePath);
            return self::renderCsvContent($fileContent, $formatName, $hasHeader, $skipDataLine, '', $skipEmptyRow);
        }

        throw new \Exception('Csv file doesn\'t exists.');
    }

    /**
     * Render csv content
     *
     * @author Armonia Tech <developer@armonia-tech.com>
     * @param string $csvContent
     * @param string $formatName
     * @param bool $hasHeader optional
     * @param int $skipDataLine optional
     * @param bool $skipEmptyRow optional default false
     * @param bool $validateDuplicateColumnValidation optional default false
     * @return array
     */
    public static function renderCsvContent(string $csvContent, string $formatName, bool $hasHeader = true, int $skipDataLine = 0, string $separator = "", bool $skipEmptyRow = false, bool $validateDuplicateColumnValidation = false)
    {
        self::checkFileFormatExists($formatName);
        self::checkJsonSchemaExists($formatName);

        $fileEncoding = self::fileDetectEncoding($csvContent);

        if ($fileEncoding != self::UTF_8) {
            $csvContent = iconv($fileEncoding, self::UTF_8."//IGNORE", $csvContent);
        }

        // remove byte order mark if encoding is UTF-8
        if ($fileEncoding === self::UTF_8) {
            $csvContent = self::removeByteOrderMark($csvContent);
        }

        // set default separator if empty
        if (empty($separator)) {
            $separator = ",";
        }

        // convert separator to dhexadecimal for regex pattern on following process of preg_replace
        $hex_dec = str_pad(dechex(ord($separator)), 2, '0', STR_PAD_LEFT);
        // the following pattern will add double quotes to columns with multiple lines
        $pattern = '/((?:(?:[^\x' . $hex_dec . '"]*)(?![\x' . $hex_dec . '])\n){2,}(?![\x' . $hex_dec . '])(?:[^\x' . $hex_dec . '"]*)(?=[\x' . $hex_dec . ']))/';
        $replaceResult = preg_replace($pattern, '"$1"', $csvContent);

        if (!is_null($replaceResult)) {
            $csvContent  = $replaceResult;
        }

        // replace double quotes with temporary quotation to avoid preg_replace the wrong double quote
        $csvContent  = str_replace('""', '$dqut', $csvContent);

        // find all next lines between double quotes in the same column and replace the next line with \n for temporary
        // due to data lines are separated by using next line when explode csv content later
        $csvContent  = preg_replace('/(\n|\r)(?=(?:[^"]*)"(,|\n))/', '\n', $csvContent);

        // put back the double quotes
        $csvContent  = str_replace('$dqut', '""', $csvContent);

        $lines       = explode(PHP_EOL, $csvContent);
        $csvData     = [];
        $return      = [];
        $allData     = [];
        $startRow    = 0 + $skipDataLine;

        foreach ($lines as $line) {
            // preg_match to check if other than double quote, space and comma have values, then it means it is not row with all empty strings
            // preg_match validation does not handle false due to error will be handled by str_getcsv which will throw error
            if (($skipEmptyRow === true && preg_match('/[^" ,]/', $line) !== 0) ||
                ($skipEmptyRow === false && !empty($line))) {
                // replace the actual next line to the data
                $new_line = str_replace('\n', "\n", $line);
                if (!empty($separator)) {
                    $csvData[] = str_getcsv($new_line, $separator);
                } else {
                    $csvData[] = str_getcsv($new_line);
                }
            }
        }

        $formatFilePath = self::$config['format_folder'].'/'.$formatName.'.php';
        $headerConfig   = require $formatFilePath;
        $headerData     = $csvData[$startRow];

        if ($hasHeader) {
            foreach ($headerConfig as $config) {
                if (!in_array($config['title'], $headerData) && !array_key_exists('default', $config)) {
                    $return['errors']['header'][] = "Header column doesn't match. Expected: ".$config['title'];
                }
            }

            if ($validateDuplicateColumnValidation === true) {
                $valid_headers = array_column($headerConfig, 'title');

                //extract duplicate column
                $error_headers = array_diff_assoc($headerData, array_unique($headerData));
                if (count($error_headers) > 0) {
                    foreach ($error_headers as $single_error_header) {
                        // ignoring invalid column validation checking
                        if (in_array($single_error_header, $valid_headers)) {
                            $return['errors']['header'][] = $single_error_header;
                        }
                    }
                }
            }
        }
        
        if (empty($return['errors'])) {
            $validator     = new Validator();
            $jsonDir       = self::$config['directory_path'] . 'Validation/' . $formatName . '.json';
            $schemaContent = file_get_contents($jsonDir);
            $schema        = json_decode($schemaContent);

            foreach ($csvData as $row => $data) {
                $validationResult = [];

                if ($row < $startRow + 1 && $hasHeader) {
                    continue;
                }

                $rowData = [];

                if (!empty($data)) {
                    foreach ($headerConfig as $index => $config) {
                        $dataIndex = $index;

                        if ($hasHeader) {
                            $dataIndex = array_search($config['title'], $headerData);
                        }

                        if ($dataIndex !== false && isset($data[$dataIndex])) {
                            $rowData[$headerConfig[$index]['name']] = trim($data[$dataIndex]);
                        } else if (array_key_exists('default', $config)) {
                            $rowData[$headerConfig[$index]['name']] = $config['default'];
                        } else {
                            $rowData[$headerConfig[$index]['name']] = '';
                        }
                    }
                }
                
                $rowDataObject    = (object) $rowData;

                if (count((array)$schema) > 0) {
                    $validationResult = $validator->validate(self::$config['directory_path'], $formatName, $rowDataObject, $schema);
                }
                
                if (!empty($validationResult)) {
                    if ($hasHeader) {
                        $return['errors']['content'][$row + $startRow + 1] = $validationResult;
                    } else {
                        $return['errors']['content'][$row + $startRow] = $validationResult;
                    }
                }

                $allData[] = $rowData;
            }
        }

        if (empty($return)) {
            $return = $allData;
        }

        return $return;
    }

    /**
     * Export data as csv format
     *
     * @author Armonia Tech <developer@armonia-tech.com>
     * @param string $data
     * @param string $formatName
     * @param string $fileName
     * @param string $encoding
     * @return string csvcontent/content header
     */
    public static function exportAsCsv(
        array $data,
        string $formatName,
        string $fileName,
        string $encoding = self::UTF_8,
        bool   $returnCsvContent = false,
        bool   $includeHeader = true
    ) {
        self::checkFileFormatExists($formatName);

        if (empty($data)) {
            throw new \Exception('Data cannot be empty.');
        }

        $csvHeader      = [];
        $csvData        = [];
        $formatFilePath = self::$config['format_folder'].'/'.$formatName.'.php';
        $headerConfig   = require $formatFilePath;
        
        if ($includeHeader) {
            foreach ($headerConfig as $index => $config) {
                $csvHeader[] = $config['title'];
            }

            $csvData[] = $csvHeader;
        }

        foreach ($data as $dataDetail) {
            $newData = [];
            foreach ($headerConfig as $index => $config) {
                if (isset($dataDetail[$config['name']])) {
                    $newData[] = $dataDetail[$config['name']];
                } else {
                    $newData[] = '';
                }
            }
            $csvData[] = $newData;
        }

        $csvContent   = self::arrayToCsv($csvData);
        $fileEncoding = self::fileDetectEncoding($csvContent);
        if ($fileEncoding != $encoding) {
            $csvContent = iconv($fileEncoding, $encoding."//TRANSLIT", $csvContent);
        }

        if ($returnCsvContent) {
            return $csvContent;
        } else {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename='. $fileName);
            header('Pragma: no-cache');
            header("Expires: 0");
            echo $csvContent;
            exit;
        }
    }

    /**
     * Export data as csv format
     *
     * @author Armonia Tech <developer@armonia-tech.com>
     * @param string $formatName
     * @param string $fileName
     * @param string $encoding
     */
    public static function downloadSampleCsv(string $formatName, string $fileName, string $encoding = self::UTF_8)
    {
        self::checkFileFormatExists($formatName);

        $csvHeader      = [];
        $csvData        = [];
        $formatFilePath = self::$config['format_folder'].'/'.$formatName.'.php';
        $headerConfig   = require_once $formatFilePath;
        
        foreach ($headerConfig as $index => $config) {
            $csvHeader[] = $config['title'];
        }

        $csvData[] = $csvHeader;

        $csvContent   = self::arrayToCsv($csvData);
        $fileEncoding = self::fileDetectEncoding($csvContent);
        if ($fileEncoding != $encoding) {
            $csvContent = iconv($fileEncoding, $encoding."//TRANSLIT", $csvContent);
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename='. $fileName);
        header('Pragma: no-cache');
        header("Expires: 0");
        echo $csvContent;
        exit;
    }

    /**
     * Create JSON schema folder and config floder
     *
     * @author Armonia Tech <developer@armonia-tech.com>
     *
     */
    private static function createConfigFoler()
    {
        self::$config['validation_folder'] = self::$config['directory_path'].'Validation';
        self::$config['format_folder']     = self::$config['directory_path'].'Format';

        if (file_exists(self::$config['directory_path'])) {
            if (!file_exists(self::$config['validation_folder'])) {
                mkdir(self::$config['validation_folder']);
            }
            if (!file_exists(self::$config['format_folder'])) {
                mkdir(self::$config['format_folder']);
            }
        } else {
            throw new \Exception('Directory doesn\'t exists.');
        }
    }
    
    /**
     * Get file encoding
     *
     * @author Armonia Tech <developer@armonia-tech.com>
     * @param string $content
     *
     */
    private static function fileDetectEncoding($content)
    {
        $encoding = mb_detect_encoding($content, self::$supportedMBEncoding, true);

        return $encoding;
    }

    /**
     * Check is file format exists
     *
     * @author Armonia Tech <developer@armonia-tech.com>
     * @param string $formatName
     *
     */
    private static function checkFileFormatExists($formatName)
    {
        $formatFilePath = self::$config['format_folder'].'/'.$formatName.'.php';

        if (!file_exists($formatFilePath)) {
            throw new \Exception($formatFilePath.' file doesn\'t exists.');
        }
    }

    /**
     * Check is json schema file exists
     *
     * @author Armonia Tech <developer@armonia-tech.com>
     * @param string $formatName
     *
     */
    private static function checkJsonSchemaExists($formatName)
    {
        $formatFilePath = self::$config['validation_folder'].'/'.$formatName.'.json';

        if (!file_exists($formatFilePath)) {
            throw new \Exception($formatFilePath.' file doesn\'t exists.');
        }
    }

    private static function arrayToCsv($array, $headerRow = true, $colSep = ",", $rowSep = "\n", $qut = '"')
    {
        if (!is_array($array) or !is_array($array[0])) {
            return false;
        }
        
        $output = '';
        //Header row.
        if ($headerRow) {
            foreach ($array[0] as $key => $val) {
                $key = str_replace($qut, "$qut$qut", $key);
                $output .= "$colSep$qut$val$qut";
            }
            $output = substr($output, 1).$rowSep;
            unset($array[0]);
        }

        //Data rows.
        foreach ($array as $key => $val) {
            $tmp = '';
            foreach ($val as $cellKey => $cellVal) {
                $cellVal = str_replace($qut, "$qut$qut", $cellVal);
                $tmp .= "$colSep$qut$cellVal$qut";
            }
            $output .= substr($tmp, 1).$rowSep;
        }
        return $output;
    }

    /*
     * Remove Byte Order Mark
     *
     * @author Armonia Tech <developer@armonia-tech.com>
     * @param string $content
     * @return string $output
     *
     */
    private static function removeByteOrderMark($content)
    {
        $output = $content;

        $byte_length = strlen(self::UTF8_BYTE_ORDER_MARK);
        if (substr($output, 0, $byte_length) == self::UTF8_BYTE_ORDER_MARK) {
            $output = substr($output, $byte_length, strlen($output));
        }

        return $output;
    }

    /**
     * Custom Export data as csv format for templorary fix
     *
     * @author Armonia Tech <developer@armonia-tech.com>
     * @param string $data
     * @param string $formatName
     * @param string $fileName
     * @param string $encoding
     * @return string csvcontent/content header
     */
    public static function customExportAsCsv(
        array $data,
        string $formatName,
        string $fileName,
        string $encoding = self::UTF_8,
        bool   $returnCsvContent = false,
        bool   $includeHeader = true,
        string $qut = '"'
    ) {
        self::checkFileFormatExists($formatName);

        if (empty($data)) {
            throw new \Exception('Data cannot be empty.');
        }

        $csvHeader      = [];
        $csvData        = [];
        $formatFilePath = self::$config['format_folder'].'/'.$formatName.'.php';
        $headerConfig   = require $formatFilePath;
        
        if ($includeHeader) {
            foreach ($headerConfig as $index => $config) {
                $csvHeader[] = $config['title'];
            }

            $csvData[] = $csvHeader;
        }

        foreach ($data as $dataDetail) {
            $newData = [];
            foreach ($headerConfig as $index => $config) {
                if (isset($dataDetail[$config['name']])) {
                    $newData[] = $dataDetail[$config['name']];
                } else {
                    $newData[] = '';
                }
            }
            $csvData[] = $newData;
        }

        $csvContent   = self::arrayToCsv($csvData, true, ',', "\r\n", $qut);

        $fileEncoding = self::fileDetectEncoding($csvContent);
        if ($fileEncoding != $encoding) {
            $csvContent = iconv($fileEncoding, $encoding."//TRANSLIT", $csvContent);
        }

        if ($returnCsvContent) {
            return $csvContent;
        } else {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename='. $fileName);
            header('Pragma: no-cache');
            header("Expires: 0");
            echo $csvContent;
            exit;
        }
    }
}
