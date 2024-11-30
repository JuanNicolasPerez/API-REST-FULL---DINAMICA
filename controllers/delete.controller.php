<?php

/*====================
    RequerimientoS a la API
=================== */
require_once "models/delete.model.php";

class DeleteController
{
    /*====================
    PETICION POST PARA EDITAR DATOS
    =================== */
    static public function deleteData($table, $id, $nameId){

        /*====================
        SOLICITAMOS RESPUESTA AL MODELO PARA EDITAR DATOS EN CUALQUIER TABLA.-
        =================== */
        $response = DeleteModel::deleteData($table, $id, $nameId);

        $return = new DeleteController();

        $return->fncResponse($response);

    }

    
    /*====================
    Respuesta del controlador
    =================== */
    public function fncResponse($response){

        if(!empty($response)){

            // CREAMOS EL DATO JSON PARA DEVOLVER LA INFORMACION.-
            $json = array(
                "status" => 200,
                "results" => $response
            );

        }else{

            // CASO QUE NO SE ENVIE INFORMACION.-
            $json = array(
                "status" => 404,
                "result" => "Not found",
                "method" => "put"
            );

        }

        // DEVOLVEMOS UNA REPUESTA Y POR CUAL METODO SE HIZO LA PETICION
        echo json_encode($json, http_response_code($json["status"]));

    }
}