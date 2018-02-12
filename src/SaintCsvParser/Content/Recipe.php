<?php

namespace SaintCsvParser\Content;

/**
 * Class Recipe
 *
 * @package SaintCsvParser
 */
class Recipe implements ContentInterface
{
    use ContentTrait;

    const NAME = 'Recipe';

    /**
     * Map data to GE wiki format
     *
     * @param $entry
     */
    private function wiki($recipe)
    {
        // ---------------------------------------------------------------------------
        // Grab any extra data
        // ---------------------------------------------------------------------------

        // grab the raw Recipe data
        $raw = $this->getRaw('Recipe', $recipe->csv_row);
        // grab the combined genre data via the raw RecipeLevelTable data
        $recipelevel = $this->getRaw('RecipeLevelTable', $raw->recipe_level_table);



        // ---------------------------------------------------------------------------
        // Handle output
        // ---------------------------------------------------------------------------

        // wiki format
//        $format = '
//
//            {{-start-}}
//            \'\'\'{name}/Recipe\'\'\'
//            {{ARR Infobox Recipe
//            |Recipe ID           = {id1}
//            |Result              = {resultname1}
//            |Result Count        = {resultquantity1}
//            |Acquired            = {unlockbook1}
//            |Specialist Only     = {specialization1}
//            |Primary Skill       = {craftclass1}
//            |Primary Skill Level = {skilllevel1}%{starlevel1}%
//            |Recipe Level        = {recipelevel1}
//            |Durability          = {{subst:#expr: trunc {{subst:#expr: {durability1}*{durabilityfactor1}/100}}}}
//            |Difficulty          = {{subst:#expr: trunc {{subst:#expr: {difficulty1}*{difficultyfactor1}/100}}}}
//            |Quality             = {{subst:#expr: trunc {{subst:#expr: {quality1}*{qualityfactor1}/100}}}}
//            |Craftsmanship Required = {craftsmanshiprequired1}
//            |Control Required    = {controlrequired1}
//            |Quick Synthesis     = {quicksynthallowed1}
//            |Quick Synthesis Craftsmanship = {quicksynthcrafts1}
//            |Quick Synthesis Control = {quicksynthcontrol1}
//            |Status Required     = {statusrequired1}
//            |Equipment Required  = {itemrequired1}
//            |Aspect              = {aspect1}
//            |Ingredient 1        = {shard1name}
//            |Ingredient 1 Amount = {shard1count}
//            |Ingredient 2        = {shard2name}
//            |Ingredient 2 Amount = {shard2count}
//            |Ingredient 3        = {ingredient1name}
//            |Ingredient 3 Amount = {ingredient1count}
//            |Ingredient 4        = {ingredient2name}
//            |Ingredient 4 Amount = {ingredient2count}
//            |Ingredient 5        = {ingredient3name}
//            |Ingredient 5 Amount = {ingredient3count}
//            |Ingredient 6        = {ingredient4name}
//            |Ingredient 6 Amount = {ingredient4count}
//            |Ingredient 7        = {ingredient5name}
//            |Ingredient 7 Amount = {ingredient5count}
//            |Ingredient 8        = {ingredient6name}
//            |Ingredient 8 Amount = {ingredient6count}
//            |Ingredient 9        = {ingredient7name}
//            |Ingredient 9 Amount = {ingredient7count}
//            |Ingredient 10       = {ingredient8name}
//            |Ingredient 10 Amount= {ingredient8count>
//            }}
//            {{-stop-}}';

        // fields
//        $data = [
//            '{name}' => $quest->name,
//            //'{category}' => $genre ? $genre->journal_category : '',
//            //'{section}' => $category ? $category->journal_section : '',
//            '{level}' => $quest->class_job_level_0,
//            '{reputationrank}' => $reputation,
//            '{repeatable}' => $repeatable,
//            //'{interval}' => $quest->repeat_interval_type,
//            '{faction}' => $faction,
//            '{requiredclass}' => $requiredclass,
//            '{instancecontent1}' => $quest->instance_content_0 ? "|Dungeon Requirement = ". $quest->instance_content_0 : "",
//            '{instancecontent2}' => $quest->instance_content_1 ? ", ". $quest->instance_content_1 : "",
//            '{instancecontent3}' => $quest->instance_content_2 ? ", ". $quest->instance_content_2 : "",
//            '{prevquest1}' => $quest->previous_quest_0 ? "|Required Quests = ". $quest->previous_quest_0 : "",
//            '{prevquest2}' => $quest->previous_quest_1 ? ", ". $quest->previous_quest_1 : "",
//            '{prevquest3}' => $quest->previous_quest_2 ? ", ". $quest->previous_quest_2 : "",
//            '{gilreward}' => $gilreward,
//            '{sealsreward}' => $sealsreward,
//            '{tomestones}' => $quest->tomestone_count_reward ? $tomestoneList[$quest->tomestone_reward] . $quest->tomestone_count_reward : '',
//            '{relations}' => $relations,
//            '{guaranteeditem11}' => $guaranteedreward11,
//            '{questoptionrewards}' => $questoptionRewards,
//            '{questgiver}' => ucwords(strtolower($quest->e_npc_resident_start)),
//            '{journal}' => implode("\n", $journal),
//            '{objectives}' => implode("\n",  $objectives),
//            '{dialogue}' => implode("\n", $dialogue),
//        ];

        // ---------------------------------------------------------------------------
        // Clean up and finish
        // ---------------------------------------------------------------------------

        // unset expensive data to free memory
        unset($raw);

        // save
        return $this->format($format, $data);
    }
}
