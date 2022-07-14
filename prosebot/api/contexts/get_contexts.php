<?php
/**
 * Get contexts list
 * METHOD: GET
 * RESPONSE: array List of contexts
 */

require_once(__DIR__.'/../vendor/autoload.php');
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/..');
$dotenv->load();

header("Access-Control-Allow-Origin: ".$_ENV['CLIENT']);
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require_once(__DIR__.'/../../global_vars.php');

echo json_encode(array_keys(get_globals()['contexts']));
http_response_code(200);
?>
