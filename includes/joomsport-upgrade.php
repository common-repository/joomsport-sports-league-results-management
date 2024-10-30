<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */


class joomsportUpgradeRef{
    public static function upgradeEvents(){
        global $wpdb;
        $wpdb->query("INSERT INTO {$wpdb->joomsport_match_events_addit}(e_id,player_id,ecount,eordering,statoriumAPI,parent_event)"
            ." SELECT e_id,player_id,ecount,eordering,statoriumAPI,additional_to FROM  {$wpdb->joomsport_match_events} WHERE additional_to != 0");
        $wpdb->query("DELETE FROM {$wpdb->joomsport_match_events} WHERE additional_to != 0");
    }

    public static function upgradeMatchDuration(){
        global $wpdb;
        $res = $wpdb->get_results("SELECT * FROM {$wpdb->postmeta} WHERE meta_key='_joomsport_match_general'");
        for($intA=0;$intA<count($res);$intA++){
            $options = unserialize($res[$intA]->meta_value);
            if($options && isset($options["match_duration"]) && $options["match_duration"]){
                $wpdb->query(
                    $wpdb->prepare(
                    "UPDATE {$wpdb->joomsport_matches} SET duration = %d WHERE postID= %d"
                    ,array(intval($options["match_duration"]),$res[$intA]->post_id)
                    )
                );
            }
        }
    }

    public static function upgradeTermMetas(){
        global $wpdb;

        $res = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}options WHERE `option_name` LIKE 'taxonomy_%_metas'");

        for ($intA = 0; $intA < count($res); $intA++) {
            $termID = str_replace('taxonomy_', '', $res[$intA]->option_name);
            $termID = (int)str_replace('_metas', '', $termID);
            if ($termID) {
                $options = unserialize($res[$intA]->option_value);
                foreach ($options as $key => $val) {
                    if($key == 'knockout'){$val = serialize($val);}
                    $wpdb->query(
                        $wpdb->prepare(
                            "INSERT INTO {$wpdb->prefix}termmeta(term_id,meta_key,meta_value) VALUES(%d,%s,%s)"
                            , array($termID, $key, $val)
                        )
                    );
                }
            }
        }
        $wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE `option_name` LIKE 'taxonomy_%_metas'");

    }
}
