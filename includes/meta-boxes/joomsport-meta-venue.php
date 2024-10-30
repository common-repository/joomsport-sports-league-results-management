<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
class JoomSportMetaVenue {
    public static function output( $post ) {
        global $post, $thepostid, $wp_meta_boxes;
        
        
        $thepostid = $post->ID;
        require_once JOOMSPORT_PATH_HELPERS . 'tabs.php';
        $etabs = new esTabs();
        wp_nonce_field( 'joomsport_venue_savemetaboxes', 'joomsport_venue_nonce' );
        ?>
        <div id="joomsportContainerBE">
            <div class="jsBEsettings" style="padding:0px;">
                <!-- <tab box> -->
                <ul class="tab-box">
                    <?php
                    echo wp_kses_post($etabs->newTab(__('Main','joomsport-sports-league-results-management'), 'main_conf', '', 'vis'));

                    do_action("joomsport_custom_tab_be_head", $thepostid, $etabs);
                    ?>
                </ul>	
                <div style="clear:both"></div>
            </div>
            <div id="main_conf_div" class="tabdiv">
                <div>
                    <div>
                    <?php
                    do_meta_boxes(get_current_screen(), 'joomsportintab_venue1', $post);
                    unset($wp_meta_boxes[get_post_type($post)]['joomsportintab_venue1']);
                    
                    do_action("joomsport_custom_tab_be_head", $thepostid, $etabs);
                    ?>
                    </div>    
                </div>
            </div> 
            <?php
            do_action("joomsport_custom_tab_be_body", $thepostid, $etabs);
            ?>
        </div>
        

        <?php
    }
        
        
    public static function js_meta_personal($post){

        $metadata = get_post_meta($post->ID,'_joomsport_venue_personal',true);

        ?>
        <div class="jstable jsminwdhtd">
            <div class="jstable-row">
                <div class="jstable-cell">
                    <?php echo esc_html__('Venue address', 'joomsport-sports-league-results-management');?>
                </div>
                <div class="jstable-cell">
                    <input type="text" name="personal[venue_addr]" value="<?php echo isset($metadata['venue_addr'])?esc_attr($metadata['venue_addr']):""?>" />
                </div>
            </div>
            <div class="jstable-row">
                <div class="jstable-cell">
                    <?php echo esc_html__('Latitude', 'joomsport-sports-league-results-management');?>
                </div>
                <div class="jstable-cell">
                    <input type="number" step=any name="personal[latitude]" value="<?php echo isset($metadata['latitude'])?esc_attr($metadata['latitude']):""?>" />
                </div>
            </div>
            <div class="jstable-row">
                <div class="jstable-cell">
                    <?php echo esc_html__('Longitude', 'joomsport-sports-league-results-management');?>
                </div>
                <div class="jstable-cell">
                    <input type="number" step=any name="personal[longitude]" value="<?php echo isset($metadata['longitude'])?esc_attr($metadata['longitude']):""?>" />
                </div>
            </div>
        </div> 
        <?php
    }
    public static function js_meta_about($post){

        $metadata = get_post_meta($post->ID,'_joomsport_venue_about',true);
        wp_editor($metadata, 'about',array("textarea_rows"=>3));


    }
    public static function js_meta_copyright($post){

        $metadata = get_post_meta($post->ID,'_joomsport_venue_copyright',true);
        wp_editor($metadata, 'image_copyright',array("textarea_rows"=>3));


    }
    
    public static function js_meta_ef($post){

        $metadata = get_post_meta($post->ID,'_joomsport_venue_ef',true);
        
        $efields = JoomSportHelperEF::getEFList('5', 0);

        if(count($efields)){
            echo '<div class="jsminwdhtd jstable">';
            foreach ($efields as $ef) {

                JoomSportHelperEF::getEFInput($ef, isset($metadata[$ef->id])?$metadata[$ef->id]:null);
                //var_dump($ef);
                ?>
                
                <div class="jstable-row">
                    <div class="jstable-cell"><?php echo esc_html($ef->name)?></div>
                    <div class="jstable-cell">
                        <?php 
                        if($ef->field_type == '2'){
                            wp_editor(isset($metadata[$ef->id])?$metadata[$ef->id]:'', 'ef_'.$ef->id,array("textarea_rows"=>3));
                            echo '<input type="hidden" name="ef['.esc_attr($ef->id).']" value="ef_'.esc_attr($ef->id).'" />';
                        }else{
                            echo wp_kses($ef->edit,JoomsportSettings::getKsesEFEdit());
                        }
                        ?>
                    </div>    
                        
                </div>    
                <?php
            }
            echo '</div>';
        }else{
            $link = get_admin_url(get_current_blog_id(), 'admin.php?page=joomsport-page-extrafields');
             printf( esc_html__( 'There are no extra fields assigned to this section. Create new one on %s Extra fields list %s', 'joomsport-sports-league-results-management' ), '<a href="'.esc_url($link).'">','</a>' );

        }

    }

