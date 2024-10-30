<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
class classJsportDlists
{
    public static function getSeasonsPlayerList($season_id)
    {
        $tourn = JoomsportTermsMeta::getTerms('joomsport_tournament', array("hide_empty" => false, 'orderby' => 'name'));

        $javascript = " onchange='this.form.submit();'";
        $jqre = '<select name="sid" id="sid" class="styled jfsubmit" size="1" '.$javascript.'>';
        $jqre .= '<option value="0">'.__('All','joomsport-sports-league-results-management').'</option>';

        for($i=0;$i<count($tourn);$i++){
                $args = array(
                    'posts_per_page' => -1,
                    'offset'           => 0,
                    'orderby'          => 'title',
                    'order'            => 'ASC',
                    'post_type'        => 'joomsport_season',
                    'post_status'      => 'publish',
                    'tax_query'        =>  array(
                        array(
                        'taxonomy' => 'joomsport_tournament',
                        'field' => 'term_id',
                        'terms' => $tourn[$i]->term_id)
                    )
                );
                
                $rows = get_posts( $args );
                
                if(count($rows)){
                        $jqre .= '<optgroup label="'.esc_attr($tourn[$i]->name).'">';
                        for($g=0;$g<count($rows);$g++){
                                $jqre .= '<option value="'.esc_attr($rows[$g]->ID).'" '.(($rows[$g]->ID == $season_id)?"selected":"").'>'.$rows[$g]->post_title.'</option>';
                        }
                        $jqre .= '</optgroup>';
                }
        }
        $jqre .= '</select>';
        $jqre .= '<input type="hidden" name="pagejs" value="1" />';
        return $jqre;
    }
    public static function getSeasonsTeamList($season_id){

        $tourn = JoomsportTermsMeta::getTerms('joomsport_tournament', array("hide_empty" => false, 'orderby' => 'name'));

        $javascript = " onchange='this.form.submit();'";
        $jqre = '<select name="sid" id="sid" class="styled jfsubmit" size="1" '.$javascript.'>';
        $jqre .= '<option value=0"">'.__('All','joomsport-sports-league-results-management').'</option>';
        for($i=0;$i<count($tourn);$i++){
                $args = array(
                    'posts_per_page' => -1,
                    'offset'           => 0,
                    'orderby'          => 'title',
                    'order'            => 'ASC',
                    'post_type'        => 'joomsport_season',
                    'post_status'      => 'publish',
                    'tax_query'        =>  array(
                        array(
                        'taxonomy' => 'joomsport_tournament',
                        'field' => 'term_id',
                        'terms' => $tourn[$i]->id)
                    )
                );
                
                $rows = get_posts( $args );

                if(count($rows)){
                        $jqre .= '<optgroup label="'.esc_attr($tourn[$i]->name).'">';
                        for($g=0;$g<count($rows);$g++){
                                $jqre .= '<option value="'.esc_attr($rows[$g]->id).'" '.(($rows[$g]->id == $season_id)?"selected":"").'>'.$rows[$g]->s_name.'</option>';
                        }
                        $jqre .= '</optgroup>';
                }
        }
        $jqre .= '</select>';

        $jqre .= '<input type="hidden" name="pagejs" value="1" />';
        return $jqre;
    }
    public static function getSeasonsTournList($tournament_id){

        $javascript = 'onchange = "this.form.submit();"';
        $restourn = array();

        $tourn = JoomsportTermsMeta::getTerms('joomsport_tournament', array("hide_empty" => false, 'orderby' => 'name'));


        for($intA = 0; $intA < count($tourn); $intA++){

            $restourn[] = JoomSportHelperSelectBox::addOption($tourn[$intA]->term_id, $tourn[$intA]->name);
        }
        
        $jqre = JoomSportHelperSelectBox::Simple('filtr_tourn', $restourn,$tournament_id,'class="styled jfsubmit" size="1" '.$javascript, true);

        $jqre .= '<input type="hidden" name="pagejs" value="1" />';
        return $jqre;
    }
}
