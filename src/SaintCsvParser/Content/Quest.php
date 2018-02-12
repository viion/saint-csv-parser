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

        $objectives = [];
        $dialogue = [];
        $journal =[];
        if ($quest->id) {
            // explode folder into 2 chunks based on the _, we want the right chunk
            // then only take the first 3 letters, that = the folder name!
            $folder = substr(explode('_', $quest->id)[1], 0, 3);

            // build the file path
            $file = 'quest/'. $folder .'/'. $quest->id;

            // loop through quest text data
            $filedata = $this->getAll($file);

            // ensure we have data
            if ($filedata) {
                foreach($filedata as $i => $entry) {
                    // grab files to a friendlier variable name
                    $id = $entry['key'];
                    $command = $entry[1];
                    $text = $entry[2];

                    // get the text group from the command
                    $textgroup = $this->getTextGroup($i, $command);

                    // ---------------------------------------------------------------
                    // Handle quest text data
                    // ---------------------------------------------------------------

                    /**
                     * Textgroup provides details on the command type, eg:
                     * type: (npc, question, todo, scene, etc
                     * npc: if "type == dialogue", then npc be the npc name!
                     * order: the entry order, might not need
                     *
                     * Fill up arrays and then you can use something like:
                     *
                     *          implode("\n", $objectives)
                     *
                     * to throw them in your wiki format at the bottom
                     */

                    // add objective
                    if ($textgroup->type == 'todo' && strlen($text) > 1) {
                        $objectives[] = '*' .$text;
                    }

                    // add dialogue
                    if ($textgroup->type == 'dialogue' && strlen($text) > 1) {
                        // example: NPC says: Blah blah blah
                        $dialogue[] = '{{Loremquote|' .$textgroup->npc .'|link=y|'. $text .'}}';
                    }

                    // add journal
                    if ($textgroup->type == 'journal' && strlen($text) > 1) {
                        $journal[] = '*' .$text;
                    }

                    // ---------------------------------------------------------------
                }
            }
        }

        // grab the combined genre data via the raw quest data journal_genre id
        $genre = $this->get('JournalGenre', $raw->journal_genre);

        // grab the raw genre data (using the same raw quest journal_genre id)
        $genreRaw = $this->getRaw('JournalGenre', $raw->journal_genre);

        // grab the category based on the raw position for journal_category
        $category = $this->get('JournalCategory', $genreRaw->journal_category);

        // change tomestone name to wiki switch template depending on name
        // converts number in tomestone_reward to name, then changes name
        $tomestoneList = [
            'Allagan Tomestone of Poetics' => '|ARRTomestone = ',
            'Allagan Tomestone of Creation' => '|TomestoneLow = ',
            'Allagan Tomestone of Mendacity' => '|TomestoneHigh = ',
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

                if ($quest->{"is_hq_reward_1_$i"} == "True") {
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
        // Slight cheat here, forcing Type = Sidequest for Sidequests. We shouldn't do that!
        $types = false;
        if ($category->journal_section == "Sidequests") {
            $string = "\n|Type = Sidequests";
        // Sidequests using Subtype show correct in-game Journal
        // Otherwise they would show things like 'Dravanian Hinterlands Sidequest'
        // instead of 'Dravanian Sidequests'. Saving in code just in case.
        // Line below not needed anymore.
        // $string .= "\n|Subtype = $quest->journal_genre";
            $string .= "\n|Subtype = $genre->journal_category";
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

        // Grab the correct EventIconType which should then show the correct Icon for a quest
        // (the 'Blue Icon' that appears above an NPC's head, instead of the minimap icon)
        $EventIconType = $this->getRaw('EventIconType', $raw->event_icon_type);
        $npcIconAvailable = $EventIconType->npc_icon_available;
        $npcIconAvailable += $npcIconAvailable ? ( $quest->is_repeatable == "False" ? 1 : 2 ) : 0;

        // ---------------------------------------------------------------------------
        // Handle output
        // ---------------------------------------------------------------------------

        // wiki format
        $format = '
        http://ffxiv.gamerescape.com/wiki/{name}?action=edit
        {{ARR Infobox Quest
        |Patch = 4.1
        |Name = {name}{types}{repeatable}{faction}{eventicon}
        |Icontype = {questicontype}.png

        {smallimage}

        |Level = {level}
        {requiredclass}
        |Required Affiliation =
        |Quest Number =

        {instancecontent1}{instancecontent2}{instancecontent3}
        {prevquest1}{prevquest2}{prevquest3}
        |Unlocks Quests =

        |Objectives =
        {objectives}

        |EXPReward ={gilreward}{sealsreward}
        {tomestones}{relations}{instanceunlock}{questrewards}{catalystrewards}{guaranteeditem7}{guaranteeditem8}{guaranteeditem9}{guaranteeditem10}{guaranteeditem11}{questoptionrewards}

        |Issuing NPC = {questgiver}
        |NPC Location =

        |NPCs Involved =
        |Mobs Involved =
        |Items Involved =

        |Description =
        |Journal =
        {journal}

        |Strategy =
        |Walkthrough =
        |Dialogue =
        |Etymology =
        |Images =
        |Notes =
        }}
        http://ffxiv.gamerescape.com/wiki/Loremonger:{name}?action=edit
        <noinclude>{{Lorempageturn|prev=|next=}}{{Loremquestheader||Mined=X|Summary=}}</noinclude>
        {{LoremLoc|Location=}}
        {dialogue}';


        // fields
        $data = [
            //'{patch}' => $this->app->getPatch(),
            '{name}' => $quest->name,
            '{questicontype}' => $npcIconAvailable,
            //'{genre}' => $quest->journal_genre,
            //'{category}' => $genre ? $genre->journal_category : '',
            //'{section}' => $category ? $category->journal_section : '',
            //'{subtype2}' => $quest->place_name,
            '{types}' => $types,
            '{eventicon}' => $eventicon,
            '{smallimage}' => $smallimage,
            '{level}' => $quest->class_job_level_0,
            '{reputationrank}' => $reputation,
            '{repeatable}' => $repeatable,
            //'{interval}' => $quest->repeat_interval_type,
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
            '{journal}' => implode("\n", $journal),
            '{objectives}' => implode("\n",  $objectives),
            '{dialogue}' => implode("\n", $dialogue),
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

    /**
     * This is from XIVDB v3 and will be maintained there.
     * Supports:
     * - BattleTalk
     * - Journal
     * - Scene
     * - Todo (Objectives)
     * - Pop
     * - Access
     * - Instance Talk
     * - Questions + Answers
     * - NPC Dialogue
     * - System
     *
     * @param $i
     * @param $command
     * @return \stdClass
     */
    private function getTextGroup($i, $command)
    {
        $data = new \stdClass();
        $data->type = null;
        $data->npc = null;
        $data->order = null;

        // split command
        $command = explode('_', $command);

        // special one (npc battle talk)
        if ($command[4] == 'BATTLETALK') {
            $data->type = 'battle_talk';
            $data->npc = ucwords(strtolower($command[3]));
            $data->order = isset($command[5]) ? intval($command[5]) : $i;
            return $data;
        }

        // build data structure from command
        switch($command[3]) {
            case 'SEQ':
                $data->type = 'journal';
                $data->order = intval($command[4]);
                break;

            case 'SCENE':
                $data->type = 'scene';
                $data->order = intval($command[7]);
                break;

            case 'TODO':
                $data->type = 'todo';
                $data->order = intval($command[4]);
                break;

            case 'POP':
                $data->type = 'pop';
                $data->order = $i;
                break;

            case 'ACCESS':
                $data->type = 'access';
                $data->order = $i;
                break;

            case 'INSTANCE':
                $data->type = 'instance_talk';
                $data->order = $i;
                break;

            case 'SYSTEM':
                $data->type = 'system';
                $data->order = $i;
                break;

            case 'QIB':
                $npc = filter_var($command[4], FILTER_SANITIZE_STRING);

                // sometimes QIB can be a todo
                if ($npc == 'TODO') {
                    $data->type = 'todo';
                    $data->order = $i;
                    break;
                }

                $data->type = 'battle_talk';
                $data->npc = ucwords(strtolower($npc));
                $data->order = $i;
                break;

            // 20 possible questions ...
            case 'Q1':  case 'Q2':  case 'Q3':  case 'Q4':  case 'Q5':
            case 'Q6':  case 'Q7':  case 'Q8':  case 'Q9':  case 'Q10':
            case 'Q11': case 'Q12': case 'Q13': case 'Q14': case 'Q15':
            case 'Q16': case 'Q17': case 'Q18': case 'Q19': case 'Q20':
                $data->type = 'qa_question';
                $data->order = intval($command[4]);
                break;

            // with 20 possible answers ...
            case 'A1':  case 'A2':  case 'A3':  case 'A4':  case 'A5':
            case 'A6':  case 'A7':  case 'A8':  case 'A9':  case 'A10':
            case 'A11': case 'A12': case 'A13': case 'A14': case 'A15':
            case 'A16': case 'A17': case 'A18': case 'A19': case 'A20':
                $data->type = 'qa_answer';
                $data->order = intval($command[4]);
                break;

            default:
                $npc = ucwords(strtolower($command[3]));
                $order = isset($command[5]) ? intval($command[5]) : intval($command[4]);

                // if npc is numeric, budge over 1
                if (is_numeric($npc)) {
                    $npc = ucwords(strtolower($command[4]));
                    $order = intval($command[3]);
                }


                $data->type = 'dialogue';
                $data->npc = $npc;
                $data->order = $order;
        }

        return $data;
    }
}
