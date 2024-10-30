<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
require_once JOOMSPORT_PATH_OBJECTS.'class-jsport-season.php';
require_once JOOMSPORT_PATH_OBJECTS.'class-jsport-team.php';
require_once JOOMSPORT_PATH_OBJECTS.'class-jsport-player.php';
class classJsportParticipant
{
    private $season_id = null;
    public $single = null;
    public function __construct($season_id, $m_single = null)
    {
        $this->season_id = $season_id;
        $obj = new classJsportSeason($this->season_id);
        if ($m_single != null && $season_id <= 0) {
            $this->single = $m_single;
        } else {
            $this->single = $obj->getSingle();
        }
    }

    public function getParticipants($group_id = null)
    {
        global $wpdb;
        if($group_id){
            $group = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->joomsport_groups} WHERE s_id = %d AND id=%d ORDER BY ordering",
                    array($this->season_id,$group_id)
                )
            );
            $partcipants = isset($group->group_partic)?  unserialize($group->group_partic):array();
        }else{

            $partcipants = get_post_meta($this->season_id,'_joomsport_season_participiants',true);
            if(!is_array($partcipants)){
                $partcipants = array();
            }

        }
        

        return $partcipants;
    }

    public function getParticipiantObj($id)
    {
        if ($id) {
            if ($this->single) {
                $obj = new classJsportPlayer($id, $this->season_id, false);
            } else {
                $obj = new classJsportTeam($id, $this->season_id, false);
            }
        } else {
            $obj = null;
        }

        return $obj;
    }
}
