<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
class JoomsportPageGenerator{
    public static function action(){
        global $wpdb;
        
         echo '<div class="jslinktopro jscenterpage">Available in <a href="http://joomsport.com/web-shop/joomsport-for-wordpress.html?utm_source=js-st-wp&utm_medium=backend-wp&utm_campaign=buy-js-pro">Pro Edition</a> only</div>'; 
    }
    
    public static function generate()
    {
        
    }

    public static function algoritm1($teams, $rounds)
    {
        /*<!--/jsonlyinproPHP-->*/
        if (count($teams) % 2 != 0) {
            array_push($teams, 0);
        }
        $halfarr = count($teams) / 2;
        $md_name = isset($_POST['mday_name'])?(sanitize_text_field(wp_unslash($_POST['mday_name']))):'Matchday';
        $round_day = 1;
        for ($intR = 0; $intR < $rounds; ++$intR) {
            $duo_teams = array_chunk($teams,  $halfarr);
            $duo_teams[1] = array_reverse($duo_teams[1]);
            $continue = true;
            $first_team = $duo_teams[0][0];
            $last_team = $duo_teams[1][0];
            while ($continue) {
                $intB = 0;
                $matchday_id = self::create_mday(0, $md_name.' '.$round_day, $round_day);
                if( is_wp_error( $matchday_id ) ) {
                    return $matchday_id;
                }
                foreach ($duo_teams[0] as $home) {
                    if ($intR % 2 == 0) {
                        $row['home'] = $home;
                        $row['away'] = $duo_teams[1][$intB];
                    } else {
                        $row['away'] = $home;
                        $row['home'] = $duo_teams[1][$intB];
                    }
                    if($matchday_id){
                        if ($row['home'] && $row['away']) {
                            self::addMatch($row, $matchday_id, $intB);
                        }
                    }    
                    ++$intB;
                }
                ++$round_day;

                $tmp = $duo_teams[0][$halfarr - 1];
                $to_top = $duo_teams[1][0];
                unset($duo_teams[1][0]);
                unset($duo_teams[0][$halfarr - 1]);
                array_push($duo_teams[1], $tmp);
                $duo_teams[1] = array_values($duo_teams[1]);
                $arr_start = array($duo_teams[0][0], $to_top);
                $arr_end = array_slice($duo_teams[0], 1);
                if (count($arr_end)) {
                    $arr_start = array_merge($arr_start, $arr_end);
                }
                $duo_teams[0] = $arr_start;
                if ($duo_teams[1][0] == $last_team) {
                    $continue = false;
                }
            }
        }
        /*<!--/jsonlyinproPHP-->*/
        return '';
    }
    public static function algoritm2($teams, $rounds){
        
        
        return '';
    }
    public static function addMatch($row, $matchday_id, $ordering)
    {
        
        return $post_id;
    }

    public static function algoritm_knock($format_post, $teams_knock)
    {
        
    }

    public static function create_mday($format, $name, $ordering)
    {
        
    }
}