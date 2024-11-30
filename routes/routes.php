<?php

require_once "models/connection.php";
require_once "controllers/get.controller.php";

/*====================
CONSULTAMOS QUE PETISION TRAE EN LA RUTA
=================== */
$routesArray = explode("/", $_SERVER['REQUEST_URI']);
$routes_array = array_filter($routesArray);

//echo '<pre>'; print_r($routes_array); echo '</pre>';

/*====================
    CUANDO NO SE HACE NINGUNA PETICION A LA API
=================== */
if (count($routes_array) == 0) {

    // CREAMOS EL DATO JSON PARA DEVOLVER LA INFORMACION.-
    $json = array(
        "status" => 404,
        "results" => "Not found"
    );

    // DEVOLVEMOS UNA REPUESTA Y POR CUAL METODO SE HIZO LA PETICION
    echo json_encode($json, http_response_code($json["status"]));

    return;
}

/*====================
    CUANDO SI SE HACE UNA PETICION A LA API
=================== */
if (count($routes_array) == 2 && isset($_SERVER['REQUEST_METHOD'])) {

    /*====================
    EXTRAMOS LA TABLA DESDE LA URL PARA PODER UTILIZARLO EN CUALQUIER PETICION
    =================== */
    $table = explode("?", $routes_array[2])[0];

    /*====================
    VALIDAMOS LA LLAVE SECRETA DE LA API
    =================== */
    if(!isset(getallheaders()["Authorization"]) || getallheaders()["Authorization"] != Connection::apiKey()){

        /*====================
        VALIDAMOS LA TABLA
        =================== */
        // if (in_array($table, Connection::publicAccess()) == 0) {

            // CASO QUE NO VENGA INFORMACION.-
            $json = array(
                "status" => 400,
                "result" => "Error: You are not authorized to make this request"
            );

            // DEVOLVEMOS UNA REPUESTA Y POR CUAL METODO SE HIZO LA PETICION.-
            echo json_encode($json, http_response_code($json["status"]));

            return;

        // }else {

            /*====================
            ACCESO PUBLICO A LA API
            =================== */
            // $response = new GetController();
            // $response -> getData($table, "*", null, null, null, null);

            // return;

        // }

    }

    /*====================
    CUANDO SI SE HACE UNA PETICION DE TIPO GET
    =================== */
    if ($_SERVER['REQUEST_METHOD'] == "GET") {

        include "services/get.php";

    }

    /*====================
    CUANDO SI SE HACE UNA PETICION DE TIPO POST
    =================== */
    if ($_SERVER['REQUEST_METHOD'] == "POST") {

        include "services/post.php";

    }

        /*====================
    CUANDO SI SE HACE UNA PETICION DE TIPO GET
    =================== */
    if ($_SERVER['REQUEST_METHOD'] == "PUT") {

        include "services/put.php";

    }

        /*====================
    CUANDO SI SE HACE UNA PETICION DE TIPO GET
    =================== */
    if ($_SERVER['REQUEST_METHOD'] == "DELETE") {

        include "services/delete.php";

    }
}