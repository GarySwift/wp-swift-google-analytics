<?php
/*
Plugin Name: 		WP Swift: Google Analytics
Description: 		A WordPress plugin allows users to save a Google Analytics tracking code and inserts the tracking tag into the page header.
Version:           	1.0.0
Author:            	Gary Swift
Author URI:        	https://github.com/GarySwift
License:           	MIT License
License URI:       	http://www.opensource.org/licenses/mit-license.php
Text Domain:       	wp-swift-google-analytics
*/
class WP_Swift_Google_Analytics {
    /*
     * Initializes the plugin.
     */
    public function __construct() {
    	add_action( 'wp_head', array($this, 'google_analytics_in_page_head') );
    	// add_action( 'admin_menu', 'ch3mlm_admin_menu' );
    	add_action( 'admin_menu', array($this, 'wp_swift_google_analytics_settings_menu'));
    	 add_action( 'admin_init', array($this, 'ch2pho_admin_init') );
    }
   
    /*
     * register_activation_hook
     */
    static function install() {
        // do not generate any output here
     	if ( get_option( 'wp_swift_google_analytics' ) === false ) {
			$new_options['ga_account_name'] = "";
			$new_options['track_outgoing_links'] = false;
			$new_options['version'] = "1.1";
			add_option( 'wp_swift_google_analytics', $new_options );
		} else {
	    	$existing_options = get_option( 'wp_swift_google_analytics' );
	    	if ( $existing_options['version'] < 1.1 ) {
				$existing_options['track_outgoing_links'] = false;
				$existing_options['version'] = "1.1";
				update_option( 'wp_swift_google_analytics', $existing_options );
			} 
		}
    }

	function wp_swift_google_analytics_settings_menu() {
		if ( empty ( $GLOBALS['admin_page_hooks']['wp-swift-brightlight-main-menu'] ) ) {
			$options_page = add_options_page( 
				'Google Analytics Configuration',
				'Google Analytics',
				'manage_options',
				'wp-swift-google-analytics-settings-menu',
				array($this, 'wp_swift_google_analytics_settings_page') 
			);	
		}
		else {
			// Create a sub-menu under the top-level menu
			$options_page = add_submenu_page( 'wp-swift-brightlight-main-menu',
			   'Google Analytics Configuration', 'Google Analytics',
			   'manage_options', 'wp-swift-google-analytics-settings-menu',
			   array($this, 'wp_swift_google_analytics_settings_page') );		
		}
		if ( $options_page ) {
           add_action( 'load-' . $options_page, array($this, 'ch2pho_help_tabs') );
		}
	}

public function ch2pho_help_tabs() {
           $screen = get_current_screen();
           $screen->add_help_tab( array(
			        'id'       => 'ch2pho-plugin-help-instructions',
			        'title'    => 'Instructions',
			        'callback' => array($this, 'ch2pho_plugin_help_instructions'),
			) );
			$screen->add_help_tab( array(
			        'id'       => 'ch2pho-plugin-help-faq',
			        'title'    => 'FAQ',
			        'callback' => array($this, 'ch2pho_plugin_help_faq'),
			) );
      $screen->set_help_sidebar( '<p>This is the sidebar content</p>' );
}

public function ch2pho_plugin_help_instructions() {
	?><p>These are instructions explaining how to use this plugin.</p><?php 
}

public function ch2pho_plugin_help_faq() { 
	?><p>These are the most frequently asked questions on the use of this plugin.</p><?php
}

