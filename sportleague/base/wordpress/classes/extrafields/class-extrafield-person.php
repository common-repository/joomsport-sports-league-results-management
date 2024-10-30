<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
class classExtrafieldPerson
{
    public static function getValue($ef)
    {
        if(intval($ef)){
            return classJsportLink::person(get_the_title($ef), $ef);
            
        }
    }
}
