<?php

namespace SaintCsvParser;

use League\Csv\Reader;

/**
 * Class ContentTrait
 *
 * @package SaintCsvParser
 */
trait ContentTrait
{
    /** @var App */
    protected $app;
    
    /**
     * ContentTrait constructor.
     *
     * @param $app
     */
    function __construct($app)
    {
        $this->app = $app;
    }
    
    /**
     * @param $filename
     * @return static
     */
    public function getCsv($filename)
    {
        return Reader::createFromPath($filename);
    }
    
    /**
     * Convert a column name to something more usable
     *
     * @param $string
     * @return mixed
     */
    public function convertColumnName($string)
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
    public function convertCamelCaseToSnakeCase($string)
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
    }
}