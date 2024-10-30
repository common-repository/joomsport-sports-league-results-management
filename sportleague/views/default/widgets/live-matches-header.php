<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */


echo '<div id="joomsport-container" class="modJSLiveMatches">';
    echo '<div class="modJSLiveMatchesFilters clearfix">';
        echo '<div class="clearfix modJSLiveFields">';
            echo '<div class="col-xs-12 col-sm-6">';
                echo '<select name="modJSLiveMatchesFiltersSelect" id="modJSLiveMatchesFiltersSelect">';
                    echo '<option value="">'.esc_html__('All','joomsport-sports-league-results-management').'</option>';
                    echo '<option value="0">'.esc_html__('Fixtures','joomsport-sports-league-results-management').'</option>';
                    echo '<option value="1">'.esc_html__('Played','joomsport-sports-league-results-management').'</option>';
                    echo '<option value="-1">'.esc_html__('Live','joomsport-sports-league-results-management').'</option>';
                echo '</select>';
            echo '</div>';
            echo '<div class="col-xs-12 col-sm-6">';
                echo '<div class="modJSLiveInputGroup input-group">';
                    echo '<div class="input-group-btn"><button id="modJSLiveMatchesPrev" class="modJSCalendarBtn btn btn-default"><span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span></button></div>';
                    echo '<input type="text" class="jsdatefield hasDatepickerr" value="'.esc_attr(gmdate("Y-m-d")).'" id="mod_filter_date" name="mod_filter_date" onChange="chngFilterLiveMatches(this.value);" />';
                    echo '<div class="input-group-btn"><button id="modJSLiveMatchesNext" class="modJSCalendarBtn btn btn-default"><span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span></button></div>';
                echo '</div>';
            echo '</div>';
        echo '</div>';
        echo '<div class="col-xs-12">';
            echo '<ul class="modJSLiveMatchesTabUL">';
                echo '<li id="modJSLiveMatchesTabAll" class="activeTab">'.esc_html__('All','joomsport-sports-league-results-management').' <span id="modJsAllMatchCounter"></span></li>';
                echo '<li id="modJSLiveMatchesTabFav">'.esc_html__('Favourites','joomsport-sports-league-results-management').' <span id="modJsFavMatchCounter">0</span></li>';
            echo '</ul>';
        echo '</div>';
    echo '</div>';
    echo '<div id="modJSLiveMatchesContainer" class="clearfix">';
    require 'live-matches.php';
    echo '</div>';
    echo '<input type="hidden" name="show_emblems" id="show_emblems" value="'.$args["emblems"].'">';
    echo '<input type="hidden" name="show_sport" id="show_sport" value="'.esc_attr($args["sport"]).'">';
echo '</div>';