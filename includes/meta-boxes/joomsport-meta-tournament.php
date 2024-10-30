<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
class JoomSportMetaTournament {
    
    public static function joomsport_tournament_edit_form_fields($term_obj){
        global $wpdb;
        $sportID = get_term_meta($term_obj->term_id,'sports_id', true);
        echo '<tr>';
            echo '<th>'.esc_html__('Sport', 'joomsport-sports-league-results-management').'</th>';
            echo '<td>';
            $tpls = $wpdb->get_results('SELECT sportID as id, sportName as name FROM '.$wpdb->joomsport_sports.' ORDER BY sportName', 'OBJECT') ;


            echo '<select name="sportID"  class="form-control">';

            if(count($tpls)){
                foreach ($tpls as $tm) {
                    $selected = '';
                    if($sportID == $tm->id){
                        $selected = ' selected';
                    }
                    echo  '<option value="'.esc_attr($tm->id).'" '.esc_attr($selected).'>'.esc_html($tm->name).'</option>';
                }
            }
            echo  '</select>';

            echo '</td>';
        echo '</tr>';
    }
    public static function joomsport_tournament_add_form_fields($term_id){
        global $wpdb;
        ?>
        <div class="form-field form-required">

            <label for="season_id"><?php echo esc_html__('Sport', 'joomsport-sports-league-results-management'); ?></label>
            <?php
            $tpls = $wpdb->get_results('SELECT sportID as id, sportName as name FROM '.$wpdb->joomsport_sports.' ORDER BY sportName', 'OBJECT') ;


            echo '<select name="sportID"  class="form-control">';

            if(count($tpls)){
                foreach ($tpls as $tm) {
                    $selected = '';
                    if(1 == $tm->id){
                        $selected = ' selected';
                    }
                    echo  '<option value="'.esc_attr($tm->id).'" '.esc_attr($selected).'>'.esc_html($tm->name).'</option>';
                }
            }
            echo  '</select>';
            ?>

        </div>

        <?php
    }
    public static function joomsport_tournament_save_form_fields($term_id){
        
        if(!isset($_POST['tag_ID']) || !intval($_POST['tag_ID'])){
            $meta_value = JoomsportSettings::get('tournament_type');
            $term_metas = JoomsportTermsMeta::getTermMeta($term_id);
            if (!is_array($term_metas)) {
                $term_metas = Array();
            }
            // Save the meta value
            $term_metas['t_single'] = $meta_value;
            JoomsportTermsMeta::updateTermMeta($term_id, $term_metas);

        }

        if(isset($_POST['sportID']) && intval($_POST['sportID'])){
            update_term_meta($term_id,"sports_id", intval($_POST['sportID']));
        }
        
    }
    public static function tournament_type_columns( $taxonomies ) {
        $new_columns = array(
        'cb' => '<input type="checkbox" />',
        'name' => __('Name'),
        'header_icon' => '',
        't_single' => __('League type', 'joomsport-sports-league-results-management'),
        'posts' => __('Posts')
        );

        return $new_columns;    
    }

 
    public static function manage_joomsport_tournament_columns($out, $column_name, $tax_id) {

        $t_single = JoomsportTermsMeta::getTermMeta($tax_id, 't_single');

        switch ($column_name) {
            case 't_single': 

                $out .= $t_single ? __('Single','joomsport-sports-league-results-management') : __('Team','joomsport-sports-league-results-management');
                break;

            default:
                break;
        }
        return $out;    
    }
}