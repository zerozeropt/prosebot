<?php

function not_found() {
    echo json_encode(array("message" => "Not found"));
    http_response_code(404);
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

if (!isset($uri[2]) || !isset($uri[3])) {
    not_found();
    exit();
}

if ($uri[2] === "properties") {
    require_once(__DIR__.'/PropertiesController.php');
    $controller = new PropertiesController();
    $controller->{$uri[3]}();
}
else if ($uri[2] === "validator") {
    require_once(__DIR__.'/ValidatorController.php');
    $controller = new ValidatorController();
    $controller->{$uri[3]}();
}
else {
    not_found();
    exit();
}
