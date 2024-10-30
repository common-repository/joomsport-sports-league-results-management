<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */

require_once JOOMSPORT_PATH_INCLUDES . 'pages' . DIRECTORY_SEPARATOR . 'joomsport-page-stages.php';
require_once JOOMSPORT_PATH_INCLUDES . 'pages' . DIRECTORY_SEPARATOR . 'joomsport-page-extrafields.php';
require_once JOOMSPORT_PATH_INCLUDES . 'pages' . DIRECTORY_SEPARATOR . 'joomsport-page-events.php';
require_once JOOMSPORT_PATH_INCLUDES . 'pages' . DIRECTORY_SEPARATOR . 'joomsport-page-settings.php';
require_once JOOMSPORT_PATH_INCLUDES . 'pages' . DIRECTORY_SEPARATOR . 'joomsport-page-help.php';
require_once JOOMSPORT_PATH_INCLUDES . 'moderator' . DIRECTORY_SEPARATOR . 'joomsport-moder-mday.php';
require_once JOOMSPORT_PATH_INCLUDES . 'pages' . DIRECTORY_SEPARATOR . 'joomsport-page-boxfields.php';

require_once JOOMSPORT_PATH_INCLUDES . 'pages' . DIRECTORY_SEPARATOR . 'joomsport-page-generator.php';

require_once JOOMSPORT_PATH_INCLUDES . 'pages' . DIRECTORY_SEPARATOR . 'joomsport-page-import.php';

require_once JOOMSPORT_PATH_INCLUDES . 'pages' . DIRECTORY_SEPARATOR . 'joomsport-page-sports.php';


class JoomSportAdminInstall {

  public static function init(){

    self::joomsport_languages();
    add_action( 'admin_menu', array('JoomSportAdminInstall', 'create_menu') );

    self::_defineTables();
    add_action( 'admin_enqueue_scripts', array( 'JoomSportAdminInstall', 'thickbox' ) );


  }
  public static function thickbox() {
        add_thickbox();
  }


  public static function create_menu() {

    add_menu_page( __('JoomSport', 'joomsport-sports-league-results-management'), __('JoomSport', 'joomsport-sports-league-results-management'),
      'manage_options', 'joomsport', array('JoomSportAdminInstall', 'action'),
      plugins_url( '../assets/images/cup.png', __FILE__ ) );
    add_submenu_page( 'joomsport', __('Settings', 'joomsport-sports-league-results-management'), __('Settings', 'joomsport-sports-league-results-management'),
      'manage_options', 'joomsport_settings', array('JoomsportPageSettings', 'action') );
    add_submenu_page( 'joomsport', __( 'Leagues', 'joomsport-sports-league-results-management' ), __( 'Leagues', 'joomsport-sports-league-results-management' ), 'manage_options', 'edit-tags.php?taxonomy=joomsport_tournament&post_type=joomsport_season');
    add_submenu_page( 'joomsport', __( 'Person categories', 'joomsport-sports-league-results-management' ), __( 'Person categories', 'joomsport-sports-league-results-management' ), 'manage_options', 'edit-tags.php?taxonomy=joomsport_personcategory&post_type=joomsport_person');

    if(current_user_can('manage_options')){
      add_submenu_page( 'joomsport', __( 'Matchday', 'joomsport-sports-league-results-management' ), __( 'Matchdays', 'joomsport-sports-league-results-management' ), 'manage_options', 'edit-tags.php?taxonomy=joomsport_matchday&post_type=joomsport_match');
    }
    if(JoomsportSettings::get('enbl_club')){
      add_submenu_page( 'joomsport', __( 'Club', 'joomsport-sports-league-results-management' ), __( 'Clubs', 'joomsport-sports-league-results-management' ), 'manage_options', 'edit-tags.php?taxonomy=joomsport_club&post_type=joomsport_team',false);
    }

    $obj = JoomSportStages_Plugin::get_instance();
    $hook = add_submenu_page( 'joomsport', __( 'Game stage', 'joomsport-sports-league-results-management' ), __( 'Game stages', 'joomsport-sports-league-results-management' ), 'manage_options', 'joomsport-page-gamestages', function(){ $obj = JoomSportStages_Plugin::get_instance();$obj->plugin_settings_page();});

    add_action( "load-$hook", function(){ $obj = JoomSportStages_Plugin::get_instance();$obj->screen_option();}  );

    add_submenu_page( 'options.php', __( 'Game stage New', 'joomsport-sports-league-results-management' ), __( 'Game stages New', 'joomsport-sports-league-results-management' ), 'manage_options', 'joomsport-gamestages-form', array('JoomSportStagesNew_Plugin', 'view'));

    $obj = JoomSportExtraField_Plugin::get_instance();
    $hook = add_submenu_page( 'joomsport', __( 'Extra field', 'joomsport-sports-league-results-management' ), __( 'Extra fields', 'joomsport-sports-league-results-management' ), 'manage_options', 'joomsport-page-extrafields', function(){ $obj = JoomSportExtraField_Plugin::get_instance();$obj->plugin_settings_page();});

    add_action( "load-$hook", function(){ $obj = JoomSportExtraField_Plugin::get_instance();$obj->screen_option();}  );

    add_submenu_page( 'options.php', __( 'Extra field New', 'joomsport-sports-league-results-management' ), __( 'Extra field New', 'joomsport-sports-league-results-management' ), 'manage_options', 'joomsport-extrafields-form', array('JoomSportExtraFieldsNew_Plugin', 'view'));

    $obj = JoomSportBoxField_Plugin::get_instance();
    $hook = add_submenu_page( 'joomsport', __( 'Box score stats', 'joomsport-sports-league-results-management' ), __( 'Box score stats', 'joomsport-sports-league-results-management' ), 'manage_options', 'joomsport-page-boxfields', function(){ $obj = JoomSportBoxField_Plugin::get_instance();$obj->plugin_settings_page();});

    add_action( "load-$hook", function(){ $obj = JoomSportBoxField_Plugin::get_instance();$obj->screen_option();}  );

    add_submenu_page( 'options.php', __( 'Box score record', 'joomsport-sports-league-results-management' ), __( 'Box score record', 'joomsport-sports-league-results-management' ), 'manage_options', 'joomsport-boxfields-form', array('JoomSportBoxFieldsNew_Plugin', 'view'));


    $obj = JoomSportEvents_Plugin::get_instance();
    $hook = add_submenu_page( 'joomsport', __( 'Events stats', 'joomsport-sports-league-results-management' ), __( 'Events stats', 'joomsport-sports-league-results-management' ), 'manage_options', 'joomsport-page-events', function(){ $obj = JoomSportEvents_Plugin::get_instance();$obj->plugin_settings_page();});

    add_action( "load-$hook", function(){ $obj = JoomSportEvents_Plugin::get_instance();$obj->screen_option();} );

    add_submenu_page( 'options.php', __( 'Event New', 'joomsport-sports-league-results-management' ), __( 'Event New', 'joomsport-sports-league-results-management' ), 'manage_options', 'joomsport-events-form', array('JoomSportEventsNew_Plugin', 'view'));


      $hook = add_submenu_page( 'joomsport', __( 'Sports', 'joomsport-sports-league-results-management' ), __( 'Sports', 'joomsport-sports-league-results-management' ), 'manage_options', 'joomsport-page-sports', function(){ $obj = JoomSportSports_Plugin::get_instance();$obj->plugin_settings_page();});

      add_action( "load-$hook", function(){ $obj = JoomSportSports_Plugin::get_instance();$obj->screen_option();} );

      add_submenu_page( 'options.php', __( 'Sport New', 'joomsport-sports-league-results-management' ), __( 'Sport New', 'joomsport-sports-league-results-management' ), 'manage_options', 'joomsport-sports-form', array('JoomSportSportsNew_Plugin', 'view'));


      add_submenu_page( 'joomsport', __('Help', 'joomsport-sports-league-results-management'), __('Help', 'joomsport-sports-league-results-management'),
          'manage_options', 'joomsport_help', array('JoomsportPageHelp', 'action') );

        /*
         * Add CSV upload
         */
        add_submenu_page( 'joomsport', __('Import', 'joomsport-sports-league-results-management'), __('Import', 'joomsport-sports-league-results-management'),
          'manage_options', 'joomsport_import', array('JoomsportPageImport', 'action') );

        JoomSportUserRights::jsp_add_theme_caps();
        JoomSportUserRights::loadModerCapabilities();
        
        
        add_submenu_page( 'options.php', __( 'Match generator', 'joomsport-sports-league-results-management' ), __( 'Match generator', 'joomsport-sports-league-results-management' ), 'manage_options', 'joomsport-match-generator', array('JoomsportPageGenerator', 'action'));
        
        // javascript
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-uidp-style', plugins_url('../assets/css/jquery-ui.css', __FILE__));
        wp_enqueue_script( 'joomsport-admin-nav-js', plugins_url('../assets/js/admin_nav.js', __FILE__) );
        wp_enqueue_style( 'joomsport-admin-nav-css', plugins_url('../assets/css/admin_nav.css', __FILE__) );
        add_action('admin_enqueue_scripts', array('JoomSportAdminInstall', 'joomsport_admin_js'));
        add_action('admin_enqueue_scripts', array('JoomSportAdminInstall', 'joomsport_admin_css'));
        
        wp_enqueue_style('jscssfont',plugins_url('../assets/css/font-awesome.min.css', __FILE__));
        
      }

