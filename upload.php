<?php
ini_set('memory_limit', '512M'); // increase this if you catch "Allowed memory size..."

require_once './global.php';
require_once 'vendor/ChipVN/ClassLoader/Loader.php';
require_once 'vendor/PhpThumb/ThumbLib.inc.php';
ChipVN_ClassLoader_Loader::registerAutoload();


$config = load_file('includes/config.php');
$options = $config['options'];

$defaults = array();
foreach ($options as $name => $option) {
    $defaults[$name] = $option['default'];
}
extract(array_map('trim', $_POST) + $defaults + array('type' => '', 'url' => ''), EXTR_PREFIX_ALL, 'data');

if (file_exists($demo = file_path('demo.php'))) include $demo;

if (in_array($data_type, array('upload', 'transload'))) {
    // Validate
    foreach (array_keys($defaults) as $name) {
        $varname = 'data_' . $name;
        if ($options[$name]['options'] && !in_array(${$varname}, array_keys($options[$name]['options']))) {
            response_json(array(
                'error'   => true,
                'message' => 'The value of "' . $name . '" is invalid.'
            ));
        }
    }
    $tempFile = $config['temp_dir'] . '/' . uniqid() . '.jpg';
    // remove comment under if you want to keep original file name
    // some service will force file name to their name (eg: imgur)
    if (isset($_FILES['files']['name'][0])) {
        $tempFile = $config['temp_dir'] . '/' . str_replace(' ', '_', $_FILES['files']['name'][0]);
    }
    try {
        $keepOriginal = true;
        $fileOriginal = null;

        if ($data_type == 'upload' && !empty($_FILES['files'])) {
            $file = array(
                'name'     => $_FILES['files']['name'][0],
                'size'     => $_FILES['files']['size'][0],
                'type'     => $_FILES['files']['type'][0],
                'tmp_name' => $_FILES['files']['tmp_name'][0],
            );
            if (!$imageSize = getimagesize($file['tmp_name'])) {
                throw new Exception('The file is not an image.');
            }
            if ($file['size'] > $config['upload']['max_file_size']) {
                throw new Exception('The image is too large.');
            }
            $fileOriginal = $file['tmp_name'];
            $phpThumb = PhpThumbFactory::create($file['tmp_name']);

        } elseif ($data_type == 'transload' && parse_url($data_url, PHP_URL_HOST)) {
            if (download_file($data_url, $tempFile)) {
                if (!$imageSize = getimagesize($tempFile)) {
                    throw new Exception('The url is not an image.');
                }
            } else {
                throw new Exception('Cannot download the url.');
            }
            $fileOriginal = $tempFile;
            $phpThumb = PhpThumbFactory::create($tempFile);

        } else {
            throw new Exception('Data is invalid.');
        }
        $phpThumb->setOptions(array(
            'resizeUp'              => false,
            'correctPermissions'    => false,
            // 'preserveAlpha'         => false,
            // 'preserveTransparency'  => false,
        ));
        $logo    = $config['logo_dir'] . '/' . $data_watermark_logo . '.png';
        $minSize = explode('x', $config['watermark_minimum_size']);
        if (
            $data_watermark
            && file_exists($logo)
            && ( empty($config['watermark_minimum_size'])
                || (count($minSize) == 2
                    && $minSize[0] <= $imageSize[0]
                    && $minSize[1] <= $imageSize[1]
                )
            )
        ) {
            $keepOriginal = false;
            $phpThumb
                ->resize($data_resize)
                ->createWatermark($logo, $data_watermark_position, 0);
        } elseif($data_resize) {
            $keepOriginal = false;
            $phpThumb->resize($data_resize);
        }
        if (!$keepOriginal) {
            $phpThumb->save($tempFile);
        } elseif ($fileOriginal != $tempFile) {
            copy($fileOriginal, $tempFile);
        }

    } catch (Exception $e) {
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
        response_json(array(
            'error'   => true,
            'message' => $e->getMessage()
        ));
    }

    $result = array();
    try {

        $server = strtolower($data_server);
        $uploader = ChipVN_ImageUploader_Manager::make(ucfirst($server));
        $uploader->useCurl($config['use_curl']);
        $uploader->setCache($config['cache_adapter'], array(
            'cache_dir' => $config['session_dir']
        ));
        $serverConfig = $config[$server];
        switch ($server) {
            case 'imgur':
                $api     = random_element($serverConfig['api_keys']);
                $account = random_element($serverConfig['accounts']);

                $uploader->setApi($api['key']);
                $uploader->setSecret($api['secret']);

                if ($account) {
                    $uploader->login($account['username'], $account['password']);
                }
                break;

            case 'imageshack':
                $account = random_element($serverConfig['accounts']);
                $apiKey  = random_element($serverConfig['api_keys']);

                $uploader->login($account['username'], $account['password']);
                $uploader->setApi($apiKey);
                break;

            case 'picasanew':
                $api   = random_element($serverConfig['api_keys']);
                $tokens = load_file($serverConfig['token_file']);
                $username = array_rand($tokens);
                $token = $tokens[$username];

                $uploader->login($username, '');
                $uploader->setApi($api['key']);
                $uploader->setSecret($api['secret']);
                if ($uploader->isTokenExpired($token)) {
                    $uploader->refreshToken($token);
                    $token = $uploader->getToken();
                    write_picasa_token($serverConfig['token_file'], $username, $token);
                }
                $uploader->setToken($token);

                foreach($serverConfig['accounts'] as $account) {
                    if ($account['username'] == $username) {
                        $albumId = random_element($account['album_ids']);
                        $uploader->setAlbumId($albumId);
                        break;
                    }
                }
                break;

            case 'picasa':
                $account = random_element($serverConfig['accounts']);
                $albumId = random_element($account['album_ids']);

                $uploader->login($account['username'], $account['password']);
                $uploader->setAlbumId($albumId);
                break;

            case 'flickr':
                $api   = random_element($serverConfig['api_keys']);
                $token = random_element(require $serverConfig['token_file']);

                $uploader->setApi($api['key']);
                $uploader->setSecret($api['secret']);
                $uploader->setAccessToken($token['token'], $token['secret']);
                break;

            case 'postimage':
                if ($account = random_element($serverConfig['accounts'])) {
                   $uploader->login($account['username'], $account['password']);
                }
                break;
        }
        // group cache identifier is made by plugin name, username
        // so we should call this after call login();
        $uploader->getCache()->garbageCollect();
        $url = $uploader->upload($tempFile);
        $result = array(
            'error' => false,
            'url'   => $url,
        );
    } catch (Exception $e) {
        $result = array(
            'error'   => true,
            'message' => $e->getMessage()
        );
    }
    unlink($tempFile);

    response_json($result);
}
