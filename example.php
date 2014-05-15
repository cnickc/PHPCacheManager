<?php 
	ini_set('display_errors', 'On');
	error_reporting(E_ALL);
	require 'CacheManager.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta charset="utf-8"/>
		<meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE"> 
		<title>
			PHP Cache Manager Test
		</title>
	</head>
	<body>
		<div id="content">
			<?php
				//load this area with the contents of the cache file from the manager.
				
				$mgr = new CacheManager(['toCache'=>'sampleCacheable.php', 'timeToExpire'=>10]);
				
				for($ctr = 0; $ctr < 35; $ctr++) {
					$f = $mgr->retrieveCache();
					$filecontents = file_get_contents(dirname(__FILE__) . "/cache/" . $f);
					echo "response " . $f . " received at: " . time() . "<br />";
					echo $ctr . " --- <br />" . $filecontents . "<br />";
					sleep(1);
				}
			?>
		</div>
	</body>
</html>