      public static function joomsport_fe_wp_head(){
        global $post,$post_type;
        $jsArray = array("joomsport_season","joomsport_match","joomsport_team","joomsport_match","joomsport_player","joomsport_venue","joomsport_person");
        if(in_array($post_type, $jsArray) || isset($_REQUEST['wpjoomsport']) || get_query_var('joomsport_tournament') || get_query_var('joomsport_matchday') || get_query_var('joomsport_club')){

            wp_register_script( 'popper-js', plugins_url('../assets/js/popper.min.js', __FILE__), ['jquery'], NULL, true );
            wp_enqueue_script( 'popper-js' );
            wp_enqueue_script('jsbootstrap-js',plugins_url('../assets/js/bootstrap.min.js', __FILE__),array ( 'jquery', 'jquery-ui-tooltip', 'popper-js' ));
         wp_enqueue_script('jsnailthumb',plugin_dir_url( __FILE__ ).'../sportleague/assets/js/jquery.nailthumb.1.1.js');
         wp_enqueue_script('jstablesorter',plugin_dir_url( __FILE__ ).'../sportleague/assets/js/jquery.tablesorter.min.js');
         wp_enqueue_script('jsselect2',plugin_dir_url( __FILE__ ).'../sportleague/assets/js/select2.min.js');
         wp_enqueue_script('jsjoomsport',plugin_dir_url( __FILE__ ).'../sportleague/assets/js/joomsport.js');

         wp_enqueue_style('jscssbtstrp',plugin_dir_url( __FILE__ ).'../sportleague/assets/css/btstrp.css');
         wp_enqueue_style('jscssjoomsport',plugin_dir_url( __FILE__ ).'../sportleague/assets/css/joomsport.css');
         if (is_rtl()) {
           wp_enqueue_style( 'jscssjoomsport-rtl',plugin_dir_url( __FILE__ ).'../sportleague/assets/css/joomsport-rtl.css' );
         }
         wp_enqueue_style('jscssbracket',plugin_dir_url( __FILE__ ).'../sportleague/assets/css/drawBracket.css');
         wp_enqueue_style('jscssnailthumb',plugin_dir_url( __FILE__ ).'../sportleague/assets/css/jquery.nailthumb.1.1.css');
         wp_enqueue_style('jscsslightbox',plugin_dir_url( __FILE__ ).'../sportleague/assets/css/lightbox.css');
         wp_enqueue_style('jscssselect2',plugin_dir_url( __FILE__ ).'../sportleague/assets/css/select2.min.css');
            wp_enqueue_style('jscssfont',plugins_url('../assets/css/font-awesome.min.css', __FILE__));
         wp_enqueue_script('jquery-ui');
         wp_enqueue_script('jquery-ui-tooltip');
         wp_enqueue_script('jquery-ui-datepicker');
         wp_enqueue_style('jquery-uidp-style', plugins_url('../assets/css/jquery-ui.css', __FILE__));
       }
     }

