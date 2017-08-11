<?php

namespace SaintCsvParser\App;

/**
 * Class Config
 */
class Config
{
    private static $config = [];
    
    /**
     * Config constructor.
     */
    private static function init()
    {
        self::$config = require_once ROOT .'/config.php';
    }
    
    /**
     * Get a config option
     *
     * @param $option
     * @return mixed
     */
    public static function get($option)
    {
        // init config if not already done
        if (!self::$config) {
            self::init();
        }
        
        return self::$config[$option];
    }
}