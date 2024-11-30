<?php

/*====================
    RequerimientoS a la API
=================== */
require_once "controllers/get.controller.php";

/*====================
CONSULTAMOS QUE PETISION TRAE EN LA RUTA,
EN CASO DE POSEER PARAMETROS EN EL METODO GET 
DESDE LA PROPIEDAD SELECT
=================== */
$table = explode("?", $routes_array[2])[0];

// echo '<pre>'; print_r($table); echo '</pre>';
// return;

/*====================
ENVIAMOS POR PARAMETRO LOS CAMPOS QUE QUEREMOS QUE SE SELECCIONE PARA LA CONSULTA
=================== */
$select = $_GET['select'] ?? "*";
$orderBy = $_GET['orderBy'] ?? null;
$orderMode = $_GET['orderMode'] ?? null;
$startAt = $_GET['startAt'] ?? null;
$endAt = $_GET['endAt'] ?? null;
$filterTo = $_GET['filterTo'] ?? null;
$inTo = $_GET['inTo'] ?? null;

/*====================
Creamos el objeto del controlador GET
=================== */
$response = new GetController();

/*====================
Petisiones GET con filtro
=================== */
if (isset($_GET['linkTo']) && isset($_GET['equalTo']) && !isset($_GET["rel"]) && !isset($_GET["type"])) {
    $response->getDataFilter(
        $table,
        $select,
        $_GET['linkTo'],
        $_GET['equalTo'],
        $orderBy,
        $orderMode,
        $startAt,
        $endAt
    );

    /*====================
    Petisiones GET sin filtro entre tablas relacionadas
    =================== */
} else if (isset($_GET["rel"]) && isset($_GET["type"]) && $table == "relations" && !isset($_GET["linkTo"]) && !isset($_GET["equalTo"])) {
    $response->getRelData(
        $_GET["rel"],
        $_GET["type"],
        $select,
        $orderBy,
        $orderMode,
        $startAt,
        $endAt
    );

    /*====================
    Petisiones GET con filtro entre tablas relacionadas
    =================== */
} else if (isset($_GET["rel"]) && isset($_GET["type"]) && $table == "relations" && isset($_GET["linkTo"]) && isset($_GET["equalTo"])) {
    $response->getRelFilterData(
        $_GET["rel"],
        $_GET["type"],
        $select,
        $_GET['linkTo'],
        $_GET['equalTo'],
        $orderBy,
        $orderMode,
        $startAt,
        $endAt
    );

    /*====================
    Petisiones GET para el buscador
    =================== */
} elseif (!isset($_GET["rel"]) && !isset($_GET["type"]) && isset($_GET['linkTo']) && isset($_GET['search'])) {
    $response->getDataSearch(
        $table,
        $select,
        $_GET['linkTo'],
        $_GET['search'],
        $orderBy,
        $orderMode,
        $startAt,
        $endAt
    );

    /*====================
    Petisiones GET para el buscador con tablas relacionadas
    =================== */
} elseif (isset($_GET["rel"]) && isset($_GET["type"]) && $table == "relations" && isset($_GET['linkTo']) && isset($_GET['search'])) {
    $response->getRelDataSearch(
        $_GET["rel"],
        $_GET["type"],
        $select,
        $_GET['linkTo'],
        $_GET['search'],
        $orderBy,
        $orderMode,
        $startAt,
        $endAt
    );

    /*====================
    Petisiones GET para seleccion de rangos
    =================== */
} elseif (!isset($_GET["rel"]) && !isset($_GET["type"]) && isset($_GET["linkTo"]) && isset($_GET["between1"]) && isset($_GET['between2'])) {
    $response->getDataRange(
        $table,
        $select,
        $_GET['linkTo'],
        $_GET['between1'],
        $_GET['between2'],
        $filterTo,
        $inTo,
        $orderBy,
        $orderMode,
        $startAt,
        $endAt
    );

    /*====================
    Petisiones GET para seleccion de rangos con tablas relacionadas
    =================== */
} elseif (isset($_GET["rel"]) && isset($_GET["type"]) && $table == "relations" && isset($_GET["linkTo"]) && isset($_GET["between1"]) && isset($_GET['between2'])) {
    $response->getRelDataRange(
        $_GET["rel"],
        $_GET["type"],
        $select,
        $_GET['linkTo'],
        $_GET['between1'],
        $_GET['between2'],
        $filterTo,
        $inTo,
        $orderBy,
        $orderMode,
        $startAt,
        $endAt
    );

    /*====================
    Petisiones GET sin filtro
    =================== */
} else {
    $response->getData(
        $table,
        $select,
        $orderBy,
        $orderMode,
        $startAt,
        $endAt
    );
}
