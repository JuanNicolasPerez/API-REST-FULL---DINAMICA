<?php

require_once "connection.php";
require_once "get.model.php";

class PutModel
{

    /*====================
    PETICION POST PARA EDITAR DATOS
    =================== */
    static public function putData($table,$data, $id, $nameId){

        // VALIDAMOS EL ID
        $response = GetModel::getDataFilter($table, $nameId, $nameId, $id, null, null, null, null);

        if (empty($response)) {

            return null;

        }

        // ACTUALIZAMOS REGISTROS
        // PREPARAMOS LAS COLUMNAS Y LOS PARAMETROS
        $set = "";

        // RECORREMOS LOS DATOS QUE TRAE POST EN $DATA
        foreach ($data as $key => $value) {

            // CON .= COMENZAMOS A CREAR LA CADENA CON LAS COLUMNAS Y PARAMETROS A TRAVES DE SU PROPIEDAD 
            $set .= $key." = :".$key.",";

        }

        // EXTRAEMOS LA ULTIMA COMA DE LA CADENA
        $set = substr($set, 0, -1);

        $sql = "UPDATE $table 
                SET $set
                WHERE $nameId = :$nameId"
        ;

        // PREPARAMOS LA VARIABLE DE CONEXION
        $link = Connection::connect();

        // PREPARAMOS LA SENTENCIA SQL
        $stmt = $link->prepare($sql);

        // RECORREMOS DATA PARA ENLAZAR LOS PARAMETROS
        foreach ($data as $key => $value) {

            //MANDAMOS LOS PARAMETROS DE FORMA DINAMICA.-
            $stmt->bindParam(':' . $key, $data[$key], PDO::PARAM_STR); 

        }

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