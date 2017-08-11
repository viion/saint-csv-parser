<?php

namespace SaintCsvParser;

use League\Csv\Reader;

/**
 * Class CsvParserTrait
 *
 * @package SaintCsvParser
 */
trait CsvParserTrait
{
    /**
     * Get filenames (also checks if the combined one exists)
     *
     * @return \stdClass
     */
    public function getFilenames()
    {
        $file = new \stdClass();
        $file->csv = Config::get('DATA_COMBINED') .'/'. $this->filename . '.csv';
        $file->raw = Config::get('DATA_RAW') .'/'. $this->filename . '.csv';
        $file->output = Config::get('DATA_OUTPUT') .'/'. $this->filename . '.%s.txt';
        $file->columns = Config::get('DATA_COLUMNS') .'/'. $this->filename . '.columns.txt';
        
        if (!file_exists($file->csv)) {
            Log::error('Error: The file could not be found: %s', [ $file->csv ]);
        }
        
        return $file;
    }
    
    /**
     * @param $filename
     * @return Reader
     */
    public function getCsv($filename)
    {
        return Reader::createFromPath($filename);
    }
    
    /**
     * @param $filename
     * @param $data
     */
    protected function saveJson($filename, $data)
    {
        file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
    }
    
    /**
     * Convert a column name to something more usable
     *
     * @param $string
     * @return mixed
     */
    protected function convertColumnName($string)
    {
        $replacements = [
            '[' => '_',
            ']' => '',
            '{' => '_',
            '}' => '',
            '<' => '_',
            '>' => '',
            '(' => '_',
            ')' => '',
            'PvP' => 'Pvp',
        ];
        
        $string = str_ireplace(array_keys($replacements), $replacements, $string);
        $string = $this->convertCamelCaseToSnakeCase($string);
        
        return $string;
    }
    
    /**
     * Convert a string from camelCase to snake_case
     *
     * @param $string
     * @return string
     */
    protected function convertCamelCaseToSnakeCase($string)
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
    }
}