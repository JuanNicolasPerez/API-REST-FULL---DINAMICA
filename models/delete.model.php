<?php

require_once "connection.php";
require_once "get.model.php";

class DeleteModel
{

    /*====================
    PETICION POST PARA CREAR DATOS
    =================== */
    static public function deleteData($table, $id, $nameId){

        // VALIDAMOS EL ID
        $response = GetModel::getDataFilter($table, $nameId, $nameId, $id, null, null, null, null);

        if (empty($response)) {

            return null;

        }

        // ELIMINAMOS REGISTROS
        $sql = "DELETE  
                FROM $table
                WHERE $nameId = :$nameId"
        ;

        // PREPARAMOS LA VARIABLE DE CONEXION
        $link = Connection::connect();

        // PREPARAMOS LA SENTENCIA SQL
        $stmt = $link->prepare($sql);

        //MANDAMOS LOS ID DE FORMA DINAMICA.-
        $stmt->bindParam(':' . $nameId, $id, PDO::PARAM_STR); 

        // SI SE EJECUTA QUE DEVUELTA UN MENSAJE
        if ($stmt->execute()) {

            $response = array(

                // RETORNAMOS EL MENSAJE
                "comment" => "The process was successful"
            );

            return $response;

        }else{

            return $link->errorInfo();

        }

    }

}