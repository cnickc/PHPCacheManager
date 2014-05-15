PHPCacheManager
===============

Background:

This is not meant to challenge established cache frameworks, like memcached, et al.  This was designed as a personal tutorial to learn some caching techniques for use in a very specific scenario.

The cache is meant to retrieve a mostly static and rather large file which is expensive to create.  For current purposes, it is assumed there are only a few such files to work with and so there is no extensive cataloguing system for these caches.

PHP was chosen for this project since it does not have an effective singleton class that would allow me to route all cache writing through a single thread.  So instead I rely on file locking mechanisms to ensure that the cache is only being written to once, while all other requests return an older version of the cache until rewriting is complete.

Use of this class requires a filesystem compatible with PHPs flock command.  