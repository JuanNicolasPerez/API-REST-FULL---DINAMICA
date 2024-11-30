<?php

/*====================
    RequerimientoS a la API
=================== */
require_once "models/post.model.php";
require_once "models/get.model.php";
require_once "models/put.model.php";
require_once "models/connection.php";

// libreria JWT
require_once "vendor/autoload.php";

use Firebase\JWT\JWT;

class PostController
{
    /*====================
    PETICION POST PARA CREAR DATOS
    =================== */
    static public function postData($table, $data)
    {

        /*====================
        SOLICITAMOS RESPUESTA AL MODELO PARA CREAR DATOS EN CUALQUIER TABLA.-
        =================== */
        $response = PostModel::postData($table, $data);

        /*====================
        RESPUESTA GLOBAL-.
        =================== */
        $return = new PostController();

        $return->fncResponse($response, null, null);
    }

    /*====================
    PETICION POST PARA CREAR AL USUARIO
    =================== */
    static public function postRegister($table, $data, $suffix)
    {

        /*====================
        VALIDAMOS QUE PASSWORD NO VENGA VACIO DESDE EL FORMULARIO.-
        =================== */
        if (isset($data['password_' . $suffix]) && $data['password_' . $suffix] != null) {

            /*====================
            ENCRIPTAMOS LA CONTRASEÑA.-
            =================== */
            $crypt = crypt($data['password_' . $suffix], '$2a$07$usesomesillystringforsalt$');

            /*====================
            ENCRIPTADA LA CONTRASEÑA LA ALMACENAMOS EN LA VARIABLE $DATA EN SU ATRIBUTO PASSWORD.-
            =================== */
            $data['password_' . $suffix] = $crypt;

            /*====================
            SOLICITAMOS RESPUESTA AL MODELO PARA CREAR AL USUARIO.-
            =================== */
            $response = PostModel::postData($table, $data);

            /*====================
            RESPUESTA GLOBAL-.
            =================== */
            $return = new PostController();

            $return->fncResponse($response, null, $suffix);

        } else {

            /*====================
            REGISTRO DE UN USUARIO DESDE UNA APP EXTERNA-.
            =================== */
            $response = PostModel::postData($table, $data);

            if (isset($response['comment']) && $response['comment'] == "The process was successful") {

                // VALIDAMOS EL USUARIO QUE EXISTA EN LA BASE DE DATOS.-
                $response = GetModel::getDataFilter($table, "*", "email_" . $suffix, $data['email_' . $suffix], null, null, null, null);

                if (!empty($response)) {

                    $token = Connection::jwt($response[0]->{"id_" . $suffix}, $response[0]->{"email_" . $suffix});

                    // GENERAMOS EL TOKEN JWT
                    $jwt = JWT::encode($token, $response[0]->{"email_" . $suffix}, 'HS256');

                    /*====================
                    ACTUALIZAMOS LA BASE DE DATOS CON EL TOKEN DEL USUARIO.-
                    =================== */
                    $data = array(
                        "token_" . $suffix => $jwt,
                        "token_exp_" . $suffix => $token["exp"]
                    );

                    $update = PutModel::putData($table, $data, $response[0]->{"id_" . $suffix}, "id_" . $suffix);

                    // MENSAJE PARA IR OSERVANDO QUE INFORMACION TRAEMOS.
                    // echo '<pre>'; print_r($update); echo '</pre>';
                    // return;

                    if (isset($update['comment']) && $update['comment'] == "The process was successful") {

                        $response[0]->{"token_" . $suffix} = $jwt;
                        $response[0]->{"token_exp_" . $suffix} = $token["exp"];

                        /*====================
                        RESPUESTA GLOBAL-.
                        =================== */
                        $return = new PostController();

                        $return->fncResponse($response, null, $suffix);
                    }
                }
            }
        }
    }