	public function wp_swift_google_analytics_settings_page() {
		// Retrieve plugin configuration options from database
		$options = get_option( 'wp_swift_google_analytics' );
		if ($options): ?>
			<div id="ch2pho-general" class="wrap">
				<?php if (isset( $_GET['message'] )&& $_GET['message'] == '1'): ?>
					<div id='message' class='updated fade'><p><strong>Settings Saved</strong></p></div>
				<?php endif ?>

				<h2>My Google Analytics</h2>

				<img src="<?php echo plugins_url( '/logo.png', __FILE__ ) ?>" alt="">

				<p>Google Analytics is a free Web analytics service that provides statistics and basic analytical tools for search engine optimization (SEO) and marketing purposes.</p>
				<hr>
				<form method="post" action="admin-post.php">
					<input type="hidden" name="action" value="save_ch2pho_options" />
					<!-- Adding security through hidden referrer field -->
					<?php wp_nonce_field( 'wp_swift_google_analytics_nonce' ); ?>
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row"><label for="ga_account_name">Account Name:</label></th>
								<td><input type="text" name="ga_account_name" value="<?php echo esc_html( $options['ga_account_name'] ); ?>"/>
								<p class="description" id="tagline-description">Tracking Code</p></td>
							</tr>
							<tr>
								<th scope="row"><label for="track_outgoing_links">Track Outgoing Links</label></th>
								<td><input type="checkbox" name="track_outgoing_links" <?php 
								if ($options['track_outgoing_links'] ) {
									echo ' checked="checked" ';
								}
								?>/></td>
							</tr>
						</tbody>
					</table>	
					<input type="submit" value="Save Changes" class="button button-primary"/>
				</form>
			</div>	
		<?php 
		endif;
	}

	public function ch2pho_admin_init() {
       add_action( 'admin_post_save_ch2pho_options', array($this, 'process_ch2pho_options') );
	}

	public function process_ch2pho_options() {
		// Check that user has proper security level
		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( 'Not allowed' );
		}
		
		// Check that nonce field created in configuration form is present
		check_admin_referer( 'wp_swift_google_analytics_nonce' );
		// Retrieve original plugin options array
		$options = get_option( 'wp_swift_google_analytics' );
		// Cycle through all text form fields and store their values in the options array
		foreach ( array( 'ga_account_name' ) as $option_name ) {
           if ( isset( $_POST[$option_name] ) ) {
               $options[$option_name] = sanitize_text_field( $_POST[$option_name] );
			} 
		}
	       // Cycle through all check box form fields and set the options
	       // array to true or false values based on presence of
	       // variables
	    foreach ( array( 'track_outgoing_links' ) as $option_name ) {
	        if ( isset( $_POST[$option_name] ) ) {
		        $options[$option_name] = true;
		    } else {
		        $options[$option_name] = false;
		    }
		}
		// Store updated options array to database	
		update_option( 'wp_swift_google_analytics', $options );
		// Redirect the page to the configuration form that was processed
		if ( empty ( $GLOBALS['admin_page_hooks']['wp-swift-brightlight-main-menu'] ) ) {
			wp_redirect( add_query_arg( array( 'page' => 'wp-swift-google-analytics-settings-menu', 'message' => '1' ), admin_url( 'admin.php' ) ) );
			exit; 
		}
		else {
			wp_redirect( add_query_arg( array( 'page' => 'wp-swift-google-analytics-settings-menu', 'message' => '1' ), admin_url( 'options-general.php' ) ) );
			exit; 
		}
	}
    function google_analytics_in_page_head() { 
    	// Retrieve plugin configuration options from database
		$options = get_option( 'wp_swift_google_analytics' );

	    if (isset($options['ga_account_name']) && $options['ga_account_name'] != ''): 

?><!-- WP Swift Google Analytics -->
<script type="text/javascript">
	var gaJsHost = ( ( "https:" == document.location.protocol ) ? 	"https://ssl." : "http://www." );
	document.write( unescape( "%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E" ) );
</script>
<script type="text/javascript">
	try {
		var pageTracker = _gat._getTracker( '<?php echo $options['ga_account_name'] ?>' );
		pageTracker._trackPageview();
	} catch( err ) {}
</script><?php

	     
	    endif;
	}

}
$wp_swift_google_analytics = new WP_Swift_Google_Analytics();
register_activation_hook( __FILE__, array( 'WP_Swift_Google_Analytics', 'install' ) );