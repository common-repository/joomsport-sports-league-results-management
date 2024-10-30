<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
require_once JOOMSPORT_PATH_ENV_CLASSES.'class-jsport-participant.php';
require_once JOOMSPORT_PATH_OBJECTS.'class-jsport-group.php';
require_once JOOMSPORT_PATH_ENV_CLASSES.'class-jsport-getmdays.php';

class classJsportTournMatches
{
    private $id = null;
    private $object = null;
    public $lists = null;
    public $pagination = null;
    const VIEW = 'calendar';

    public function __construct($object)
    {
        $this->object = $object;
        $this->id = $this->object->ID;
    }

    private function loadObject()
    {
    }

    public function getObject()
    {
        return $this->object;
    }

    public function getTable($group_id, $place = 0)
    {
        global $jsDatabase;

        $query = 'SELECT * FROM '.$jsDatabase->db->joomsport_season_table.' '
                .' WHERE season_id = '.intval($this->id)
                .' AND group_id = '.$group_id
                .' ORDER BY ordering';
        if($place){
            $query .= ' LIMIT '.$place;
        }
        $table = $jsDatabase->select($query);
        
        if (!$table) {
            classJsportPlugins::get('generateTableStanding', array('season_id' => $this->id));
            $query = 'SELECT * FROM '.$jsDatabase->db->joomsport_season_table.' '
                .' WHERE season_id = '.intval($this->id)
                .' AND group_id = '.$group_id
                .' ORDER BY ordering';
            if($place){
                $query .= ' LIMIT '.$place;
            }
            $table = $jsDatabase->select($query);

        }
        $this->getExtraFieldsTable($table);

        if (isset($this->lists['columns']['curform_chk']) && $this->lists['columns']['curform_chk']) {
            $this->getTeamFormGraph($table);
        }
        if (isset($this->lists['columns']['nextmatch_chk']) && $this->lists['columns']['nextmatch_chk']) {
            $this->getTeamNextMatch($table);

        }

        return $table;
    }

    public function getTeamFormGraph(&$tbl)
    {
        for ($intT = 0; $intT < count($tbl); ++$intT) {
            if($tbl[$intT]->curForm === NULL) {
                $tid = $tbl[$intT]->participant_id;

                $options = array('team_id' => $tid, 'season_id' => $this->id);

                $options['limit'] = 5;
                $options['played'] = '1';
                $options['ordering_dest'] = 'desc';
                $obj = new classJsportMatches($options);
                $rows = $obj->getMatchList();

                $matches = array();

                if ($rows['list']) {
                    foreach ($rows['list'] as $row) {
                        $match = new classJsportMatch($row->ID, false, $row->mdID, $row->seasonID);
                        $matches[] = $match->getRowSimple();
                    }
                }


                $from_str = '';
                $formArrINs = [];
                $matches = array_reverse($matches);

                for ($intA = 0; $intA < 5; ++$intA) {
                    $formArr = jsHelper::JsFormViewElement(isset($matches[$intA]) ? ($matches[$intA]) : null, $tid, $this->id);
                    if($formArr && is_array($formArr)){

                        if($formArr["matchID"]) {
                            $link = classJsportLink::match('', $formArr["matchID"], true);
                            $from_str .= '<a href="' . $link . '" title="' . esc_attr($formArr["title"]) . '" class="jstooltip"><span class="jsform_none ' . $formArr["class"] . '">' . $formArr["text"] . '</span></a>';
                        }else{
                            $from_str .= '<span class="jsform_none match_quest">?</span>';
                        }
                        $formArrINs[] = $formArr;
                    }

                }

                $tbl[$intT]->curform_chk = $from_str;
                if(is_array($formArrINs) && $this->id){
                    global $wpdb;
                    $form =  json_encode($formArrINs);

                    $wpdb->query(
                        $wpdb->prepare(
                            'UPDATE '.$wpdb->joomsport_season_table.' '
                            .' SET curForm = %s'
                            .' WHERE season_id = %d'
                            .' AND participant_id = %d',
                            array($form, $this->id, $tid)
                        )
                    );
                }
            }else{
                $tbl[$intT]->curform_chk = '';

                if($tbl[$intT]->curForm){
                    $curForm = json_decode($tbl[$intT]->curForm, true);
                    if($curForm && is_array($curForm)){
                        $from_str = '';
                        //var_dump($curForm);
                        foreach($curForm as $cf){
                            //var_dump($cf);
                            if($cf["matchID"]) {
                                $link = classJsportLink::match('', $cf["matchID"], true);
                                $from_str .= '<a href="' . $link . '" title="' . esc_attr($cf["title"]) . '" class="jstooltip"><span class="jsform_none ' . $cf["class"] . '">' . $cf["text"] . '</span></a>';
                            }else{
                                $from_str .= '<span class="jsform_none match_quest">?</span>';
                            }
                        }
                        $tbl[$intT]->curform_chk = $from_str;
                    }
                }

            }
        }

    }

