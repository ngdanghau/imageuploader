<?php

class ChipVN_Cache_Manager
{
    /**
     * Create new Cache instrance.
     *
     * @param  string                        $adapter
     * @param  array                         $options
     * @return ChipVN_Cache_Adapter_Abstract
     */
    public static function make($adapter = 'Session', array $options = array())
    {
        $class = 'ChipVN_Cache_Adapter_'.ucfirst($adapter);

        return new $class($options);
    }
}
