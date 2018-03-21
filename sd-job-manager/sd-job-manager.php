<?php
/**
 * Plugin Name: SmartDiamond Job Manager
 * Plugin URI: https://smartdiamond.co/
 * Description: Manage job listings from the SmartDiamond admin panel, and allow users to show the jobs directly to your site.
 * Version: 0.2
 * Author: SmartDiamond
 * Author URI: http://smartsdiamond.co/
 * Requires at least: 4.1
 * Text Domain: sd-job-manager
 * Domain Path: /languages/
 * License: GPL2+
 */
 //https://codex.wordpress.org/Creating_Options_Pages
 
define('SMARTD_URL', plugins_url('', __FILE__));
define('SMARTD_DIR', plugin_dir_path(__FILE__));
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
function custom_rewrite_basic() {
  add_rewrite_rule('^job/([0-9]+)/?', 'index.php?_sd_job=$matches[1]', 'top');
  add_rewrite_tag( '%_sd_job%', '([0-9]+)' );
}
add_action('init', 'custom_rewrite_basic');

add_filter( 'query_vars', function($vars) {
array_push($vars, '_sd_job');
array_push($vars, 'search_keywords');
array_push($vars, 'search_location');
return $vars;
} );
add_filter( 'template_include', function($template) {
 
$job_id = get_query_var('_sd_job', null);
 
if($job_id) {
$template = __DIR__ . '/job.php';
}
return $template;
}, 99 );
// create custom plugin settings menu
add_action('admin_menu', 'config_plugin_create_menu');

function config_plugin_create_menu() {

	//create new top-level menu
	add_menu_page('Smart Diamond Configurações', 'Smart Diamond', 'administrator', __FILE__, 'sd_plugin_settings_page' , 'dashicons-businessman' );

	//call register settings function
	add_action( 'admin_init', 'register_sd_plugin_settings' );
}

function sd_jobs() {
	$search_keywords = get_query_var('search_keywords', "");
	$search_location = get_query_var('search_location', "");
	$request = wp_remote_post("http://smartdiamond.co/Api/Job/List?token=".esc_attr( get_option('clientKey') )."&term=".$search_keywords."&local=".$search_location);
	if( is_wp_error( $request ) ) {
		//return "Not Found"; // Bail early
	}

	$body = wp_remote_retrieve_body( $request );
	$jobs = json_decode( $body );

?>
	<link rel="stylesheet" href="<?=SMARTD_URL?>/css/bootstrap.css">
	<link rel="stylesheet" href="<?=SMARTD_URL?>/css/job.css">
	<link rel="stylesheet" href="<?=SMARTD_URL?>/css/styles.css">
	<link rel="stylesheet" href="<?=SMARTD_URL?>/css/frontend.css">
    <div class="job_listings" data-location="" data-keywords="" data-show_filters="true" data-show_pagination="false" data-per_page="10" data-orderby="featured" data-order="DESC" data-categories="">
       <form class="job_filters">
          <div class="search_jobs">
             <div class="search_keywords">
                <label for="search_keywords">Palavras-chave</label>
                <input type="text" name="search_keywords" id="search_keywords" placeholder="Palavras-chave" value="<?=$search_keywords?>">
             </div>
             <div class="search_location">
                <label for="search_location">Local</label>
                <input type="text" name="search_location" id="search_location" placeholder="Local" value="<?=$search_location?>">
             </div>
          </div>
       </form>
       <ul id="job_listing" class="job_listings">
			<?php 
				if( ! empty( $jobs ) ) {
					
					foreach( $jobs as $job ) {
						?>
						<li class="job_listing post-' + item.Id + ' type-job_listing status-publish hentry" style="visibility: visible;">
						 <a href="/job/<?=$job->Id?>/">
							<div class="position">
							   <h3><?=$job->Name?></h3>
							</div>
							<div class="location">
							   <?=$job->Local?>
							</div>
							<ul class="meta">
							   <li class="date">
								  <date><?=$job->DateRegister?></date>
							   </li>
							</ul>
						 </a>
					  </li>
						<?php
					}
				}
			?>
       </ul>
       <a class="load_more_jobs" href="#" style="display:none;"><strong>Carregar mais anúncios</strong></a>
    </div>
<?php
}
add_shortcode( 'sd_jobs', 'sd_jobs' );


function register_sd_plugin_settings() {
	//register our settings
	register_setting( 'sd-settings-group', 'clientKey' );
}

function sd_plugin_settings_page() {
?>
<div class="wrap">
<h1>Smart Diamnod</h1>

<form method="post" action="options.php">
    <?php settings_fields( 'sd-settings-group' ); ?>
    <?php do_settings_sections( 'sd-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Client Key</th>
        <td><input type="text" name="clientKey" class="regular-text" value="<?php echo esc_attr( get_option('clientKey') ); ?>" /></td>
        </tr>
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php }