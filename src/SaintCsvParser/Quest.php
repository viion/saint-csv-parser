<?php

namespace SaintCsvParser;

/**
 * Class Quest
 *
 * @package SaintCsvParser
 */
class Quest implements ContentInterface
{
    use ContentTrait;
    
    const NAME = 'Quest';
    
    /**
     * Map data to wiki format
     *
     * @param $entry
     */
    private function wiki($entry)
    {
        // wiki format
        $format = '
        {{ARR Infobox Quest
        |Patch = {patch}
        |Name = {name}
        |Level = {level}
        |
        }}';

        // fields
        $data = [
            '{patch}' => $this->app->getPatch(),
            '{name}' => $entry->name,
            '{level}' => $entry->class_level_0,
        ];

        // set format
        $format = str_ireplace(array_keys($data), $data, $format);
        $format = str_ireplace('    ', null, $format);

        return trim($format) . "\n\n";
    }
}
