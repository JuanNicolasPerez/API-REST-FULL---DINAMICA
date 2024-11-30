<?php

require_once "connection.php";

class PostModel
{

    /*====================
    PETICION POST PARA CREAR DATOS
    =================== */
    static public function postData($table,$data){

        // PREPARAMOS LAS COLUMNAS Y LOS PARAMETROS
        $columns = "";
        $params = "";

        // RECORREMOS LOS DATOS QUE TRAE POST EN $DATA
        foreach ($data as $key => $value) {

            // CON .= COMENZAMOS A CREAR LA CADENA CON LAS COLUMNAS Y PARAMETROS A TRAVES DE SU PROPIEDAD 
            $columns .= $key.",";
            $params .= ":".$key.",";

        }

        // EXTRAEMOS LA ULTIMA COMA DE LA CADENA
        $columns = substr($columns, 0, -1);
        $params = substr($params, 0, -1);

        $sql = "INSERT INTO $table ($columns)
                VALUES ($params)"
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