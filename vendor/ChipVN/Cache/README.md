# PHP Cache Library
* A php library used to manage cache
* Support PHP >= 5
* Support more adapters: File, Array, Session, Memcached, Memcache

----
* Author: Phan Thanh Cong <ptcong90@gmail.com>
* License: MIT

## Changelogs
##### Version 1.1 (Jan 20, 2015)
* Rewrite the library
* Optimize File adapter
* Add new Array adapter (sometimes for testing)
* Don't need class loader, suppport autoload adapter by name
* <del>Remove support prefix, grouped</del>

##### Version 1.0 (Apr 08, 2014)
* First release, make fast to use for Image Remote Upload library

### Features
* All basic features (set/ put/ get/ delete/ flush/ garbage collect)

### Requirements
* PHP >= 5 _(not use namespace because still have hosting services that i'm using use PHP 5.2)_

### Support Adapters
- File
- Array
- Session
- Memcache (always return real value even value is true/false)
- Memcached

### Usage

    $cacher = ChipVN_Cache_Manager::make('File', array(
        'cache_dir' => __DIR__ . '/cache', // need declare
        'expires'   => 900, // default expires
    ));
    $cacher->set('key', 'value here'); // use default expires
    $cacher->set('key', 'new value', 10); // 10 seconds
    // you also can use
    $cacher->put('key', value);
    var_dump($cacher->has('key')); // true


    $cacher = ChipVN_Cache_Manager::make('Memcache', array(
        'host' => '127.0.0.1',
        'port' => 11211,
    ));
    // host and port is default, you can make without options
    $cacher = ChipVN_Cache_Manager::make('Memcache');
    $cacher->put('false_value', false);

    // always return real value instead of an empty string or "1" for false, true value.
    var_dump($cacher->get('false_value')); // return false

    // Cacher don't automatic run garbage collect,
    // so you should call this when you want to run garbage
    $cacher->garbageCollect();



