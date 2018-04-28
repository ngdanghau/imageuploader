<?php

return array(
    // Nếu hosting enable SAFE MODE và không ghi được file cache.
    // Sửa 'File' thành 'Session'
    'cache_adapter' => 'File', // 'File' or 'Session'

    // Nếu hosting không enable socket, change "false" to "true"
    // để dùng cURL thay thế cho việc upload
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
     * Hệ thống sẽ watermark vào file có kích cỡ tối thiểu widthxheight
     * VD: 300x200
     * => Chiều rộng của ảnh phải lớn hơn 300px, chiều cao của ảnh phải lớn hơn 200px
     * Nếu muốn ảnh nào cũng watermark thì để trống
     * 'watermark_minimum_size' => '',
     */
    'watermark_minimum_size' => '300x200',

    'options' => array(
        'watermark' => array(
            'label'   => 'Watermark',
            'default' => 1,
            'options' => array(
                1 => 'Yes',
                0 => 'No', // xóa dòng này nếu bắt buộc user sử dụng watermark
            )
        ),
        'watermark_position' => array(
            'label'   => 'Watermark position',
            'default' => 'br',
            'type' => 'select',
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
                // key => text hiển thị
                // thêm // ở trước nếu muốn tắt một server
                'imgur'      => 'Imgur',
                'flickr'     => 'Flickr',
                'imageshack' => 'Imageshack',
                'picasanew' => 'Picasa',
                'postimage'  => 'Postimage',
            )
        ),
    ),

    'postimage' => array(
        // Không bắt buộc
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
        /**
         * Bắt buộc - phải có ít nhất 1
         * Register: {@link https://imageshack.com/contact/api}.
         */
        'api_keys' => array(
            'your API key here',
            // 'other API',
            // 'other API',
        ),
        /**
         * Bắt buộc - phải có ít nhất 1
         */
        'accounts' => array(
            array(
                'username' => 'your username',
                'password' => 'your password',
            ),
            /**
             * Có thể thêm nhiều account khác theo mẫu tương tự bên dưới
             */
            // array(
            //     'username' => 'user2',
            //     'password' => 'pass2',
            // ),
        ),
    ),
    'imgur' => array(
        /**
         * Bắt buộc - phải có ít nhất 1
         * Đăng ký API (Client) tại đây {@link https://api.imgur.com/oauth2/addclient}
         */
        'api_keys' => array(
            array(
                'key'    => 'your client id', // Client ID
                'secret' => 'your client secret', // Client secret
            ),
            /**
             * Có thể thêm nhiều api khác theo mẫu tương tự bên dưới
             */
            // array(
            //     'key'    => 'your value',
            //     'secret' => 'your value',
            // ),
        ),
        /**
         * Không bắt buộc nhưng nên có để tránh bị giới hạn upload.
         */
        'accounts' => array(
            array(
                'username' => 'your username',
                'password' => 'your password',
            ),
            /**
             * Có thể thêm nhiều account khác theo mẫu tương tự bên dưới
             */
            // array(
            //     'username' => 'user2',
            //     'password' => 'pass2',
            // ),
        ),
    ),
    'picasanew' => array(
        /**
         * Uploader này để dự phòng nếu picasa không hoạt động.
         *
         * Để sử dụng uploader này, bạn phải đăng ký ít nhất 1 API ở bên dưới
         * {@link https://console.developers.google.com/}
         * Create project -> APIs & auth -> Credencials -> Create new Client ID với các thông tin như bên dưới.
         * ** Chú ý ** Đây là OAuth API chứ không phải Public API Access.
         *
         *     * Application type: Web application
         *     * Authorized redirect URIs: URL to get_picasa_token.php file.
         *         Example: http://yourdomain.com/upload-path/get_picasa_token.php.
         *
         * Sau đó mở browser, chạy link http://yourdomain.com/upload-path/get_picasa_token.php giống như flickr get token.
         */
        'token_file' => file_path('/includes/picasa_token.php'),

        'api_keys' => array(
            // Yêu cầu phải có ít nhất 1, không cần nhiều
            array(
                'key'      => 'your client id',
                'secret'   => 'your client secret',
            ),
        ),
        'accounts' => array(
            // Không cần password, chỉ cần username
            array(
                'username' => 'your account',
                /**
                 * Không bắt buộc, nhưng nếu không có thì hệ thống sẽ tự upload vào album "default".
                 * Có thể dùng nhiều album id, hệ thống sẽ tự lấy random mỗi lần upload
                 * Mẫu để dùng nhiều album array('album id', 'album id', 'album id')
                 */
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
         * Chạy script "get_flickr_token.php" để get token
         * Code sẽ tự động lấy token và lưu vào file này.
         *
         * Mỗi TOKEN tương ứng với 1 ACCOUNT (hiện tại 1 account có 1T free cho việc upload)
         *
         * CHMOD to 0777.
         */
        'token_file' => file_path('/includes/flickr_token.php'),

        /**
         * Bắt buộc - phải có ít nhất 1
         * Có thể sử dụng nhiều API, nhưng 1 cái cũng có thể dùng cho nhiều TOKENs, nhiều ACCOUNTs
         * Đăng ký API tại đây {@link https://www.flickr.com/services/apps/create/noncommercial/}
         */
        'api_keys' => array(
            array(
                'key'    => 'your api key',
                'secret' => 'your secret key',
            ),
            /**
             * Có thể thêm nhiều account khác theo mẫu tương tự bên dưới
             */
            // array(
            //     'key'    => 'your value',
            //     'secret' => 'your value',
            // ),
        ),
    ),
);
