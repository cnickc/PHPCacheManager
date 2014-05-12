PHPCacheManager
===============

A way to handle cache requests in PHP such that only one process will attempt to update the cache file. Other requests will return the old cache file until the new cache is ready to be used.
