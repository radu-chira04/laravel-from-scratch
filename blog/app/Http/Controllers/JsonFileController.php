<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class JsonFileController extends Controller
{
    const NEW_LINE = '<br/>';
    const UK_FLAT_FILE = 'Flat.File.HomeImprovement.uk.xlsm';
    const DE_FLAT_FILE = 'Flat.File.HomeImprovement.de.xlsm';
    const FR_FLAT_FILE = 'Flat.File.HomeImprovement.fr.xlsm';

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

    /**
     * @param string $fileName
     * @return string
     */
    private function getLanguage($fileName)
    {
        if (preg_match("/uk/is", $fileName)) {
            $language = "en";
        } elseif (preg_match("/de/is", $fileName)) {
            $language = "de";
        } elseif (preg_match("/fr/is", $fileName)) {
            $language = "fr";
        }

        return $language;
    }

    /**
     * @param array $worksheetContent
     * @return array
     */
    private function getPredefinedFieldsValuesData($worksheetContent)
    {
        $predefinedValuesForFields = [];
        foreach ($worksheetContent[1] as $key => $value) {
            $predefinedValuesForFields[$value] = [];
        }
        foreach ($worksheetContent as $key => $values) {
            if ($key > 1) {# to skip the header
                foreach ($values as $k => $v) {
                    if (!empty($v) && !in_array($v, $predefinedValuesForFields[$worksheetContent[1][$k]])) {
                        $predefinedValuesForFields[$worksheetContent[1][$k]][] = $v;
                    }
                }
            }
        }

        return $predefinedValuesForFields;
    }

    /**
     * @param array $worksheetContent
     * @return array
     */
    private function getTheFieldsWithPredefinedValuesUp50($worksheetContent)
    {
        $fieldsWithPredefinedValuesMore50 = [];
        if (isset($worksheetContent[50]) && is_array($worksheetContent[50])) {
            foreach ($worksheetContent[50] as $key => $value) {
                if (isset($value) && !empty($value)) {
                    $fieldsWithPredefinedValuesMore50[] = $worksheetContent[1][$key];
                }
            }
        }
        $fieldsWithPredefinedValuesMore50 = array_unique($fieldsWithPredefinedValuesMore50);

        return $fieldsWithPredefinedValuesMore50;
    }

    /**
     * @param array $worksheetValue
     * @param string $dataProvider
     * @param string $language
     * @param array $predefinedValuesForFields
     * @return array
     */
    private function rowGeneratorFieldsWithPredefinedValues($worksheetValue, $dataProvider, $language, $predefinedValuesForFields)
    {
        $row['identifier'] = $worksheetValue[1];
        $row['provider'] = $dataProvider;
        $row['key'] = $worksheetValue[1];
        $row['label'][$language] = $worksheetValue[2];
        if (isset($worksheetValue[10]) && $language != 'en') {
            $row['label']['en'] = $worksheetValue[10];
        }
        $required = $this->getRequiredValue($worksheetValue, $language);
        $row['required'] = $required;
        $row['isMapping'] = true;
        $row['_meta']['definition'][$language] = $worksheetValue[3];
        if (isset($worksheetValue[11]) && $language != 'en') {
            $row['_meta']['definition']['en'] = $worksheetValue[11];
        }
        $row['_meta']['isRecommended'] = true;# todo
        if (isset($predefinedValuesForFields) && is_array($predefinedValuesForFields)) {
            $row['rows'] = [];
            foreach ($predefinedValuesForFields as $key => $value) {
                $row['rows'][] = [
                    'value' => $value,
                    'label' => [
                        $language => $value,
                    ],
                    'required' => $required
                ];
            }
        }

        return $row;
    }

    /**
     * @param array $worksheetValue
     * @param string $language
     * @return array
     */
    private function rowGeneratorFieldsWithoutPredefinedValues($worksheetValue, $language)
    {
        $row['key'] = $worksheetValue[1];
        $required = $this->getRequiredValue($worksheetValue, $language);
        $row['required'] = $required;
        $row['label'][$language] = $worksheetValue[2];
        if (isset($worksheetValue[10])) {
            $row['label']['en'] = $worksheetValue[10];
        }
        $row['_meta']['definition'][$language] = $worksheetValue[3];
        if (isset($worksheetValue[11])) {
            $row['_meta']['definition']['en'] = $worksheetValue[11];
        }
        $row['_meta']['isRecommended'] = false;# todo

        return $row;
    }

    /**
     * @param array $worksheetValue
     * @param string $language
     * @return bool
     */
    private function getRequiredValue($worksheetValue, $language)
    {
        if ($language != 'en' && isset($worksheetValue[14])) {
            $requiredFieldToCheck = $worksheetValue[14];
        } elseif ($language == 'en' && isset($worksheetValue[6])) {
            $requiredFieldToCheck = $worksheetValue[6];
        }
        if (!empty($requiredFieldToCheck) && preg_match("/required/is", $requiredFieldToCheck)) {
            $required = true;
        } else {
            $required = false;
        }

        return $required;
    }

    public function JsonFlatFileGenerator()
    {
        $fileName = self::FR_FLAT_FILE;
        $startTime = microtime(true);
        $language = $this->getLanguage($fileName);
        $filePath = __DIR__ . "/" . $fileName;
        $tmpPath = $this->createTempFile($filePath);

        $jsonDataArray = [];
        $jsonDataArray['filters'][] = [
            'name' => 'itemBase.hasAmazonProductType',
            'params' => [
                'name' => 'flatfile',
                'value' => 'HomeImprovement'
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
            # for getting fields with more values
            $worksheet = $spreadsheet->getSheet(5);
            $worksheetContent = $worksheet->toArray();
            $fieldsWithPredefinedValues = $worksheetContent[1];

            $predefinedValuesForFields = $this->getPredefinedFieldsValuesData($worksheetContent);
            $fieldsWithPredefinedValuesMore50 = $this->getTheFieldsWithPredefinedValuesUp50($worksheetContent);
            # for getting other details to generate json file
            $worksheet = $spreadsheet->getSheet(2);
            $worksheetContent = $worksheet->toArray();

            $duplicateValuesCheck = [];
            foreach ($worksheetContent as $key => $worksheetValue) {
                if ($key > 2 && isset($worksheetValue[1]) && !empty($worksheetValue[1])) {

                    # condition for every field without predefined or specific values (BaseDataProvider)
                    if (!in_array(trim($worksheetValue[1]), $fieldsWithPredefinedValues)) {
                        $baseRow = $this->rowGeneratorFieldsWithoutPredefinedValues($worksheetValue, $language);
                        $jsonDataArray['mappings'][0]['rows'][] = $baseRow;

                    # condition for every field with predefined values, less than 50 (KeyDataProvider)
                    } elseif (in_array(trim($worksheetValue[1]), $fieldsWithPredefinedValues) &&
                        !in_array(trim($worksheetValue[1]), $fieldsWithPredefinedValuesMore50) &&
                        !in_array(trim($worksheetValue[1]), $duplicateValuesCheck)
                    ) {
                        $keyRow = $this->rowGeneratorFieldsWithPredefinedValues(
                            $worksheetValue, 'keyDataProvider',
                            $language, $predefinedValuesForFields[$worksheetValue[1]]
                        );
                        $duplicateValuesCheck[] = trim($worksheetValue[1]);
                        $jsonDataArray['mappings'][] = $keyRow;

                    # condition for every field with predefined values, more than 50 (NestedKeyDataProvider)
                    } elseif (in_array(trim($worksheetValue[1]), $fieldsWithPredefinedValuesMore50) &&
                        !in_array(trim($worksheetValue[1]), $duplicateValuesCheck)
                    ) {
                        $nestedKeyRow = $this->rowGeneratorFieldsWithPredefinedValues(
                            $worksheetValue, 'nestedKeyDataProvider',
                            $language, $predefinedValuesForFields[$worksheetValue[1]]
                        );
                        $duplicateValuesCheck[] = trim($worksheetValue[1]);
                        $jsonDataArray['mappings'][] = $nestedKeyRow;
                    }
                }
            }

            $this->printVariable($jsonDataArray, __METHOD__, __LINE__);

            $jsonData = json_encode($jsonDataArray);
            file_put_contents('v1_HomeImprovement_' . strtoupper($language) . '.json', $jsonData);

            $runningTime = number_format((microtime(true) - $startTime) / 60, 2);
            $this->printVariable($runningTime . ' Minutes', __METHOD__, __LINE__);

        } catch (\Exception $e) {
            $this->printVariable($e->getMessage(), __METHOD__, __LINE__);
        }

        try {
            unlink($tmpPath);
        } catch (\Exception $e) {
            $this->printVariable($e->getMessage(), __METHOD__, __LINE__);
        }
    }
}
