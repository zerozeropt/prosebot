<?php
/**
 * Delete template file
 * METHOD: DELETE
 * PARAMS: $context, $lang, $name
 * RESPONSE: string Confirmation or error
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (isset($_GET["name"]) && isset($_GET["context"]) && isset($_GET["lang"])) {
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    $path = __DIR__.'/../templates/'.$_GET["context"].'/'.$_GET["lang"].'/'.$_GET["name"];
    if (file_exists($path)) {
        unlink($path);
        echo json_encode(array("message" => "File deleted."));
        http_response_code(200);
    }
    else {
        echo json_encode(array("message" => "Unable to locate file."));
        http_response_code(400);
    }
}
else {
    echo json_encode(array("message" => "Unable to locate file."));
    http_response_code(400);
}