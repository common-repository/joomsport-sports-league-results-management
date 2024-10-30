<?php
/**
 * WP-JoomSport
 * @author      BearDev
 * @package     JoomSport
 */
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class JoomSportSports_List_Table extends WP_List_Table {

    public function __construct() {

        parent::__construct( array(
                'singular' => __( 'Sports', 'joomsport-sports-league-results-management' ),
                'plural'   => __( 'Sports list', 'joomsport-sports-league-results-management' ),
                'ajax'     => false 

        ) );

        /** Process bulk action */
        $this->process_bulk_action();

    }

    public static function get_stages( $per_page = 5, $page_number = 1 ) {

        global $wpdb;
        
        
        $sql = "SELECT * FROM {$wpdb->joomsport_sports}";

        if ( ! empty( $_REQUEST['orderby'] ) ) {
          //$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
          //$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
          $sql .= ' ORDER BY ' . sanitize_sql_orderby( "{$_REQUEST['orderby']} {$_REQUEST['order']}" );
        }else{
            $sql .= ' ORDER BY ordering';
        }

        $sql .= " LIMIT %d";

        $sql .= ' OFFSET %d' ;

//echo $sql;die();
        $offs = ( $page_number - 1 ) * $per_page;
        $result = $wpdb->get_results( $wpdb->prepare($sql, $per_page, $offs), 'ARRAY_A' );

        return $result;
    }
    public static function delete_sport( $id ) {
        global $wpdb;

        $wpdb->delete(
          "{$wpdb->joomsport_sports}",
          array('sportID' => $id ),
          array( '%d' )
        );
    }
    public static function record_count() {
        global $wpdb;
        return $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->joomsport_sports}" );
    }
    public function no_items() {
        echo esc_html__( 'No sports available.', 'joomsport-sports-league-results-management' );
    }
    function column_name( $item ) {

        // create a nonce
        $delete_nonce = wp_create_nonce( 'joomsport_delete_sport' );

        $title = '<strong><a href="'.get_admin_url(get_current_blog_id(), 'admin.php?page=joomsport-sports-form&id='.absint( $item['sportID'] )).'">' . $item['sportName'] . '</a></strong>';

        $actions = array(
          'delete' => sprintf( '<a href="?page=%s&action=%s&sport=%s&_wpnonce=%s" class="wpjsDeleteConfirm">Delete</a>', (isset($_REQUEST['page'])?( sanitize_text_field(wp_unslash($_REQUEST['page'])) ):''), 'delete', absint( $item['sportID'] ), $delete_nonce )
        );

        return $title . $this->row_actions( $actions );
    }
    
    function column_cb( $item ) {
        return sprintf(
          '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['sportID']
        );
    }
    function get_columns() {
        $columns = array(
          'cb'      => '<input type="checkbox" />',
          'name'    => __( 'Name', 'joomsport-sports-league-results-management' ),
          'sport_tmpl'    => __( 'Sport Template', 'joomsport-sports-league-results-management' ),
        );

        return $columns;
    }
    function column_default($item, $column_name){
        switch($column_name){

            case 'sport_tmpl':
                return $item["sportTemplateID"];

            default:
                return print_r($item,true); //Show the whole array for troubleshooting purposes
        }
    }
    public function get_sortable_columns() {
        $sortable_columns = array(
          'name' => array( 'sportName', true )
        );

        return $sortable_columns;
    }
    public function get_bulk_actions() {
        $actions = array(
          'bulk-delete' => 'Delete'
        );

        return $actions;
    }
    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();

        

        $per_page     = $this->get_items_per_page( 'jssports_per_page', 5 );
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args( array(
          'total_items' => $total_items, //WE have to calculate the total number of items
          'per_page'    => $per_page //WE have to determine how many items to show on a page
        ) );


        $this->items = self::get_stages( $per_page, $current_page );
    }
    public function process_bulk_action() {
        global $wpdb;
        //Detect when a bulk action is being triggered...
        if ( 'delete' === $this->current_action() ) {
          // In our file that handles the request, verify the nonce.
          $nonce = isset($_REQUEST['_wpnonce'])?esc_attr( sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])) ):'';

          if ( ! wp_verify_nonce( $nonce, 'joomsport_delete_sport' ) ) {
            die( 'Error' );
          }
          else {
              if(isset($_GET['sport']) && absint( $_GET['sport'] ) !== 1){
                  self::delete_sport( absint( $_GET['sport'] ) );
                  $wpdb->query("UPDATE {$wpdb->termmeta} SET meta_value='1' WHERE meta_key='sport_id' AND meta_value=".absint( $_GET['sport']));
              }

            wp_redirect( esc_url(get_dashboard_url(). 'admin.php?page=joomsport-page-sports' ) );
            exit;
          }

        }

        // If the delete bulk action is triggered
        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
             || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
        ) {

          $delete_ids = isset( $_POST['bulk-delete'] )?array_map('absint',wp_unslash($_POST['bulk-delete'])):array();

          // loop over the array of record IDs and delete them
          foreach ( $delete_ids as $id ) {
              if($id !== 1) {
                  self::delete_sport($id);
                  $wpdb->query("UPDATE {$wpdb->termmeta} SET meta_value='1' WHERE meta_key='sport_id' AND meta_value=".absint( $id));
              }
          }

          wp_redirect( esc_url(get_dashboard_url(). 'admin.php?page=joomsport-page-sports' ) );
          exit;
        }
    }
    
}


