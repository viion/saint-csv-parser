<?php

namespace SaintCsvParser\Content;

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

        // hard-coded list of Allagan Tomestones. For 'tomestone_reward' conversion
        $tomestoneList = [
            1 => 'Allagan Tomestone of Poetics',
            2 => 'Allagan Tomestone of Verity',
            3 => 'Allagan Tomestone of Creation',
            4 => 'Allagan Tomestone of Scripture',
            5 => 'Allagan Tomestone of Lore',
        ];

        // change tomestone name to wiki switch template depending on name
        $tomestoneList = [
            1 => '|ARRTomestone = ',
            2 => '|TomestoneLow = ',
            3 => '|TomestoneHigh = ',
        ];
        // ---------------------------------------------------------------------------
        // Handle output
        // ---------------------------------------------------------------------------

        // wiki format
        $format = '
        {{ARR Infobox Quest
        |Patch = {patch}
        |Name = {name}
        |Section = {section}
        |Type = {category}
        |Subtype = {genre}
        |Subtype2= {subtype2}
        |Event = <!-- {eventicon} -->
        |Required Reputation = {reputationrank}
        |Repeatable = {repeatable} - ({interval})
        |Faction = {faction}

        |SmallImage = {name} Image.png <!-- {smallimage} -->

        |Level = {level}

        |Required Class = {class}
        |Required Affiliation =
        |Quest Number =

        |Dungeon Requirement = {instancecontent1} {instancecontent2} {instancecontent3}
        |Required Quests = {prevquest1} {prevquest2} {prevquest3}
        |Unlocks Quests =

        |Objectives =

        |Description =

        |EXPReward =
        |GilReward = {gilreward}
        |SealsReward = {sealsreward}
        {tomestones}
        |Relations = {relations}
        |Misc Reward = [[{instanceunlock}]] unlocked.

        |QuestReward 1 = {guaranteeditem1}
        |QuestReward 1 Count = {guaranteeditemcount1}
        |QuestReward 2 = {guaranteeditem2}
        |QuestReward 2 Count = {guaranteeditemcount2}
        |QuestReward 3 = {guaranteeditem3}
        |QuestReward 3 Count = {guaranteeditemcount3}
        |QuestReward 4 = {guaranteeditem4}
        |QuestReward 4 Count = {guaranteeditemcount4}
        |QuestReward 5 = {guaranteeditem5}
        |QuestReward 5 Count = {guaranteeditemcount5}
        |QuestReward 6 = {guaranteeditem6}
        |QuestReward 6 Count = {guaranteeditemcount6}
        |QuestReward 7 = {catalyst1}
        |QuestReward 7 Count = {catalystcount1}
        |QuestReward 8 = {catalyst2}
        |QuestReward 8 Count = {catalystcount2}
        |QuestReward 9 = {catalyst3}
        |QuestReward 9 Count = {catalystcount3}
        |QuestReward 10 = {guaranteeditem7}
        |QuestReward 11 = {guaranteeditem8}
        |QuestReward 12 = {guaranteeditem9}
        |QuestReward 13 = {guaranteeditem10}
        |QuestReward 14 = {guaranteeditem11}

        |QuestRewardOption 1 = {optionalitem1}
        |QuestRewardOption 1 Count = {optionalitemcount1}
        |QuestRewardOption 1 HQ = {optionalitemhq1}
        |QuestRewardOption 2 = {optionalitem2}
        |QuestRewardOption 2 Count = {optionalitemcount2}
        |QuestRewardOption 2 HQ = {optionalitemhq2}
        |QuestRewardOption 3 = {optionalitem3}
        |QuestRewardOption 3 Count = {optionalitemcount3}
        |QuestRewardOption 3 HQ = {optionalitemhq3}
        |QuestRewardOption 4 = {optionalitem4}
        |QuestRewardOption 4 Count = {optionalitemcount4}
        |QuestRewardOption 4 HQ = {optionalitemhq4}
        |QuestRewardOption 5 = {optionalitem5}
        |QuestRewardOption 5 Count = {optionalitemcount5}
        |QuestRewardOption 5 HQ = {optionalitemhq5}

        |Issuing NPC = {questgiver}
        |NPC Location =

        |NPCs Involved =
        |Mobs Involved =
        |Items Involved =

        |Journal =

        |Strategy =
        |Walkthrough =
        |Dialogue =
        |Etymology =
        |Images =
        |Notes =
        }}';


        // fields
        $data = [
            '{patch}' => $this->app->getPatch(),
            '{name}' => $quest->name,
            '{genre}' => $quest->journal_genre,
            '{category}' => $genre ? $genre->journal_category : '',
            '{section}' => $category ? $category->journal_section : '',
            '{subtype2}' => $quest->place_name,
            '{eventicon}' => $quest->icon_special,
            '{smallimage}' => $quest->icon,
            '{level}' => $quest->class_level_0,
            '{reputationrank}' => $quest->beast_reputation_rank,
            '{repeatable}' => $quest->is_repeatable,
            '{interval}' => $quest->repeat_interval_type,
            '{faction}' => $quest->beast_tribe,
            '{class}' => $quest->class_job_required,
            '{instancecontent1}' => $quest->instance_content_0 ? $quest->instance_content_0 . "," : "",
            '{instancecontent2}' => $quest->instance_content_1 ? $quest->instance_content_1 . "," : "",
            '{instancecontent3}' => $quest->instance_content_2 ? $quest->instance_content_2 . "," : "",
            '{prevquest1}' => $quest->previous_quest_0 ? $quest->previous_quest_0 . "," : "",
            '{prevquest2}' => $quest->previous_quest_1 ? $quest->previous_quest_1 . "," : "",
            '{prevquest3}' => $quest->previous_quest_2 ? $quest->previous_quest_2 . "," : "",
            '{gilreward}' => $quest->gil_reward,
            '{sealsreward}' => $quest->gc_seals,
            '{tomestones}' => $quest->tomestone_count_reward ? $tomestoneList[$quest->tomestone_reward] . $quest->tomestone_count_reward : '',
            '{relations}' => $quest->reputation_reward,
            '{instanceunlock}' => $quest->instance_content_unlock,
            '{catalyst1}' => $quest->item_catalyst_0,
            '{catalystcount1}' => $quest->item_count_catalyst_0,
            '{catalyst2}' => $quest->item_catalyst_1,
            '{catalystcount2}' => $quest->item_count_catalyst_1,
            '{catalyst3}' => $quest->item_catalyst_2,
            '{catalystcount3}' => $quest->item_count_catalyst_2,
            '{guaranteeditem1}' => $quest->item_reward_0_0,
            '{guaranteeditemcount1}' => $quest->item_count_reward_0_0,
            '{guaranteeditem2}' => $quest->item_reward_0_1,
            '{guaranteeditemcount2}' => $quest->item_count_reward_0_1,
            '{guaranteeditem3}' => $quest->item_reward_0_2,
            '{guaranteeditemcount3}' => $quest->item_count_reward_0_2,
            '{guaranteeditem4}' => $quest->item_reward_0_3,
            '{guaranteeditemcount4}' => $quest->item_count_reward_0_3,
            '{guaranteeditem5}' => $quest->item_reward_0_4,
            '{guaranteeditemcount5}' => $quest->item_count_reward_0_4,
            '{guaranteeditem6}' => $quest->item_reward_0_5,
            '{guaranteeditemcount6}' => $quest->item_count_reward_0_5,
            '{guaranteeditem7}' => $quest->emote_reward,
            '{guaranteeditem8}' => $quest->action_reward,
            '{guaranteeditem9}' => $quest->general_action_reward_0,
            '{guaranteeditem10}' => $quest->general_action_reward_1,
            '{guaranteeditem11}' => $quest->other_reward,
            '{optionalitem1}' => $quest->item_reward_1_0,
            '{optionalitemcount1}' => $quest ->item_count_reward_1_0,
            '{optionalitemhq1}' => $quest ->item_reward_1_is_hq_0,
            '{optionalitem2}' => $quest->item_reward_1_1,
            '{optionalitemcount2}' => $quest ->item_count_reward_1_1,
            '{optionalitemhq2}' => $quest ->item_reward_1_is_hq_1,
            '{optionalitem3}' => $quest->item_reward_1_2,
            '{optionalitemcount3}' => $quest ->item_count_reward_1_2,
            '{optionalitemhq3}' => $quest ->item_reward_1_is_hq_2,
            '{optionalitem4}' => $quest->item_reward_1_3,
            '{optionalitemcount4}' => $quest ->item_count_reward_1_3,
            '{optionalitemhq4}' => $quest ->item_reward_1_is_hq_3,
            '{optionalitem5}' => $quest->item_reward_1_4,
            '{optionalitemcount5}' => $quest ->item_count_reward_1_4,
            '{optionalitemhq5}' => $quest ->item_reward_1_is_hq_4,
            '{questgiver}' => $quest ->e_npc_resident_start,
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
