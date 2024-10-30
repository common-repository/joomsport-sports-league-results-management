<?php
?>
<div>
    <form action="" name="formPlayerEditFE" id="formPlayerEditFE" autocomplete="off" method="post" enctype="multipart/form-data">

        <?php
        JoomSportMetaPlayer::js_meta_personal($playerPost);
        //JoomSportMetaTeam::js_meta_about($thisPost);
        JoomSportMetaPlayer::js_meta_ef($playerPost);
        $ids = get_post_meta($playerID, 'vdw_gallery_id', true);
        if(JoomsportSettings::get('upload_player_img')) {
            ?>
            <div class="jsminwdhtd jstable">
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

        <div class="jsmodsave pull-right clearfix">
            <input type="submit" class="btn btn-success" value="<?php echo esc_attr(__('Save','joomsport-sports-league-results-management'));?>" />
            <input type="hidden" name="playerID" value="<?php echo esc_attr($playerID)?>" />
        </div>
    </form>
</div>
