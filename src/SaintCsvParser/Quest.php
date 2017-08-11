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

    /**
     * @var
     */
    private $data;

    /**
     * Parse CSV data
     *
     * @return $this
     */
    public function parse()
    {
        Log::write('Parsing quest information ...');

        // get csv
        Log::write('- Getting CSV data');
        $csv = $this->getCsv(Config::get('DATA_QUESTS'));

        // parse headers
        Log::write('- Getting headers');
        $headers = [];
        foreach($csv->setOffset(1)->setLimit(1)->fetchAll()[0] as $offset => $column) {
            if (strlen($column) > 1) {
                $headers[$this->convertColumnName($column)] = $offset;
            }
        }

        // save headers
        Log::write('- Save headers to: '. Config::get('DATA_QUESTS_OFFSETS'));
        file_put_contents(
            Config::get('DATA_QUESTS_OFFSETS'), json_encode($headers, JSON_PRETTY_PRINT)
        );

        // parse data
        $data = [];
        Log::write('- Parsing CSV');
        foreach($csv->setOffset(3)->fetchAll() as $row => $quest) {
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

        $questChunks = array_chunk($this->data, Config::get('CSV_ENTRIES_PER_FILE'));

        // chunk up the data
        foreach($questChunks as $count => $chunkdata) {
            $count = $count + 1;

            // loop through data
            foreach ($chunkdata as $i => $entry) {
                // build filename
                $filename = sprintf(Config::get('DATA_QUESTS_OUTPUT'), $count);

                // map entry to a wiki format
                $entry = $this->mapToWiki((Object)$entry);

                // save
                file_put_contents($filename, $entry, ($i == 0) ? false : FILE_APPEND);
            }

            unset($chunkdata);
            Log::write('- Saved quest chunk: '. $count .'/'. count($questChunks));
        }
    }

    /**
     * Map data to wiki format
     *
     * @param $entry
     */
    private function mapToWiki($entry)
    {
        // wiki format
        $format = '
        {{ARR Infobox Quest
        |Patch = {patch}
        |Name = {name}
        |Level = {level}
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