    public function calculateTable($allcolumns = false, $group_id = 0, $place = 0)
    {
        //global $jsDatabase;
        //get knockout
        $this->getKnock();
        $this->getPlayoffs();
        //get matchdays group
        
        //$show_table = false;

        $show_table = jsHelperEvents::isHideTable($this->id);

        if (!$show_table || (!count($this->lists['playoffs']) && !count($this->lists['knockout']))) {
            //get groups
            $groupsObj = new classJsportGroup($this->id);
            $groups = $groupsObj->getGroups();
            $this->lists['columns'] = $this->getTournColumns($allcolumns);
            $this->lists['groups'] = $groups;
            $columnsCell = array();
            //get participants
            if (count($groups)) {
                foreach ($groups as $group) {
                    if($group_id == 0 || $group_id == $group->id){
                        $columnsCell[$group->group_name] = $this->getTable($group->id, $place);
                    }
                }
            } else {
                $columnsCell[] = $this->getTable(0, $place);
            }
            $this->lists['columnsCell'] = $columnsCell;
        }
        //get season options
        //get variables for table view
        // multisort
        // save to db
    }

    public function getTournColumns($allcolumns)
    {
        global $jsDatabase;
        $this->lists['available_options'] = JoomsportSettings::getStandingColumns();
        $this->lists['available_options'][]= array('emblem_chk' => array());
        $this->lists['available_options_short'] = json_decode(JoomsportSettings::get('columnshort'),true);
        
        $lists = array();
        $listsss = get_post_meta($this->id,'_joomsport_season_standindgs',true);

        if($allcolumns){
            if($listsss && count($listsss)){
                foreach ($listsss as $key => $value) {
                    $lists[$key] = $value;
                }
            }
        }

        if($listsss && count($listsss)){
            foreach ($listsss as $key => $value) {
                if($value)
                $lists[$key] = $value;
            }
        }

        return $lists;
    }

    public function getKnock()
    {
        if($this->id){
            /*$options = array();
            $options['season_id'] = $this->id;
            $options['mday_type'] = '1';
            $mdays = classJsportgetmdays::getMdays($options);*/
            $mdays = jsHelperEvents::getKnockMds($this->id);

            $this->lists['knockout'] = array();
            if(count($mdays)){
                wp_enqueue_style('jscssbracket',plugin_dir_url( __FILE__ ).'../../../assets/css/drawBracketBE.css');
            }

            for ($intA = 0; $intA < count($mdays); ++$intA) {
                require_once JOOMSPORT_SL_PATH. '/../includes/classes/matchday_types/joomsport-class-matchday-knockout.php';
                $knockObj = new JoomSportClassMatchdayKnockout($mdays[$intA]->id);
                $this->lists['knockout'][] = $knockObj->getView();

            }
        }
    }

    public function getPartById($partId)
    {
        $obj = new classJsportParticipant($this->id);
        $participant = $obj->getParticipiantObj($partId);

        return $participant;
    }

