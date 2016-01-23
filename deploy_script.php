<?php

// payload is a "post" variable sent by webhook's bitBucket
// If "payload" isn't setted, then it redirect to other page.. It prevent direct access.
if (!isset($_POST['payload'])) {
	header("Location: https://www.google.com/");
	exit;
} else {
	

	// web repository
	$web_dir = '~/public_html/booking/teste/';

	// git directory
	// in bash use command "which git" to discover
	$git_bin_path = '/usr/bin/git';
	
	// Log file name
	$log_name = 'deploy_script-booking-9f164f640.log';

	// Name of the branches of production and development
	$branchProduction = 'master';
	$branchDevelopment = 'develop';

	$update = false;

	// Parse data from Bitbucket hook payload
	$payload = json_decode($_POST['payload']);

	if (empty($payload->commits)){
		// When merging and pushing to bitbucket, the commits array will be empty.
		// In this case there is no way to know what branch was pushed to, so we will do an update.
		$update = true;
	} else {
		foreach ($payload->commits as $commit) {
			$branch = $commit->branch;
			if ($branch === $branchDevelopment || isset($commit->branches) && in_array($branchDevelopment, $commit->branches)) {
				$update =	true;
				break;
			}
		}
	}

	if ($update) {
		// Access the repository and then do a pull to repository in bitBucket
		shell_exec("cd ".$web_dir." && ".$git_bin_path." pull origin ".$branchDevelopment);
		
		// Log the deployment
		$commit_hash = shell_exec('cd '.$web_dir.' && '.$git_bin_path.' rev-parse --short HEAD');
		file_put_contents($log_name, date('m/d/Y h:i:s a')." Deployed branch: ".$branch." Commit: ".$commit_hash." \n", FILE_APPEND);
	}
	
}

?>