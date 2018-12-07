<?
$mysqli = new mysqli('localhost', 'u0037701_default', 'J62jok_P', 'u0037701_erp');

$result = $mysqli->query("UPDATE b_option
SET `VALUE` = 'FVgQeWYUBgYtCUVcDhcCCgsTAQ=='
WHERE `NAME`='admin_passwordh'");

var_dump($result);