<?php
// Check that code was called from WordPress with
// uninstallation constant declared
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
   exit;
// Check if options exist and delete them if present
if ( get_option( 'wp_swift_google_analytics' ) != false ) {
    delete_option( 'wp_swift_google_analytics' );
}