     public static function action(){

     }


     public static function joomsport_admin_js(){
      global $post_type;
      wp_enqueue_script( 'joomsport-common-js', plugins_url('../assets/js/common.js', __FILE__), array('jquery', 'jquery-ui-sortable') );
      wp_localize_script('joomsport-common-js', 'jslrmObj', array("jnonce" => wp_create_nonce("joomsportajaxnonce")));

      wp_enqueue_script( 'joomsport-jchosen-js', plugins_url('../assets/js/chosen.jquery.min.js', __FILE__),array('jquery') );
      wp_enqueue_script( 'joomsport-jchosen-order-js', plugins_url('../assets/js/chosen.order.jquery.min.js', __FILE__),array('jquery') );
      
      wp_enqueue_media();
      if($post_type == 'joomsport_season'){
        wp_enqueue_script( 'joomsport-colorgrid-js', plugins_url('../includes/3d/color_piker/201a.js', __FILE__) );
      }
    }
    
    public static function joomsport_admin_css(){
      global $post_type;
      $post_type_array = array('joomsport_team','joomsport_season','joomsport_player','joomsport_match','joomsport_venue');
      if (in_array($post_type,$post_type_array)) :
        wp_enqueue_style( 'joomsport-customdash-css', plugins_url('../assets/css/customdash.css', __FILE__) );
      endif;
      if($post_type == 'joomsport_season'){
        wp_enqueue_style( 'joomsport-colorgrid-css', plugins_url('../includes/3d/color_piker/style.css', __FILE__) );
      }
      wp_enqueue_style( 'joomsport-common-css', plugins_url('../assets/css/common.css', __FILE__) );
      wp_enqueue_style( 'joomsport-jchosen-css', plugins_url('../assets/css/chosen.min.css', __FILE__) );

    }
    
    public static function _defineTables()
    {
      global $wpdb;
      $wpdb->joomsport_config = $wpdb->prefix . 'joomsport_config';
      $wpdb->joomsport_maps = $wpdb->prefix . 'joomsport_maps';
      $wpdb->joomsport_ef = $wpdb->prefix . 'joomsport_extra_fields';
      $wpdb->joomsport_ef_select = $wpdb->prefix . 'joomsport_extra_select';
      $wpdb->joomsport_events = $wpdb->prefix . 'joomsport_events';
      $wpdb->joomsport_seasons = $wpdb->prefix . 'joomsport_seasons';
      $wpdb->joomsport_match_statuses = $wpdb->prefix . 'joomsport_match_statuses';
      $wpdb->joomsport_groups = $wpdb->prefix . 'joomsport_groups';
      $wpdb->joomsport_season_table = $wpdb->prefix . 'joomsport_season_table';
      $wpdb->joomsport_playerlist = $wpdb->prefix . 'joomsport_playerlist';
      $wpdb->joomsport_match_events = $wpdb->prefix . 'joomsport_match_events';
      $wpdb->joomsport_squad = $wpdb->prefix . 'joomsport_squad';
      $wpdb->joomsport_box = $wpdb->prefix . 'joomsport_box_fields';
      $wpdb->joomsport_box_match = $wpdb->prefix . 'joomsport_box_match';
      $wpdb->joomsport_events_depending = $wpdb->prefix . 'joomsport_events_depending';
      $wpdb->joomsport_matches = $wpdb->prefix . 'joomsport_matches';
      $wpdb->joomsport_teamstats = $wpdb->prefix . 'joomsport_teamstats';
      $wpdb->joomsport_teamplayers = $wpdb->prefix . 'joomsport_teamplayers';
      $wpdb->joomsport_match_events_addit = $wpdb->prefix . 'joomsport_match_events_addit';

      $wpdb->joomsport_sports_template = $wpdb->prefix . 'joomsport_sports_template';
      $wpdb->joomsport_sports = $wpdb->prefix . 'joomsport_sports';
    }

    public static function _installdb(){
      global $wpdb;

      flush_rewrite_rules();
      update_option( 'joomsport_flush_rewrite_rules', 'yes' );

        self::_defineTables();

      include_once( ABSPATH.'/wp-admin/includes/upgrade.php' );

      $charset_collate = '';
      if ( $wpdb->has_cap( 'collation' ) ) {
        if ( ! empty($wpdb->charset) )
          $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if ( ! empty($wpdb->collate) )
          $charset_collate .= " COLLATE $wpdb->collate";
      }


      $create_config_sql = "CREATE TABLE {$wpdb->joomsport_config} (
      `id` smallint NOT NULL AUTO_INCREMENT ,
      `cName` varchar( 100 ) NOT NULL default '',
      `cValue` longtext NOT NULL,
      PRIMARY KEY ( `id` )) $charset_collate;";
      maybe_create_table( $wpdb->joomsport_config, $create_config_sql );

