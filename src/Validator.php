<?php
namespace ArmoniaCsv;

use JsonSchema\Validator as JsonValidator;
use JsonSchema\SchemaStorage;
use JsonSchema\Constraints\Factory;

/**
 * Validator Class to validate json Schema data
 *
 * @author Seon <keangsiang.pua@armonia-tech.com>
 */
class Validator
{
    protected $jsonValidator = null;
    protected $msgNameSpace  = null;


    /**
     * Get Validator
     *
     * @author Seon <keangsiang.pua@inotechs.com>
     * @return type
     */
    private function getValidator()
    {
        if (!isset($this->jsonValidator)) {
            $definitionaFilePath = __DIR__ .'/Definition/GeneralDefinition.json';
            $jsonSchemaObject    = json_decode(file_get_contents($definitionaFilePath));
            $schemaStorage       = new SchemaStorage();
            $schemaStorage->addSchema('file://GeneralDefinition', $jsonSchemaObject);

            $jsonValidator = new JsonValidator(new Factory($schemaStorage));
            
            
            $this->jsonValidator = $jsonValidator;
        }
        return $this->jsonValidator;
    }
    
    /**
     * Check Validation data
     *
     * @author Seon <keangsiang.pua@armonia-tech.com>
     * @param string $api_service_group
     * @param string $api_service
     * @param object $input
     * @param object $schema
     * @return array
     */
    public function validate(string $csvFolder, string $csvFilename, object $input, object $schema)
    {
        $jsonValidator = $this->getValidator();

        $result = [];
        // Create json validator to validate json schema
        $jsonValidator->reset();
        $jsonValidator->validate($input, $schema);
        
        if ($jsonValidator->isValid() === false) {
            foreach ($jsonValidator->getErrors() as $row) {
                $result[] = $this->getErrorMessage($row);
            }
        }
        return $result;
    }

    /**
     */
    private function getErrorMessage(array $error)
    {
        return 'Column ' . $error['property'] . ": " . $error['message'];
    }



}