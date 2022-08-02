<?php

function not_found()
{
    echo json_encode(array("message" => "Not found"));
    http_response_code(404);
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

if (!isset($uri[2]) || $uri[2] !== "templates") {
    not_found();
    exit();
}

require_once(__DIR__ . '/TemplateController.php');
$controller = new TemplateController();
switch ($_SERVER['REQUEST_METHOD']) {
    case 'DELETE': {
        if (!isset($uri[3])) {
            not_found();
            exit();
        }
        $controller->delete($uri[3]);
        break;
    }
    case 'GET': {
        if (!isset($uri[3])) {
            not_found();
            exit();
        }
        if (isset($uri[4])) {
            $controller->{$uri[4]}($uri[3]);
        } 
        else {
            $controller->{$uri[3]}();
        }
        break;
    }
    case 'POST': {
        $controller->create();
        break;
    }
    case 'PUT': {
        if (!isset($uri[3])) {
            not_found();
            exit();
        }
        if (isset($uri[4])) {
            $controller->{"update_" . $uri[4]}($uri[3]);
        } else {
            not_found();
            exit();
        }
        break;
    }
    case 'OPTIONS': {
        break;
    }
    default: {
        not_found();
        break;
    }
}
