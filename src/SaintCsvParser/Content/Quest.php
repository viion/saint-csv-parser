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

        // change tomestone name to wiki switch template depending on name
        // converts number in tomestone_reward to name, then changes name
        $tomestoneList = [
            1 => '|ARRTomestone = ',
            2 => '|TomestoneLow = ',
            3 => '|TomestoneHigh = ',
        ];

        // Loop through guaranteed QuestRewards and display the item
        $questRewards = [];
        foreach(range(0,5) as $i) {
            if ($quest->{"item_count_reward_0_$i"} > 0) {
                $string = "\n\n|QuestReward ". ($i+1) ." = ". $quest->{"item_reward_0_$i"};

                if ($quest->{"item_count_reward_0_$i"} > 1) {
                    $string .= "\n|QuestReward ". ($i+1) ." Count = ". $quest->{"item_count_reward_0_$i"} . "\n";
                }

                $questRewards[] = $string;
            }
        }
        $questRewards = implode("\n", $questRewards);

        // Loop through catalyst rewards and display them as QuestReward 6 - QuestReward 8
        $catalystRewards = [];
        foreach(range(0,2) as $i) {
            if ($quest->{"item_count_catalyst_$i"} > 0) {
                $string = "\n|QuestReward ". (6+$i) ." = ". $quest->{"item_catalyst_$i"};

                if ($quest->{"item_count_catalyst_$i"} > 1) {
                    $string .= "\n|QuestReward ". (6+$i) ." Count = ". $quest->{"item_count_catalyst_$i"} ."\n";
                }

                $catalystRewards[] = $string;
            }
        }
        $catalystRewards = implode("\n", $catalystRewards);

        // Loop through optional quest rewards and display them, as QuestRewardOption #
        $questoptionRewards = [];
        foreach(range(0,4) as $i) {
            // if optional item count is greater than zero, show the reward. If count is greater than 1,
            // show the count. If reward is HQ, show HQ. Otherwise do nothing.

            if ($quest->{"item_count_reward_1_$i"} > 0) {
                $string = "\n|QuestRewardOption ". ($i+1) ." = ". $quest->{"item_reward_1_$i"};

                if ($quest->{"item_count_reward_1_$i"} > 1) {
                    $string .= "\n|QuestRewardOption ". ($i+1) ." Count = ". $quest->{"item_count_reward_1_$i"};
                }

                if ($quest->{"item_reward_1_is_hq_$i"} == "True") {
                    $string .= "\n|QuestRewardOption ". ($i+1) ." HQ = x";
                }

                $questoptionRewards[] = $string;
            }
        }
        $questoptionRewards = implode("\n", $questoptionRewards);

        // don't display QuestReward 10 if no "Emote" is rewarded
        $guaranteedreward7 = false;
        if ($quest->emote_reward) {
            $string = "\n|QuestReward 10 = $quest->emote_reward";
            $guaranteedreward7 = $string;
        }

        // don't display QuestReward 11 if no "Action" is rewarded
        $guaranteedreward8 = false;
        if ($quest->action_reward) {
            $string = "\n|QuestReward 11 = $quest->action_reward";
            $guaranteedreward8 = $string;
        }

        // don't display QuestReward 12 if no "General Action 0" is rewarded
        $guaranteedreward9 = false;
        if ($quest->general_action_reward_0) {
            $string = "\n|QuestReward 12 = $quest->general_action_reward_0";
            $guaranteedreward9 = $string;
        }

        // don't display QuestReward 13 if no "General Action 1" is rewarded
        $guaranteedreward10 = false;
        if ($quest->general_action_reward_1) {
            $string = "\n|QuestReward 13 = $quest->general_action_reward_1";
            $guaranteedreward10 = $string;
        }

        // don't display QuestReward 14 if no "Other Reward" is rewarded
        $guaranteedreward11 = false;
        if ($quest->other_reward) {
            $string = "\n|QuestReward 14 = $quest->other_reward";
            $guaranteedreward11 = $string;
        }

        // don't display the event icon if it's 000000. If it's not, then show it in html comment
        $eventicon = false;
        if ($quest->icon_special == "ui/icon/000000/000000.tex") {
        } else {
            $string = "\n|Event = <!-- $quest->icon_special -->";
            $eventicon = $string;
        }

        // don't display the "SmallIcon" if it's 000000. If it's not, then show it in html comment
        $smallimage = false;
        if ($quest->icon == "ui/icon/000000/000000.tex") {
        } else {
            $string = "\n|SmallImage = $quest->name Image.png <!-- $quest->icon -->";
            $smallimage = $string;
        }

        // don't display Beast Tribe Faction if "None", otherwise show it
        $faction = false;
        if ($quest->beast_tribe) {
            $string = "\n|Faction = ". ucwords(strtolower($quest->beast_tribe));
            $faction = $string;
        }

        // don't display 'Beast Tribe Reputation Required' if equal to "None", otherwise show it
        $reputation = false;
        if ($quest->beast_reputation_rank == "None") {
        } else {
            $string = "\n|Required Reputation = $quest->beast_reputation_rank";
            $reputation = $string;
        }

        // don't display Misc Reward Dungeon unlock unless one is defined
        $instanceunlock = false;
        if ($quest->instance_content_unlock) {
            $string = "\nMisc Reward = [[$quest->instance_content_unlock]] unlocked.";
            $instanceunlock = $string;
        }

        // don't display Grand Company Seal Reward if it's zero
        $sealsreward = false;
        if ($quest->gc_seals > 0) {
            $string = "\n|SealsReward = $quest->gc_seals";
            $sealsreward = $string;
        }

        // don't display Relations reward if it's zero
        $relations = false;
        if ($quest->reputation_reward > 0) {
            $string = "\n|Relations = $quest->reputation_reward";
            $relations = $string;
        }

        // don't display required class if equal to adventurer
        $requiredclass = false;
        if ($quest->class_job_required == "adventurer") {
        } else {
            $string = "\n|Required Class = ". ucwords(strtolower($quest->class_job_required));
            $requiredclass = $string;
        }

        // blank GilReward if equal to 0
        $gilreward = false;
        if ($quest->gil_reward > 0) {
            $string = "\n|GilReward = $quest->gil_reward";
            $gilreward = $string;
        } else {
            $string = "\n|GilReward =";
            $gilreward = $string;
        }

        // if section = Sidequests, then show Section, Subtype and Subtype2, otherwise show
        // Section, Type, and Subtype (making assumption that Type is obsolete with sidequests
        // due to Type and Subtype being identical in the dats for those)
        $types = false;
        if ($category->journal_section == "Sidequests") {
            $string = "\n|Section = $category->journal_section";
            $string .= "\n|Subtype = $quest->journal_genre";
            $string .= "\n|Subtype2 = $quest->place_name";
            $types = $string;
        } else {
            $string = "\n|Section = $category->journal_section";
            $string .= "\n|Type = $genre->journal_category";
            $string .= "\n|Subtype = $quest->journal_genre";
            $types = $string;
        }

        // Show Repeatable as 'Yes' for instantly repeatable quests, or 'Daily' for dailies, or none
        $repeatable = false;
        if (($quest->is_repeatable == "True") && ($quest->repeat_interval_type == "1")) {
            $string = "\n|Repeatable = Daily";
            $repeatable = $string;
        } elseif (($quest->is_repeatable == "True") && ($quest->repeat_interval_type == "0")) {
            $string = "\n|Repeatable = Yes";
            $repeatable = $string;
        }

        // ---------------------------------------------------------------------------
        // Handle output
        // ---------------------------------------------------------------------------

        // wiki format
        $format = '
        {{ARR Infobox Quest
        |Patch = {patch}
        |Name = {name}{types}{repeatable}{faction}{eventicon}

        {smallimage}

        |Level = {level}
        {requiredclass}
        |Required Affiliation =
        |Quest Number =

        {instancecontent1}{instancecontent2}{instancecontent3}
        {prevquest1}{prevquest2}{prevquest3}
        |Unlocks Quests =

        |Objectives =

        |Description =

        |EXPReward ={gilreward}{sealsreward}
        {tomestones}{relations}{instanceunlock}{questrewards}{catalystrewards}{guaranteeditem7}{guaranteeditem8}{guaranteeditem9}{guaranteeditem10}{guaranteeditem11}{questoptionrewards}

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
//            '{genre}' => $quest->journal_genre,
//            '{category}' => $genre ? $genre->journal_category : '',
//            '{section}' => $category ? $category->journal_section : '',
//            '{subtype2}' => $quest->place_name,
            '{types}' => $types,
            '{eventicon}' => $eventicon,
            '{smallimage}' => $smallimage,
            '{level}' => $quest->class_level_0,
            '{reputationrank}' => $reputation,
            '{repeatable}' => $repeatable,
//            '{interval}' => $quest->repeat_interval_type,
            '{faction}' => $faction,
            '{requiredclass}' => $requiredclass,
            '{instancecontent1}' => $quest->instance_content_0 ? "|Dungeon Requirement = ". $quest->instance_content_0 : "",
            '{instancecontent2}' => $quest->instance_content_1 ? ", ". $quest->instance_content_1 : "",
            '{instancecontent3}' => $quest->instance_content_2 ? ", ". $quest->instance_content_2 : "",
            '{prevquest1}' => $quest->previous_quest_0 ? "|Required Quests = ". $quest->previous_quest_0 : "",
            '{prevquest2}' => $quest->previous_quest_1 ? ", ". $quest->previous_quest_1 : "",
            '{prevquest3}' => $quest->previous_quest_2 ? ", ". $quest->previous_quest_2 : "",
            '{gilreward}' => $gilreward,
            '{sealsreward}' => $sealsreward,
            '{tomestones}' => $quest->tomestone_count_reward ? $tomestoneList[$quest->tomestone_reward] . $quest->tomestone_count_reward : '',
            '{relations}' => $relations,
            '{instanceunlock}' => $instanceunlock,
            '{questrewards}' => $questRewards,
            '{catalystrewards}' => $catalystRewards,
            '{guaranteeditem7}' => $guaranteedreward7,
            '{guaranteeditem8}' => $guaranteedreward8,
            '{guaranteeditem9}' => $guaranteedreward9,
            '{guaranteeditem10}' => $guaranteedreward10,
            '{guaranteeditem11}' => $guaranteedreward11,
            '{questoptionrewards}' => $questoptionRewards,
            '{questgiver}' => ucwords(strtolower($quest->e_npc_resident_start)),
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
