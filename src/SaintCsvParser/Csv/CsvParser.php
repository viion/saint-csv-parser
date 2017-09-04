<?php

namespace SaintCsvParser\Csv;

use League\Csv\Reader;

/**
 * Class CsvParser
 *
 * @package SaintCsvParser
 */
class CsvParser
{
    use CsvParserTrait;
    
    /** @var Reader $csv */
    private $csv;

    private $filenames;
    private $filename;
    private $type;
    private $limit;
    private $columns;
    private $data;
    
    
    function __construct($filename, $type, $limit = false)
    {
        $this->filename = $filename;
        $this->type = $type;
        $this->limit = $limit;

        // get CSV and headers
        $this->filenames = $this->getFilenames();
        $this->csv = $this->getCsv($this->filenames->{$type});
        
        // parse columns and data
        $this->getColumns();
        $this->getData();
    }
    
    /**
     * Return all data
     *
     * @return mixed
     */
    public function all()
    {
        return $this->data;
    }
    
    /**
     * Get the columns from the CSV file
     */
    private function getColumns()
    {
        $headers = $this->csv->setOffset(1)->setLimit(1)->fetchAll()[0];

        // loop through headers
        foreach($headers as $i => $column) {
            // if hash, it's the key
            if ($column == '#') {
                $this->columns[$i] = 'key';
                continue;
            }

            // if "all", append on the increment count
            if ($this->type == 'all') {
                $this->columns[$i] = $i . $column;
                continue;
            }

            // ensure over 1 character
            if (strlen($column) > 1) {
                $this->columns[$i] = $this->convertColumnName($column);
            }
        }

        unset($headers);
        $this->saveJson($this->filenames->columns, $this->columns);
    }
    
    /**
     * Get data
     */
    private function getData()
    {
        // start past the header data + w/e offset we provide
        $csv = $this->csv->setOffset(3);

        if ($this->limit) {
            $csv->setLimit($this->limit);
        }

        // loop through CSV
        foreach($csv->fetchAll() as $row => $data) {
            $arr = [];
    
            // add CSV row
            $arr['csv_row'] = $row;
        
            // only grab data that we have headers for
            foreach($this->columns as $i => $column) {
                $arr[$column] = $data[$i];
            }
        
            $this->data[] = $arr;
        }
        
        unset($csv);
        unset($arr);
    }
}