<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div class="jsOverflowHidden">
    <div class="table-responsive">
        <?php
        if (JoomsportSettings::get('tlb_position') && $rows->lists['curposition']) {
            $aoptions = array(
                'played_chk' => _x('Played','Standings column','joomsport-sports-league-results-management'),
                'win_chk' => __('Wins','joomsport-sports-league-results-management'),
                'draw_chk' => __('Draw','joomsport-sports-league-results-management'),
                'lost_chk' => __('Losts','joomsport-sports-league-results-management'),
                'gd_chk' => __('GD','joomsport-sports-league-results-management'),
                'point_chk' => __('Points','joomsport-sports-league-results-management'),

            );
            $json = $rows->lists['curposition']->options?json_decode($rows->lists['curposition']->options, true):array();
            ?>
            <div class="overviewBlocks">
                <div class="center-block jscenter">
                    <h3><?php echo esc_html__('Position','joomsport-sports-league-results-management');
            ?></h3>
                </div>
                <table class="tblPosition">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Rank','joomsport-sports-league-results-management');
            ?></th>
                            <?php
                                foreach ($aoptions as $key => $value) {
                                    ?>
                                    <th><?php echo esc_html($value);
                                    ?></th>
                                    <?php

                                }
            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo esc_html($rows->lists['curposition']->ordering);
            ?></td>
                            <?php
                                foreach ($aoptions as $key => $value) {
                                    ?>
                                    <td><?php echo esc_html($json[$key]);
                                    ?></td>
                                    <?php

                                }
            ?>
                        </tr>
                    </tbody>
                </table>
            </div>    
            <?php

        }

        ?>
        
        <?php
        if (JoomsportSettings::get('tlb_form') && count($rows->lists['matches_latest'])) {
            ?>
            <div class="overviewBlocks">
                <div class="center-block jscenter">
                    <h3><?php echo esc_html__('Current form','joomsport-sports-league-results-management');
            ?></h3>
                </div>
                <table class="tblPosition">
                    <thead>
                        <tr>
                            <?php 
                            $formarr = array_reverse($rows->lists['matches_latest']);
                            for ($intA = 0; $intA < 5; ++$intA) {
                                if (isset($formarr[$intA]->object)) {
                                    $home_team = get_post_meta( $formarr[$intA]->id, '_joomsport_home_team', true );
                                    if ($home_team == $rows->object->ID) {
                                        echo '<th>'.esc_html__('H','joomsport-sports-league-results-management').'</th>';
                                    } else {
                                        echo '<th>'.esc_html__('A','joomsport-sports-league-results-management').'</th>';
                                    }
                                } else {
                                    echo '<th></th>';
                                }
                            }
            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <?php 
                            for ($intA = 0; $intA < 5; ++$intA) {
                                echo '<td>';
                                $formArr = jsHelper::JsFormViewElement(isset($formarr[$intA]) ? ($formarr[$intA]) : null, $rows->object->ID);
                                if($formArr && is_array($formArr)){
                                    $from_str = '';
                                    if($formArr["matchID"]) {
                                        $link = classJsportLink::match('', $formArr["matchID"], true);
                                        $from_str .= '<a href="' . $link . '" title="' . esc_attr($formArr["title"]) . '" class="jstooltip"><span class="jsform_none ' . $formArr["class"] . '">' . $formArr["text"] . '</span></a>';
                                    }else{
                                        $from_str = '<span class="jsform_none match_quest">?</span>'.$from_str;
                                    }
                                    echo $from_str;
                                }
                                echo '</td>';
                            }
            ?>
                            
                        </tr>
                    </tbody>
                </table>
            </div>    
        <?php 
        }

        ?>
        
        <?php
        if (JoomsportSettings::get('tlb_latest') && count($rows->lists['matches_latest'])) {
            ?>
            <div class="overviewBlocks">
                <div class="center-block jscenter">
                    <h3><?php echo esc_html__('Results','joomsport-sports-league-results-management');
            ?></h3>
                </div>
                <table class="tblPosition">
                    <thead>
                        <tr>
                            <th width="25%">
                                <?php echo esc_html__('Date','joomsport-sports-league-results-management');
            ?>
                            </th>
                            <th class="jsTextAlignLeft">
                                <?php echo esc_html__('Team','joomsport-sports-league-results-management');
            ?>
                            </th>
                            <th width="15%">
                                <?php echo esc_html__('Location','joomsport-sports-league-results-management');
            ?>
                            </th>
                            <th width="20%">
                                <?php echo esc_html__('Results','joomsport-sports-league-results-management');
            ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php 

                        for ($intA = 0; $intA < count($rows->lists['matches_latest']); ++$intA) {
                            $match = $rows->lists['matches_latest'][$intA];
                            $home_team = get_post_meta( $match->id, '_joomsport_home_team', true );
                            $m_date = get_post_meta($match->id,'_joomsport_match_date',true);
                            $m_time = get_post_meta($match->id,'_joomsport_match_time',true);        
                            $match_date = classJsportDate::getDate($m_date, $m_time);
                            if ($rows->object->ID == $home_team) {
                                $field = __('H','joomsport-sports-league-results-management');
                                if(JoomsportSettings::get('partdisplay_awayfirst',0) == 1){
                                    $opponent = $match->getParticipantHome();
                                }else{
                                    $opponent = $match->getParticipantAway();
                                }
                                
                            } else {
                                $field = __('A','joomsport-sports-league-results-management');
                                if(JoomsportSettings::get('partdisplay_awayfirst',0) == 1){
                                    $opponent = $match->getParticipantAway();
                                }else{
                                    $opponent = $match->getParticipantHome();
                                }
          
                            }
                            echo '<tr>';
                            echo '<td>'.esc_html($match_date).'</td>';
                            
                            echo '<td class="jsTextAlignLeft">';
                            if(!empty($opponent)){
                                echo wp_kses_post($opponent->getEmblem().' '.$opponent->getName(true));
                            }
                            echo '</td>';
                            echo '<td>'.esc_html($field).'</td>';
                            echo '<td>'.jsHelper::getScore($match).'</td>';
                            echo '</tr>';
                        }
            ?>
                            

                    </tbody>
                </table>
            </div>    
        <?php 
        }

        ?>
        
        <?php
        if (JoomsportSettings::get('tlb_next') && count($rows->lists['matches_next'])) {
            ?>
            <div class="overviewBlocks">
                <div class="center-block jscenter"><h3><?php echo esc_html__('Fixtures','joomsport-sports-league-results-management');
            ?></h3></div>
                <table class="tblPosition">
                    <thead>
                        <tr>
                            <th width="25%">
                                <?php echo esc_html__('Date','joomsport-sports-league-results-management');
            ?>
                            </th>
                            <th class="jsTextAlignLeft">
                                <?php echo esc_html__('Team','joomsport-sports-league-results-management');
            ?>
                            </th>
                            <th width="15%">
                                <?php echo esc_html__('Location','joomsport-sports-league-results-management');
            ?>
                            </th>
                            <th width="20%">
                                <?php echo esc_html__('Results','joomsport-sports-league-results-management');
            ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php 
                        for ($intA = 0; $intA < count($rows->lists['matches_next']); ++$intA) {
                            $match = $rows->lists['matches_next'][$intA];
                            $home_team = get_post_meta( $match->id, '_joomsport_home_team', true );
                            $m_date = get_post_meta($match->id,'_joomsport_match_date',true);
                            $m_time = get_post_meta($match->id,'_joomsport_match_time',true);        
                            $match_date = classJsportDate::getDate($m_date, $m_time);
                            if ($rows->object->ID == $home_team) {
                                $field = __('H','joomsport-sports-league-results-management');
                                if(JoomsportSettings::get('partdisplay_awayfirst',0) == 1){
                                    $opponent = $match->getParticipantHome();
                                }else{
                                    $opponent = $match->getParticipantAway();
                                }
                                
                            } else {
                                $field = __('A','joomsport-sports-league-results-management');
                                if(JoomsportSettings::get('partdisplay_awayfirst',0) == 1){
                                    $opponent = $match->getParticipantAway();
                                }else{
                                    $opponent = $match->getParticipantHome();
                                }
                                
                            }
                            echo '<tr>';
                            echo '<td>'.$match_date.'</td>';
                            echo '<td class="jsTextAlignLeft">';
                            if(!empty($opponent)){
                                echo wp_kses_post($opponent->getEmblem().' '.$opponent->getName(true));
                            }
                            echo '</td>';
                            echo '<td>'.esc_html($field).'</td>';
                            echo '<td>'.jsHelper::getScore($match).'</td>';
                            echo '</tr>';
                        }
            ?>
                            

                    </tbody>
                </table>
            </div>    
        <?php 
        }

        ?>
    </div>
</div>
    