<?php

namespace SaintCsvParser;

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
    private $limit;
    private $offset;
    private $columns;
    private $data;
    
    
    function __construct($filename, $type, $limit = null, $offset = 0)
    {
        $this->filename = $filename;
        $this->limit = $limit;
        $this->offset = $offset;
    
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
     * Just return the one row
     *
     * @return mixed
     */
    public function one()
    {
        return (Object)$this->data[0];
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
        $csv = $this->csv->setOffset($this->offset + 3);
    
        // is a limit provided
        if ($this->limit) {
            $csv->setLimit($this->limit);
        }
    
        foreach($csv->fetchAll() as $row => $quest) {
            $arr = [];
    
            // add CSV row
            $arr['csv_row'] = $row;
        
            // only grab data that we have headers for
            foreach($this->columns as $i => $column) {
                $arr[$column] = $quest[$i];
            }
        
            $this->data[] = $arr;
        }
        
        unset($csv);
        unset($arr);
    }
}