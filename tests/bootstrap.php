<?php

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Preserve any legacy test defines if present.
$defines = __DIR__ . '/defines.php';
if (file_exists($defines)) {
    require_once $defines;
}
