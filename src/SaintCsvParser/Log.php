<?php

namespace SaintCsvParser;

/**
 * Class Log
 */
class Log
{
    /**
     * Simple write
     *
     * @param $message
     * @param array $vars
     */
    public static function write($message, $vars = [])
    {
        $time = (new \DateTime())->format("Y-m-d H:i:s.v");
        $time = substr($time, 0, 23);
        
        $message = sprintf('[%s]  %s', $time, vsprintf($message, $vars));
        
        echo $message ."\n";
    }
    
    /**
     * Simple error
     *
     * @param $message
     * @param array $vars
     */
    public static function error($message, $vars = [])
    {
        self::write('ERROR !');
        self::write($message, $vars);
        die("\n");
    }
}