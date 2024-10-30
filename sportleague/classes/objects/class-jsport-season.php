<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

require_once JOOMSPORT_PATH_MODELS.'model-jsport-season.php';
require_once JOOMSPORT_PATH_OBJECTS.'class-jsport-seasonlist.php';
require_once JOOMSPORT_PATH_ENV_CLASSES.'class-jsport-participant.php';
foreach (glob(__DIR__.DIRECTORY_SEPARATOR.'tournament_types/*.php') as $filename) {
    include $filename;
}

class classJsportSeason
{
    private $id = null;
    public $object = null;
    public $season = null;
    public $lists = null;
    public $modelObj = null;

    public function __construct($id = 0, $loadList = true)
    {
        $this->id = $id;
        if (!$this->id) {
            $this->id = get_the_ID();
        }
        if (!$this->id) {
            die('ERROR! SEASON ID not DEFINED');
        }
        $this->loadObject($this->id, $loadList);
        
    }

    private function loadObject($id, $loadList)
    {
        $obj = $this->modelObj = new modelJsportSeason($id);
        $this->object = $obj->getRow();
        if($loadList){
            $this->lists = $obj->loadLists();
        }

    }

    public function getObject()
    {
        return $this->object;
    }

    public function getSingle()
    {
        return JoomSportHelperObjects::getTournamentType($this->id);
    }
    public function getTournType()
    {
        return 0; //(int)$this->object->tournament_type;
    }

    //

    public function getChild()
    {

        $type = $this->getTournType();
        if ($type == 0) {
            $this->season = new classJsportTournMatches($this->object);
        } else {
            $this->season = new classJsportTournRace($this->object);
        }

        return $this->season;
    }

    public function getRow()
    {
        if(!empty($this->object)){
            $child = $this->getChild();
            $child->calculateTable();
            $this->getLists();

            $this->lists['bonuses'] = $this->getSeasonBonuses();
            $this->getTournamentHistory();
            $this->setHeaderOptions();
            return $this;
        }else{
            //JError::raiseError('404', 'Not found');
        }


    }

    public function getLists()
    {
        $this->lists['options'] = $this->object->season_options?json_decode($this->object->season_options):array();
        $colors = $this->modelObj->getColors();
        
        $this->season->lists['tblcolors'] = $colors[0];
        $this->lists['legend'] = $colors[1];
    }

    public function getTabs()
    {
        $tabs = array();
        $intA = 0;
        //main tab

        $tabs[$intA]['id'] = 'stab_main';
        $tabs[$intA]['title'] = __('Standings','joomsport-sports-league-results-management');
        $tabs[$intA]['body'] = 'table-group.php';
        $tabs[$intA]['text'] = '';
        $tabs[$intA]['class'] = '';
        $tabs[$intA]['ico'] = 'js-stand';

        //about
        $s_descr = get_post_meta($this->id,'_joomsport_season_rules',true);

        //rules
        if ($s_descr) {
            ++$intA;
            $tabs[$intA]['id'] = 'stab_rules';
            $tabs[$intA]['title'] = __('Rules','joomsport-sports-league-results-management');
            $tabs[$intA]['body'] = '';
            $tabs[$intA]['text'] = classJsportText::getFormatedText($s_descr);
            $tabs[$intA]['class'] = '';
            $tabs[$intA]['ico'] = 'js-rules';
        }
        
        if ( has_filter( 'joomsport_custom_tab_fe' ) ){
            $tabs = apply_filters("joomsport_custom_tab_fe", $this->id, $tabs);
        }


        return $tabs;
    }

