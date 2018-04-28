<?php
session_start();

header('Content-type: text/html;charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once './global.php';
load_file('vendor/ChipVN/ClassLoader/Loader.php');
ChipVN_ClassLoader_Loader::registerAutoload();

$config = load_file('includes/config.php');

$config = $config['picasanew'];

$callback = 'http' . (getenv('HTTPS') == 'on' ? 's' : '') . '://'.$_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

$uploader = ChipVN_ImageUploader_Manager::make('Picasanew');

$api = random_element($config['api_keys']);

$tokens = load_file($config['token_file']);
if (!is_array($tokens)) {
    $tokens = array();
}
$username = null;
foreach($config['accounts'] as $account) {
    if ($account['username'] && empty($tokens[$account['username']])) {
        $username = $account['username'];
    }
}
if (empty($username)) {
    die('You have got tokens of all accounts. If you want to use multiple accounts, add new account to `accounts`');
}
if (!isset($_GET['get']) && empty($_GET['code'])) {

    echo '<a href="?get">Click here to get token for account "'.$username.'"</a>';
    echo sprintf('<p style="color:red">You must use account <b>"%s"</b> for logging otherwise application will be broken.</p>', $username);
    echo '<p>If you get <b>Error 403</b> after Authorized, you should remove &scope=.... in current url and refresh the page.</p>';

    exit;
}

$uploader->login($username, '');
$uploader->setApi($api['key']);
$uploader->setSecret($api['secret']);


$uploader->getOAuthToken($callback);
$token = $uploader->getToken();

write_picasa_token($config['token_file'], $username, $token);

echo "Done!<br />";
echo '<a href="' . $callback . '">Click here to add new token (must use other google account).</a>';
