<?php

function load_file($file, $once = true)
{
    $file = file_path($file, true);

    return $once ? include_once($file) : include($file);
}

function file_path($file, $exit = false)
{
    $file = ltrim(str_replace(DOCROOT, '', $file), '\/');
    foreach (array(DOCROOT . '/dev/' . $file, DOCROOT . '/' . $file) as $path) {
        if (file_exists($path)) return $path;
    }
    if ($exit) die("File '$file' does not exists.");

    return false;
}

function download_file($url, $destination)
{
    $url = strtr(trim(rawurldecode($url)), array(' ' => '%20'));
    if ($data = fopen($url, "rb")) {
        $newfile = fopen($destination, "w");
        while ($buff = fread($data, 1024*8)) {
            fwrite($newfile, $buff);
        }
        fclose($data);
        fclose($newfile);

        return true;
    }

    return false;
}

function response_json(array $result)
{
    echo json_encode($result);
    exit;
}

function random_element($array)
{
    if (is_array($array) && null !== $key = array_rand($array)) {
        return $array[$key];
    }

    return null;
}

function write_picasa_token($token_file, $username, $token)
{
    if (file_exists($token_file)) {
        $existing = load_file($token_file);
    }
    if (empty($existing) || !is_array($existing)) $existing = array();

    $data = array(
        strtolower($username) => $token
    );

    $exported = var_export(array_merge($existing, $data), true);

    return file_put_contents($token_file, '<?php' . PHP_EOL .'return ' . $exported . ';');
}

function write_flickr_token($token_file, $username, $token, $secret)
{
    if (file_exists($token_file)) {
        $existing = load_file($token_file);
    }
    if (empty($existing) || !is_array($existing)) $existing = array();

    $data = array(
        strtolower($username) => array(
            'token'  => $token,
            'secret' => $secret,
        )
    );

    $exported = var_export(array_merge($existing, $data), true);

    return file_put_contents($token_file, '<?php' . PHP_EOL .'return ' . $exported . ';');
}
