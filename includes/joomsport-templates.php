<?php

/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */

class JoomsportTemplates {
    
    public static function init() {
        add_action( 'parse_request', array('JoomsportTemplates', 'joomsport_parse_request') );
        add_filter( 'the_content', array( 'JoomsportTemplates', 'joomsport_content' ) );
        add_filter('template_include',array( 'JoomsportTemplates', 'override_tax_template'));
    }

    public static function override_tax_template($template){
        // is a specific custom taxonomy being shown?
        $taxonomy_array = array('joomsport_club');
        foreach ($taxonomy_array as $taxonomy_single) {
            if ( is_tax($taxonomy_single) ) {
                if(file_exists(JOOMSPORT_PATH . 'templates' .DIRECTORY_SEPARATOR. 'taxonomy-'.$taxonomy_single.'.php')) {
                    $template = JOOMSPORT_PATH . 'templates' .DIRECTORY_SEPARATOR. 'taxonomy-'.$taxonomy_single.'.php';

                }

                break;
            }
        }

        return $template;
    }

    public static function joomsport_parse_request( &$wp )
        {
            if (isset($_REQUEST['wpjoomsport'])) {
                include JOOMSPORT_PATH. 'templates'.DIRECTORY_SEPARATOR.'single_1.php';
                exit();
            }

            return;
        }
        
    public static function joomsport_content($content){
        if($content) return $content;
        if ( !in_the_loop() ) return $content;
        global $controllerSportLeague;
        remove_filter('has_post_thumbnail', "joomsport_filter_has_team_thumb");
        remove_filter('post_thumbnail_html', 'joomsport_filter_pt');
        if(is_singular('joomsport_team')
                || is_singular('joomsport_season')
                || is_singular('joomsport_venue')
                || is_singular('joomsport_match')
                || is_singular('joomsport_player')
                || is_singular('joomsport_person')
                || isset($_REQUEST['wpjoomsport'])
                ){

            require JOOMSPORT_PATH . 'sportleague' . DIRECTORY_SEPARATOR . 'sportleague.php';

            if ( post_password_required() ) {
                echo get_the_password_form();
                return;
            }
            ob_start();
            $controllerSportLeague->execute();
            return ob_get_clean();
            
        }
        return $content;
    }   

}


JoomsportTemplates::init();
