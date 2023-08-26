<?php
#require 'vendor/autoload.php';
require "medoo.php";
use Medoo\Medoo;

$database = new Medoo([
	'database_type' => 'mysql',
	'database_name' => '',
	'server' => 'db.dw141.webglobe.com',
	'username' => '.cz',
	'password' => ''
]);

// SumUp konfigurace
$sumUp = [
    'client_id' => 'cc_classicLyMxEDzq',
    'client_secret' => 'cc_sk_classic_Rq3Cii0ZZIxY9',
    'redirect_uri' => 'https://dob....-up/redirect_uri.php'
];
