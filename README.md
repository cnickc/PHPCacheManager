PHPCacheManager
===============

###Background:

This is not meant to challenge established cache frameworks, like memcached, et al.  This was designed as a personal tutorial to learn some caching techniques for use in a very specific scenario.
The cache is meant to retrieve a mostly static and rather large file which is expensive to create.  For current purposes, it is assumed there are only a few such files to work with and so there is no extensive cataloguing system for these caches.
PHP was chosen for this project since it does not have an effective singleton class that would allow me to route all cache writing through a single thread.  So instead I rely on file locking mechanisms to ensure that the cache is only being written to once, while all other requests return an older version of the cache until rewriting is complete.

Use of this class requires a filesystem compatible with PHPs flock command.  

### Usage:
To create the CacheManager object, up to five parameters can be given in the form of an associative array: 
- toCache (the php file that will create the contents of the cache),
- timeToExpire (the max age of the cache before the next request will rebuild the cache, default: 30s),
- cachedir (the location of the cache of current interest.  Each cache should have its own folder under the current structure),
- tmpfile (the name to use for the temporary working file),
- cacheHistory (the number of cache files to keep around, in order from most recent, since sometimes it's useful to see the history of your cache files, default: 2).

To create a cache that has no time expiration (i.e. cache has to be forced to expire), set timeToExpire to a value less than 0.  e.g.
```php
$cmgr = new CacheManager(['timeToExpire'=> -1, 'toCache'=>'path/to/whatIwanttocache.php']);
```
Setting a timeToExpire equal to zero is valid, but will attempt to rebuild cache on every request (will only retrieve older cache while a new cache is currently being built).  

The following commands are made public from this class:
- retrieveCache(),
- forceExpiration().

The following code will rebuild the cache if the most recent available cache is older than the maximum cache lifespan, as previously defined, only if the cache is not currently rebuilding due to a previous request.
If the cache is already in the process of being updated, or if the current cache is still fresh enough, then the name of the most recent completed cache file is returned.
```php 
	$file = $cmgr->retrieveCache();
```

The following code snippet will force the expiration of the current cache file, and return the name of the new cache file once completed.  This is useful for caches that have unlimited expiration times.
```php 
	$file = $cmgr->forceExpiration();
```