    //calendar
    public function getCalendar($options = array())
    {
        global $wpdb;
        $this->lists['enable_search'] = JoomsportSettings::get('enbl_calmatchsearch',1);
        if (classJsportRequest::get('tmpl') == 'component') {
            $this->lists['enable_search'] = 0;
        }
        if ($this->lists['enable_search'] && JoomsportSettings::get('jscalendar_theme',0) == 0) {
            $this->lists['options']['tourn'] = '<a href="javascript:void(0);" id="aSearchFieldset">'.__('Search the matches','joomsport-sports-league-results-management').'</a>';
        }
        if(isset($options['season_id']) && !is_array($options['season_id'])){
            $options['season_id'] = $this->id;
        }
        $this->lists["complexseason"] = isset($options['season_id'])?$options['season_id']:null;
        $filtersvar = classJsportRequest::get('filtersvar');

        if ($filtersvar) {
            classJsportSession::set('filtersvar_calendar_'.$this->id, wp_json_encode($filtersvar));
        }
        $apply_filters = false;
        if (classJsportSession::get('filtersvar_calendar_'.$this->id)) {
            $filters = json_decode(classJsportSession::get('filtersvar_calendar_'.$this->id));

            $this->lists['filtersvar'] = $filters;
            if ($filters->mday) {
                $options['matchday_id'] = $filters->mday;
                $apply_filters = true;
            }
            if (isset($filters->partic) && $filters->partic) {
                $options['team_id'] = $filters->partic;
                $apply_filters = true;
            }
            if (isset($filters->date_from) && $filters->date_from) {
                $k = preg_match("^\\d{4}-\d{2}-\d{2}^", $filters->date_from);
                if(!$k){
                    $filters->date_from = "";
                }
                $options['date_from'] = wp_strip_all_tags(htmlspecialchars($filters->date_from, ENT_QUOTES));
                $apply_filters = true;
            }
            if (isset($filters->date_to) && $filters->date_to) {
                $k = preg_match("^\\d{4}-\d{2}-\d{2}^", $filters->date_to);
                if(!$k){
                    $filters->date_to = "";
                }
                $options['date_to'] = htmlspecialchars($filters->date_to);
                $apply_filters = true;
            }
            if (isset($filters->place) && $filters->place) {
                $options['place'] = $filters->place;
                $apply_filters = true;
            }
        }
        $this->lists['apply_filters'] = $apply_filters;

        $this->lists['filters'] = array();
        if(JoomsportSettings::get('jscalendar_theme',0) == 1){
            //$options['ordering'] = 'md.ordering, md.m_name, md.id';
            $mday = $this->getLastMday();
            if (!isset($options['matchday_id'])) {
                $options['matchday_id'] = $mday;
                $this->lists['filtersvar'] = new stdClass();
                $this->lists['filtersvar']->mday = $mday;
            }  
            $this->lists['prevlink'] = $this->getPrev($options['matchday_id']);
            $this->lists['nextlink'] = $this->getNext($options['matchday_id']);
        }
        if ($this->lists['enable_search'] || JoomsportSettings::get('jscalendar_theme',0) == 1) {

            $this->lists['filters']['mday_list'] = classJsportgetmdays::getMdays($options);
        }
        if ($this->lists['enable_search']) {
            $partObj = new classJsportParticipant($this->id);
            $partic = $partObj->getParticipants();
            for ($intA = 0; $intA < count($partic); ++$intA) {
                $item = $partObj->getParticipiantObj($partic[$intA]);
                $this->lists['filters']['partic_list'][$partic[$intA]] = $item->getName(false);
            }
        }
        $link = $this->lists["actionlink"] = classJsportLink::calendar('', $this->id, true);
        
        // allready played matches
        $playedMatches = 0;

        if(count($options) == 1){

            $query = "SELECT COUNT(postID)"
                . " FROM {$wpdb->joomsport_matches}"
                ." WHERE status = 1"
                .(is_array($options['season_id'])?" AND seasonID IN (".implode(",",array_map('absint',$options['season_id'])).")":" AND seasonID = ".intval($options['season_id']));
            $playedMatches = $wpdb->get_var($query);
        }
        //end
        
        $pagination = new classJsportPagination($link, $playedMatches);
        if(JoomsportSettings::get('jscalendar_theme',0) == 0 && !isset($_GET["option"])){
            $options['limit'] = $pagination->getLimit();
            $options['offset'] = $pagination->getOffset();
        }

        $obj = new classJsportMatches($options);
        $rows = $obj->getMatchList();

        if(JoomsportSettings::get('jscalendar_theme',0) == 0){
            $pagination->setPages($rows['count']);
        }else{
            $pagination = null;
        }
        $this->pagination = $pagination;
        $matches = array();

        $showMatchDetales = JSCONF_ENBL_MATCH_TOOLTIP;
        if(classJsportRequest::get('jsformat') == 'json'){
            $showMatchDetales = false;
        }

        //require_once JS_PATH_ENV_CLASSES . 'class-jsport-calc-player-list.php';
        if ($rows['list']) {
            foreach ($rows['list'] as $row) {
                $match = new classJsportMatch($row->ID, $showMatchDetales, $row->mdID, $row->seasonID);
                if($showMatchDetales) {
                    $match->getPlayerObj($match->lists['m_events_home']);
                    $match->getPlayerObj($match->lists['m_events_away']);
                }
                $matches[] = $match->getRowSimple();
                //$obj = new classJsportCalcPlayerList($row->id);
            }
        }
        $type = 2;
        $this->lists['ef_table'] = $ef = classJsportExtrafields::getExtraFieldListTable($type);
        if (is_array($ef) && count($ef) && count($matches)) {

            for ($intA = 0; $intA < count($matches); ++$intA) {
                for ($intB = 0; $intB < count($ef); ++$intB) {
                    $ttt = classJsportExtrafields::getExtraFieldValue($ef[$intB], $matches[$intA]->id, $type, $this->id);
                    $matches[$intA]->lists['ef_'.$ef[$intB]->id] = $ttt;

                }
            }
        }
        return $matches;
    }

