<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class jsHelperEvents
{

    public static function addLinkedEvents($eventArr)
    {
        global $wpdb;
        $joomsport_refactoring_v = (int) get_option("joomsport_refactoring_v", 0);

            if (isset($eventArr["additional_to"]) && intval($eventArr["additional_to"])
                && isset($eventArr["e_id"]) && intval($eventArr["e_id"])
                && isset($eventArr["player_id"]) && intval($eventArr["player_id"])
            ) {
                if($joomsport_refactoring_v) {
                    $exist = $wpdb->get_var("SELECT id FROM  {$wpdb->joomsport_match_events} WHERE id=" . intval($eventArr["additional_to"]));
                    if ($exist) {
                        $query = "INSERT INTO {$wpdb->joomsport_match_events_addit}(e_id,player_id,ecount,eordering,".(isset($eventArr["statoriumAPI"])?"statoriumAPI,":"")."parent_event)";
                        if(isset($eventArr["statoriumAPI"])){
                            $query .= " VALUES(%d, %d, %d, %d, %d, %d)";
                            $wpdb->query($wpdb->prepare($query, intval($eventArr["e_id"]), intval($eventArr["player_id"]),
                                isset($eventArr["ecount"]) ? intval($eventArr["ecount"]) : 1, isset($eventArr["eordering"]) ? intval($eventArr["eordering"]) : 0,
                                isset($eventArr["statoriumAPI"]) ? intval($eventArr["statoriumAPI"]) : NULL, intval($eventArr["additional_to"])));
                        }else{
                            $query .= " VALUES(%d, %d, %d, %d, %d)";
                            $wpdb->query($wpdb->prepare($query, intval($eventArr["e_id"]), intval($eventArr["player_id"]),
                                isset($eventArr["ecount"]) ? intval($eventArr["ecount"]) : 1, isset($eventArr["eordering"]) ? intval($eventArr["eordering"]) : 0, intval($eventArr["additional_to"])));
                        }

                        $insertedID = $wpdb->insert_id;
                        return $insertedID;
                    }
                }else {
                    $insertFld = array(
                        "e_id" => intval($eventArr["e_id"]),
                        "match_id" => intval($eventArr["match_id"]),
                        "player_id" => intval($eventArr["player_id"]),
                        "t_id" => intval($eventArr["t_id"]),
                        "ecount" => isset($eventArr["ecount"]) ? intval($eventArr["ecount"]) : 1,
                        "minutes" => isset($eventArr["minutes"]) ? ($eventArr["minutes"]) : '',
                        "eordering" => isset($eventArr["eordering"]) ? intval($eventArr["eordering"]) : 0,
                        "season_id" => intval($eventArr["season_id"]),
                        "minutes_input" => isset($eventArr["minutes_input"]) ? ($eventArr["minutes_input"]) : '',
                        "additional_to" => intval($eventArr["additional_to"]),
                        "stage_id" => isset($eventArr["stage_id"]) ? intval($eventArr["stage_id"]) : 0,
                        (isset($eventArr["statoriumAPI"])?intval($eventArr["statoriumAPI"]):"")
                    );
                    $insertVl = array('%d', '%d','%d','%d','%d','%s','%d', '%d','%s', '%d', '%d');
                    if(isset($eventArr["statoriumAPI"])){
                        $insertVl[] = '%d';
                    }
                    $wpdb->insert($wpdb->joomsport_match_events, $insertFld, $insertVl);
                }

            }


        return 0;

    }

    public static function getSeasonEvents($season_id){
        global $jsDatabase;

        $events = get_post_meta($season_id, "_joomsport_season_events", true);
        if($events){
            return $events;
        }

        $events = $jsDatabase->selectColumn('SELECT * FROM (SELECT DISTINCT(ev.id) as id, ev.e_name as name,ev.ordering'
            . ' FROM '.$jsDatabase->db->joomsport_events.' as ev'
            . ' JOIN '.$jsDatabase->db->joomsport_match_events.' as mev ON ev.id=mev.e_id'
            . ' WHERE ev.player_event="1"'
            . ($season_id?(is_array($season_id)?' AND mev.season_id IN ('.implode(',',$season_id).')':' AND mev.season_id='.$season_id):'')
            . ' UNION ALL '
            . ' SELECT DISTINCT(ev2.id) as id, ev2.e_name as name,ev2.ordering'
            . ' FROM '.$jsDatabase->db->joomsport_events.' as ev2'
            . ' JOIN '.$jsDatabase->db->joomsport_match_events_addit.' as mad ON ev2.id=mad.e_id'
            . ' JOIN '.$jsDatabase->db->joomsport_match_events.' as mev2 ON mev2.id=mad.parent_event'
            . ' WHERE ev2.player_event="1"'
            . ($season_id?(is_array($season_id)?' AND mev2.season_id IN ('.implode(',',$season_id).')':' AND mev2.season_id='.$season_id):'')
            .' ) as a'
            . ' ORDER BY ordering') ;

            update_post_meta($season_id, "_joomsport_season_events", $events);


        return $events;
    }

    public static function isHideTable($seasonID){
        $opts = get_post_meta($seasonID, '_joomsport_seas_opt',true);
        if(!is_array($opts)){
            $opts = array();
        }
        if($opts && isset($opts["hideTable"])){
            return $opts["hideTable"]?true:false;
        }
        $mdays = jsHelperTermMatchday::getInstance();

        if(count($mdays)){
            foreach($mdays as $mday){
                $season_id = $mday->season_id;
                $md_type = $mday->matchday_type;
                if($seasonID == $season_id && $md_type == '0'){
                    $opts["hideTable"] = 0;
                    update_post_meta($seasonID, '_joomsport_seas_opt',$opts);
                    return false;
                }
            }
        }
        $opts["hideTable"] = 1;
        update_post_meta($seasonID, '_joomsport_seas_opt',$opts);
        return false;
    }

    public static function getKnockMds($seasonID){
        $opts = get_post_meta($seasonID, '_joomsport_seas_opt',true);
        if(!is_array($opts)){
            $opts = array();
        }
        if($opts && isset($opts["knockMds"])){
            return $opts["knockMds"];
        }
        $options = array();
        $options['season_id'] = $seasonID;
        $options['mday_type'] = '1';
        $mdays = classJsportgetmdays::getMdays($options);

        $opts["knockMds"] = $mdays;
        update_post_meta($seasonID, '_joomsport_seas_opt',$opts);

        return $mdays;

    }

    public static function getPlayoffMds($seasonID){
        $opts = get_post_meta($seasonID, '_joomsport_seas_opt',true);
        if(!is_array($opts)){
            $opts = array();
        }
        if($opts && isset($opts["playoffMds"])){
            return $opts["playoffMds"];
        }
        $options = array();
        $options['season_id'] = $seasonID;
        $options['mday_type'] = '0';
        $options['is_playoff'] = '1';
        $mdays = classJsportgetmdays::getMdays($options);

        $opts["playoffMds"] = $mdays;
        update_post_meta($seasonID, '_joomsport_seas_opt',$opts);

        return $mdays;

    }

}
