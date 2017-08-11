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
     * Map data to GE wiki format
     *
     * @param $entry
     */
    private function wiki($quest)
    {
        // wiki format
        $format = '
        {{ARR Infobox Quest
        |Patch = {patch}
        |Name = {name}
        |Level = {level}
        |Genre = {genre}
        |Category = {category}
        }}';
        
        // ---------------------------------------------------------------------------
        
        // grab the raw CSV file based on the same row as the current quest
        // we only want 1 quest (this quest) so we do `->one()`
        $raw = $this->getRaw('Quest', $quest->csv_row)->one();
        
        // default value for genre (some quests don't link to some, placeholders)
        $genre = null;
    
        // if the journal genre id is above 0
        if ($raw->journal_genre > 0) {
            // grab the genre based on the raw journal_genre id
            // we only want 1 journal entry so we do `->one()`
            $genre = $this->get('JournalGenre', $raw->journal_genre)->one();
        }
    
        // ---------------------------------------------------------------------------

        // fields
        $data = [
            '{patch}' => $this->app->getPatch(),
            '{name}' => $quest->name,
            
            '{genre}' => $quest->journal_genre,
            '{category}' => $genre ? $genre->journal_category : '',
            
            '{level}' => $quest->class_level_0,
        ];
    
        // ---------------------------------------------------------------------------

        // unset expensive data to free memory
        unset($raw);
        unset($genre);
        
        // save
        return $this->format($format, $data);
    }
}
