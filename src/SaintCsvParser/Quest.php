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
        // ---------------------------------------------------------------------------
        // Grab any extra data
        // ---------------------------------------------------------------------------
        
        // grab the raw CSV file based on the same row as the current quest
        // we only want 1 quest (this quest) so we do `->one()`
        $raw = $this->getRaw('Quest', $quest->csv_row)->one();

        // grab the genre based on the raw journal_genre id
        // we only want 1 journal entry so we do `->one()`
        $genre = $this->get('JournalGenre', $raw->journal_genre)->one();

        // get the raw version of journal genre at the same position as we did above
        $genreRaw = $this->getRaw('JournalGenre', $raw->journal_genre)->one();

        // grab the category based on the raw position for journal_category
        $category = $this->get('JournalCategory', $genreRaw->journal_category)->one();

        // ---------------------------------------------------------------------------
        // Handle output
        // ---------------------------------------------------------------------------

        // wiki format
        $format = '
        {{ARR Infobox Quest
        |Patch = {patch}
        |Name = {name}
        |Level = {level}
        |Genre = {genre}
        |Category = {category}
        |Section = {section}
        }}';


        // fields
        $data = [
            '{patch}' => $this->app->getPatch(),
            '{name}' => $quest->name,

            '{genre}' => $quest->journal_genre,
            '{category}' => $genre ? $genre->journal_category : '',
            '{section}' => $category ? $category->journal_section : '',
            
            '{level}' => $quest->class_level_0,
        ];
    
        // ---------------------------------------------------------------------------
        // Clean up and finish
        // ---------------------------------------------------------------------------

        // unset expensive data to free memory
        unset($raw);
        unset($genre);
        unset($category);
        
        // save
        return $this->format($format, $data);
    }
}
