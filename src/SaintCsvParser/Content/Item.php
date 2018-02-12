<?php

namespace SaintCsvParser\Content;

/**
 * Class Item
 *
 * @package SaintCsvParser
 */
class Item implements ContentInterface
{
    use ContentTrait;

    const NAME = 'Item';

    /**
     * Map data to GE wiki format
     *
     * @param $entry
     */
    private function wiki($item)
    {
        // ---------------------------------------------------------------------------
        // Grab any extra data
        // ---------------------------------------------------------------------------

        // grab the raw item data
        $raw = $this->getRaw('Item', $item->csv_row);

        // Display Male or Female on appropriate gear where there is a gender restriction, along with Fits = Race
        $equip_race_category = [
            0 => "",
            1 => "",
            2 => "\n| Gender = Male",
            3 => "\n| Gender = Female",
            4 => "\n| Fits = Hyur\n| Gender = Male",
            5 => "\n| Fits = Hyur\n| Gender = Female",
            6 => "\n| Fits = Elezen\n| Gender = Male",
            7 => "\n| Fits = Elezen\n| Gender = Female",
            8 => "\n| Fits = Lalafell\n| Gender = Male",
            9 => "\n| Fits = Lalafell\n| Gender = Female",
            10 => "\n| Fits = Miqo'te\n| Gender = Male",
            11 => "\n| Fits = Miqo'te\n| Gender = Female",
            12 => "\n| Fits = Roegadyn\n| Gender = Male",
            13 => "\n| Fits = Roegadyn\n| Gender = Female",
            14 => "\n| Fits = Au Ra\n| Gender = Male",
            15 => "\n| Fits = Au Ra\n| Gender = Female",
        ];

        // Don't show Advanced Melds and Slots if slots available is 0. Otherwise if Slots is greater than 0 show them
        $advanced_melds_slots = false;
        if ((!$item->is_advanced_melding_permitted == "False") and ($item->materia_slot_count) > 0) {
            $string = "\n| Slots = $item->materia_slot_count\n| Advanced Melds = True";
            $advanced_melds_slots = $string;
        } elseif (!$item->materia_slot_count == 0) {
                $string = "\n| Slots = $item->materia_slot_count";
                $advanced_melds_slots = $string;
        }

        //Don't display Stack if it's only 1
        $stack_size = false;
        if ($item->stack_size > 1) {
            $string = "\n| Stack = $item->stack_size";
            $stack_size = $string;
        }

        ////Don't display Requires if it's blank
        //$class_job_category = false;
        //if ($item->class_job_category == "") {
        //} else {
        //    $string = "\n| Requires = $item->class_job_category";
        //    $class_job_category = $string;
        //}

        // Combine the first HQ Parameter with its NQ value
        $hq_param_1 = false;
        $hq_param_1 = ($item->base_param_value_0 + $item->base_param_value_special_2);

        // ---------------------------------------------------------------------------
        // Handle output
        // ---------------------------------------------------------------------------

        // wiki format
        $format = '
        {{ARR Infobox Item
        | Patch = 4.1
        | Index = {key}
        | Rarity = {rarity}
        | Name = {name}
        | Subheading = {item_ui_category}{description}{advanced_melds_slots}{stack_size}{class_job_category}{equip_race_category}
        | Required Level = {level_equip}
        | Item Level  = {level_item}
        | Untradable = {is_untradable}
        | Unique = {is_unique}
        | Convertible = {materialize_type}
        | Sells = {price_low}
        | HQ = {can_be_hq}
        | Dye Allowed = {is_dyeable}
        | Crest Allowed = {is_crest_worthy}
        | Projectable = {item_glamour}
        | Desynthesizable= {salvage}
        | Collectable = {is_collectable}
        | Repair Class = {class_job_repair}

        | Other Conditions = {grand_company}
        | SetBonus {item_special_bonus} = [[{item_series}]]<br>
        :Active Under Lv. {item_special_bonus_param}<br>
        :2 Equipped: [[{base_param_special_0}]] +{base_param_value_special_0}<br>
        :3 Equipped: [[{base_param_special_1}]] +{base_param_value_special_1}<br>
        :4 Equipped: [[{base_param_special_2}]] +{base_param_value_special_2}<br>
        :5 Equipped: [[{base_param_special_3}]] +{base_param_value_special_3}<br>
        :6 Equipped: [[{base_param_special_4}]] +{base_param_value_special_4}<br>
        :7 Equipped: [[{base_param_special_5}]] +{base_param_value_special_5}

        | Bonus {base_param_0} = +{base_param_value_0}
        | Bonus {base_param_special_2} HQ = + {hq_param_1}
        | Bonus {base_param_1} = +{base_param_value_1}
        | Bonus {base_param_special_3} HQ = +{base_param_value_special_3}
        | Bonus {base_param_2} = +{base_param_value_2}
        | Bonus {base_param_special_4} HQ = +{base_param_value_special_4}
        | Bonus {base_param_3} = +{base_param_value_3}
        | Bonus {base_param_special_5} HQ = +{base_param_value_special_5}
        | Bonus {base_param_4} = +{base_param_value_4}
        | Bonus {base_param_5} = +{base_param_value_5}

        | Physical Damage = {damage_phys}
        | Physical Damage HQ = {damage_phys} + {base_param_value_special_0}
        | Magic Damage = {damage_mag}
        | Magic Damage HQ = {damage_mag} + {base_param_value_special_1}
        | Defense = {defense_phys}
        | Defense HQ = {defense_phys} + {base_param_value_special_0}
        | Magic Defense = {defense_mag}
        | Magic Defense HQ = {defense_mag} + {base_param_value_special_1}
        | Block Strength = {block}
        | Block Strength HQ = {block} + {base_param_value_special_0}
        | Block Rate = {block_rate}
        | Block Rate HQ = {block_rate} + {base_param_value_special_1}
        | Auto-attack = {delay_ms}
        {{subst:#sub:{{subst:#expr: ({{subst:#expr: \g<delay1>/1000 round3}}/3)*\g<physicaldamage1> round3}}|0|5}}
        | Auto-attack HQ =
        {{subst:#sub:{{subst:#expr: ({{subst:#expr: \g<delay1>/1000 round3}}/3)*{{subst:#expr: \g<physicaldamage1>+\g<hqvalue1>}} round 3}}|0|5}}
        | Delay =
        {{subst:#expr: \g<delay1>/1000 round2}}

        | Miscellaneous Acquisition =
        | Miscellaneous Use =
        | Mount =

        | Gallery =
        | Notes =
        | Etymology =
        }}';


        // fields
        $data = [
            '{key}' => $item->key,
            '{rarity}' => $item->rarity,
            '{name}' => $item->name,
            '{item_ui_category}' => $item->item_ui_category,
            '{description}' => $item->description ? "\n| Description = " . $item->description : "",
            //'{materia_slot_count}' => $item->materia_slot_count,
            //'{is_advanced_melding_permitted}' => $item->is_advanced_melding_permitted,
            '{advanced_melds_slots}' => $advanced_melds_slots,
            //'{stack_size}' => $item->stack_size,
            '{stack_size}' => $stack_size,
            //'{class_job_category}' => $item->class_job_category,
            //'{class_job_category}' => $class_job_category,
            '{class_job_category}' => $item->class_job_category ? "\n| Requires = " . $item->class_job_category : "",
            //'{equip_race_category}' => $item->equip_race_category,
            //'{equip_race_category}' => $item->equip_restriction,
            //'{equip_race_category}' => $gender,
            '{equip_race_category}' => $raw->equip_restriction ? $equip_race_category[$raw->equip_restriction] : '',
            '{level_equip}' => $item->level_equip,
            '{level_item}' => $item->level_item,
            '{is_untradable}' => $item->is_untradable,
            '{is_unique}' => $item->is_unique,
            '{materialize_type}' => $item->materialize_type,
            '{price_low}' => $item->price_low,
            '{can_be_hq}' => $item->can_be_hq,
            '{is_dyeable}' => $item->is_dyeable,
            '{is_crest_worthy}' => $item->is_crest_worthy,
            '{item_glamour}' => $item->item_glamour,
            '{salvage}' => $item->salvage,
            '{is_collectable}' => $item->is_collectable,
            '{class_job_repair}' => $item->class_job_repair,
            '{grand_company}' => $item->grand_company,
            '{hq_param_1}' => $hq_param_1,
            '{item_special_bonus}' => $item->item_special_bonus,
            '{item_series}' => $item->item_series,
            '{item_special_bonus_param}' => $item->item_special_bonus_param,
            '{base_param_special_0}' => $item->base_param_special_0,
            '{base_param_value_special_0}' => $item->base_param_value_special_0,
            '{base_param_special_1}' => $item->base_param_special_1,
            '{base_param_value_special_1}' => $item->base_param_value_special_1,
            '{base_param_special_2}' => $item->base_param_special_2,
            '{base_param_value_special_2}' => $item->base_param_value_special_2,
            '{base_param_special_3}' => $item->base_param_special_3,
            '{base_param_value_special_3}' => $item->base_param_value_special_3,
            '{base_param_special_4}' => $item->base_param_special_4,
            '{base_param_value_special_4}' => $item->base_param_value_special_4,
            '{base_param_special_5}' => $item->base_param_special_5,
            '{base_param_value_special_5}' => $item->base_param_value_special_5,
            '{base_param_0}' => $item->base_param_0,
            '{base_param_value_0}' => $item->base_param_value_0,
            '{base_param_1}' => $item->base_param_1,
            '{base_param_value_1}' => $item->base_param_value_1,
            '{base_param_2}' => $item->base_param_2,
            '{base_param_value_2}' => $item->base_param_value_2,
            '{base_param_3}' => $item->base_param_3,
            '{base_param_value_3}' => $item->base_param_value_3,
            '{base_param_4}' => $item->base_param_4,
            '{base_param_value_4}' => $item->base_param_value_4,
            '{base_param_5}' => $item->base_param_5,
            '{base_param_value_5}' => $item->base_param_value_5,
            '{damage_phys}' => $item->damage_phys,
            '{damage_mag}' => $item->damage_mag,
            '{defense_phys}' => $item->defense_phys,
            '{defense_mag}' => $item->defense_mag,
            '{block}' => $item->block,
            '{block_rate}' => $item->block_rate,
            '{delay_ms}' => $item->delay_ms,
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
