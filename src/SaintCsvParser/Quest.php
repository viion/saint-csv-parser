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
        
        // grab the raw quest data
        $raw = $this->getRaw('Quest', $quest->csv_row);

        // grab the combined genre data via the raw quest data journal_genre id
        $genre = $this->get('JournalGenre', $raw->journal_genre);

        // grab the raw genre data (using the same raw quest journal_genre id)
        $genreRaw = $this->getRaw('JournalGenre', $raw->journal_genre);

        // grab the category based on the raw position for journal_category
        $category = $this->get('JournalCategory', $genreRaw->journal_category);

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
