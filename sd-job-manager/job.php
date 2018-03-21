<?php get_header();
define('SMARTD_DIR', plugin_dir_path(__FILE__));
$job_id = get_query_var('_sd_job', null);
$request = wp_remote_get("http://smartdiamond.co/Api/Job/Get/".$job_id."?token=".esc_attr( get_option('clientKey') ));
if( is_wp_error( $request ) ) {
	return false; // Bail early
}

$body = wp_remote_retrieve_body( $request );
$job = json_decode( $body );
?>
  <link rel="stylesheet" href="<?=SMARTD_URL?>/css/bootstrap.css">
  <link rel="stylesheet" href="<?=SMARTD_URL?>/css/job.css">
  <link rel="stylesheet" href="<?=SMARTD_URL?>/css/styles.css">
  <link rel="stylesheet" href="<?=SMARTD_URL?>/css/frontend.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <div class="col-md-offset-2 col-md-8 single_job_listing">
    <div class="main-title">
      <h2 id="job_name"><?=$job->Name ?></h2>
    </div>
		<ul class="job-listing-meta meta">
		<li class="location" itemprop="jobLocation"><?=$job->Local ?></li>
		<li class="date-posted"><date><?=$job->DateRegister ?></date></li>
	</ul>
      <div id="job_description"><?=$job->Description ?></div>
      <div class="job_application application">
		<a class="application_button button" href="http://smartdiamond.com.br/Painel/Candidate/Apply/<?=$job->Id ?>">Candidatar-se</a>
	</div>
  </div>
  <div style="clear:both;"></div>
<?php get_footer(); ?>
