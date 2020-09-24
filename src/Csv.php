<?php
namespace ArmoniaCsv;

use ArmoniaCsv\Validator;

class Csv
{
    const UTF_8 = 'UTF-8';

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
     * @param int $skipDataLine optional
     * @return array
     */
    public static function renderCsv(string $filePath, string $formatName, bool $hasHeader = true, int $skipDataLine = 0)
    {
        if (file_exists($filePath)) {
            $fileContent  = file_get_contents($filePath);
            return self::renderCsvContent($fileContent, $formatName, $hasHeader, $skipDataLine);
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
     * @return array
     */
    public static function renderCsvContent(string $csvContent, string $formatName, bool $hasHeader = true, int $skipDataLine = 0, string $separator = "")
    {
        self::checkFileFormatExists($formatName);
        self::checkJsonSchemaExists($formatName);

        $fileEncoding = self::fileDetectEncoding($csvContent);

        if ($fileEncoding != self::UTF_8) {
            $csvContent = iconv($fileEncoding, self::UTF_8."//IGNORE", $csvContent);
        }

        $csvContent  = preg_replace('/(,|\n|^)"(?:([^\n"]*)\n([^\n"]*))*"/', '$1"$2 $3"', $csvContent);
        $lines       = explode(PHP_EOL, $csvContent);
        $csvData     = [];
        $return      = [];
        $allData     = [];
        $startRow    = 0 + $skipDataLine;

        foreach ($lines as $line) {
            if (!empty($line)) {
                if (!empty($separator)) {
                    $line = str_replace($separator, ",", $line);
                }
                $csvData[] = str_getcsv($line);
            }
        }

        $formatFilePath = self::$config['format_folder'].'/'.$formatName.'.php';
        $headerConfig   = require $formatFilePath;
        $headerData     = $csvData[$startRow];

        if ($hasHeader) {
            foreach ($headerConfig as $index => $config) {
                if (!isset($headerData[$index]) || $config['title'] !=  trim($headerData[$index])) {
                    $columnIndex = $index+1;
                    $return['errors']['header'][] = "Header column [".$columnIndex."] doesn't match. Expection: ".$config['title'];
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
                        if (isset($data[$index])) {
                            $rowData[$headerConfig[$index]['name']] = trim($data[$index]);
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
            $output = substr($output, 1)."\n";
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
}
