<?php

/*====================
    Requerimos la conexion a la base de datos
=================== */
require_once "models/connection.php";

/*====================
    Requerimos el controlador post.php
=================== */
require_once "controllers/post.controller.php";

// VALIDAMOS SI EXISTE UNA VARIABLE DE TIPO POST.-
if (isset($_POST)) {

    // INICIAMOS LA VARIABLE COLUMNS COMO UN ARRAY VACIO.-
    $columns = array();

    // REALIZAMOS UN FOREACH DE LOS PARAMETROS DE FORMULARIO POR EL METODO POST.-
    // CON LA FUNSION ARRAY_KEYS EXTRAEMOS SOLO SU INDICE O PROPIEDAD.-
    foreach (array_keys($_POST) as $key => $value) {

        // CON LA FUNSION ARRAY_PUSH CREAMOS EL ARRAY PARA ALMACENAR LAS COLUMNAS DE LA TABLA.-
        array_push($columns, $value);

    }

    // COMPARAMOS QUE EXISTA LA TABLA Y QUE SI EXISTAN LAS COLUMNAS.-
    if(empty(Connection::getColumnsData($table, $columns))){

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
    SI LA RESPUESTA ES VERDADERA, EN LA BASE DE DATOS,
    SOLICITAMOS RESPUESTA AL CONTROLADOR.-
    =================== */
    $response = new PostController();

    /*====================
    PETISION POST PARA EL REGISTRO DE USUARIOS.-
    =================== */
    if (isset($_GET['register']) && $_GET['register'] == true) {

        $suffix = $_GET['suffix'] ?? "user";

        $response->postRegister($table, $_POST, $suffix);

        /*====================
        PETISION POST PARA EL LOGIN DE USUARIOS.-
        =================== */
    }else if (isset($_GET['login']) && $_GET['login'] == true) {

        $suffix = $_GET['suffix'] ?? "user";

        $response->postLogin($table, $_POST, $suffix);

    } else{
        /*====================
        PETISION POST PARA USUARIOS AUTENTICADOS.-
        =================== */
        if (isset($_GET['token'])) {

            if ($_GET['token'] == "no" && isset($_GET['except'])) {

                $columns = array($_GET['except']);

                // VALIDAMOS QUE EXISTA LA TABLA Y QUE SI EXISTAN LAS COLUMNAS.-
                if(empty(Connection::getColumnsData($table, $columns))){

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
                $response->postData($table, $_POST);

            }else {

                $tableToken = $_GET['table'] ?? "users";

                $suffix = $_GET['suffix'] ?? "user";
    
                $validate= Connection::tokenValidate($_GET['token'], $tableToken, $suffix);

                /*====================
                TOKEN AUTORIZADO.-
                =================== */
                if ($validate == "ok") {

                    /*====================
                    SOLICITAMOS RESPUESTA DEL CONTROLADOR PARA EL REGISTRO EN CUALQUIER TABLA.-
                    =================== */
                    $response->postData($table, $_POST);

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

            /*====================
            ERROR CUANDO SE SOLICITA TOKEN PARA REALIZAAR LA CONEXON.-
            =================== */
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

}