    public function getExtraFieldsTableNoEmpty(&$table)
    {
        $type = JoomSportHelperObjects::getTournamentType($this->id) ? 0 : 1;
        $this->lists['ef_table'] = array();
        $efEmpty = array();
        $ef = classJsportExtrafields::getExtraFieldListTable($type);
        if (count($ef) && count($table)) {
            for ($intA = 0; $intA < count($table); ++$intA) {
                for ($intB = 0; $intB < count($ef); ++$intB) {
                    $table[$intA]->{'ef_'.$ef[$intB]->id} = classJsportExtrafields::getExtraFieldValue($ef[$intB], $table[$intA]->participant_id, $type, $this->id);
                    if($table[$intA]->{'ef_'.$ef[$intB]->id} != ''){
                        $efEmpty[$intB] = true;
                    }
                }
            }
            
            for ($intB = 0; $intB < count($ef); ++$intB) {
                
                if(isset($efEmpty[$intB]) && $efEmpty[$intB]){
                    $this->lists['ef_table'][] = $ef[$intB];
                }
            }
            
        }
    }
    
    public function getPlayoffs()
    {
        global $jsDatabase;

        /*$options['season_id'] = $this->id;
        $options['mday_type'] = '0';
        $options['is_playoff'] = '1';
        $mdays = classJsportgetmdays::getMdays($options);*/

        $mdays = jsHelperEvents::getPlayoffMds($this->id);

        $matches = array();
        if ($mdays) {
            foreach ($mdays as $mday) {
                $mdId = $mday->id;
                $options = array();
                $options['season_id'] = $this->id;
                $options["matchday_id"] = $mdId;

                $obj = new classJsportMatches($options);
                $rows = $obj->getMatchList();
                
                if ($rows["list"]) {
                    foreach ($rows["list"] as $row) {
                        $match = new classJsportMatch($row->ID, false, $row->mdID, $row->seasonID);
                        $matches[] = $match->getRowSimple();
                        
                        //$obj = new classJsportCalcPlayerList($row->id);
                    }
                }
            }
        }    
        
        

        
        
        $this->lists['playoffs'] = $matches;
    }
    
    
    public function getExtraFieldsTable(&$table)
    {
        $type = JoomSportHelperObjects::getTournamentType($this->id) ? 0 : 1;
        $this->lists['ef_table'] = $ef = classJsportExtrafields::getExtraFieldListTable($type);
        if (is_array($ef) && count($ef) && count($table)) {
            for ($intA = 0; $intA < count($table); ++$intA) {
                for ($intB = 0; $intB < count($ef); ++$intB) {
                    $table[$intA]->{'ef_'.$ef[$intB]->id} = classJsportExtrafields::getExtraFieldValue($ef[$intB], $table[$intA]->participant_id, $type, $this->id);
                }
            }
        }
    }

    public function getCalendarView()
    {
        if(JoomsportSettings::get('jscalendar_theme',0) == 1){
            return 'calendar_mday';
            exit();
        }
        return self::VIEW;
    }
    
