<?php
	class CacheManager
	{
		// property declaration
		private $tmpCacheFile = 'tmp.cache';
		private $folder = '/cache/';
		private $maxAge = 30;
		
		//constructors
		function __construct($arr = array()) {
			if(array_key_exists('timeToExpire', $arr))
				$this->maxAge = $timeToExpire;	//the maximum lifetime of a cache file in seconds
			if(array_key_exists('cachedir', $arr))
				$this->tmpCacheFile = $tmpfile;	//the name of the tmp working file
			if(array_key_exists('tmpfile', $arr))
				$this->folder = $cachedir;		//the directory containing the cache files				
		} 

		// method declaration
		public function retrieveCache() {
		
			$file = $this->retrieveCacheFile();
			if(!empty($file) && !$this->isExpired($file)) {
				return $file;		//This file is current. Return reference to it
			}
			
			//otherwise, file is out of date or does not exist.  Attempt to create one
			$fp = fopen(dirname(__FILE__) . $this->folder . $this->tmpCacheFile, "r+");

			if (!flock($fp, LOCK_EX | LOCK_NB)) {  // acquire an exclusive lock
				// Someone else is already updating the cache file. Nothing more to do here.
				echo "file is already locked: " . time() . "<br />";
				fclose($fp);			
				return $file;	//return old file.  Will return null if no suitable file exists.
			} 
			
			echo "exclusive lock acquired: " . time();

			// Exclusive lock acquired.  This means its up to me to write a new cache file
			$this->writeCacheFile($fp);
			
			// rename used instead of copy for atomicity reasons
			touch (dirname(__FILE__) . $this->folder . $this->tmpCacheFile);	//touch before to set modify time
			$name = uniqid('c') . '.cache';
			rename(dirname(__FILE__) . $this->folder . $this->tmpCacheFile, dirname(__FILE__) . $this->folder . $name);
			touch (dirname(__FILE__) . $this->folder . $this->tmpCacheFile);	//touch after to create new tmp file
			flock($fp, LOCK_UN);    // release the lock
			fclose($fp);			// close the file
			$this->removeOldCache();
			
			echo "exclusive lock released: " . time() . "<br />";
			return $name;
		}
		
		public function forceExpiration() {

		}
		
		private function writeCacheFile($fp){
			//this needs work
			ftruncate($fp, 0);
			date_default_timezone_set('UTC');
			fwrite($fp, 'This file was started at: ' . date('Ydm-His'));
			sleep(10);
			fwrite($fp, 'This file was completed at: ' . date('Ydm-His'));
			return;
		}
		
		private function removeOldCache() {
			//get the list of files in the cache folder.  There should only be cache files in here.
			$files = $this->getCacheFileList();

			// remove files from the oldest to newest until there's only a couple left
			$rfiles = array_reverse($files, true);
			foreach($rfiles as $key=>$val){
				if(count($rfiles) <= 3) {
					break;
				}
				unset($rfiles[$key]);
				unlink(dirname(__FILE__) . $this->folder . $val);
			}
		
		}
		
		private function retrieveCacheFile(){
			//get the list of files in the cache folder.  There should only be cache files in here.
			$files = $this->getCacheFileList();
		   
			//loop through files and return first one that isn't locked.
			foreach($files as $modTime=>$f) {
				$fp = fopen(dirname(__FILE__) . $this->folder . $f, "r");

				if (flock($fp, LOCK_SH | LOCK_NB)) {  // acquire an reader lock
					flock($fp, LOCK_UN);    // release the lock
					fclose($fp);			// close the file
					return $f;				// return this file
				} 
				fclose($fp);
			}	
			//if there are no unlocked files, return null
			return null;		
		}
		
		private function getCacheFileList() {
			//get the list of files in the cache folder.  There should only be cache files in here.
			$files = array();
			if ($handle = opendir(dirname(__FILE__) . $this->folder)) {
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != ".." && $file != $this->tmpCacheFile && strpos($file, '.cache') !== false) {
						$files[filemtime(dirname(__FILE__) . $this->folder . $file)] = $file;
					}
				}
				closedir($handle);
			}

			ksort($files);		//should be a short number of files.
			$rfiles = array_reverse($files, true);	//return the list with the newest files first
		   
			return $rfiles;
		}
		
		private function isExpired($file) {
			$time = time();
			$mtime = filemtime(dirname(__FILE__) . $this->folder . $file);
			$diff =  $time - $mtime; 
			
			return ($diff >= $this->maxAge);		
		}
		
	}
?>
