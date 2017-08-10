<?php

namespace SaintCsvParser;

/**
 * Class App
 */
class App
{
    /** @var array */
    private $arguments = [];
    
    /**
     * Icons constructor.
     */
    function __construct($arguments)
    {
        Log::write('Saint CSV Parser v0.1');
        Log::write('Memory Limit: '. ini_get('memory_limit'));
        
        $this->arguments = $arguments;
        
        // if no action die
        if (!$action = $this->getArgument('action')) {
            Log::write('No action was provided.');
            return;
        }
        
        // run action
        switch($action) {
            default:
                Log::write('The provided action was not recognised');
                break;
            
            case 'quest':
                $class = new Quest($this);
                break;
        }
    
        // no class? do nothing
        if (!isset($class)) {
            return;
        }
    
        // parse and sae
        $class->parse()->save();
    }
    
    /**
     * Get a passed argument
     *
     * @param $name
     * @param bool $default
     * @return bool
     */
    public function getArgument($name, $default = false)
    {
        foreach($this->arguments as $arg) {
            if (stripos($arg, $name .'=') !== false) {
                return explode('=', $arg, 2)[1];
            }
        }
        
        return $default;
    }
    
    /**
     * Return the current patch number
     *
     * @return bool|mixed
     */
    public function getPatch()
    {
        $patch = Config::get('DEFAULT_GAME_PATCH');
        return $this->getArgument('patch') ? $this->getArgument('patch') : $patch;
    }
}