      if(!$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->joomsport_config}")){
        $wpdb->insert($wpdb->joomsport_config,array('cName' => 'general'),array("%s"));
        $wpdb->insert($wpdb->joomsport_config,array('cName' => 'player_reg'),array("%s"));
        $wpdb->insert($wpdb->joomsport_config,array('cName' => 'team_moder'),array("%s"));
        $wpdb->insert($wpdb->joomsport_config,array('cName' => 'season_admin'),array("%s"));
        $wpdb->insert($wpdb->joomsport_config,array('cName' => 'layouts'),array("%s"));
        $wpdb->insert($wpdb->joomsport_config,array('cName' => 'other'),array("%s"));
      }

      $create_config_sql = "CREATE TABLE {$wpdb->joomsport_maps} (
      `id` smallint NOT NULL AUTO_INCREMENT ,
      `m_name` varchar( 100 ) NOT NULL default '',
      `map_descr` longtext NOT NULL,
      PRIMARY KEY ( `id` )) $charset_collate;";
      maybe_create_table( $wpdb->joomsport_maps, $create_config_sql );

      $create_ef_sql = "CREATE TABLE {$wpdb->joomsport_ef} (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL DEFAULT '',
      `published` char(1) NOT NULL DEFAULT '1',
      `type` char(1) NOT NULL DEFAULT '0',
      `ordering` int(11) NOT NULL DEFAULT '0',
      `e_table_view` char(1) NOT NULL DEFAULT '0',
      `field_type` char(1) NOT NULL DEFAULT '0',
      `reg_exist` char(1) NOT NULL DEFAULT '0',
      `reg_require` char(1) NOT NULL DEFAULT '0',
      `fdisplay` char(1) NOT NULL DEFAULT '1',
      `season_related` varchar(1) NOT NULL DEFAULT '0',
      `faccess` varchar(1) NOT NULL DEFAULT '0',
      `display_playerlist` varchar(1) NOT NULL DEFAULT '0',
      PRIMARY KEY ( `id` )) $charset_collate;";
      maybe_create_table( $wpdb->joomsport_ef, $create_ef_sql );
      $is_col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->joomsport_ef} LIKE 'options'");

      if (empty($is_col)) {
        $wpdb->query('ALTER TABLE '.$wpdb->joomsport_ef.' ADD `options` TEXT NULL DEFAULT NULL');
      }

      $create_ef_select_sql = "CREATE TABLE {$wpdb->joomsport_ef_select} (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `fid` int(11) NOT NULL default '0',
      `sel_value` varchar(255) NOT NULL default '',
      `eordering` int(11) NOT NULL default '0',
      PRIMARY KEY  (`id`),
      KEY `fid` (`fid`)) $charset_collate;";
      maybe_create_table( $wpdb->joomsport_ef_select, $create_ef_select_sql );

      $create_events_sql = "CREATE TABLE {$wpdb->joomsport_events} (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `e_name` varchar(255) NOT NULL default '',
      `e_img` varchar(255) NOT NULL default '',
      `player_event` char(1) NOT NULL default '0',
      `result_type` VARCHAR( 1 ) NOT NULL DEFAULT  '0',
      `sumev1` INT NOT NULL,
      `sumev2` INT NOT NULL,
      `ordering` INT NOT NULL,
      PRIMARY KEY  (`id`)) $charset_collate;";
      maybe_create_table( $wpdb->joomsport_events, $create_events_sql );

      $create_season_sql = "CREATE TABLE {$wpdb->joomsport_seasons} (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `post_id` int(11) NOT NULL,
      `season_options` longtext,
      `s_descr` text NOT NULL,
      `s_rules` text NOT NULL,
      `season_columns` text NOT NULL,
      PRIMARY KEY  (`id`),
      UNIQUE KEY (`post_id`)) $charset_collate;";
      maybe_create_table( $wpdb->joomsport_seasons, $create_season_sql );

      $create_match_statuses_sql = "CREATE TABLE {$wpdb->joomsport_match_statuses} (
      `id` int(11) NOT NULL auto_increment,
      `stName` varchar(100) NOT NULL,
      `stShort` varchar(20) NOT NULL,
      `ordering` tinyint(4) NOT NULL,
      PRIMARY KEY  (`id`)) $charset_collate AUTO_INCREMENT=2;";
      maybe_create_table( $wpdb->joomsport_match_statuses, $create_match_statuses_sql );

      $create_groups_sql = "CREATE TABLE {$wpdb->joomsport_groups} (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `s_id` int(11) NOT NULL,
      `group_name` varchar(255) NOT NULL DEFAULT '',
      `group_partic` text NOT NULL,
      `ordering` int(11) NOT NULL,
      PRIMARY KEY  (`id`)) $charset_collate;";
      maybe_create_table( $wpdb->joomsport_groups, $create_groups_sql );
      $is_col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->joomsport_groups} LIKE 'options'");

      if (empty($is_col)) {
        $wpdb->query('ALTER TABLE '.$wpdb->joomsport_groups.' ADD `options` TEXT NOT NULL');
      }

      $create_season_table_sql = "CREATE TABLE {$wpdb->joomsport_season_table} (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `season_id` int NOT NULL,
      `group_id` int NOT NULL,
      `participant_id` int NOT NULL,
      `options` text NOT NULL,
      `ordering` tinyint NOT NULL,
      PRIMARY KEY  (`id`),
      UNIQUE KEY `season` (`season_id`,`group_id`,`ordering`)) $charset_collate;";
      maybe_create_table( $wpdb->joomsport_season_table, $create_season_table_sql );

      $create_playerlist_sql = "CREATE TABLE {$wpdb->joomsport_playerlist} (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `player_id` int(11) NOT NULL,
      `season_id` int(11) NOT NULL,
      `team_id` int(11) NOT NULL,
      `played` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY  (`id`),
      UNIQUE KEY `player_id` (`player_id`,`season_id`,`team_id`)) $charset_collate;";
      maybe_create_table( $wpdb->joomsport_playerlist, $create_playerlist_sql );

      $create_matchevents_sql = "CREATE TABLE {$wpdb->joomsport_match_events} (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `e_id` int(11) NOT NULL default '0',
      `player_id` int(11) NOT NULL default '0',
      `match_id` int(11) NOT NULL default '0',
      `season_id` int(11) NOT NULL default '0',
      `ecount`  TINYINT NOT NULL default '0',
      `minutes` varchar(20) NOT NULL default '',
      `t_id` int(11) NOT NULL default '0',
      `eordering`  TINYINT NOT NULL,
      PRIMARY KEY  (`id`),
      KEY `player_id` (`player_id`,`match_id`,`t_id`)) $charset_collate;";
      maybe_create_table( $wpdb->joomsport_match_events, $create_matchevents_sql );

      $create_squad_sql = "CREATE TABLE {$wpdb->joomsport_squad} (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `player_id` int(11) NOT NULL default '0',
      `team_id` int(11) NOT NULL default '0',
      `match_id` int(11) NOT NULL default '0',
      `season_id` int(11) NOT NULL default '0',
      `is_subs`  varchar(2) NOT NULL default '0',
      `squad_type`  varchar(1) NOT NULL default '0',
      `minutes` varchar(20) NOT NULL default '',
      `player_subs` int(11) NOT NULL default '0',
      `ordering`  TINYINT NOT NULL,
      PRIMARY KEY  (`id`)) $charset_collate;";
      maybe_create_table( $wpdb->joomsport_squad, $create_squad_sql );

      $create_box_sql = "CREATE TABLE {$wpdb->joomsport_box} (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL,
      `complex` varchar(1) NOT NULL,
      `parent_id` int(11) NOT NULL,
      `ftype` varchar(1) NOT NULL,
      `published` varchar(1) NOT NULL DEFAULT '1',
      `options` text NOT NULL,
      `ordering` smallint(6) NOT NULL,
      `displayonfe` varchar(1) NOT NULL DEFAULT '1',
      PRIMARY KEY ( `id` )) $charset_collate;";
      maybe_create_table( $wpdb->joomsport_box, $create_box_sql );

      $create_boxmatch_sql = "CREATE TABLE {$wpdb->joomsport_box_match} (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `player_id` int(11) NOT NULL default '0',
      `team_id` int(11) NOT NULL default '0',
      `match_id` int(11) NOT NULL default '0',
      `season_id` int(11) NOT NULL default '0',
      PRIMARY KEY ( `id` )) $charset_collate;";
      maybe_create_table( $wpdb->joomsport_box_match, $create_boxmatch_sql );


        //add columns to playerlist
      $is_col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->joomsport_playerlist} LIKE 'career_lineup'");

      if (empty($is_col)) {
        $wpdb->query("ALTER TABLE ".$wpdb->joomsport_playerlist." ADD `career_lineup` SMALLINT NOT NULL DEFAULT '0' , ADD `career_minutes` SMALLINT NOT NULL DEFAULT '0' , ADD `career_subsin` SMALLINT NOT NULL DEFAULT '0' , ADD `career_subsout` SMALLINT NOT NULL DEFAULT '0'");
        
        $wpdb->query("UPDATE {$wpdb->joomsport_squad} SET is_subs = '-1' WHERE is_subs='1' AND squad_type='2'");
        $wpdb->query("UPDATE {$wpdb->joomsport_squad} SET is_subs = '1' WHERE is_subs='-1' AND squad_type='1'");

      }

        //add columns to events
      $is_col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->joomsport_events} LIKE 'events_sum'");

      if (empty($is_col)) {
        $wpdb->query("ALTER TABLE ".$wpdb->joomsport_events." ADD `events_sum` VARCHAR(1) NOT NULL DEFAULT '0' , ADD `subevents` TEXT NOT NULL DEFAULT '' ");

        $sumev = $wpdb->get_results("SELECT * FROM {$wpdb->joomsport_events} WHERE player_event = '2'");
        for($intA=0;$intA<count($sumev);$intA++){
          $evs = array($sumev[$intA]->sumev1,$sumev[$intA]->sumev2);
          $wpdb->query($wpdb->prepare("UPDATE {$wpdb->joomsport_events} SET events_sum = '1', player_event = '1',subevents=%s WHERE id = %d",wp_json_encode($evs),$sumev[$intA]->id));

        }


      }
        //add minutes string field
      $is_col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->joomsport_match_events} LIKE 'minutes_input'");

      if (empty($is_col)) {
        $wpdb->query("ALTER TABLE ".$wpdb->joomsport_match_events." ADD `minutes_input` VARCHAR(20) NULL DEFAULT NULL");
      }
      
      //event depending
      $is_col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->joomsport_match_events} LIKE 'additional_to'");
      if (empty($is_col)) {
            $wpdb->query("ALTER TABLE {$wpdb->joomsport_match_events} ADD `additional_to` int(11) NOT NULL DEFAULT '0'");
            
        }
      $is_col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->joomsport_events} LIKE 'dependson'");
      if (empty($is_col)) {
            $wpdb->query("ALTER TABLE {$wpdb->joomsport_events} ADD `dependson` VARCHAR(100) NOT NULL DEFAULT ''");
            
        }
        
       
        
     $create_depending_sql = "CREATE TABLE {$wpdb->joomsport_events_depending} (
      `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `event_id` int(11) NOT NULL,
                    `subevent_id` int(11) NOT NULL
                  ) $charset_collate;";
      maybe_create_table( $wpdb->joomsport_events_depending, $create_depending_sql );

        $create_joomsport_matches_sql = "CREATE TABLE {$wpdb->joomsport_matches} (
                    `postID` int NOT NULL,
                    `mdID` int(11) NOT NULL,
                    `seasonID` int(11) NOT NULL,
                    `teamHomeID` int(11) NOT NULL,
                    `teamAwayID` int(11) NOT NULL,
                    `groupID` int(11) NOT NULL,
                    `status` int(11) NOT NULL,
                    `date` date NOT NULL,
                    `time` varchar(10) NOT NULL,
                    `scoreHome` decimal(10,2) NOT NULL,
                    `scoreAway` decimal(10,2) NOT NULL,
                    `post_status` varchar(20) NOT NULL DEFAULT 'publish',
                    `duration` int(11) NOT NULL DEFAULT 0,	
                    PRIMARY KEY  (`postID`),
                    KEY `seasonID` (`seasonID`,`date`,`time`),
                    KEY `teamHomeID` (`teamHomeID`,`seasonID`),
                    KEY `teamAwayID` (`teamAwayID`,`seasonID`)
                  ) $charset_collate;";
        maybe_create_table( $wpdb->joomsport_matches, $create_joomsport_matches_sql );
        try{
            $exist = $wpdb->get_var("SELECT COUNT(1) IndexIsThere FROM INFORMATION_SCHEMA.STATISTICS"
                    ." WHERE table_schema=DATABASE() AND table_name='".$wpdb->joomsport_squad."' AND index_name='matchID';");
            if(!$exist){
                $wpdb->query("CREATE INDEX `matchID` ON ".$wpdb->joomsport_squad." (`match_id`)");
            }
            $exist = $wpdb->get_var("SELECT COUNT(1) IndexIsThere FROM INFORMATION_SCHEMA.STATISTICS"
                ." WHERE table_schema=DATABASE() AND table_name='".$wpdb->joomsport_squad."' AND index_name='playerID';");
            if(!$exist){
                $wpdb->query("CREATE INDEX `playerID` ON ".$wpdb->joomsport_squad." (`player_id`)");
            }

        }catch(Exception $e){

        }

        //stages
        $is_col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->joomsport_maps} LIKE 'separate_events'");
        if (empty($is_col)) {
            $wpdb->query("ALTER TABLE {$wpdb->joomsport_maps} ADD `separate_events` VARCHAR(1) NOT NULL DEFAULT '0', ADD `time_from` TINYINT NOT NULL DEFAULT 0, ADD `time_to` TINYINT NOT NULL DEFAULT 0");

        }

        $is_col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->joomsport_match_events} LIKE 'stage_id'");
        if (empty($is_col)) {
            $wpdb->query("ALTER TABLE {$wpdb->joomsport_match_events} ADD `stage_id` INT(11) NOT NULL DEFAULT 0");

        }

        $is_col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}terms LIKE 'term_order'");

        if (empty($is_col)) {
            $wpdb->query( "ALTER TABLE {$wpdb->prefix}terms ADD `term_order` INT (11) NOT NULL DEFAULT 0" );
        }

        $create_teamstat_sql = "CREATE TABLE {$wpdb->joomsport_teamstats} (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `seasonID` int(11) NOT NULL,
                  `partID` int(11) NOT NULL,
                  `eventID` int(11) NOT NULL,
                  `sumVal` float NOT NULL,
                  `avgVal` float NOT NULL,
                  PRIMARY KEY ( `id` ),
                  KEY `seasonID` (`seasonID`)
                  ) $charset_collate;";
        maybe_create_table( $wpdb->joomsport_teamstats, $create_teamstat_sql );

        //departed option
        $is_col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->joomsport_playerlist} LIKE 'departed'");

        if (empty($is_col)) {
            $wpdb->query('ALTER TABLE '.$wpdb->joomsport_playerlist.' ADD `departed` VARCHAR(1) NOT NULL DEFAULT "0"');
        }


        $create_joomsport_teamplayers_sql = "CREATE TABLE {$wpdb->joomsport_teamplayers} (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `seasonID` int(11) NOT NULL,
                  `teamID` int(11) NOT NULL,
                  `playerID` int(11) NOT NULL,
                  `departed` varchar(1) NOT NULL DEFAULT '0',
                  PRIMARY KEY ( `id` ),
                  UNIQUE KEY `playerID` (`playerID`,`seasonID`,`teamID`)
                  ) $charset_collate;";
        maybe_create_table( $wpdb->joomsport_teamplayers, $create_joomsport_teamplayers_sql );

        //curform cache
        $is_col = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->joomsport_season_table} LIKE 'curForm'");

        if (empty($is_col)) {
            $wpdb->query('ALTER TABLE '.$wpdb->joomsport_season_table.' ADD `curForm` text DEFAULT NULL');
        }

        try{
            $exist = $wpdb->get_var("SELECT COUNT(1) IndexIsThere FROM INFORMATION_SCHEMA.STATISTICS"
                ." WHERE table_schema=DATABASE() AND table_name='".$wpdb->joomsport_groups."' AND index_name='s_id';");
            if(!$exist){
                $wpdb->query("CREATE INDEX `s_id` ON ".$wpdb->joomsport_groups." (`s_id`)");
            }

            $exist = $wpdb->get_var("SELECT COUNT(1) IndexIsThere FROM INFORMATION_SCHEMA.STATISTICS"
                ." WHERE table_schema=DATABASE() AND table_name='".$wpdb->joomsport_squad."' AND index_name='season_id';");
            if(!$exist){
                $wpdb->query("CREATE INDEX `season_id` ON ".$wpdb->joomsport_squad." (`season_id`,`squad_type`,`team_id`,`player_id`)");
            }

            $exist = $wpdb->get_var("SELECT COUNT(1) IndexIsThere FROM INFORMATION_SCHEMA.STATISTICS"
                ." WHERE table_schema=DATABASE() AND table_name='".$wpdb->joomsport_events."' AND index_name='player_event';");
            if(!$exist){
                $wpdb->query("CREATE INDEX `player_event` ON ".$wpdb->joomsport_events." (`player_event`,`ordering`)");
            }


        }catch(Exception $e){

        }

        $create_mevaddit_sql = "CREATE TABLE {$wpdb->joomsport_match_events_addit} (
                  `id` int NOT NULL AUTO_INCREMENT,
                  `e_id` smallint NOT NULL,
                  `player_id` int NOT NULL,
                  `ecount` smallint NOT NULL DEFAULT '1',
                  `eordering` tinyint UNSIGNED NOT NULL DEFAULT '0',
                  `statoriumAPI` int DEFAULT NULL,
                  `parent_event` int NOT NULL,
                  PRIMARY KEY ( `id` ),
                  KEY `parent_event` (`parent_event`),
                  KEY `e_id` (`e_id`)
                  ) $charset_collate;";
        maybe_create_table( $wpdb->joomsport_match_events_addit, $create_mevaddit_sql );

        $sports_template_sql = "CREATE TABLE {$wpdb->joomsport_sports_template} (
                  `sportTemplateID` smallint UNSIGNED NOT NULL AUTO_INCREMENT,
                  `sportTemplateName` varchar(200) NOT NULL,
                  `sportTemplateClass` varchar(200) NOT NULL,
                  PRIMARY KEY ( `sportTemplateID` )
                  ) $charset_collate;";
        maybe_create_table( $wpdb->joomsport_sports_template, $sports_template_sql );
        if(!$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->joomsport_sports_template}")) {
            $wpdb->query("INSERT INTO {$wpdb->joomsport_sports_template} (`sportTemplateID`, `sportTemplateName`, `sportTemplateClass`) VALUES(1, 'Default', 'JoomSportSportDefaultTmpl')");
        }

        $sports_sql = "CREATE TABLE {$wpdb->joomsport_sports} (
                  `sportID` int UNSIGNED NOT NULL AUTO_INCREMENT,
                  `sportName` varchar(150) NOT NULL,
                  `sportTemplateID` smallint UNSIGNED NOT NULL,
                  `ordering` smallint UNSIGNED NOT NULL DEFAULT '0',
                  `image` int DEFAULT NULL,
                  PRIMARY KEY ( `sportID` )
                  ) $charset_collate;";
        maybe_create_table( $wpdb->joomsport_sports, $sports_sql );
        if(!$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->joomsport_sports}")) {
            $wpdb->query("INSERT INTO {$wpdb->joomsport_sports} (`sportID`, `sportName`, `sportTemplateID`, `ordering`, `image`) VALUES ('1', 'Default', '1', '0', NULL)");

            $termsTourn = $wpdb->get_results(
                "SELECT tags.*
                FROM
                    {$wpdb->prefix}terms tags
                    INNER JOIN {$wpdb->prefix}term_taxonomy tags_tax ON (tags_tax.term_id = tags.term_id)
                    
                WHERE
                    tags_tax.taxonomy = 'joomsport_tournament'"
            );


            if($termsTourn && count($termsTourn)) {
                for ($intA = 0; $intA < count($termsTourn); $intA++) {
                    update_term_meta($termsTourn[$intA]->term_id, 'sports_id', 1);
                }
            }

            $wpdb->query("ALTER TABLE {$wpdb->joomsport_events} ADD `sportID` INT NOT NULL DEFAULT '1'");
        }


        $joomsport_refactoring_v = (int) get_option("joomsport_refactoring_v", 0);
        if(!$joomsport_refactoring_v){
            joomsportUpgradeRef::upgradeTermMetas();
            joomsportUpgradeRef::upgradeMatchDuration();
            joomsportUpgradeRef::upgradeEvents();
            update_option("joomsport_refactoring_v", 1);
        }

        $res = jsHelperMatchesDB::checkMatchesSeason();
        if($res == 0){
            jsHelperMatchesDB::pullAllMatches();
        }


    }
    
    public static function joomsport_languages() {
      $locale = apply_filters( 'plugin_locale', get_locale(), 'joomsport-sports-league-results-management' );

      load_textdomain( 'joomsport-sports-league-results-management', plugin_basename( dirname( __FILE__ ) . "/../languages/joomsport-sports-league-results-management-$locale.mo" ));
      load_plugin_textdomain( 'joomsport-sports-league-results-management', false, plugin_basename( dirname( __FILE__ ) . "/../languages" ) );
    }
  }

  add_action( 'init', array( 'JoomSportAdminInstall', 'init' ), 4);
  add_action( 'wp_enqueue_scripts', array('JoomSportAdminInstall','joomsport_fe_wp_head') );
  add_filter( 'custom_menu_order', 'wpsejs_joomsport_submenu_order' );

  function wpsejs_joomsport_submenu_order( $menu_ord ) 
  {
    global $submenu;

    $sort_array = array(
      __('Leagues','joomsport-sports-league-results-management'),
      _x( 'Seasons', 'Admin menu name Seasons', 'joomsport-sports-league-results-management' ),
      __('Matchdays','joomsport-sports-league-results-management'),

      __('Clubs','joomsport-sports-league-results-management'),
      _x('Teams','Admin menu name Teams','joomsport-sports-league-results-management'),
      _x('Players','Admin menu name Players','joomsport-sports-league-results-management'),
      _x('Venues','Admin menu name Venues','joomsport-sports-league-results-management'),
        _x( 'Persons', 'Admin menu name Players', 'joomsport-sports-league-results-management' ),
      __('Import','joomsport-sports-league-results-management'),
        __('Sports','joomsport-sports-league-results-management'),
      __('Events stats','joomsport-sports-league-results-management'),
      __('Box score stats','joomsport-sports-league-results-management'),
      __('Person categories','joomsport-sports-league-results-management'),
      __('Game stages','joomsport-sports-league-results-management'),
      __('Extra fields','joomsport-sports-league-results-management'),
      __('Settings','joomsport-sports-league-results-management'),
      __('Help','joomsport-sports-league-results-management'),
      );

    $arr = array();
    if(count($sort_array)){
      foreach ($sort_array as $sarr) {
        if(isset($submenu['joomsport']) && count($submenu['joomsport'])){
          foreach ($submenu['joomsport'] as $sub) {
            if($sub[0] == $sarr){
              $arr[] = $sub;
            }
          }
        }
      }
    }
    
    $submenu['joomsport'] = $arr;

    return $menu_ord;
  }

  function jsmatch_hide_that_stuff() {
    if('joomsport_match' == get_post_type()){
      echo '<style type="text/css">
          #favorite-actions {display:none;}
      .add-new-h2{display:none;}
      .tablenav{display:none;}
      .page-title-action{display:none;}
    </style>';
  }elseif('joomsport_season' == get_post_type()){
    if(!wp_count_terms('joomsport_tournament')){
      $txt = addslashes(sprintf(__('League required to create Season. Let\'s %s add league %s first.','joomsport-sports-league-results-management'),'<a href="'.(get_admin_url(get_current_blog_id(), 'edit-tags.php?taxonomy=joomsport_tournament')).'">','</a>'));
      echo '<script>jQuery( document ).ready(function() {jQuery(".wrap").html("<div class=\'jswarningbox\'><p>'.esc_js($txt).'</p></div>");});</script>';
    }

  }
}
function joomsport_setup_theme() {
  if ( ! current_theme_supports( 'post-thumbnails' ) ) {
    add_theme_support( 'post-thumbnails' );
  }

        // Add image sizes
  add_image_size( 'joomsport-thmb-medium',  310, 'auto', false );
  //add_image_size( 'joomsport-thmb-mini',  60, 'auto', false );
}
add_action('admin_head', 'jsmatch_hide_that_stuff');
add_action( 'after_setup_theme', 'joomsport_setup_theme' );

if(!function_exists('joomsport_set_current_menu')){

  function joomsport_set_current_menu($parent_file){
    global $submenu_file, $current_screen, $pagenow, $plugin_page;

    $ptypes = array("joomsport_team","joomsport_season","joomsport_match");
        // Set the submenu as active/current while anywhere in your Custom Post Type (nwcm_news)
    if(in_array($current_screen->post_type,$ptypes)) {

      if($pagenow == 'post.php'){
        if($current_screen->post_type == 'joomsport_match'){

          $submenu_file = 'edit-tags.php?taxonomy=joomsport_matchday&post_type='.$current_screen->post_type;

        }else{
          $submenu_file = 'edit.php?post_type='.$current_screen->post_type;
        }
      }

      if($pagenow == 'edit-tags.php' || $pagenow == 'term.php'){
        switch ($current_screen->post_type) {
          case 'joomsport_season':
          $submenu_file = 'edit-tags.php?taxonomy=joomsport_tournament&post_type='.$current_screen->post_type;


          break;
          case 'joomsport_team':
          $submenu_file = 'edit-tags.php?taxonomy=joomsport_club&post_type='.$current_screen->post_type;


          break;
          case 'joomsport_match':
          $submenu_file = 'edit-tags.php?taxonomy=joomsport_matchday&post_type='.$current_screen->post_type;


          break;

          default:
          break;
        }
      }

      $parent_file = 'joomsport';

    }
    if($current_screen->id == 'admin_page_joomsport-events-form'){
      $parent_file = 'joomsport';
      $submenu_file = 'joomsport-page-events';
      $plugin_page = 'joomsport-page-events';
    }
    if($current_screen->id == 'admin_page_joomsport-boxfields-form'){
      $parent_file = 'joomsport';
      $submenu_file = 'joomsport-page-boxfields';
      $plugin_page = 'joomsport-page-boxfields';
    }
    if($current_screen->id == 'admin_page_joomsport-gamestages-form'){
      $parent_file = 'joomsport';
      $submenu_file = 'joomsport-page-gamestages';
      $plugin_page = 'joomsport-page-gamestages';
    }
    if($current_screen->id == 'admin_page_joomsport-extrafields-form'){
      $parent_file = 'joomsport';
      $submenu_file = 'joomsport-page-extrafields';
      $plugin_page = 'joomsport-page-extrafields';
    }

    if($current_screen->id == 'edit-joomsport_personcategory'){
      $parent_file = 'joomsport';
      $submenu_file = 'edit-tags.php?taxonomy=joomsport_personcategory&post_type=joomsport_person';
      $plugin_page = 'edit-tags.php?taxonomy=joomsport_personcategory&post_type=joomsport_person';
    }

      if($current_screen->id == 'admin_page_joomsport-sports-form') {
          $parent_file = 'joomsport';
          $submenu_file = 'joomsport-page-sports';
          $plugin_page = 'joomsport-page-sports';
      }

    return $parent_file;

  }

  add_filter('parent_file', 'joomsport_set_current_menu',10,1);

}
add_action('init', 'joomsport_myStartSessionJS', 1);
function joomsport_myStartSessionJS() {
  if(!session_id()) {
    @session_start(
        array('read_and_close' => true)
    );
  }
}

function joomsport_custom_wpkses_post_tags( $tags, $context ) {
    global $post,$post_type;
    $jsArray = array("joomsport_season","joomsport_match","joomsport_team","joomsport_match","joomsport_player","joomsport_venue","joomsport_person");
        
	if ( in_array($post_type, $jsArray) && $context === 'post') {
		$tags['iframe'] = array(
			'src'             => true,
			'height'          => true,
			'width'           => true,
			'frameborder'     => true,
			'allowfullscreen' => true,
		);
	}
	return $tags;
}
add_filter( 'wp_kses_allowed_html', 'joomsport_custom_wpkses_post_tags', 10, 2 );


function joomsport_enable_plugin_auto_updates( $value, $item ) {
    if ( 'joomsport-sports-league-results-management' === $item->slug ) {
        update_option( 'joomsport_flush_rewrite_rules', 'yes' );
    }

    return $value;
}
add_filter( 'auto_update_plugin', 'joomsport_enable_plugin_auto_updates', 10, 2 );