    public function setHeaderOptions()
    {
        $this->lists['options']['calendar'] = $this->id;
        //$seaslistObj = new classJsportSeasonlist();
        if ($this->canJoin($this->object)) {
            $this->lists['options']['joinseason'] = $this->id;
        }
        if (!$this->getSingle() && JoomsportSettings::get('enbl_linktoplayerlist',1) == '1') {
            $this->lists['options']['playerlist'] = $this->id;
        }
        $this->lists['options']['print'] = '<a href="javascript:void(0);" onclick="componentPopup();"><span class="glyphicon glyphicon-print"></span></a>';
        //social
        if (JoomsportSettings::get('jsbp_season') == '1') {
            $this->lists['options']['social'] = true;
            //classJsportAddtag::addCustom('og:title', $this->object->tsname);
            $img = $this->object->tourn_logo;
            if (is_file(JOOMSPORT_PATH_IMAGES.$img)) {
                //classJsportAddtag::addCustom('og:image', JS_LIVE_URL_IMAGES.$img);
            }
            //classJsportAddtag::addCustom('og:description', $this->object->s_descr);
        }
    }
    public function getSeasonBonuses(){
        global $wpdb;
        $html = '';
        $bonuses = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->postmeta} WHERE meta_key=%s AND meta_value != '0'",
                array('_joomsport_team_bonuses_'.$this->id)
            )
        );
        if($bonuses && count($bonuses)){
            $obj = new classJsportParticipant($this->id);
            $participants = $obj->getParticipants();
            if($participants && count($participants)) {
                for ($intA = 0; $intA < count($bonuses); $intA++) {
                    $bonus_point = $bonuses[$intA]->meta_value;

                    if (intval($bonus_point) != 0 && in_array($bonuses[$intA]->post_id, $participants)) {
                        $p = $obj->getParticipiantObj($bonuses[$intA]->post_id);
                        $html .= "<div>" . $p->getName(false) . ": " . $bonus_point . "</div>";
                    }
                }
            }
        }

        return $html;
    }
    
    public function isComplex(){
        return get_post_meta($this->id,'_joomsport_season_complex',true);
    }
    public function getSeasonChildrens(){
        $args = array(
                'post_parent' => $this->id,
                'post_type'   => 'joomsport_season', 
                'numberposts' => -1,
                'post_status' => 'published',
                'orderby' => 'menu_order title',
                'order'   => 'ASC',
        );
        $children = get_children( $args );
        return $children;
    }
    public function hideTable(){
        return jsHelperEvents::isHideTable($this->id);
    }
    
    public function canJoin($season)
    {
        $metadata = get_post_meta($season->ID,'_joomsport_season_sreg',true);
        if(!isset($metadata['s_reg']) || !$metadata['s_reg']){
            return false;
        }
        if(!$metadata['reg_start']){
            $metadata['reg_start'] = '0000-00-00 00:00:00';
        }else{
            $metadata['reg_start'] .= ' 00:00:00';
        }
        if(!$metadata['reg_end']){
            $metadata['reg_end'] = '0000-00-00 00:00:00';
        }else{
            $metadata['reg_end'] .= ' 00:00:00';
        }
        $reg_start = mktime(substr($metadata['reg_start'], 11, 2), substr($metadata['reg_start'], 14, 2), 0, substr($metadata['reg_start'], 5, 2), substr($metadata['reg_start'], 8, 2), substr($metadata['reg_start'], 0, 4));
        $reg_end = mktime(substr($metadata['reg_end'], 11, 2), substr($metadata['reg_end'], 14, 2), 0, substr($metadata['reg_end'], 5, 2), substr($metadata['reg_end'], 8, 2), substr($metadata['reg_end'], 0, 4));

        $part_count = $this->partCount($season);

        if ($metadata['s_reg'] && ($part_count < $metadata['s_participant'] || $metadata['s_participant'] == 0) && ($reg_start <= time() && (time() <= $reg_end || $metadata['reg_end'] == '0000-00-00 00:00:00'))) {
            return true;
        }

        return false;
    }
    public function partCount($season)
    {
        $metadata = get_post_meta($season->ID,'_joomsport_season_participiants',true);
        if(!$metadata || !is_array($metadata)){
            return 0;
        }
        return count($metadata);
    }

    public  function getTournamentHistory(){

        $term_list = get_the_terms($this->id, 'joomsport_tournament');
        if($term_list && count($term_list)){
            $term_id = $term_list[0]->term_id;
            $slist = JoomSportHelperObjects::getSeasonsByTourn($term_id);
            $html = '';
            if($slist && count($slist) > 1){
                $html .= '<select name="history_sid" id="history_sid">';
                foreach($slist as $sl){
                    $html .= '<option value="'.get_permalink($sl->ID).'" '.($sl->ID == $this->id?" selected":"").'>'.$sl->post_title.'</option>';
                }
                $html .= '</select>';
                $this->lists['options']['tourn'] = $html;
            }


        }

    }
}
