<?php

function loadEnv() {
    $path = dirname(__DIR__, 2);
    $dotenv = $path . '/.env';

    if (!file_exists($dotenv)) {
        error_log("The .env file was not found at: $dotenv");
        return false;
    }

    $lines = file($dotenv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {

        $trimmedLine = trim($line);
        if (empty($trimmedLine) || strpos($trimmedLine, '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);

            $name = trim($name);
            $value = trim($value);

            if (!empty($name)) {
                putenv("$name=$value");
            }
        } else {
            error_log("Malformed line in .env file: $line");
        }
    }
    return true;
}

/**
 * This function is used to dump any data
 */
function dd(array $arr = []){

    echo "<pre>";
    print_r($arr);
    die;
}

/**
 * This function is used to dump any data
 */
function dump(array $arr = []){
    echo "<pre>";
    print_r($arr);
}


function prnt($data){
    echo "<pre>";
    print_r($data);
    echo "</pre>"; 
    exit;
}