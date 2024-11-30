<?php

/*====================
    Mostrar errores
=================== */
ini_set('display_errors', 1);
ini_set("log_errors", 1);
ini_set("error_log", "C:/xampp/htdocs/apirest-dinamica/php_error_log");

/*====================
    Configurar CORS
=================== */
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origen, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow--Methods: GET, POST, PUT, DELETE');
header('content-type: application/json; charset=utf-8');

/*====================
    RequerimientoS a la API
=================== */
require_once "models/connection.php";
require_once "controllers/routes.controller.php";

$index = new RoutesController();
$index->index();