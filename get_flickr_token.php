<?php
session_start();

header('Content-type: text/html;charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once './global.php';
load_file('vendor/ChipVN/ClassLoader/Loader.php');
ChipVN_ClassLoader_Loader::registerAutoload();

$config = load_file('includes/config.php');

$config = $config['flickr'];

$callback = 'http' . (getenv('HTTPS') == 'on' ? 's' : '') . '://'.$_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

$uploader = ChipVN_ImageUploader_Manager::make('Flickr');

$api = random_element($config['api_keys']);

$uploader->setApi($api['key']);
$uploader->setSecret($api['secret']);
$token = $uploader->getOAuthToken($callback);


write_flickr_token($config['token_file'], $token['username'], $token['oauth_token'], $token['oauth_token_secret']);

echo "Done!<br />";
echo '<a href="' . $callback . '">Click here to add new token (must use other yahoo account).</a>';
