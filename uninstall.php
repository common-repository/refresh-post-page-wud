<?php
 /*
 * === Refresh Post Page WUD ===
 * Contributors: wistudatbe
 * Author: Danny WUD
 */
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) { 
exit(); 
} 
delete_post_meta_by_key( 'Wud_Rpp_Value' );
?>
