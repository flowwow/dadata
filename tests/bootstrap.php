<?php

require __DIR__.'/../vendor/autoload.php';
$handler = static function ($errno, $errstr, $errfile = null, $errline = null) {
    $typeMap = [
        E_USER_ERROR => 'E_USER_ERROR',
        E_USER_WARNING => 'E_USER_WARNING',
        E_USER_NOTICE => 'E_USER_NOTICE',
        E_USER_DEPRECATED => 'E_USER_DEPRECATED',
        E_ERROR => 'E_ERROR',
        E_WARNING => 'E_WARNING',
        E_NOTICE => 'E_NOTICE',
        E_DEPRECATED => 'E_DEPRECATED',
        E_STRICT => 'E_STRICT',
    ];
    $type = $typeMap[$errno] ?? '#'.$errno;

    throw new ErrorException("{$type} {$errstr} in {$errfile}:{$errline}", $errno, 1, $errfile, $errline);
};
set_error_handler($handler, ini_get('error_reporting'));