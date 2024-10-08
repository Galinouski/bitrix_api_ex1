<?php

function debug($data){
    echo "Debug results: ";
    echo '<pre>' . print_r($data, 1) . '</pre>';
}

if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include.php")) {
    include_once $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include.php";
}

if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/sotbit/testAgentRouter.php")) {
    include_once $_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/sotbit/testAgentRouter.php";
}