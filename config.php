<?php

return [
    // The current game patch (can be overridden via: patch=x)
    'DEFAULT_GAME_PATCH' => '4.05',
    
    // how many entries to save per file
    'CSV_ENTRIES_PER_FILE' => 200,
    
    // Quest csv file
    'DATA_QUESTS' => __DIR__ .'/data/Quest.csv',
    'DATA_QUESTS_OFFSETS' => __DIR__ .'/data/Quest.offsets.json',
    'DATA_QUESTS_OUTPUT' => __DIR__ .'/data/Quest.%s.txt',
];