    public function getLastMday(){
        $mdID = 0;
        $query = new WP_Query( 
                     array(
                        'posts_per_page' => 1,
                        'post_type'        => 'joomsport_match',
                        'post_status'      => 'publish',
                         'orderby' => 'meta_value',
                         'order'            => 'DESC',
                         'meta_key' => '_joomsport_match_date',
                        'meta_query' => array(
                            'relation' => 'AND',
                            
                            array(
                                'key' => '_joomsport_seasonid',
                                'value' => $this->lists["complexseason"],
                                'compare' => (is_array($this->lists["complexseason"])?'IN':'=')
                            ),
                            array(
                                'key' => '_joomsport_match_played',
                                'value' => '1'
                            )
                        )    
                        ) 
                     );
        if(isset($query->posts[0])){
        
            $term_list = get_the_terms($query->posts[0]->ID, 'joomsport_matchday');
            if(count($term_list)){
                $mdID =  $term_list[0]->term_id;
            }
        }
        
           
        if(!$mdID){
            $mdoptions = array();
            $mdoptions['season_id'] = $this->id;
            $mdoptions['ordering'] = 'md.ordering, md.m_name, md.id';
            $mdays = classJsportgetmdays::getMdays($mdoptions);
            if(isset($mdays[0])){
                $mdID = $mdays[0]->id;
            }
        }

        return $mdID;
    }
    public  function getMdayArray(){
        $mdaysArray = array();
        $mdoptions['season_id'] = $this->id;
        $mdoptions['ordering'] = 'md.ordering, md.m_name, md.id';
        $mdays = classJsportgetmdays::getMdays($mdoptions);
        for($intA=0; $intA < count($mdays); $intA++){
            $mdaysArray[] =  $mdays[$intA]->id;
        }
        return $mdaysArray;
    }
    public  function getNext($mdId){
        $html = '&nbsp;';
        $mdays = $this->getMdayArray();
        if(isset($mdays[0])){
            $key = (int) array_search($mdId, $mdays);
            if(isset($mdays[$key+1])){
                $link = classJsportLink::calendar('', $this->id, true,'',true,array(array("name" => "filtersvar[mday]", "value" => $mdays[$key+1])));
                $html = '<a href="'.$link.'">'.__('Next','joomsport-sports-league-results-management').'</a>';
            }
        }
        return $html;
    }
    public  function getPrev($mdId){
        $html = '&nbsp;';
        $mdays = $this->getMdayArray();
        if(isset($mdays[0])){
            $key = (int) array_search($mdId, $mdays);
            if(isset($mdays[$key-1])){
                $link = classJsportLink::calendar('', $this->id, true,'',true,array(array("name" => "filtersvar[mday]", "value" => $mdays[$key-1])));
                $html = '<a href="'.$link.'">'.__('Previous','joomsport-sports-league-results-management').'</a>';
            }
        }
        return $html;
    }

    public function getTeamNextMatch(&$tbl){
        global $wpdb;

        $mdays = $wpdb->get_col(
            $wpdb->prepare(
                $query = "SELECT mdID"
                    . " FROM {$wpdb->joomsport_matches} as m"
                    . " JOIN {$wpdb->posts} as p ON p.ID = m.postID"
                    ." WHERE p.post_status='publish'"
                    ." AND seasonID = %d",
                array($this->id)
            )
        );

        $inmd = array();
        for($intA=0;$intA<count($mdays);$intA++){
            //$term_meta = get_option( "taxonomy_".$mdays[$intA]."_metas");
            $term_meta = JoomsportTermsMeta::getTermMeta($mdays[$intA]);
            if(isset($term_meta["matchday_type"]) && $term_meta["matchday_type"] == '0'){
                $inmd[] = (int) $mdays[$intA];
            }
        }

        if(!count($inmd)){
            return '';
        }

        for ($intT = 0; $intT < count($tbl); ++$intT) {
            $tid = $tbl[$intT]->participant_id;

            $list = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT m.postID AS ID, mdID, seasonID"
                    . " FROM {$wpdb->joomsport_matches} as m"
                    . " JOIN {$wpdb->posts} as p ON p.ID = m.postID"
                    ." WHERE p.post_status='publish'"
                    ." AND seasonID = %d"
                    ." AND (m.teamHomeID = %d OR m.teamAwayID = %d)"
                    ." AND m.status = 0"
                    ." AND mdID IN (".implode(",",array_map("absint",$inmd)).")"
                    ." ORDER BY m.date asc, m.time asc"
                    ." LIMIT 1",
                    array($this->id, $tid, $tid)
                )
            );

            $from_str = '';
            if ($list) {
                foreach ($list as $row) {
                    $match = new classJsportMatch($row->ID, false, $row->mdID, $row->seasonID);
                    $partic_home = $match->getParticipantHome();
                    $partic_away = $match->getParticipantAway();

                    if(is_object($partic_home) && $partic_home->object->ID != $tid){
                        $from_str = $partic_home->getEmblem(false);
                    }elseif(is_object($partic_away) && $partic_away->object->ID != $tid){
                        $from_str = $partic_away->getEmblem(false);
                    }
                }
            }



            $tbl[$intT]->nextmatch_chk = $from_str;
        }
    }
    
}
