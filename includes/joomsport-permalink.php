<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */



add_filter('the_title', 'jоomsport_filter_seasontitle', 999, 2);
function jоomsport_filter_seasontitle($title, $id = null) {
    global $post_type, $post, $pagenow;

    if($pagenow == 'nav-menus.php'){
        $tpost  = get_post($id);
        if($tpost->post_type == 'joomsport_season'){
            $terms = wp_get_object_terms( $id, 'joomsport_tournament' );
            $post_name = '';
            if( $terms ){

                $post_name .= $terms[0]->name;
            }
            $post_name .= " ".$title;

            return $post_name;
        }
    }
    if(!$post){
        return $title;
    }

    if ( !in_the_loop() ) return $title;

    if($title != $post->post_title){
        return $title;
    }
    if($id != $post->ID){
        return $title;
    }
    if($post_type == 'joomsport_season'){
        $terms = wp_get_object_terms( $post->ID, 'joomsport_tournament' );
        $post_name = '';
        if( $terms ){

            $post_name .= $terms[0]->name;
        }
        $post_name .= " ".$title;

        return $post_name;
    }
    return $title;
}

add_filter( 'document_title_parts', function( $title_parts_array ) {
    global $post_type, $post;

    if(!$post){
        return $title_parts_array;
    }
    if($post_type == 'joomsport_season'){
        $terms = wp_get_object_terms( $post->ID, 'joomsport_tournament' );
        $post_name = '';
        if( $terms ){

            $post_name .= $terms[0]->name;
        }
        $title_parts_array['title'] =  $post_name ." ".$title_parts_array['title'];
    }
    /*if($post_type == 'joomsport_match'){
        $m_played = get_post_meta( $post->ID, '_joomsport_match_played', true );
        $m_date = get_post_meta($post->ID, '_joomsport_match_date', true);
        $home = get_post_meta($post->ID, '_joomsport_home_team', true);
        $away = get_post_meta($post->ID, '_joomsport_away_team', true);
        remove_filter( 'the_title', 'jоomsport_filter_pro_matchtitle' );

        $newDateString = '';
        if($m_date){
            try {
                $myDateTime = DateTime::createFromFormat('Y-m-d', $m_date);
                if($myDateTime) {
                    $newDateString = $myDateTime->format('d-m-Y');
                }
            }catch (Exception $e){

            }
        }

        $new_title = get_post( $home )->post_title." - ".get_post( $away )->post_title;
        if($m_played == 1){
            $score1 = get_post_meta($post->ID, '_joomsport_home_score', true);
            $score2 = get_post_meta($post->ID, '_joomsport_away_score', true);

            $new_title.= " (".$score1."-".$score2.") ";
            if($newDateString) {
                $new_title .= " | " . $newDateString;
            }

        }else if($newDateString){
            $new_title .= " | " . $newDateString;
        }


        $title_parts_array['title'] =  $new_title;
    }*/
    return $title_parts_array;
} );

add_filter( 'pre_get_document_title', function( $title )
  {
    global $post_type, $post;
    if(!$title){
        return '';
    }
    if(!$post){
        return $title;
    }
    if($post_type == 'joomsport_season'){
        $terms = wp_get_object_terms( $post->ID, 'joomsport_tournament' );
        $post_name = '';
        if( $terms ){

            $post_name .= $terms[0]->name;
        }
        $title =  $post_name ." ".$title;
    }

    return $title;
  }, 999, 1 );




add_filter('post_thumbnail_html', 'joomsport_filter_pt',99,5);
function joomsport_filter_pt($html, $post_id, $post_thumbnail_id, $size, $attr) {
    global $post_type;
    if ( !in_the_loop() ){return $html;};
    $width = JoomsportSettings::get('set_emblemhgonmatch', 60);
    if($post_type == 'joomsport_team'){
        $src = wp_get_attachment_image_src(get_post_thumbnail_id(), array($width,'0'));
        $html = '<img src="' . esc_attr($src['0']) . '" width="'.$width.'" />';

        if(JoomsportSettings::get('enabl_team_featimg', 1) == 0){
            $html = '';
        }
    }
    return $html;
}

add_filter( 'has_post_thumbnail', 'joomsport_filter_has_team_thumb', 10, 3 );
function joomsport_filter_has_team_thumb( $has_thumbnail, $post, $thumbnail_id ){
    global $post_type;
    if ( !in_the_loop() ){return $has_thumbnail;};
    if($post_type == 'joomsport_team'){
        if(JoomsportSettings::get('enabl_team_featimg', 1) == 0){
            return false;
        }
    }

    return $has_thumbnail;
}

