<?php

require_once "get.model.php";

class Connection
{

    /*====================
    INFORMACION DE LA BASE DE DATOS
    =================== */
    static public function infoDatabase()
    {
        $infoDB = array(
            "database" => "database-1",
            "user" => "root",
            "pass" => ""
        );

        return $infoDB;
    }

    /*====================
    ACCESO PRIVADO A LA API A TRAVES DE LA LLAVE DE SEGURIDAD
    =================== */
    public static function apiKey(){
        return "pfwAvABFH7eyPS5QT3HeMMa1Nu34Bh";
    }

    /*====================
    ACCESO PUBLICO A LA API SIN LA LLAVE DE SEGURIDAD
    =================== */
    public static function publicAccess(){

        $table = ["courses"];

        return $table;

    }

    /*====================
    CONEXION DE LA BASE DE DATOS
    =================== */
    static public function connect()
    {
        try {
            $link = new PDO(
                "mysql:host=localhost;dbname=" . Connection::infoDatabase()["database"],
                Connection::infoDatabase()["user"],
                Connection::infoDatabase()["pass"],
            );

            $link->exec("set names utf8");
        } catch (PDOException $e) {
            die("Error: " . $e->getMessage());
        }

        return $link;
    }

    /*====================
    VALIDAR EXISTENCIA DE UNA TABLA EN LA BD
    =================== */
    static public function getColumnsData($table, $columns){

        /*====================
        TRAER EL NOMBRE DE LA BASE DE DATOS
        =================== */
        $database = Connection::infoDatabase()["database"];

        /*====================
        TRAEMOS TODAS LAS COLUMNAS DE UNA TABLA
        =================== */
        $validate = Connection::connect()
                    ->query("SELECT COLUMN_NAME AS item FROM information_schema.columns WHERE table_schema = '$database' AND table_name = '$table'")
                    ->fetchAll(PDO::FETCH_OBJ)
        ;

        /*====================
        VALIDAR EXISTENCIA DE LA TABLA EN LA BD
        =================== */
        if (empty($validate)) {

            return null;

        }else {

            /*====================
            AJUSTE DE SOLICITUD DE COLUMNAS GLOBALES
            =================== */
            if ($columns[0] == "*") {

                array_shift($columns);

            }

            /*====================
            VALIDAR EXISTENCIA DE COLUMNAS EN LA BD
            =================== */
            $sum = 0;

            foreach ($validate as $key => $value) {
                $sum += in_array($value->item, $columns);
            }

            return $sum ==  count($columns) ? $validate : null;

        }

    }

    /*====================
    GENERAR TOKEN DE AUTENTICACION
    =================== */
    static public function jwt($id, $email){

        $time = time();

        $token = array(
            "iat" => $time, //TIEMPO EN EL QUE INICIA EL TOKEN.-
            "exp" => $time + (60*60*24), // TIEMPO EN EL QUE EXPIRA EL TOKEN.-
            "data" => [
                "id" => $id,
                "email"=> $email
            ],
            "signature" => "SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c"
        );

        // RETORNADAMOS TOKEN PARA CODIGO JWT
        return $token;
    }

    /*====================
    VALIDAR EL TOKEN DE SEGURIDAD
    =================== */
    public static function tokenValidate($token, $table, $suffix){

        /*====================
        TRAEMOS AL USUARIO SEGUN EL TOKEN
        =================== */
        $user = GetModel::getDataFilter($table, "token_exp_".$suffix,  "token_".$suffix, $token, null, null, null, null);

        if(!empty($user)){

            /*====================
            VALIDAR EL TOKEN DE SEGURIDAD NO HAYA EXPIRADO
            =================== */
            $time = time();

            if ($user[0]->{"token_exp_".$suffix} > $time) {

                return "ok";

            }else{

                return "expired";

            }

        }else{

            return "no-auth";

        }

    }

}
