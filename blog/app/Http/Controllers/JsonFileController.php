<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class JsonFileController extends Controller
{
    const NEW_LINE = '<br/>';

    /**
     * @param string $method
     * @param int $line
     */
    private function debugMessage($method, $line)
    {
        echo self::NEW_LINE . $method . "  line:" . $line . self::NEW_LINE . self::NEW_LINE;
    }

    /**
     * @param mixed $variable
     * @param string $method
     * @param int $line
     */
    private function printVariable($variable, $method, $line)
    {
        $this->debugMessage($method, $line);
        if (is_array($variable) || is_object($variable)) {
            echo '<pre>';
            print_r($variable);
            echo '</pre>';
        } else {
            echo var_export($variable);
        }
        echo self::NEW_LINE . self::NEW_LINE;
    }

    /**
     * @param string $path
     * @return string
     */
    private function createTempFile($path)
    {
        $fileName = pathinfo($path, PATHINFO_BASENAME);
        $tmpPath = '/tmp/xlsxDecoder_' . (microtime(true) * 1000) . '_' . $fileName;
        file_put_contents($tmpPath, file_get_contents($path));

        return $tmpPath;
    }

    private function getPredefinedFieldsValuesData($worksheetContent, $fieldsWithPredefinedValuesMore50)
    {
        $predefinedValuesForFields = [];
        foreach ($worksheetContent[1] as $key => $value) {
            $predefinedValuesForFields[trim($value)] = [];
        }
        foreach ($worksheetContent as $key => $values) {
            if ($key > 1) {# to skip the header
                foreach ($values as $k => $value) {
                    $value = trim($value);
                    $fieldValue = $worksheetContent[1][$k];
                    if (!empty($value) && !in_array($value, $predefinedValuesForFields[$fieldValue])) {
                        if (in_array($fieldValue, $fieldsWithPredefinedValuesMore50)) {
                            if (preg_match("/\d/", $value[0])) {
                                $key0 = 'numbers';
                            } elseif (preg_match("/[a-zA-Z]/", $value[0])) {
                                $key0 = strtoupper($value[0]);
                            } elseif (preg_match("/[^a-zA-Z\d]/", $value[0])) {
                                $key0 = 'specialCharacters';
                            }
                            if (!in_array($key0, array_keys($predefinedValuesForFields[$fieldValue][$key0]))) {
                                $predefinedValuesForFields[$fieldValue][$key0] = [];
                            }
                            if (!in_array($value, $predefinedValuesForFields[$fieldValue][$key0])) {
                                $predefinedValuesForFields[$fieldValue][$key0][] = trim($value);
                            }
                        } else {
                            $predefinedValuesForFields[$fieldValue][] = trim($value);
                        }
                    }
                }
            }
        }

        return $predefinedValuesForFields;
    }

    private function getTheFieldsWithPredefinedValuesUp50($worksheetContent)
    {
        $fieldsWithPredefinedValuesMore50 = [];
        if (isset($worksheetContent[49]) && is_array($worksheetContent[49])) {
            foreach ($worksheetContent[49] as $key => $value) {
                if (isset($value) && !empty($value)) {
                    $fieldsWithPredefinedValuesMore50[] = $worksheetContent[1][$key];
                }
            }
        }
        $fieldsWithPredefinedValuesMore50 = array_unique($fieldsWithPredefinedValuesMore50);

        return $fieldsWithPredefinedValuesMore50;
    }

    private function rowGeneratorKeyDataProvider($worksheetValue, $language, $predefinedValuesForFields)
    {
        $row['identifier'] = $worksheetValue[1];
        $row['provider'] = 'keyDataProvider';
        $row['key'] = $worksheetValue[1];
        $row['label'][$language] = $worksheetValue[2];
        if (isset($worksheetValue[10]) && $language != 'en') {
            $row['label']['en'] = $worksheetValue[10];
        }
        $required = $this->getRequiredValue($worksheetValue);
        $row['required'] = $required;
        $row['_meta']['definition'][$language] = $worksheetValue[3];
        if (isset($worksheetValue[11]) && $language != 'en') {
            $row['_meta']['definition']['en'] = $worksheetValue[11];
        }
        $row['_meta']['isRecommended'] = true; # todo
        $row['isMapping'] = true; # todo
        if (isset($predefinedValuesForFields) && is_array($predefinedValuesForFields)) {
            $row['rows'] = [];
            foreach ($predefinedValuesForFields as $key => $value) {
                if (mb_detect_encoding($value) === 'UTF-8') {
                    $value = utf8_decode($value);
                }
                $row['rows'][] = [
                    'value' => $value,
                    'label' => [
                        $language => $value,
                    ],
                    //'required' => $required
                ];
            }
        }

        return $row;
    }

    private function rowGeneratorNestedKeyDataProvider($worksheetValue, $language, $predefinedValuesForFields)
    {
        $row['identifier'] = $worksheetValue[1];
        $row['provider'] = 'nestedKeyDataProvider';
        $row['key'] = $worksheetValue[1];
        $row['label'][$language] = $worksheetValue[2];
        if (isset($worksheetValue[10]) && $language != 'en') {
            $row['label']['en'] = $worksheetValue[10];
        }
        $required = $this->getRequiredValue($worksheetValue);
        $row['required'] = $required;
        $row['_meta']['definition'][$language] = $worksheetValue[3];
        if (isset($worksheetValue[11]) && $language != 'en') {
            $row['_meta']['definition']['en'] = $worksheetValue[11];
        }
        $row['_meta']['isRecommended'] = true; # todo
        $row['isMapping'] = true; # todo
        if (isset($predefinedValuesForFields) && is_array($predefinedValuesForFields)) {
            $row['rows'] = [];
            foreach ($predefinedValuesForFields as $key => $values) {
                $children = [];
                foreach ($values as $k => $value) {
                    if (mb_detect_encoding($value) === 'UTF-8') {
                        $value = utf8_decode($value);
                    }
                    $children[] = [
                        'value' => $value,
                        'label' => [
                            $language => $value,
                        ],
                        //'required' => $required
                    ];
                }
                $row['rows'][] = [
                    'value' => $key,
                    'label' => [
                        $language => $key,
                    ],
                    'hasChildren' => true,
                    'children' => $children
                ];
            }
        }

        return $row;
    }

    private function rowGeneratorFieldsWithoutPredefinedValues($worksheetValue, $language)
    {
        $hardcodedFieldsValues = [
            ['key' => 'generic_keywords', 'value' => 'generic_keywords1 - generic_keywords5'],
            ['key' => 'other_image_url', 'value' => 'other_image_url1 - other_image_url8']
        ];
        foreach ($hardcodedFieldsValues as $value) {
            if (isset($worksheetValue[1]) && preg_match("/" . $value['key'] . "/is", $worksheetValue[1])) {
                $worksheetValue[1] = $value['value'];
            }
        }

        $startWith = $endWith = 0;
        $multipleValuesFields = false;
        preg_match('/(\w+)\d/', $worksheetValue[1], $match);
        preg_match_all('/\d+\.*\d*/', $worksheetValue[1], $matches);
        if (isset($matches[0]) && !empty($matches[0][0]) && !empty($matches[0][1])) {
            $multipleValuesFields = true;
            $startWith = trim($matches[0][0]);
            $endWith = trim($matches[0][1]);
        }

        $rows = [];
        for ($i = $startWith; $i <= $endWith; $i++) {
            if($multipleValuesFields){
                $row['key'] = trim($match[1]) . $i;
            } else {
                $row['key'] = $worksheetValue[1];
            }
            $required = $this->getRequiredValue($worksheetValue);
            $row['required'] = $required;
            $row['label'][$language] = $worksheetValue[2];
            if (isset($worksheetValue[10])) {
                $row['label']['en'] = $worksheetValue[10];
            }
            $row['_meta']['definition'][$language] = $worksheetValue[3];
            if (isset($worksheetValue[11])) {
                $row['_meta']['definition']['en'] = $worksheetValue[11];
            }
            $row['_meta']['isRecommended'] = false; # todo

            $rows[] = $row;
        }

        return $rows;
    }

    private function getRequiredValue($worksheetValue)
    {
        $key = count($worksheetValue) - 1;
        $requiredFieldToCheck = $worksheetValue[$key];
        if (!empty($requiredFieldToCheck) &&
            (
                preg_match("/erforderlich/i", $requiredFieldToCheck) ||
                preg_match("/required/i", $requiredFieldToCheck)
            )
        ) {
            return true;
        } else {
            return false;
        }
    }

    public static function convert_from_latin1_to_utf8_recursively($dat)
    {
        if (is_string($dat)) {
            return utf8_encode($dat);
        } elseif (is_array($dat)) {
            $ret = [];
            foreach ($dat as $i => $d) {
                $ret[$i] = self::convert_from_latin1_to_utf8_recursively($d);
            }

            return $ret;
        } elseif (is_object($dat)) {
            foreach ($dat as $i => $d) {
                $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);
            }

            return $dat;
        } else {
            return $dat;
        }
    }

    public function JsonFlatFileGenerator($fileName)
    {
        $this->printVariable($fileName, __METHOD__, __LINE__);
        $nameDetails = explode(".", $fileName);
        if (preg_match("/HomeImprovement/i", $nameDetails[2])) {
            $fieldValueSheet = 5;
            $fieldDetailSheet = 2;
        } else {
            $fieldValueSheet = 12;
            $fieldDetailSheet = 10;
        }

        $language = $nameDetails[3];
        $filePath = __DIR__ . "/" . $fileName;
        $tmpPath = $this->createTempFile($filePath);

        $jsonDataArray = [];
        $jsonDataArray['filters'][] = [
            'name' => 'itemBase.hasAmazonProductType',
            'params' => [
                'name' => 'flatfile',
                'value' => $nameDetails[2]
            ],
        ];
        $jsonDataArray['settings'] = [];
        $jsonDataArray['_meta'] = [];
        $jsonDataArray['mappings'][] = [
            'identifier' => 'general',
            'provider' => 'baseDataProvider',
            'rows' => []
        ];

        try {
            $reader = new Xlsx();
            $spreadsheet = $reader->load($tmpPath);
            /** Header that will contain every value for every field */
            $worksheet = $spreadsheet->getSheet($fieldValueSheet);
            $worksheetContent = $worksheet->toArray();
            $fieldsWithPredefinedValues = $worksheetContent[1];

//            $this->printVariable($worksheetContent, __METHOD__, __LINE__);
//            die();

            $fieldsWithPredefinedValuesMore50 = $this->getTheFieldsWithPredefinedValuesUp50($worksheetContent);
            $predefinedValuesForFields = $this->getPredefinedFieldsValuesData($worksheetContent, $fieldsWithPredefinedValuesMore50);

//            $this->printVariable($predefinedValuesForFields, __METHOD__, __LINE__);
//            die();

            /**
             *  Header that contains all details for fields
             *  Array
             *  (
             *      [0] =>
             *      [1] => feed_product_type
             *      [2] => Product Type
             *      [3] => Select from the list of valid values
             *      [4] => LIGHT_MOTOR_VEHICLE
             *      [5] => Erforderlich
             *   )
             */
            $worksheet = $spreadsheet->getSheet($fieldDetailSheet);
            $worksheetContent = $worksheet->toArray();

//            $this->printVariable($worksheetContent, __METHOD__, __LINE__);
//            die();

            $duplicateValuesCheck = [];
            foreach ($worksheetContent as $key => $worksheetValue) {
                if ($key > 2 && isset($worksheetValue[1]) && !empty($worksheetValue[1])) {# skip the header
                    if (isset($worksheetValue[2]) && mb_detect_encoding($worksheetValue[2]) === 'UTF-8') {
                        $worksheetValue[2] = utf8_decode($worksheetValue[2]);
                    }
                    if (isset($worksheetValue[3]) && mb_detect_encoding($worksheetValue[3]) === 'UTF-8') {
                        $worksheetValue[3] = utf8_decode($worksheetValue[3]);
                    }
                    # condition for every field without predefined or specific values (BaseDataProvider)
                    if (!in_array(trim($worksheetValue[1]), $fieldsWithPredefinedValues)) {
                        $baseRow = $this->rowGeneratorFieldsWithoutPredefinedValues($worksheetValue, $language);
                        $cnt = count($baseRow);
                        for ($j = 0; $j < $cnt; $j++) {
                            $jsonDataArray['mappings'][0]['rows'][] = $baseRow[$j];
                        }

                    # condition for every field with predefined values, less than 50 (KeyDataProvider)
                    } elseif (in_array(trim($worksheetValue[1]), $fieldsWithPredefinedValues) &&
                        !in_array(trim($worksheetValue[1]), $fieldsWithPredefinedValuesMore50) &&
                        !in_array(trim($worksheetValue[1]), $duplicateValuesCheck)
                    ) {
                        $keyRow = $this->rowGeneratorKeyDataProvider(
                            $worksheetValue, $language, $predefinedValuesForFields[$worksheetValue[1]]
                        );
                        $duplicateValuesCheck[] = trim($worksheetValue[1]);
                        $jsonDataArray['mappings'][] = $keyRow;

                    # condition for every field with predefined values, more than 50 (NestedKeyDataProvider)
                    } elseif (in_array(trim($worksheetValue[1]), $fieldsWithPredefinedValuesMore50) &&
                        !in_array(trim($worksheetValue[1]), $duplicateValuesCheck)
                    ) {
                        $nestedKeyRow = $this->rowGeneratorNestedKeyDataProvider(
                            $worksheetValue, $language, $predefinedValuesForFields[$worksheetValue[1]]
                        );
                        $duplicateValuesCheck[] = trim($worksheetValue[1]);
                        $jsonDataArray['mappings'][] = $nestedKeyRow;
                    }
                }
            }

            //$this->printVariable($jsonDataArray, __METHOD__, __LINE__);
            $jsonDataArray = self::convert_from_latin1_to_utf8_recursively($jsonDataArray);
            $jsonData = json_encode($jsonDataArray);
            $jsonDataArray = [];
            if (is_bool($jsonData)) {
                $this->printVariable(json_last_error_msg(), __METHOD__, __LINE__);
            }

            file_put_contents('v1_' . $nameDetails[2] . '_' . strtoupper($language) . '.json', $jsonData);
            $jsonData = '';
        } catch (\Exception $e) {
            $this->printVariable($e->getMessage(), __METHOD__, __LINE__);
        }

        try {
            unlink($tmpPath);
        } catch (\Exception $e) {
            $this->printVariable($e->getMessage(), __METHOD__, __LINE__);
        }
    }

    private function downloadXlsmFlatFile()
    {
        $content = file_get_contents(__DIR__ . "/amazon_ff_urls.txt");
        $contentArray = explode("\n", $content);
        $startWith = 0;
        $endWith = 5;
        foreach ($contentArray as $key => $url) {
            if ($key >= $startWith && $key <= $endWith) {
                $this->printVariable($url, __METHOD__, __LINE__);
                $downloadedFileContents = file_get_contents($url);
                if ($downloadedFileContents === false) {
                    throw new Exception('Failed to download file at: ' . $url);
                }

                preg_match("/flat\.file.*/is", $url, $match);
                $fileName = $match[0];
                $save = file_put_contents($fileName, $downloadedFileContents);
                if ($save === false) {
                    throw new Exception('Failed to save file to: ', $fileName);
                }
            }
        }
    }

    private function listXlsmFlatFiles()
    {
        $files = scandir(__DIR__);
        $flatFilesArray = [];
        foreach ($files as $file){
            if(preg_match("/\.xlsm/i", $file)){
                $flatFilesArray[] = $file;
            }
        }

        return $flatFilesArray;
    }

    public function runJsonGenerator()
    {
        $this->downloadXlsmFlatFile();
        $flatFilesArray = $this->listXlsmFlatFiles();
        $this->printVariable($flatFilesArray, __METHOD__, __LINE__);
        foreach ($flatFilesArray as $flatFile) {
            try {
                $startTime = microtime(true);
                $this->JsonFlatFileGenerator($flatFile);
                $runningTime = number_format((microtime(true) - $startTime) / 60, 2);
                $this->printVariable($runningTime . ' Minutes', __METHOD__, __LINE__);
                $this->printVariable("delete file: " . $flatFile, __METHOD__, __LINE__);
                unlink($flatFile);
            } catch (\Exception $e) {
                $this->printVariable($e->getMessage(), __METHOD__, __LINE__);
            }
        }
    }


}
