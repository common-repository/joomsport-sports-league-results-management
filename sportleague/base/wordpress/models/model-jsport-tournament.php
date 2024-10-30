<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
class modelJsportTournament
{
    public $row = null;
    public $lists = null;

    public function __construct($id)
    {
        $this->row = get_term_by('id',$id,'joomsport_tournament');
        $this->lists['slist'] = JoomSportHelperObjects::getSeasonsByTourn($id);
    }
    public function getRow()
    {
        return $this->row;
    }
}
