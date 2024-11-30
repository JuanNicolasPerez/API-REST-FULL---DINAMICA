<?php

/*====================
    Requerimos la conexion a la base de datos
=================== */
require_once "models/connection.php";

/*====================
    Requerimos el controlador put.php
=================== */
require_once "controllers/put.controller.php";

// VALIDAMOS SI EXISTE UNA VARIABLE DE TIPO POST.-
if (isset($_GET['id']) && isset($_GET['nameId'])) {

    // CAPTURAMOS LOS DATOS DEL FORMULARIO
    $data = array();

    // CON LA FUNSION get_file_contents() LEEMOS EL CONTENIDO DEL ARCHIVO EN UNA CADENA
    // CON LA FUNSION parse_str() SE ANALIZA LA CONSULTA Y LA CONVIERTE EN UN ARRAY
    parse_str(file_get_contents('php://input'), $data);

    // INICIAMOS LA VARIABLE COLUMNS COMO UN ARRAY VACIO.-
    $columns = array();

    // REALIZAMOS UN FOREACH DE LOS PARAMETROS DE FORMULARIO POR EL METODO POST.-
    // CON LA FUNSION ARRAY_KEYS EXTRAEMOS SOLO SU INDICE O PROPIEDAD.-
    foreach (array_keys($data) as $key => $value) {

        // CON LA FUNSION ARRAY_PUSH CREAMOS EL ARRAY PARA ALMACENAR LAS COLUMNAS DE LA TABLA.-
        array_push($columns, $value);
    }

    // CON LA FUNSION ARRAY_PUSH CREAMOS EL ARRAY PARA ALMACENAR LAS COLUMNAS DE LA TABLA.-
    array_push($columns, $_GET['nameId']);

    $columns = array_unique($columns);

    // COMPARAMOS QUE EXISTA LA TABLA Y QUE SI EXISTAN LAS COLUMNAS.-
    if (empty(Connection::getColumnsData($table, $columns))) {

        // CASO QUE NO VENGA INFORMACION.-
        $json = array(
            "status" => 400,
            "result" => "Error: Fields is the form do not match the database"
        );

        // DEVOLVEMOS UNA REPUESTA Y POR CUAL METODO SE HIZO LA PETICION.-
        echo json_encode($json, http_response_code($json["status"]));

        return;
    }

    /*====================
    PETISION POST PARA USUARIOS AUTENTICADOS.-
    =================== */
    if (isset($_GET['token'])) {

        /*====================
        PETISION POST PARA USUARIOS SIN TOKEN.-
        =================== */
        if ($_GET['token'] == "no" && isset($_GET['except'])) {

            $columns = array($_GET['except']);

            // VALIDAMOS QUE EXISTA LA TABLA Y QUE SI EXISTAN LAS COLUMNAS.-
            if (empty(Connection::getColumnsData($table, $columns))) {

                // CASO QUE NO VENGA INFORMACION.-
                $json = array(
                    "status" => 400,
                    "result" => "Error: Fields is the form do not match the database"
                );

                // DEVOLVEMOS UNA REPUESTA Y POR CUAL METODO SE HIZO LA PETICION.-
                echo json_encode($json, http_response_code($json["status"]));

                return;
            }

            /*====================
            SOLICITAMOS RESPUESTA DEL CONTROLADOR PARA EL REGISTRO EN CUALQUIER TABLA.-
            =================== */
            $response = new PutController();
            $response->putData($table, $data, $_GET['id'], $_GET['nameId']);

            /*====================
            PETISION POST PARA USUARIOS CON TOKEN.-
            =================== */
        } else {
            $tableToken = $_GET['table'] ?? "users";

            $suffix = $_GET['suffix'] ?? "user";

            $validate = Connection::tokenValidate($_GET['token'], $tableToken, $suffix);

            /*====================
            TOKEN AUTORIZADO.-
            =================== */
            if ($validate == "ok") {

                /*====================
                SI LA RESPUESTA ES VERDADERA, EN LA BASE DE DATOS,
                SOLICITAMOS RESPUESTA AL CONTROLADOR PARA EDITAR DATOS EN CUALQUIER TABLA.-
                =================== */
                $response = new PutController();
                $response->putData($table, $data, $_GET['id'], $_GET['nameId']);
            }

            /*====================
            TOKEN EXPIRADO.-
            =================== */
            if ($validate == "expired") {

                // CASO QUE NO SE ENVIE INFORMACION.-
                $json = array(
                    "status" => 303,
                    "result" => "Error: The token has expired",
                );

                // DEVOLVEMOS UNA REPUESTA Y POR CUAL METODO SE HIZO LA PETICION
                echo json_encode($json, http_response_code($json["status"]));

                return;
            }

            /*====================
            TOKEN NO COINCIDE EN BASE DE DATOS.-
            =================== */
            if ($validate == "no-auth") {

                // CASO QUE NO SE ENVIE INFORMACION.-
                $json = array(
                    "status" => 400,
                    "result" => "Error: The user is not authorized",
                );

                // DEVOLVEMOS UNA REPUESTA Y POR CUAL METODO SE HIZO LA PETICION
                echo json_encode($json, http_response_code($json["status"]));

                return;
            }
        }
    } else {

        // CASO QUE NO SE ENVIE TOKEN.-
        $json = array(
            "status" => 400,
            "result" => "Error: Authorization required",
        );

        // DEVOLVEMOS UNA REPUESTA Y POR CUAL METODO SE HIZO LA PETICION
        echo json_encode($json, http_response_code($json["status"]));

        return;
    }
}
