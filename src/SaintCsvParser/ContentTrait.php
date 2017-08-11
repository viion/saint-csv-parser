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
    
    /** @var array */
    private $data;
    
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
     * Parse CSV data
     *
     * @return $this
     */
    public function parse()
    {
        Log::write('Parsing CSV data for: '. self::NAME);
        
        // get csv
        Log::write('- Getting CSV data');
        
        // get filenames
        $filename = $this->getFilenames();
        
        // get csv data
        /** @var Reader $csv */
        $csv = $this->getCsv($filename->csv);
        
        // parse headers
        Log::write('- Getting headers');
        $headers = [];
        foreach($csv->setOffset(1)->setLimit(1)->fetchAll()[0] as $offset => $column) {
            // if hash, it's the key
            if ($column == '#') {
                $headers['Key'] = $offset;
                continue;
            }
            
            if (strlen($column) > 1) {
                $headers[$this->convertColumnName($column)] = $offset;
            }
        }
        
        // save headers
        Log::write('- Saving offsets');
        file_put_contents($filename->offsets, json_encode($headers, JSON_PRETTY_PRINT));
        
        // start past the header
        $csv = $csv->setOffset(3);
        
        // is a limit passed?
        if ($limit = $this->app->getArgument('limit')) {
            $csv->setLimit($limit);
        }
        
        // parse data
        $data = [];
        Log::write('- Parsing CSV');
        foreach($csv->fetchAll() as $row => $quest) {
            $arr = [];
            
            // only grab data that we have headers for
            foreach($headers as $column => $offset) {
                $arr[$column] = $quest[$offset];
            }
            
            $data[] = $arr;
        }
        
        $this->data = $data;
        
        unset($csv);
        unset($headers);
        
        return $this;
    }
    
    /**
     * Save data
     */
    public function save()
    {
        Log::write('- Saving CSV data');
    
        // get filenames
        $filename = $this->getFilenames();
        
        // split data into chunks
        $questChunks = array_chunk($this->data, Config::get('CSV_ENTRIES_PER_FILE'));
        
        // chunk up the data
        foreach($questChunks as $count => $chunkdata) {
            $count = $count + 1;
            
            // loop through data
            foreach ($chunkdata as $i => $entry) {
                // map entry to a wiki format
                $entry = $this->wiki((Object)$entry);
                
                // save
                file_put_contents($filename->output, $entry, ($i == 0) ? false : FILE_APPEND);
            }
            
            unset($chunkdata);
            Log::write('- Saved quest chunk: '. $count .'/'. count($questChunks));
        }
    }
    
    /**
     * Get data from another CSV
     *
     * @param $content
     * @param $offset
     * @return mixed
     */
    public function get($content, $offset)
    {
        /** @var Reader $csv */
        $csv = $this->getCsv(Config::get('DATA_COMBINED') .'/'. $content .'.csv');
        return $csv->setOffset($offset)->setLimit(1)->fetchAll()[0];
    }
    
    /**
     * Get data from another RAW CSV
     *
     * @param $content
     * @param $offset
     * @return mixed
     */
    public function getRaw($content, $offset)
    {
        /** @var Reader $csv */
        $csv = $this->getCsv(Config::get('DATA_RAW') .'/'. $content .'.csv');
        return $csv->setOffset($offset)->setLimit(1)->fetchAll()[0];
    }
    
    /**
     * Get filenames (also checks if the combined one exists)
     *
     * @return \stdClass
     */
    public function getFilenames()
    {
        $file = new \stdClass();
        $file->csv = Config::get('DATA_COMBINED') .'/'. self::NAME . '.csv';
        $file->raw = Config::get('DATA_RAW') .'/'. self::NAME . '.csv';
        $file->output = Config::get('DATA_OUTPUT') .'/'. self::NAME . '.%s.txt';
        $file->offsets = Config::get('DATA_OFFSETS') .'/'. self::NAME . '.offsets.txt';
    
        if (!file_exists($file->csv)) {
            Log::error('Error: The file could not be found: %s', [ $file->csv ]);
        }
        
        return $file;
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