class JoomSportSports_Plugin {

	// class instance
	static $instance;

	// customer WP_List_Table object
	public $customers_obj;

	// class constructor
	public function __construct() {
		add_filter( 'set-screen-option', array( __CLASS__, 'set_screen' ), 10, 3 );
		//add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
	}


	public static function set_screen( $status, $option, $value ) {
		return $value;
	}


	/**
	 * Plugin settings page
	 */
	public function plugin_settings_page() {
		?>
		<div class="wrap">
			<h2><?php echo esc_html__('Sport', 'joomsport-sports-league-results-management');?>
                        <a class="add-new-h2"
                                 href="<?php echo esc_url(get_admin_url(get_current_blog_id(), 'admin.php?page=joomsport-sports-form'));?>"><?php echo esc_html__('Add new', 'joomsport-sports-league-results-management')?></a>
                        </h2>

			<div id="poststuff">
				<div id="post-body" class="metabox-holder">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->customers_obj->prepare_items();
								$this->customers_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
                    <script type="text/javascript" id="UR_initiator"> (function () { var iid = 'uriid_'+(new Date().getTime())+'_'+Math.floor((Math.random()*100)+1); if (!document._fpu_) document.getElementById('UR_initiator').setAttribute('id', iid); var bsa = document.createElement('script'); bsa.type = 'text/javascript'; bsa.async = true; bsa.src = '//beardev.useresponse.com/sdk/supportCenter.js?initid='+iid+'&wid=6'; (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(bsa); })(); </script>
		</div>
	<?php
	}

	/**
	 * Screen options
	 */
	public function screen_option() {
	    $mscr = isset($_POST['wp_screen_options']['option'])?intval($_POST['wp_screen_options']['option']):0;
            if($mscr){
                update_user_meta(get_current_user_id(), 'jssports_per_page', $mscr);

            }
		$option = 'per_page';
		$args   = array(
			'label'   => 'Sportss',
			'default' => 5,
			'option'  => 'jssports_per_page'
		);

		add_screen_option( $option, $args );

		$this->customers_obj = new JoomSportSports_List_Table();
	}


	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}

class JoomSportSportsNew_Plugin {
    public static function view(){

        global $wpdb;
        $table_name = $wpdb->joomsport_sports;

        $message = '';
        $notice = '';

        // this is default $item which will be used for new records
        $default = array(
            'sportID' => 0,
            'sportName' => '',
            'sportTemplateID' => 1,
            'ordering' => 0,
            'image' => '',
        );
        
        $item = array();
        // here we are verifying does this request is post back and have correct nonce
        if (isset($_REQUEST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), basename(__FILE__))) {
            // combine our default item with request params
            $item = shortcode_atts($default, array_map( 'sanitize_text_field', wp_unslash( $_REQUEST )));
            $lists = self::getListValues($item);
            // validate data, and if all ok save item to database
            // if id is zero insert otherwise update
            $item_valid = self::joomsport_sports_validate($item);

            if ($item_valid === true) {
                if ($item['sportID'] == 0) {
                    $result = $wpdb->insert($table_name, $item);
                    $item['sportID'] = $wpdb->insert_id;

                    if ($result) {
                        $message = __('Item was successfully saved', 'joomsport-sports-league-results-management');
                    } else {
                        $notice = __('There was an error while saving item', 'joomsport-sports-league-results-management');
                    }
                } else {
                    
                    $result = $wpdb->update($table_name, $item, array('sportID' => $item['sportID']));

                    if ($result) {
                        $message = __('Item was successfully updated', 'joomsport-sports-league-results-management');
                    } else {
                        $notice = __('There was an error while updating item', 'joomsport-sports-league-results-management');
                    }
                }
                echo '<script> window.location="'.(esc_url(get_dashboard_url())).'admin.php?page=joomsport-page-sports"; </script> ';
                
            } else {
                // if $item_valid not true it contains error message(s)
                $notice = $item_valid;
            }
        }
        else {
            // if this is not post back we load item to edit or give new one to create
            $item = $default;
            if (isset($_REQUEST['id'])) {
                $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM %s WHERE sportID = %d", array($table_name,intval($_REQUEST['id']))), ARRAY_A);
                if (!$item) {
                    $item = $default;
                    $notice = __('Item not found', 'joomsport-sports-league-results-management');
                }
            }
            $lists = self::getListValues($item);
        }

