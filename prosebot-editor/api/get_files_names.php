<?php
/**
 * Get the names of all template files for a given context and language
 * METHOD: GET
 * PARAMS: $context, $lang
 * RESPONSE: array List of names
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (isset($_GET['context']) && isset($_GET['lang'])) {
    $dir = __DIR__ . '/../templates/' . $_GET["context"] . '/' . $_GET["lang"] . '/';
    $templates = scandir($dir);
    $data = [];
    foreach ($templates as $template_name) {
        if ($template_name == "." || $template_name == "..")
            continue;
        array_push($data, $template_name);
    }
    echo json_encode($data);
    http_response_code(200);
} 
else {
    echo json_encode(array("message" => "Bad request."));
    http_response_code(400);
}
