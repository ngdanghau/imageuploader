<?php

/**
 * Note: Life time of cache entries can't longer than session expires
 */
class ChipVN_Cache_Adapter_Session extends ChipVN_Cache_Adapter_Array
{
    /**
     * Create Cache instance.
     *
     * @return void
     */
    public function __construct(array $options = array())
    {
        // Sure that session is initialized for saving somethings.
        if (!session_id()) {
            if (headers_sent()) {
                throw new Exception('Session is not initialized. Please sure that session_start(); was called at the top of the script.');
            }
            session_start();
        }
        parent::__construct($options);
        $this->data = & $_SESSION;
    }
}
