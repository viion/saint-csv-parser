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
    private $cache;

    /**
     * ContentTrait constructor.
     *
     * @param $app
     */
    function __construct($app)
    {
        $this->app = $app;
        $this->cache = new \stdClass();
        $this->cache->raw = [];
        $this->cache->csv = [];
    }

    /**
     * Parse CSV data
     *
     * @return $this
     */
    public function parse()
    {
        Log::write('Parsing CSV data for: '. self::NAME);

        // starting csv
        Log::write('Reading %s into memory ... (this could take a while)', [ self::NAME ]);
        $this->csv = new CsvParser(self::NAME, 'csv', $this->app->getArgument('limit'));
        Log::write('Complete! %s CSV loaded in memory', [ self::NAME ]);

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
    public function get($content, $offset = false)
    {
        return $this->getHandler($content, $offset, 'csv');
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
        return $this->getHandler($content, $offset, 'raw');
    }

    /**
     * Handle the get and getRaw requests
     *
     * @param $content
     * @param $offset
     * @param $type
     * @return object
     */
    private function getHandler($content, $offset, $type)
    {
        if (!isset($this->cache->{$type}[$content])) {
            Log::write('Reading %s %s data into memory ... (this could take a while)', [ $content, $type ]);
            $this->cache->{$type}[$content] = (new CsvParser($content, $type))->all();
            Log::write('Complete! %s %s loaded in memory', [ $content, $type ]);
        }

        // if offset false, send all csv data
        if ($offset === false) {
            return $this->cache->{$type}[$content];
        }

        // if offset missing, show error
        if (!isset($this->cache->{$type}[$content][$offset])) {
            Log::error('Invalid offset for %s in %s of type: %s', [ $offset, $content, $type ]);
        }

        return (Object)$this->cache->{$type}[$content][$offset];
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