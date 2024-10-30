<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */

?>
<td class="jsMatchDate">
    <?php echo esc_html($match_date);?>
</td>
<td class="jsMatchTeamLogo">
    <?php
    if(is_object($LMpartic)){
        echo wp_kses_post($LMpartic->getEmblem());
    }
    ?>
</td>
<td class="jsMatchTeamName">
    <?php
    if(is_object($LMpartic)){
        echo  wp_kses_post(jsHelper::nameHTML($LMpartic->getName(true)));
    }
    ?>
</td>
<td class="jsMatchPlayedStatus">
    <?php $formArr = jsHelper::JsFormViewElement($lMatch, $partic_home->object->ID);
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
    ?>
</td>
<td class="jsMatchPlayedScore">
    <?php echo jsHelper::getScore($lMatch, '');?>
</td>
<td class="jsMatchPlace">
    <?php echo $lMatch->opposite?'<i class="fa fa-home" title="Home" aria-hidden="true"></i>':'<i class="fa fa-plane" title="Away" aria-hidden="true"></i>';?>
</td>