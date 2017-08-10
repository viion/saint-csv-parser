<?php

namespace SaintCsvParser;

/**
 * Interface ContentInterface
 */
interface ContentInterface
{
    /**
     * Parse the content
     *
     * @return mixed
     */
    function parse();
    
    /**
     * Save the content into a specific format
     *
     * @return mixed
     */
    function save();
}