        // here we adding our custom meta box
        add_meta_box('joomsport_sports_form_meta_box', __('Details', 'joomsport-sports-league-results-management'), array('JoomSportSportsNew_Plugin','joomsport_sports_form_meta_box_handler'), 'joomsport-sports-form', 'normal', 'default');
        
        wp_enqueue_script('media-upload');
        wp_enqueue_script('wp-mediaelement');

        ?>
        <div class="wrap">
            <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
            <h2><?php echo esc_html__('Sport', 'joomsport-sports-league-results-management')?> <a class="add-new-h2"
                                        href="<?php echo esc_url(get_admin_url(get_current_blog_id(), 'admin.php?page=joomsport-page-sports'));?>"><?php echo esc_html__('back to list', 'joomsport-sports-league-results-management')?></a>
            </h2>

            <?php if (!empty($notice)): ?>
            <div id="notice" class="error"><p><?php echo esc_html($notice) ?></p></div>
            <?php endif;?>
            <?php if (!empty($message)): ?>
            <div id="message" class="updated"><p><?php echo esc_html($message) ?></p></div>
            <?php endif;?>
            <script>
            jQuery(function($){

  // Set all variables to be used in scope
  var frame,
      metaBox = $('#jseventcontainer'), // Your meta box id here
      addImgLink = metaBox.find('#jsSportImage'),
      delImgLink = metaBox.find( '.delete-jssport-img'),
      imgContainer = metaBox.find( '.jssport-img-container'),
      imgIdInput = metaBox.find( '.jssport-img-id' );
  
  // ADD IMAGE LINK
  addImgLink.on( 'click', function( event ){
    
    event.preventDefault();
    
    // If the media frame already exists, reopen it.
    if ( frame ) {
      frame.open();
      return;
    }
    
    // Create a new media frame
    frame = wp.media({
      title: 'Select or Upload Media Of Your Chosen Persuasion',
      button: {
        text: 'Use this media'
      },
      multiple: false  // Set to true to allow multiple files to be selected
    });

    
    // When an image is selected in the media frame...
    frame.on( 'select', function() {
      
      // Get media attachment details from the frame state
      var attachment = frame.state().get('selection').first().toJSON();

      // Send the attachment URL to our custom image input field.
      imgContainer.append( '<img src="'+attachment.url+'" alt="" style="max-width:100%;"/>' );

      // Send the attachment id to our hidden input
      imgIdInput.val( attachment.id );

      // Hide the add image link
      addImgLink.addClass( 'hidden' );

      // Unhide the remove image link
      delImgLink.removeClass( 'hidden' );
    });

    // Finally, open the modal on click
    frame.open();
  });
  
  
  // DELETE IMAGE LINK
  delImgLink.on( 'click', function( event ){

    event.preventDefault();

    // Clear out the preview image
    imgContainer.html( '' );

    // Un-hide the add image link
    addImgLink.removeClass( 'hidden' );

    // Hide the delete image link
    delImgLink.addClass( 'hidden' );

    // Delete the image id from the hidden input
    imgIdInput.val( '' );

  });

});
            </script>
            <form id="form" method="POST">
                <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce(basename(__FILE__)))?>"/>
                <?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
                <input type="hidden" name="sportID" value="<?php echo esc_attr($item['sportID']) ?>"/>

