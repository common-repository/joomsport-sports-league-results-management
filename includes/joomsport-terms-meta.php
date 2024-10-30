<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
class JoomsportTermsMeta {

    public static function getTermMeta($term_id, $meta_key = ''){
        $return = '';
        $joomsport_refactoring_v = (int) get_option("joomsport_refactoring_v", 0);
        if($joomsport_refactoring_v){
            if($meta_key){
                $return = get_term_meta($term_id,$meta_key,true);
            }else{
                $tmp = get_term_meta($term_id,$meta_key);

                if($tmp){
                    $return = array();
                    foreach ($tmp as $key=>$val){
                        $return[$key] = $val[0];
                    }
                    return $return;
                }

                return '';
            }

        }else{
            $metas = get_option("taxonomy_{$term_id}_metas");
            if($meta_key && isset($metas[$meta_key])){
                $return = $metas[$meta_key];
            }
            if(!$meta_key){
                $return = $metas;
            }
        }

        return $return;
    }

    public static function updateTermMeta($term_id, $term_metas){
        $joomsport_refactoring_v = (int) get_option("joomsport_refactoring_v", 0);
        if($joomsport_refactoring_v){
           if($term_metas && count($term_metas)){
                foreach ($term_metas as $key => $val){
                    update_term_meta($term_id, $key, $val);
                }
            }
        }else{
            update_option( "taxonomy_{$term_id}_metas", $term_metas );
        }
    }

    public static function getTerms($taxonomy, $taxParams = array("hide_empty" => false), $metaQuery = null){
        $joomsport_refactoring_v = (int) get_option("joomsport_refactoring_v", 0);
        $params = array(
            "taxonomy" => $taxonomy,
        );
        if(is_array($taxParams)){
            $params = array_merge($params, $taxParams);
        }

        $tx = null;

        if($metaQuery){

            if($joomsport_refactoring_v){
                $mqA = array();
                foreach ($metaQuery as $key=>$val){
                    $mqA[] = array(
                        'key'       => $key,
                        'value'     => $val,
                        'compare'   => '=');
                }
                $params['meta_query'] = $mqA;
                $tx = get_terms($params);
            }else{
                $tx = get_terms($params);

                $newTx = array();
                if(is_array($tx)) {
                    for ($intA = 0; $intA < count($tx); $intA++) {
                        //$metas = get_option("taxonomy_{$tx[$intA]->term_id}_metas");
                        $metas = JoomsportTermsMeta::getTermMeta($tx[$intA]->term_id);
                        foreach ($metaQuery as $key => $val) {
                            if (isset($metas[$key]) && $metas[$key] == $val) {

                            } else {
                                continue;
                            }
                        }
                        $newTx[] = $tx[$intA];

                    }
                }
                $tx = $newTx;
            }
        }else{
            $tx = get_terms($params);
        }



        return $tx;
    }

}