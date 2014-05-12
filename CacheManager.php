<?php
	class CacheManager
	{
		// property declaration
		private $tmpCacheFile = 'tmp.cache';
		private $folder = './cache/';
		private $maxAge = 30;
		
		//constructors
		function __construct(arr) {
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
			if(!empty($file) && !isExpired($file)) {
				return $file;		//This file is current. Return reference to it
			}
			
			//otherwise, file is out of date or does not exist.  Attempt to create one
			$fp = fopen($this->folder . $this->tmpCacheFile, "r+");

			if (!flock($fp, LOCK_EX | LOCK_NB)) {  // acquire an exclusive lock
				// Someone else is already updating the cache file. Nothing more to do here.
				fclose($fp);			
				return $file;	//return old file.  Will return null if no suitable file exists.
			} 

			// Exclusive lock acquired.  This means its up to me to write a new cache file
			$this->writeCacheFile($fp);
			
			fclose($fp);			// close the file
			// rename used instead of copy for atomicity reasons
			touch ($this->folder . $this->tmpCacheFile);	//touch before to set modify time
			rename($this->folder . $this->tmpCacheFile, $this->folder . uniqid('cache'));
			touch ($this->folder . $this->tmpCacheFile);	//touch after to create new tmp file
			flock($fp, LOCK_UN);    // release the lock
			
			return;
		}
		
		public function forceExpiration() {
			// Start by trying to obtain an exclusive lock on tmp file
			$fp = fopen($this->folder . $this->tmpCacheFile, "r+");

			if (!flock($fp, LOCK_EX | LOCK_NB)) {  // acquire an exclusive lock
				// Someone else is already updating the cache file. Nothing more to do here.
				fclose($fp);			
				return $file;	//return old file.  Will return null if no suitable file exists.
			} 

			// Exclusive lock acquired.  This means its up to me to write a new cache file
			$this->writeCacheFile($fp);
			
			fclose($fp);			// close the file
			// rename used instead of copy for atomicity reasons
			touch ($this->folder . $this->tmpCacheFile);	//touch before to set modify time
			rename($this->folder . $this->tmpCacheFile, $this->folder . uniqid('cache'));
			touch ($this->folder . $this->tmpCacheFile);	//touch after to create new tmp file
			flock($fp, LOCK_UN);    // release the lock
			
			return;
		}
		
		private function writeCacheFile($fp){
			//this needs work
		}
		
		private function removeOldCache() {
			//get the list of files in the cache folder.  There should only be cache files in here.
			$files = getCacheFileList();

			// remove files from the oldest to newest until there's only a couple left
			$rfiles = array_reverse($files, true);
			for($rfiles as $key->$val){
				if(count($rfiles <= 3) {
					break;
				}
				unset($files[$key]);
				unlink($this->folder . $val);
			}
		
		}
		
		private function retrieveCacheFile(){
			//get the list of files in the cache folder.  There should only be cache files in here.
			$files = getCacheFileList();
		   
			//loop through files and return first one that isn't locked.
			for($files as $modTime->$f) {
				$fp = fopen($this->folder . $f, "r");

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
			if ($handle = opendir($this->folder)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != ".." && $file != $this->tmpCacheFile) {
					$files[filemtime($file)] = $file;
				}
			}
			closedir($handle);

			ksort($files);		//should be a short number of files.
		   
			return $files;
		}
		
		private function isExpired($file) {
			return (time() - filemtime($file) >= $this->maxAge);		
		}
		
	}
?>