    /*====================
    PETICION POST PARA LOGEAR AL USUARIO
    =================== */
    static public function postLogin($table, $data, $suffix)
    {

        // VALIDAMOS EL USUARIO QUE EXISTA EN LA BASE DE DATOS.-
        $response = GetModel::getDataFilter($table, "*", "email_" . $suffix, $data['email_' . $suffix], null, null, null, null);

        if (!empty($response)) {

            if ($response[0]->{"password_" . $suffix} != null) {
                /*====================
                ENCRIPTAMOS LA CONTRASEÑA.-
                =================== */
                $crypt = crypt($data['password_' . $suffix], '$2a$07$usesomesillystringforsalt$');

                // COMPARAMOS QUE LAS CONTRASEÑA SEAN IGUALES
                if ($response[0]->{"password_" . $suffix} == $crypt) {

                    /*====================
                    GENERAMOS EL TOKEN DESDE EL USUARIO EN BASE DE DATOS-.
                    =================== */
                    $token = Connection::jwt($response[0]->{"id_" . $suffix}, $response[0]->{"email_" . $suffix});

                    // GENERAMOS EL TOKEN JWT
                    $jwt = JWT::encode($token, $response[0]->{"password_" . $suffix}, 'HS256');

                    /*====================
                    ACTUALIZAMOS LA BASE DE DATOS CON EL TOKEN DEL USUARIO.-
                    =================== */
                    $data = array(
                        "token_" . $suffix => $jwt,
                        "token_exp_" . $suffix => $token["exp"]
                    );

                    $update = PutModel::putData($table, $data, $response[0]->{"id_" . $suffix}, "id_" . $suffix);

                    // MENSAJE PARA IR OSERVANDO QUE INFORMACION TRAEMOS.
                    // echo '<pre>'; print_r($update); echo '</pre>';
                    // return;

                    if (isset($update['comment']) && $update['comment'] == "The process was successful") {

                        $response[0]->{"token_" . $suffix} = $jwt;
                        $response[0]->{"token_exp_" . $suffix} = $token["exp"];

                        /*====================
                        RESPUESTA GLOBAL-.
                        =================== */
                        $return = new PostController();

                        $return->fncResponse($response, null, $suffix);
                    }
                } else {

                    // CASO QUE ESTE MAL LA CONTRAÑESA NOS DEVUELVE
                    $response = null;

                    /*====================
                    RESPUESTA GLOBAL-.
                    =================== */
                    $return = new PostController();

                    $return->fncResponse($response, "wrong password", $suffix);
                }
            } else {
                /*====================
                LOGEO DE UN USUARIO DESDE UNA APP EXTERNA, SIN CONTRASEÑA-.
                =================== */

                $token = Connection::jwt($response[0]->{"id_" . $suffix}, $response[0]->{"email_" . $suffix});

                // GENERAMOS EL TOKEN JWT
                $jwt = JWT::encode($token, $response[0]->{"email_" . $suffix}, 'HS256');

                /*====================
                ACTUALIZAMOS LA BASE DE DATOS CON EL TOKEN DEL USUARIO.-
                =================== */
                $data = array(
                    "token_" . $suffix => $jwt,
                    "token_exp_" . $suffix => $token["exp"]
                );

                $update = PutModel::putData($table, $data, $response[0]->{"id_" . $suffix}, "id_" . $suffix);

                // MENSAJE PARA IR OSERVANDO QUE INFORMACION TRAEMOS.
                // echo '<pre>'; print_r($update); echo '</pre>';
                // return;

                if (isset($update['comment']) && $update['comment'] == "The process was successful") {

                    $response[0]->{"token_" . $suffix} = $jwt;
                    $response[0]->{"token_exp_" . $suffix} = $token["exp"];

                    /*====================
                    RESPUESTA GLOBAL-.
                    =================== */
                    $return = new PostController();

                    $return->fncResponse($response, null, $suffix);
                }
            }

        } else {

            // CASO QUE ESTE MAL EL EMAIL NOS DEVUELVE
            $response = null;

            /*====================
            RESPUESTA GLOBAL-.
            =================== */
            $return = new PostController();

            $return->fncResponse($response, "wrong email", $suffix);
        }
    }

    /*====================
    Respuesta del controlador
    =================== */
    public function fncResponse($response, $error, $suffix)
    {

        if (!empty($response)) {

            /*====================
            QUITAMOS LA CONTRASEÑA DE LA RESPUESTA
            =================== */
            if (isset($response[0]->{'password_' . $suffix})) {

                //CON LA FUNSION UNSET() ELIMINAMOS UNA VARIABLE ESPECIFICA, POR EJEMPLO LA PROPIEDAD PASSWORD_INSTRUCTOR DEL ARRAY.-
                unset($response[0]->{'password_' . $suffix});
            }


            // CREAMOS EL DATO JSON PARA DEVOLVER LA INFORMACION.-
            $json = array(
                "status" => 200,
                "results" => $response
            );
        } else {

            if ($error != null) {

                // CASO QUE ERROR NO VENGA NULO.-
                $json = array(
                    "status" => 400,
                    "result" => $error,
                );
            } else {

                // CASO QUE NO SE ENVIE INFORMACION.-
                $json = array(
                    "status" => 404,
                    "result" => "Not found",
                    "method" => "post"
                );
            }
        }

        // DEVOLVEMOS UNA REPUESTA Y POR CUAL METODO SE HIZO LA PETICION
        echo json_encode($json, http_response_code($json["status"]));
    }
}