                <div class="metabox-holder" id="poststuff">
                    <div id="post-body">
                        <div id="post-body-content" class="jsRemoveMB">
                            <?php /* And here we call our custom meta box */ ?>
                            <?php do_meta_boxes('joomsport-sports-form', 'normal', array($item,$lists)); ?>
                            <input type="submit" value="<?php echo esc_attr(__('Save & close', 'joomsport-sports-league-results-management'))?>" id="submit" class="button-primary" name="submit">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php
    }
    public static function joomsport_sports_form_meta_box_handler($item)
    {
        $lists = $item[1];
        $item = $item[0];
    ?>

    <div class="jsrespdiv12">
    <div class="jsBepanel">
        <div class="jsBEheader">
            <?php echo esc_html__('General', 'joomsport-sports-league-results-management'); ?>
        </div>
        <div class="jsBEsettings" id="jseventcontainer">		
		<table>
			<tr>
				<td width="200">
					<?php echo esc_html__('Sport name', 'joomsport-sports-league-results-management'); ?>
				</td>
				<td>
					<input type="text" name="sportName" size="50" value="<?php echo esc_attr($item['sportName'])?>" id="evname" maxlength="255" onKeyPress="return disableEnterKey(event);" />
				</td>
			</tr>
			<tr>
				<td width="200" valign="middle">
					<?php echo esc_html__('Sport Template type', 'joomsport-sports-league-results-management'); ?>
				</td>
				<td>
					<?php echo wp_kses($lists['tpls'], JoomsportSettings::getKsesSelect());?>
				</td>
			</tr>

            <tr>
				<td width="200" valign="middle">
					<?php echo esc_html__('Sport image', 'joomsport-sports-league-results-management'); ?>
				</td>
				<td>
                                    <div>
                                        <div class="jssport-img-container">
                                            <?php
                                            if($item['image']){
                                                echo wp_get_attachment_image($item['image']);
                                            }
                                            ?>
                                        </div>
                                        <input type="hidden" name="image" class="jssport-img-id"  value="<?php echo intval($item['image']);?>"/>

                                        <a href="" class="delete-jssport-img<?php if(!$item['image']){ echo ' hidden';}?>"><?php echo esc_html__('Remove image', 'joomsport-sports-league-results-management');?></a>

                                    </div>

                                    <button class="button<?php if($item['image']){ echo ' hidden';}?>" id="jsSportImage"><?php echo esc_html__('Add image', 'joomsport-sports-league-results-management'); ?></button>

                                </td>
			</tr>
			<tr>
                            <td width="200">
                                    <?php echo esc_html__('Ordering', 'joomsport-sports-league-results-management')?>
                            </td>
                            <td>
                                <input type="number" name="ordering" value="<?php echo esc_attr($item['ordering'])?>" />
                            </td>
                        </tr>
		</table>
            </div>
        </div>
    </div>
    <?php
    }
    public static function joomsport_sports_validate($item)
    {
        $messages = array();

        if (empty($item['sportName'])) $messages[] = __('Name is required', 'joomsport-sports-league-results-management');
        if (empty($item['sportTemplateID']) || intval($item['sportTemplateID']) < 1) $messages[] = __('Sport Template is required', 'joomsport-sports-league-results-management');


        if (empty($messages)) return true;
        return implode('<br />', $messages);
    }
    public static function getListValues($item){
        global $wpdb;
        $lists = array();

        $tpls = $wpdb->get_results('SELECT sportTemplateID as id, sportTemplateName as name FROM '.$wpdb->joomsport_sports_template.' ORDER BY sportTemplateName', 'OBJECT') ;


        $lists['tpls'] = '<select name="sportTemplateID"  class="jswf-chosen-select">';

        if(count($tpls)){
            foreach ($tpls as $tm) {
                $selected = '';
                if($item['sportTemplateID'] == $tm->id){
                    $selected = ' selected';
                }
                $lists['tpls'] .=  '<option value="'.esc_attr($tm->id).'" '.$selected.'>'.esc_html($tm->name).'</option>';
            }
        }
        $lists['tpls'] .=  '</select>';
        
        
        return $lists;

    }
}