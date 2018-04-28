<?php

function chipvn_cache_autoload($class)
{
    if (strpos($class, 'ChipVN_Cache') === 0) {
        require strtr($class, array(
            'ChipVN_Cache' => dirname(__FILE__),
            '_'            => DIRECTORY_SEPARATOR
        )).'.php';
    }
}
spl_autoload_register('chipvn_cache_autoload');