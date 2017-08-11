<?php

namespace SaintCsvParser;


/**
 * Class ContentTrait
 *
 * @package SaintCsvParser
 */
trait ContentTrait
{
    /** @var App */
    protected $app;
    
    /** @var CsvParser */
    private $csv;
    
    /** @var array */
    private $data;
    
    /** @var array */
    private $cache;
    
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
     * Parse CSV data
     *
     * @return $this
     */
    public function parse()
    {
        Log::write('Parsing CSV data for: '. self::NAME);
        
        // parse CSV
        $this->csv = new CsvParser(self::NAME, 'csv', $this->app->getArgument('limit'));

        return $this;
    }
    
    /**
     * Save data
     */
    public function save()
    {
        Log::write('Saving CSV data');
        
        // split data into chunks
        $chunks = array_chunk(
            $this->csv->all(),
            Config::get('CSV_ENTRIES_PER_FILE')
        );
        
        // chunk up the data
        foreach($chunks as $count => $data) {
            $count = $count + 1;
            
            // loop through data
            foreach ($data as $i => $entry) {
                $entry = (Object)$entry;
    
                // log
                Log::write(sprintf('>> %s/%s (Chunk: %s/%s) %s',
                    ($i + 1), count($data), $count, count($chunks), $entry->name
                ));
    
                // map entry to a wiki format
                $entry = $this->wiki($entry);
                
                // save
                $filename = sprintf($this->csv->getFilenames()->output, $count);
                file_put_contents($filename, $entry, ($i == 0) ? false : FILE_APPEND);
            }
            
            unset($data);
            Log::write(sprintf('- Saved chunk: %s/%s', $count, count($chunks)));
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
        if (isset($this->cache[$content][$offset])) {
            return $this->cache[$content][$offset];
        }
        
        $data = new CsvParser($content, 'csv', 1, $offset);
        $this->cache['csv'][$content][$offset] = $data;
        
        return $data;
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
        if (isset($this->cache[$content][$offset])) {
            return $this->cache[$content][$offset];
        }
    
        $data = new CsvParser($content, 'raw', 1, $offset);
        $this->cache['raw'][$content][$offset] = $data;
    
        return $data;
    }
    
    /**
     * Return formatted string
     *
     * @param $data
     * @param $format
     * @return string
     */
    public function format($format, $data)
    {
        // set format
        $format = str_ireplace(array_keys($data), $data, $format);
        $format = str_ireplace('    ', null, $format);
    
        return trim($format) . "\n\n";
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