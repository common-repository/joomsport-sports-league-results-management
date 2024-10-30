<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
class classJsportgetmatches
{
    public static function getMatches($options)
    {

        if ($options) {
            extract($options);
        }
        return classJsportgetmatches::getMatchesFromDB($options);
    }
    public static function  joomsport_ordermatchbydatetime($orderby) {
        global $wpdb;
        return str_replace($wpdb->prefix.'posts.post_date',$wpdb->prefix.'postmeta.meta_value,  mt1.meta_value,mt2.meta_value, '.$wpdb->prefix.'posts.ID, '.$wpdb->prefix.'posts.post_date ', $orderby);

   }
   public static function  joomsport_ordermatchbydatetimeDesc($orderby) {
        global $wpdb;
        return str_replace($wpdb->prefix.'posts.post_date',$wpdb->prefix.'postmeta.meta_value desc,  mt1.meta_value desc,mt2.meta_value desc, '.$wpdb->prefix.'posts.post_date', $orderby);

   }

    public static function getMatchesFromDB($options){
        global $wpdb;
        $result_array = array();

        if ($options) {
            extract($options);
        }


        $aSeasons = jsHelperPublishedSeasons::getInstance();
        $seasonsArray = array();
        foreach($aSeasons as $aSeason){
            $seasonsArray[] = $aSeason->ID;
        }


        if (isset($ordering_dest) && $ordering_dest == 'desc'){
            $orderby = 'm.date desc,m.time desc,m.postID';
        }else{
            $orderby = 'm.date,m.time,m.postID';
        }


        $queryTeam = '';
        if((isset($team_id) && intval($team_id))){
            if(isset($place) && $place == '1'){
                $queryTeam = " AND m.teamHomeID = ".intval($team_id);
            }elseif(isset($place) && $place == '2'){
                $queryTeam = " AND m.teamAwayID = ".intval($team_id);
            }else{
                $queryTeam = " AND (m.teamHomeID = ".intval($team_id)." OR m.teamAwayID = ".intval($team_id).")";
            }

        }

        $querySeason = '';
        if(isset($season_id) && is_array($season_id)){
            $querySeason = " AND m.seasonID IN (".implode(",",array_map("absint",$season_id)).")";
        }else if(isset($season_id) && $season_id > 0){
            $querySeason = " AND m.seasonID = ".intval($season_id);
        }else{
            if(count($seasonsArray)){
                $querySeason = " AND m.seasonID IN (".implode(",",array_map("absint",$seasonsArray)).")";

            }else{
                $querySeason = " AND m.seasonID = -1";

            }


        }


        $limitSql = (isset($limit) && $limit?" LIMIT ".((isset($offset)&&intval($offset))?intval($offset).",":"")." ".intval($limit):"");

        $query = "SELECT m.postID AS ID, mdID, seasonID"
            . " FROM {$wpdb->joomsport_matches} as m"
            . " JOIN {$wpdb->posts} as p ON p.ID = m.postID"
            .$querySeason
            ." WHERE m.post_status='publish'"
            .((isset($matchday_id) && $matchday_id)?" AND m.mdID = ".intval($matchday_id):"")
            .((isset($group_id) && $group_id)?" AND m.groupID = ".intval($group_id):"")
            .((isset($played))?" AND m.status = ".intval($played):"")
            .((isset($date_from) && $date_from)?" AND m.date >= '".gmdate("Y-m-d", strtotime($date_from))."'":"")
            .((isset($date_to) && $date_to)?" AND m.date <= '".gmdate("Y-m-d", strtotime($date_to))."'":"")
            .((isset($date_exclude) && $date_exclude)?" AND m.date != '".gmdate("Y-m-d", strtotime($date_exclude))."'":"")
            .$queryTeam
            .$querySeason
            ." ORDER BY {$orderby}";

        $query_cnt = "SELECT COUNT(*)"
            . " FROM {$wpdb->joomsport_matches} as m"
            . " JOIN {$wpdb->posts} as p ON p.ID = m.postID"
            .$querySeason
            ." WHERE m.post_status='publish'"
            .((isset($matchday_id) && $matchday_id)?" AND m.mdID = ".intval($matchday_id):"")
            .((isset($group_id) && $group_id)?" AND m.groupID = ".intval($group_id):"")
            .((isset($played))?" AND m.status = ".intval($played):"")
            .((isset($date_from) && $date_from)?" AND m.date >= '".gmdate("Y-m-d", strtotime($date_from))."'":"")
            .((isset($date_to) && $date_to)?" AND m.date <= '".gmdate("Y-m-d", strtotime($date_to))."'":"")
            .((isset($date_exclude) && $date_exclude)?" AND m.date != '".gmdate("Y-m-d", strtotime($date_exclude))."'":"")
            .$queryTeam
            .$querySeason;

        $list = $wpdb->get_results($query.$limitSql);
        $list_count = $wpdb->get_var($query_cnt);

        $result_array["list"] = $list;
        $result_array["count"] = $list_count;
        return $result_array;
    }
}
