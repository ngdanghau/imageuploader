<?php

return array(
    // If your hosting enable SAFE MODE and don't allows to write cache file.
    // Change 'File' to 'Session'.
    'cache_adapter' => 'File', // 'File' or 'Session'

    // If your hosting have not enabled socket, change "false" to "true"
    // to use cURL for sending request alternative.
    'use_curl' => false,

    // CHMOD to 0777
    'temp_dir'    => file_path('/temp'),
    // CHMOD to 0777
    'session_dir' => file_path('/sessions'),

    'logo_dir' => file_path('/logos'),

    'upload' => array(
        'allow_file_types' => array('jpg', 'jpeg', 'gif', 'png'),
        'max_file_size'    => 2097152, // 2 * 1024 * 1024 // 2mb
    ),
    /**
     * If set, system will only add logo to images that have minimum size:
     * Ex: 300x200
     * Width of image must be greater than 300px, Height of images must be greater than 200px
     * If want to watermark to all images use this:
     * 'watermark_minimum_size' => '',.
     */
    'watermark_minimum_size' => '300x200',

    'options' => array(
        'watermark' => array(
            'label'   => 'Watermark',
            'default' => 1,
            'options' => array(
                1 => 'Yes',
                0 => 'No',
            )
        ),
        'watermark_position' => array(
            'label'   => 'Watermark position',
            'default' => 'br',
            'type'    => 'select',
            'options' => array(
                'tl' => 'top-left',
                'tr' => 'top-right',
                'bl' => 'bottom-left',
                'br' => 'bottom-right',
                'mc' => 'middle-center',
                'rd' => 'random'
            ),
        ),
        'watermark_logo' => array(
            'label'   => 'Logo',
            'default' => '1',
            'options' => array(
                '1' => 'Logo script',  // mean {logo_dir}/1.png
            )
        ),
        'resize' => array(
            'label'   => 'Resize',
            'default' => 0,
            'type'    => 'select',
            'options' => array(
                0    => 'Full size',
                100  => '100x',
                150  => '150x',
                320  => '320x',
                640  => '640x',
                800  => '800x',
                1024 => '1024x'
            )
        ),
        'server' => array(
            'label'   => 'Server',
            'default' => 'imgur',
            'options' => array(
                // key => text to display
                // comment which you want to disable
                'imgur'      => 'Imgur',
                'flickr'     => 'Flickr',
                'imageshack' => 'Imageshack',
                'picasanew'  => 'Picasa',
                'postimage'  => 'Postimage',
            )
        ),
    ),
    'postimage' => array(
        // not Required, but recommend should have
        'accounts' => array(
            // array(
            //     'username' => 'user1',
            //     'password' => 'pass1',
            // ),
            // array(
            //     'username' => 'user1',
            //     'password' => 'pass1',
            // ),
        ),
    ),

    'imageshack' => array(
        // Required
        // Register: {@link https://imageshack.com/contact/api}.
        'api_keys' => array(
            // 'other API',
            // 'other API',
        ),
        // Required
        'accounts' => array(
            // array(
            //     'username' => 'user1',
            //     'password' => 'pass1',
            // ),
            // array(
            //     'username' => 'user2',
            //     'password' => 'pass2',
            // ),
        ),
    ),
    'imgur' => array(
        /**
         * Required
         * Register an API here {@link https://api.imgur.com/oauth2/addclient}.
         */
        'api_keys' => array(
            array(
                'key'    => 'your client id', // Client ID
                'secret' => 'your client secret', // Client secret
            ),
            // array(
            //     'key'    => 'your value',
            //     'secret' => 'your value',
            // ),
        ),
        // not Required, but recommend should have
        'accounts' => array(
            // array(
            //     'username' => 'user1',
            //     'password' => 'pass1',
            // ),
            // array(
            //     'username' => 'user1',
            //     'password' => 'pass1',
            // ),
        ),
    ),

    'picasanew' => array(
        /**
         * THIS FOR BACKUP IF PICASA NOT WORKING
         *
         * The PicasaNew use OAuth to authentication.
         * You must register an API in {@link https://console.developers.google.com/}
         * Create project -> APIs & auth -> Credencials -> Create new Client ID with info like under:
         * ** Note **: This is an OAuth API, not a Public API Access.
         *
         *     * Application type: Web application
         *     * Authorized redirect URIs: URL to get_picasa_token.php file.
         *         Example: http://yourdomain.com/upload-path/get_picasa_token.php.
         *
         * Then open browser, run http://yourdomain.com/upload-path/get_picasa_token.php as Flickr get token.
         */
        'token_file' => file_path('/includes/picasa_token.php'),

        'api_keys' => array(
            // Require but don't need more than one.
            array(
                'key'      => 'your client id',
                'secret'   => 'your client secret',
            ),
        ),
        'accounts' => array(
            // don't need password here, you can use multiple accounts here
            array(
                'username' => 'your account',
                // not Required, but recommend should have
                // you can use many album ids with array('album id', 'album id', 'album id')
                'album_ids' => array(),
            ),
            // array(
            //     'username' => 'another account',
            //     'album_ids' => array(),
            // ),
        ),
    ),

    'flickr' => array(
        /**
         * Run script "get_flickr_token.php" to get and automatic add TOKEN to config file.
         *
         * CHMOD to 0777.
         */
        'token_file' => file_path('/includes/flickr_token.php'),

        /**
         * Required
         * Register {@link https://www.flickr.com/services/apps/create/noncommercial/}.
         */
        'api_keys' => array(
            array(
                'key'    => '',
                'secret' => '',
            ),
            // array(
            //     'key'    => 'your value',
            //     'secret' => 'your value',
            // ),
        ),
    ),
);
