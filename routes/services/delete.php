<?php

/*====================
    Requerimos la conexion a la base de datos
=================== */
require_once "models/connection.php";

/*====================
    Requerimos el controlador put.php
=================== */
require_once "controllers/delete.controller.php";

// VALIDAMOS SI EXISTE UNA VARIABLE DE TIPO POST.-
if (isset($_GET['id']) && isset($_GET['nameId'])) {

    // CREAMOS EL ARRAY PARA ALMACENAR LA COLUMNA
    $columns = array($_GET['nameId']);

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

        $tableToken = $_GET['table'] ?? "users";

        $suffix = $_GET['suffix'] ?? "user";

        $validate = Connection::tokenValidate($_GET['token'], $tableToken, $suffix);

        /*====================
        TOKEN AUTORIZADO.-
        =================== */
        if ($validate == "ok") {

            /*====================
            SI LA RESPUESTA ES VERDADERA, EN LA BASE DE DATOS,
            SOLICITAMOS RESPUESTA AL CONTROLADOR PARA ELIMINAR DATOS EN CUALQUIER TABLA.-
            =================== */
            $response = new DeleteController();
            $response->deleteData($table, $_GET['id'], $_GET['nameId']);
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
    }else{

        // CASO QUE NO SE ENVIE INFORMACION.-
        $json = array(
            "status" => 400,
            "result" => "Error: Authorization required",
        );

        // DEVOLVEMOS UNA REPUESTA Y POR CUAL METODO SE HIZO LA PETICION
        echo json_encode($json, http_response_code($json["status"]));

        return;

    }
}
