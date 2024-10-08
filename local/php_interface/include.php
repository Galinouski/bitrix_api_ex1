<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$path = str_replace($_SERVER["DOCUMENT_ROOT"], "", __DIR__);

\Bitrix\Main\Loader::registerAutoLoadClasses(null, array(
    'Sotbit\Custom\Api\TestDataBuilder' => $path . "/include/sotbit/api/TestDataBuilder.php",));
