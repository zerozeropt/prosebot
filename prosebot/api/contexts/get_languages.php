<?php
/**
 * Get languages list
 * METHOD: GET
 * RESPONSE: array List of languages
 */

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require_once(__DIR__.'/../../global_vars.php');

echo json_encode(array_keys(get_globals()['languages']));
http_response_code(200);
