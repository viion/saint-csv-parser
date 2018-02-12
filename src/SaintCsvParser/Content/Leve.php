<?php

namespace SaintCsvParser\Content;

/**
 * Class Leve
 *
 * @package SaintCsvParser
 */
class Leve implements ContentInterface
{
    use ContentTrait;

    const NAME = 'Leve';

    /**
     * Map data to GE wiki format
     *
     * @param $entry
     */
    private function wiki($leve)
    {
        // ---------------------------------------------------------------------------
        // Grab any extra data
        // ---------------------------------------------------------------------------

        // grab the raw leve data
        $raw = $this->getRaw('Leve', $leve->csv_row);

        // convert the LeveVFX# to the appropriate Lore Name for the Levequest
        $guildlevetype = [
            "LeveVfx#0" => '',
            "LeveVfx#6" => 'Valor',
            "LeveVfx#7" => 'Tenacity (Guildleve)',
            "LeveVfx#8" => 'Wisdom',
            "LeveVfx#9" => 'Justice',
            "LeveVfx#10" => 'Dilligence',
            "LeveVfx#11" => 'Temperance',
            "LeveVfx#13" => 'Veracity',
            "LeveVfx#14" => 'Piety (Guildleve)',
            "LeveVfx#15" => 'Candor',
            "LeveVfx#18" => 'Constancy',
            "LeveVfx#19" => 'Ingenuity',
            "LeveVfx#21" => 'Promptitude',
            "LeveVfx#22" => 'Prudence',
            "LeveVfx#23" => 'Resolve',
            "LeveVfx#25" => 'Benevolence',
            "LeveVfx#26" => 'Charity',
            "LeveVfx#29" => 'Munificence',
            "LeveVfx#30" => 'Sincerity',
            "LeveVfx#33" => 'Service',
            "LeveVfx#34" => 'Equity',
            "LeveVfx#36" => 'Unity',
            "LeveVfx#40" => 'Confidence',
            "LeveVfx#41" => 'Sympathy',
            "LeveVfx#42" => 'Concord',
        ];

        // Define what the Levequest Type is, based off of the Recommended Classes
        $levetype = [
            "Carpenter" => 'Tradecraft',
            "Leatherworker" => 'Tradecraft',
            "Blacksmith" => 'Tradecraft',
            "Armorer" => 'Tradecraft',
            "Culinarian" => 'Tradecraft',
            "Alchemist" => 'Tradecraft',
            "Weaver" => 'Tradecraft',
            "Goldsmith" => 'Tradecraft',
            "Botanist" => 'Fieldcraft',
            "Miner" => 'Fieldcraft',
            "Fisher" => 'Fieldcraft',
            "The Maelstrom" => 'Battlecraft',
            "Order of the Twin Adder" => 'Battlecraft',
            "Immortal Flames" => 'Battlecraft',
            "Battlecraft" => 'Battlecraft',
            NULL => '',
        ];

        // Change the names of the Recommended Classes to better reflect Wiki code
        $classes = [
            "Carpenter" => 'Carpenter',
            "Leatherworker" => 'Leatherworker',
            "Blacksmith" => 'Blacksmith',
            "Armorer" => 'Armorer',
            "Culinarian" => 'Culinarian',
            "Alchemist" => 'Alchemist',
            "Weaver" => 'Weaver',
            "Goldsmith" => 'Goldsmith',
            "Botanist" => 'Botanist',
            "Miner" => 'Miner',
            "Fisher" => 'Fisher',
            "The Maelstrom" => 'Disciples of War, Disciples of Magic',
            "Order of the Twin Adder" => 'Disciples of War, Disciples of Magic',
            "Immortal Flames" => 'Disciples of War, Disciples of Magic',
            "Battlecraft" => 'Disciples of War, Disciples of Magic',
            NULL => '',
        ];

        // Display Grand Company, if leve_assignment_type is defined as a Grand Company
        $grandcompany = false;
        if ($leve->leve_assignment_type == "The Maelstrom") {
            $string = "\n|Grand Company      = The Maelstrom";
            $grandcompany = $string;
        } elseif ($leve->leve_assignment_type == "Order of the Twin Adder") {
            $string = "\n|Grand Company      = The Order of the Twin Adder";
            $grandcompany = $string;
        } elseif ($leve->leve_assignment_type == "Immortal Flames") {
            $string = "\n|Grand Company      = The Immortal Flames";
            $grandcompany = $string;
        };

        // Display Leve Duration if equal to Battlecraft or Grand Company, otherwise ignore
        $duration = false;
        if (($leve->leve_assignment_type == "Battlecraft") or ($leve->leve_assignment_type == "Botanist") or ($leve->leve_assignment_type == "Miner") or ($leve->leve_assignment_type == "The Maelstrom") or ($leve->leve_assignment_type == "Order of the Twin Adder") or ($leve->leve_assignment_type == "Immortal Flames")) {
            $string = "\n|Leve Duration      = 20";
            $duration = $string;
        };

        // Decide which file to open depending on DATAID
        $dataid = false;
        if (($leve->data_id > 65000) && ($leve->data_id < 99999)) {
            $string = "This Leve is a Battlecraft Leve.";
            $dataid = $string;
        } elseif (($leve->data_id > 131000) && ($leve->data_id < 139999)) {
            $string = "This Levequest is a Gathering Leve.";
            $dataid = $string;
        } elseif (($leve->data_id > 190000) && ($leve->data_id < 199999)) {
            $string = "This Levequest is a Grand Company Leve.";
            $dataid = $string;
        } elseif (($leve->data_id > 917000) && ($leve->data_id < 999999)) {
            $string = "This Levequest is a Crafting Leve.";
            $dataid = $string;
        };

        // ---------------------------------------------------------------------------
        // Handle output
        // ---------------------------------------------------------------------------

        // wiki format
        $format = '
        {{ARR Infobox Levequest
        |DataID = {dataid}
        |Patch = 4.0
        |Name = {name}
        |Level = {level}

        |Guildleve Type     = {guildtype}
        |Levequest Type     = {levetype}{duration}
        |Levequest Location = {placename}{grandcompany}

        |Recommended Classes = {classjobcategory}

        |Objectives =

        |Description = {description}

        |EXPReward = {exp}
        |GilReward = {gil}
        |SealsReward =

        |Levequest Reward List = {rewardlist}

        |LevequestReward 1       = {reward1}
        |LevequestReward 1 Count = {reward1count}

        {placename}
        |Issuing NPC =
        |Client = {client}

        |NPCs Involved  = {npcs}
        |Mobs Involved  = {mobs}
        |Items Involved = {items}
        |Wanted Target  = {wanted}

        |Strategy =
        |Walkthrough =
        |Dialogue =
        |Etymology =
        |Images =
        |Notes =
        }}';


        // fields
        $data = [
            '{dataid}' => $dataid,
            '{name}' => $leve->name,
            '{level}' => $leve->class_job_level,
            '{guildtype}' => $guildlevetype[$leve->leve_vfx],
            //'{levetype}' => $leve->class_job_category,
            '{levetype}' => $levetype[$leve->leve_assignment_type],
            '{grandcompany}' => $grandcompany,
            '{classjobcategory}' => $classes[$leve->leve_assignment_type],
            '{duration}' => $duration,
            '{placename}' =>$leve->place_name_issued,
            '{description}' => $leve->description,
            '{exp}' => $leve->exp_reward,
            '{gil}' => $leve->gil_reward,
            //'{npc}' => $leve->level_levemete,
            '{client}' => $leve->leve_client,
            //'{faction}' => $faction,
            //'{instancecontent1}' => $quest->instance_content_0 ? "|Dungeon Requirement = ". $quest->instance_content_0 : "",
            //'{instancecontent2}' => $quest->instance_content_1 ? ", ". $quest->instance_content_1 : "",
            //'{prevquest1}' => $quest->previous_quest_0 ? "|Required Quests = ". $quest->previous_quest_0 : "",
            //'{prevquest2}' => $quest->previous_quest_1 ? ", ". $quest->previous_quest_1 : "",
            //'{tomestones}' => $quest->tomestone_count_reward ? $tomestoneList[$quest->tomestone_reward] . $quest->tomestone_count_reward : '',
            //'{questgiver}' => ucwords(strtolower($quest->e_npc_resident_start)),
            //'{journal}' => implode("\n", $journal),
            //'{objectives}' => implode("\n",  $objectives),
            //'{dialogue}' => implode("\n", $dialogue),
        ];

        // ---------------------------------------------------------------------------
        // Clean up and finish
        // ---------------------------------------------------------------------------

        // unset expensive data to free memory
        unset($raw);

        // save
        return $this->format($format, $data);
    }
}
