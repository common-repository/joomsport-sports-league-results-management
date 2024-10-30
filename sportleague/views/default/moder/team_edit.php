<?php
?>
<div>
    <form action="" name="formTeamEditFE" id="formTeamEditFE" autocomplete="off"  method="post" enctype="multipart/form-data">
        <div class="jstable">
            <div class="jstable-row">
                <div class="jstable-cell"><?php echo esc_html__('Title','joomsport-sports-league-results-management');?></div>
                <div class="jstable-cell">
                    <input type="text" class="form-control" value="<?php echo esc_attr(get_the_title($teamID));?>" name="teamName" />
                </div>
            </div>
        </div>

        <?php
        JoomSportMetaTeam::js_meta_personal($teamPost);
        //JoomSportMetaTeam::js_meta_about($thisPost);
        JoomSportMetaTeam::js_meta_ef($teamPost);
        $ids = get_post_meta($teamID, 'vdw_gallery_id', true);
        if(JoomsportSettings::get('upload_team_img')) {
            ?>
            <div class="jsminwdhtd jstable">
                <div class="jstable-row">
                    <div class="jstable-cell">Logo</div>
                    <div class="jstable-cell">
                        <input type="file" id="teamLogo" name="teamLogo" accept="image/*"/>
                        <div class="moderPlayerImg">
                            <?php
                            if (has_post_thumbnail($teamID)) {

                                $image = wp_get_attachment_image_src(get_post_thumbnail_id($this->id)); ?>


                                <input type="hidden" name="logo_id"
                                       value="<?php echo esc_attr(get_post_thumbnail_id($this->id)); ?>">
                                <img class="image-preview" src="<?php echo esc_url($image[0]); ?>">
                                <small><a class="remove-image-md"
                                          href="#"><?php echo esc_attr(__('Remove Logo', 'joomsport-sports-league-results-management')); ?></a></small>
                                <?php
                            }


                            ?>
                        </div>
                    </div>
                </div>
                <div class="jstable-row">
                    <div class="jstable-cell">Image</div>
                    <div class="jstable-cell">
                        <input type="file" id="playerImg" name="playerImg" accept="image/*"/>
                        <div class="moderPlayerImg">
                            <?php if ($ids) {
                                foreach ($ids as $key => $value) {
                                    $image = wp_get_attachment_image_src($value); ?>


                                    <input type="hidden" name="vdw_gallery_id[<?php echo intval($key); ?>]"
                                           value="<?php echo esc_attr($value); ?>">
                                    <img class="image-preview" src="<?php echo esc_url($image[0]); ?>">
                                    <small><a class="remove-image-md"
                                              href="#"><?php echo esc_attr(__('Remove Image', 'joomsport-sports-league-results-management')); ?></a></small>


                                    <?php
                                    break;
                                }
                            } ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
        <div class="jsmodnotice">
            <?php
            $results = JoomSportHelperObjects::getParticipiantSeasons($teamID);
            echo esc_html__('Select Season', 'joomsport-sports-league-results-management').'&nbsp;&nbsp;';
            if(!empty($results)){
                echo wp_kses(JoomSportHelperSelectBox::Optgroup('stb_season_id', $results, ''), JoomsportSettings::getKsesSelect());
                JoomSportMetaTeam::js_meta_players($teamPost);
            }else{
                echo '<div>'.esc_html__('Participant is not assigned to any season.', 'joomsport-sports-league-results-management').'</div>';
            }
            ?>
        </div>
        <div class="jsmodsave pull-right clearfix">
            <input type="submit" class="btn btn-success" value="<?php echo esc_attr(__('Save','joomsport-sports-league-results-management'));?>" />
            <input type="hidden" name="teamID" value="<?php echo esc_attr($teamID);?>" />
        </div>
    </form>
</div>
