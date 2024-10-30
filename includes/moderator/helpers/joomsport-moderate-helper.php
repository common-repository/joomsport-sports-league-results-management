<?php
require_once JOOMSPORT_PATH_INCLUDES . 'moderator' . DIRECTORY_SEPARATOR . 'joomsport-moderate-acl.php';
class JoomsportModerateHelper{

    public static function getModerTeams(){
        $teams = new WP_Query(array(
            'post_type' => 'joomsport_team',
            'posts_per_page'   => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key'=>'_joomsport_team_moderator',
                    'value'=>get_current_user_id(),
                    'compare'=>'=',
                )
            )
        ));

        return $teams->posts;
    }
    public static function getModerPlayers(){
        $players = new WP_Query(array(
            'post_type' => 'joomsport_player',
            'posts_per_page'   => -1,
            'post_status' => 'publish',
            'author' => get_current_user_id(),

        ));

        return $players->posts;
    }

    public static function getModerMatches($filters){
        
        //var_dump($results);
        return $results;
    }

    public static function getMdMatches($matchdayID){
        
        return $matches;
    }


    public static function Can($task, $itemID){
        return JoomsportModerateACL::parse($task, $itemID);
    }

    public static function uploadImg($tempFile)
    {
        
        return 0;
    }
}
