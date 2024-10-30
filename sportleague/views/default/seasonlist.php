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
<div class="table-responsive">
    <table class="table table-striped seasonList">
        <thead>
            <tr>
                <th>
                    <?php echo esc_html__('Name','joomsport-sports-league-results-management');?>
                </th>
                <th><?php echo esc_html__('League type','joomsport-sports-league-results-management');?></th>
                <th><?php echo esc_html__('Open registration','joomsport-sports-league-results-management');?></th>
                <th><?php echo esc_html__('Start date','joomsport-sports-league-results-management');?></th>
                <th><?php echo esc_html__('End date','joomsport-sports-league-results-management');?></th>
                <th><?php echo esc_html__('Participants','joomsport-sports-league-results-management');?></th>
                <th style="text-align:center;"></th>
            </tr>
        </thead>
        <tbody>
            <?php
            //var_dump($rows);
            for ($intA = 0; $intA < count($rows); ++$intA) {
                $unable_reg = $this->model->canJoin($rows[$intA]);
                $part_count = $this->model->partCount($rows[$intA]);
                $regdata = get_post_meta($rows[$intA]->ID,'_joomsport_season_sreg',true);
                ?>
            <tr>
                <td>
                    <?php echo classJsportLink::season($rows[$intA]->post_title, $rows[$intA]->ID);
                ?>
                </td>
                <td><?php echo JoomSportHelperObjects::getTournamentType($rows[$intA]->ID) ? esc_html__('Single','joomsport-sports-league-results-management') : esc_html__('Team','joomsport-sports-league-results-management');
                ?></td>
                <td class="open-reg"><?php echo $unable_reg ? '<img src="'.JOOMSPORT_LIVE_URL_IMAGES_DEF.'active.png" width="14" height="14" alt="" />' : '<img src="'.JOOMSPORT_LIVE_URL_IMAGES_DEF.'negative.png" width="14" height="14" alt="" />'?></td>
                <td><p class="event-date"><?php  if ($regdata['reg_start'] != '0000-00-00 00:00:00') {
     echo esc_html($regdata['reg_start']);
 }
                ?></p></td>
                <td><p class="event-date"><?php  if ($regdata['reg_end'] != '0000-00-00 00:00:00') {
     echo esc_html($regdata['reg_end']);
 }
                ?></p></td>
                <td><?php echo esc_html($part_count.($regdata['s_participant'] ? '('.$regdata['s_participant'].')' : ''));
                ?></td>
                <td>
                        <?php

                        if ($unable_reg) {
                            $link = classJsportLink::joinseason($rows[$intA]->ID);
                            echo "<a href='".esc_url($link)."' class='join-button'><button type='button' class='btn btn-default'><i class='arrow-right'></i>".esc_html__('Join','joomsport-sports-league-results-management').'</button></a>';
                        } else {
                            echo '&nbsp;';
                        }
                ?>
                </td>
            </tr>
            <?php

            }
            ?>
        </tbody>
    </table>
</div>
