<?php

/*====================
    RequerimientoS a la API
=================== */
require_once "models/get.model.php";

class GetController
{

    /*====================
    Petisiones GET sin filtro
    =================== */
    static public function getData($table, $select, $orderBy, $orderMode, $startAt, $endAt)
    {

        $response = GetModel::getData($table, $select,$orderBy, $orderMode,$startAt, $endAt);

        $return = new GetController();

        $return->fncResponse($response);

    }

    /*====================
    Petisiones GET con filtro
    =================== */
    static public function getDataFilter($table, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt)
    {

        $response = GetModel::getDataFilter($table, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt);

        $return = new GetController();

        $return->fncResponse($response);

    }

    /*====================
    Petisiones GET sin filtros entre tablas relacionadas
    =================== */
    static public function getRelData($rel , $type, $select, $orderBy, $orderMode, $startAt, $endAt)
    {

        $response = GetModel::getRelData($rel , $type, $select, $orderBy, $orderMode, $startAt, $endAt);

        $return = new GetController();

        $return->fncResponse($response);

    }

    /*====================
    Petisiones GET con filtros entre tablas relacionadas
    =================== */
    static public function getRelFilterData($rel, $type, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt)
    {

        $response = GetModel::getRelDataFilter($rel, $type, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt);

        $return = new GetController();

        $return->fncResponse($response);

    }

    /*====================
    Petisiones GET para el buscador
    =================== */
    static public function getDataSearch($table, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt)
    {

        $response = GetModel::getDataSearch($table, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt);

        $return = new GetController();

        $return->fncResponse($response);

    }

    /*====================
    Petisiones GET para el buscador con tablas relacionadas
    =================== */
    static public function getRelDataSearch($rel, $type, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt)
    {

        $response = GetModel::getRelDataSearch($rel, $type, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt);

        $return = new GetController();

        $return->fncResponse($response);

    }

    /*====================
    Petisiones GET para seleccion de rangos
    =================== */
    static public function getDataRange($table, $select, $linkTo, $between1, $between2, $filterTo, $inTo, $orderBy, $orderMode, $startAt, $endAt)
    {

        $response = GetModel::getDataRange($table, $select, $linkTo, $between1, $between2, $filterTo, $inTo, $orderBy, $orderMode, $startAt, $endAt);

        $return = new GetController();

        $return->fncResponse($response);

    }

        /*====================
    Petisiones GET para seleccion de rangos con tablas relacionadas
    =================== */
    static public function getRelDataRange($rel, $type, $select, $linkTo, $between1, $between2, $filterTo, $inTo, $orderBy, $orderMode, $startAt, $endAt)
    {

        $response = GetModel::getRelDataRange($rel, $type, $select, $linkTo, $between1, $between2, $filterTo, $inTo, $orderBy, $orderMode, $startAt, $endAt);

        $return = new GetController();

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
                "total"=> count($response),
                "results" => $response
            );

        }else{

            // CASO QUE NO SE ENVIE INFORMACION.-
            $json = array(
                "status" => 404,
                "result" => "Not found",
                "method" => "get"
            );

        }

        // DEVOLVEMOS UNA REPUESTA Y POR CUAL METODO SE HIZO LA PETICION
        echo json_encode($json, http_response_code($json["status"]));

    }

}