    public static function joomsport_venue_save_metabox($post_id, $post){
        // Add nonce for security and authentication.
        $nonce_name   = isset( $_POST['joomsport_venue_nonce'] ) ? sanitize_text_field(wp_unslash($_POST['joomsport_venue_nonce'])) : '';
        $nonce_action = 'joomsport_venue_savemetaboxes';
 
        // Check if nonce is set.
        if ( ! isset( $nonce_name ) ) {
            return;
        }
 
        // Check if nonce is valid.
        if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
            return;
        }
 
        // Check if user has permissions to save data.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
 
        // Check if not an autosave.
        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }
 
        // Check if not a revision.
        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }
        $pstTYpe = isset($_POST['post_type'])?sanitize_text_field(wp_unslash($_POST['post_type'])):'';
        if('joomsport_venue' == $pstTYpe ){
            self::saveMetaPersonal($post_id);
            self::saveMetaAbout($post_id);
            self::saveMetaCopyright($post_id);
            self::saveMetaEF($post_id);
            do_action("joomsport_custom_tab_be_save", $post_id);
        }
    }
    
    private static function saveMetaPersonal($post_id){
        $nonce_name   = isset( $_POST['joomsport_venue_nonce'] ) ? sanitize_text_field(wp_unslash($_POST['joomsport_venue_nonce'])) : '';
        $nonce_action = 'joomsport_venue_savemetaboxes';

        // Check if nonce is set.
        if ( ! isset( $nonce_name ) ) {
            return;
        }

        // Check if nonce is valid.
        if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
            return;
        }
        $meta_array = array();
        $meta_array = array_map( 'sanitize_text_field', isset($_POST['personal'])?wp_unslash( $_POST['personal'] ):array() );
        update_post_meta($post_id, '_joomsport_venue_personal', $meta_array);
    }
    private static function saveMetaAbout($post_id){
        $nonce_name   = isset( $_POST['joomsport_venue_nonce'] ) ? sanitize_text_field(wp_unslash($_POST['joomsport_venue_nonce'])) : '';
        $nonce_action = 'joomsport_venue_savemetaboxes';

        // Check if nonce is set.
        if ( ! isset( $nonce_name ) ) {
            return;
        }

        // Check if nonce is valid.
        if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
            return;
        }
        $meta_data = isset($_POST['about'])?  wp_kses_post(wp_unslash($_POST['about'])):'';
        update_post_meta($post_id, '_joomsport_venue_about', $meta_data);
    }
    private static function saveMetaCopyright($post_id){
        $nonce_name   = isset( $_POST['joomsport_venue_nonce'] ) ? sanitize_text_field(wp_unslash($_POST['joomsport_venue_nonce'])) : '';
        $nonce_action = 'joomsport_venue_savemetaboxes';

        // Check if nonce is set.
        if ( ! isset( $nonce_name ) ) {
            return;
        }

        // Check if nonce is valid.
        if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
            return;
        }
        $meta_data = isset($_POST['image_copyright'])?  wp_kses_post(wp_unslash($_POST['image_copyright'])):'';
        if($meta_data){
            update_post_meta($post_id, '_joomsport_venue_copyright', $meta_data);
        }else{
            delete_post_meta($post_id, '_joomsport_venue_copyright');
        }


    }
    private static function saveMetaEF($post_id){
        $nonce_name   = isset( $_POST['joomsport_venue_nonce'] ) ? sanitize_text_field(wp_unslash($_POST['joomsport_venue_nonce'])) : '';
        $nonce_action = 'joomsport_venue_savemetaboxes';

        // Check if nonce is set.
        if ( ! isset( $nonce_name ) ) {
            return;
        }

        // Check if nonce is valid.
        if ( ! wp_verify_nonce( $nonce_name, $nonce_action ) ) {
            return;
        }
        $meta_array = array();
        $ef = isset($_POST['ef'])?array_map("sanitize_text_field", wp_unslash($_POST['ef'])):array();
        if($ef && count($ef)){
            foreach ($ef as $key => $value){
                if(isset($_POST['ef_'.$key])){
                    $meta_array[$key] = sanitize_text_field(wp_unslash($_POST['ef_'.$key]));
                }else{
                    $meta_array[$key] = sanitize_text_field($value);
                }
            }
        }
        //$meta_data = serialize($meta_array);
        update_post_meta($post_id, '_joomsport_venue_ef', $meta_array);
    }
}