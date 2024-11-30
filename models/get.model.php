<?php

require_once "connection.php";

class GetModel
{

    // $table =TRAE LA TABLE .-
    // $select = TRAE LOS CAMPOS O COLUMNAS DE LA TABLA.-
    // $linkTo = TRAE LOS CAMPOS O COLUMNAS DE LA TABLA PARA UNA BUSQUEDA ESPECIFICA DESPUES DEL WHERE.-
    // $equalTo = TRAE LOS VALORES QUE DEBE COMPARAR.-
    // $rel =TRAE LAS TABLE .-

    /*====================
    Petisiones GET sin filtro
    =================== */
    static public function getData($table, $select, $orderBy, $orderMode, $startAt, $endAt)
    {

        /*====================
        Validar existencia de la tabla y de las columnas
        =================== */
        $selectArray = explode(",", $select);

        if (empty(Connection::getColumnsData($table, $selectArray))) {
            return null;
        }

        /*====================
        Sin ordenada y sin limitar datos
        =================== */
        $sql = "SELECT $select 
                    FROM $table";

        /*====================
        Ordenar datos sin limites
        =================== */
        if ($orderBy != null && $orderMode != null  && $startAt == null  && $endAt == null) {
            $sql = "SELECT $select 
                    FROM $table
                    ORDER BY $orderBy $orderMode";
        }

        /*====================
        Ordenar y limitar datos
        =================== */
        if ($orderBy != null && $orderMode != null  && $startAt != null  && $endAt != null) {
            $sql = "SELECT $select 
                    FROM $table
                    ORDER BY $orderBy $orderMode
                    LIMIT $startAt, $endAt";
        }

        /*====================
        Limitar datos sin ordenar
        =================== */
        if ($orderBy == null && $orderMode == null  && $startAt != null  && $endAt != null) {
            $sql = "SELECT $select 
                    FROM $table
                    LIMIT $startAt, $endAt";
        }

        $stmt = Connection::connect()->prepare($sql);

        try {

            $stmt->execute();
        } catch (PDOException $Exception) {

            return null;
        }

        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }

    /*====================
    Petisiones GET con filtro
    =================== */
    static public function getDataFilter($table, $select,  $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt)
    {

        /*====================
        Validar existencia de la tabla y de las columnas
        =================== */
        $linkToArray = explode(",", $linkTo); //SEPARAMOS LOS PARAMETROS PARA BUSCAR LAS COLUMNAS POR COMAS.-
        $selectArray = explode(",", $select);

        foreach ($linkToArray as $key => $value) {
            array_push($selectArray, $value);
        }

        $selectArray = array_unique($selectArray);

        if (empty(Connection::getColumnsData($table, $selectArray))) {
            return null;
        }

        /*====================
        Petisiones con varios filtros
        =================== */
        $equalToArray = explode(",", $equalTo); //SEPARAMOS LOS VALORES POR BARRA_PISO PARA PARAR EL VALOR A BUSCAR.-

        $linkToText = ""; //CREAMOS UNA CADENA DE TEXTO PARA QUE SE GENERE LA CONSULTA DE MANERA DINAMICA.-

        if (count($linkToArray) > 1) { //CONTAMOS SI EL LA CONSULTA SE PASA MAS DE UN PARAMETRO DE BUSQUEDA.-
            foreach ($linkToArray as $key => $value) { //RECORREMOS LA CADENA DE TEXTO PARA IR FORMANDO LA CONSULTA.-
                if ($key > 0) { //INICIAMOS EN EL INDICE 0 DEL ARRAY.-
                    $linkToText .= "AND " . $value . " = :" . $value . " "; //CREAMOS LAS CONSULTAS DINAMICAS.-
                }
            }
        }

        /*====================
        Sin ordenada y limitar datos
        =================== */
        $sql = "SELECT $select 
                        FROM $table 
                        WHERE $linkToArray[0] = :$linkToArray[0] $linkToText"; //DESDE EL WHERE SE AGRAGAN LAS COLUMNAS  SEGUN EL INDICE QUE TRAE EL PARAMETRO VALOR.-
        //DESDE EL $linkToText DE VAN.-

        /*====================
        Ordenar datos sin limites
        =================== */
        if ($orderBy != null && $orderMode != null && $startAt == null  && $endAt == null) {

            $sql = "SELECT $select 
                        FROM $table 
                        WHERE $linkToArray[0] = :$linkToArray[0] $linkToText
                        ORDER BY $orderBy $orderMode";
        }

        /*====================
        Ordenar y limitar datos
        =================== */
        if ($orderBy != null && $orderMode != null  && $startAt != null  && $endAt != null) {
            $sql = "SELECT $select 
                        FROM $table 
                        WHERE $linkToArray[0] = :$linkToArray[0] $linkToText
                        ORDER BY $orderBy $orderMode
                        LIMIT $startAt, $endAt";
        }

        /*====================
        Limitar datos sin ordenar
        =================== */
        if ($orderBy == null && $orderMode == null  && $startAt != null  && $endAt != null) {
            $sql = "    SELECT $select 
                        FROM $table 
                        WHERE $linkToArray[0] = :$linkToArray[0] $linkToText
                        LIMIT $startAt, $endAt";
        }

        $stmt = Connection::connect()->prepare($sql);

        foreach ($linkToArray as $key => $value) {
            $stmt->bindParam(':' . $value, $equalToArray[$key], PDO::PARAM_STR); //MANDAMOS LOS PARAMETROS DE FORMA DINAMICA.-
        }

        try {

            $stmt->execute();
        } catch (PDOException $Exception) {

            return null;
        }

        return $stmt->fetchAll(PDO::FETCH_CLASS);

        /*====================
        PARA MOSTRAR UNA RESPUESTA
        =================== */
        // echo '<pre>'; print_r($stmt); echo '</pre>';
        // return;
    }

    /*====================
    Petisiones GET sin filtro entre tablas relacionadas
    =================== */
    static public function getRelData($rel, $type, $select, $orderBy, $orderMode, $startAt, $endAt)
    {

        /*====================
        EXTRAMOS LOS VALORES O RUTAS DE LOS PARAMETROS, MOTIVO POR EL QUE VIENE COOMO UNA CADENA SEPARADA POR COMAS
        =================== */
        $relArray = explode(",", $rel);
        $typeArray = explode(",", $type);
        $innnerJoinText = "";

        if (count($relArray) > 1) {

            /*====================
            CREAMOS LA RELACION EN UNA CADENA DE TEXTO PARA QUE LA CONSULTA SEA MAS DINAMICA
            =================== */
            foreach ($relArray as $key => $value) {

                /*====================
                Validar existencia de la tabla
                =================== */
                if (empty(Connection::getColumnsData($value, ["*"]))) {
                    return null;
                }

                if ($key > 0) {
                    $innnerJoinText .= "INNER JOIN " . $value . " ON " . $relArray[0] . ".id_" . $typeArray[$key] . "_" . $typeArray[0] . " = " . $value . ".id_" . $typeArray[$key] . " ";
                }
            }

            /*====================
            Sin ordenada y sin limitar datos
            =================== */
            $sql = "SELECT $select 
                        FROM $relArray[0] $innnerJoinText";

            /*====================
            Ordenar datos sin limites
            =================== */
            if ($orderBy != null && $orderMode != null  && $startAt == null  && $endAt == null) {
                $sql = "SELECT $select 
                            FROM $relArray[0] $innnerJoinText
                            ORDER BY $orderBy $orderMode";
            }

            /*====================
            Ordenar y limitar datos
            =================== */
            if ($orderBy != null && $orderMode != null  && $startAt != null  && $endAt != null) {
                $sql = "SELECT $select 
                            FROM $relArray[0] $innnerJoinText
                            ORDER BY $orderBy $orderMode
                            LIMIT $startAt, $endAt";
            }

            /*====================
            Limitar datos sin ordenar
            =================== */
            if ($orderBy == null && $orderMode == null  && $startAt != null  && $endAt != null) {
                $sql = "SELECT $select 
                            FROM $relArray[0] $innnerJoinText
                            LIMIT $startAt, $endAt";
            }

            $stmt = Connection::connect()->prepare($sql);

            try {

                $stmt->execute();
            } catch (PDOException $Exception) {

                return null;
            }

            return $stmt->fetchAll(PDO::FETCH_CLASS);
        } else {
            return null;
        }
    }

    /*====================
    Petisiones GET con filtro entre tablas relacionadas
    =================== */
    static public function getRelDataFilter($rel, $type, $select, $linkTo, $equalTo, $orderBy, $orderMode, $startAt, $endAt)
    {

        /*====================
        Petisiones con varios filtros
        =================== */
        $linkToArray = explode(",", $linkTo); //SEPARAMOS LOS PARAMETROS PARA BUSCAR LAS COLUMNAS POR COMAS.-

        $equalToArray = explode(",", $equalTo); //SEPARAMOS LOS VALORES POR BARRA_PISO PARA PARAR EL VALOR A BUSCAR.-

        $linkToText = ""; //CREAMOS UNA CADENA DE TEXTO PARA QUE SE GENERE LA CONSULTA DE MANERA DINAMICA.-

        if (count($linkToArray) > 1) { //CONTAMOS SI EL LA CONSULTA SE PASA MAS DE UN PARAMETRO DE BUSQUEDA.-

            foreach ($linkToArray as $key => $value) { //RECORREMOS LA CADENA DE TEXTO PARA IR FORMANDO LA CONSULTA.-

                if ($key > 0) { //INICIAMOS EN EL INDICE 0 DEL ARRAY.-

                    $linkToText .= "AND " . $value . " = :" . $value . " "; //CREAMOS LAS CONSULTAS DINAMICAS.-

                }

            }

        }

        /*====================
        EXTRAMOS LOS VALORES O RUTAS DE LOS PARAMETROS, MOTIVO POR EL QUE VIENE COMO UNA CADENA SEPARADA POR COMAS
        =================== */
        $relArray = explode(",", $rel);
        $typeArray = explode(",", $type);
        $innnerJoinText = "";

        if (count($relArray) > 1) {

            /*====================
            CREAMOS LA RELACION EN UNA CADENA DE TEXTO PARA QUE LA CONSULTA SEA MAS DINAMICA
            =================== */
            foreach ($relArray as $key => $value) {

                /*====================
                Validar existencia de la tabla
                =================== */
                if (empty(Connection::getColumnsData($value, ["*"]))) {
                    return null;
                }

                if ($key > 0) {
                    $innnerJoinText .= "INNER JOIN " . $value . " ON " . $relArray[0] . ".id_" . $typeArray[$key] . "_" . $typeArray[0] . " = " . $value . ".id_" . $typeArray[$key] . " ";
                }
            }

            /*====================
            Sin ordenada y sin limitar datos
            =================== */
            $sql = "SELECT $select 
                        FROM $relArray[0] $innnerJoinText
                        WHERE $linkToArray[0] = :$linkToArray[0] $linkToText";

            /*====================
            Ordenar datos sin limites
            =================== */
            if ($orderBy != null && $orderMode != null  && $startAt == null  && $endAt == null) {
                $sql = "SELECT $select 
                            FROM $relArray[0] $innnerJoinText
                            WHERE $linkToArray[0] = :$linkToArray[0] $linkToText
                            ORDER BY $orderBy $orderMode";
            }

            /*====================
            Ordenar y limitar datos
            =================== */
            if ($orderBy != null && $orderMode != null  && $startAt != null  && $endAt != null) {
                $sql = "SELECT $select 
                            FROM $relArray[0] $innnerJoinText
                            WHERE $linkToArray[0] = :$linkToArray[0] $linkToText
                            ORDER BY $orderBy $orderMode
                            LIMIT $startAt, $endAt";
            }

            /*====================
            Limitar datos sin ordenar
            =================== */
            if ($orderBy == null && $orderMode == null  && $startAt != null  && $endAt != null) {
                $sql = "SELECT $select 
                            FROM $relArray[0] $innnerJoinText
                            WHERE $linkToArray[0] = :$linkToArray[0] $linkToText
                            LIMIT $startAt, $endAt";
            }

            $stmt = Connection::connect()->prepare($sql);

            foreach ($linkToArray as $key => $value) {
                $stmt->bindParam(':' . $value, $equalToArray[$key], PDO::PARAM_STR); //MANDAMOS LOS PARAMETROS DE FORMA DINAMICA.-
            }

            try {

                $stmt->execute();
            } catch (PDOException $Exception) {

                return null;
            }

            return $stmt->fetchAll(PDO::FETCH_CLASS);
        } else {
            return null;
        }
    }

    /*====================
    Petisiones GET para el buscador
    =================== */
    static public function getDataSearch($table, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt)
    {

        /*====================
        Validar existencia de la tabla y de las columnas
        =================== */
        $linkToArray = explode(",", $linkTo); //SEPARAMOS LOS PARAMETROS PARA BUSCAR LAS COLUMNAS POR COMAS.-
        $selectArray = explode(",", $select);

        foreach ($linkToArray as $key => $value) {
            array_push($selectArray, $value);
        }

        $selectArray = array_unique($selectArray);

        if (empty(Connection::getColumnsData($table, $selectArray))) {
            return null;
        }

        /*====================
        Petisiones con varios filtros
        =================== */
        $linkToArray = explode(",", $linkTo); //SEPARAMOS LOS PARAMETROS PARA BUSCAR LAS COLUMNAS POR COMAS.-

        $searchArray = explode(",", $search); //SEPARAMOS LOS VALORES POR BARRA_PISO PARA PARAR EL VALOR A BUSCAR.-

        $linkToText = ""; //CREAMOS UNA CADENA DE TEXTO PARA QUE SE GENERE LA CONSULTA DE MANERA DINAMICA.-

        if (count($linkToArray) > 1) { //CONTAMOS SI EL LA CONSULTA SE PASA MAS DE UN PARAMETRO DE BUSQUEDA.-

            foreach ($linkToArray as $key => $value) { //RECORREMOS LA CADENA DE TEXTO PARA IR FORMANDO LA CONSULTA.-

                if ($key > 0) { //INICIAMOS EN EL INDICE 0 DEL ARRAY.-
                    $linkToText .= "AND " . $value . " = :" . $value . " "; //CREAMOS LAS CONSULTAS DINAMICAS.-
                }
            }
        }

        /*====================
        Sin ordenada y sin limitar datos
        =================== */
        $sql = "SELECT $select 
                    FROM $table
                    WHERE $linkToArray[0]
                    LIKE '%$searchArray[0]%'
                    $linkToText";

        /*====================
        Ordenar datos sin limites
        =================== */
        if ($orderBy != null && $orderMode != null  && $startAt == null  && $endAt == null) {
            $sql = "SELECT $select 
                    FROM $table
                    WHERE $linkToArray[0]
                    LIKE '%$searchArray[0]%'
                    $linkToText
                    ORDER BY $orderBy $orderMode";
        }

        /*====================
        Ordenar y limitar datos
        =================== */
        if ($orderBy != null && $orderMode != null  && $startAt != null  && $endAt != null) {
            $sql = "SELECT $select 
                    FROM $table
                    WHERE $linkToArray[0]
                    LIKE '%$searchArray[0]%'
                    $linkToText
                    ORDER BY $orderBy $orderMode
                    LIMIT $startAt, $endAt";
        }

        /*====================
        Limitar datos sin ordenar
        =================== */
        if ($orderBy == null && $orderMode == null  && $startAt != null  && $endAt != null) {
            $sql = "SELECT $select 
                    FROM $table
                    WHERE $linkToArray[0]
                    LIKE '%$searchArray[0]%'
                    $linkToText
                    LIMIT $startAt, $endAt";
        }

        $stmt = Connection::connect()->prepare($sql);

        foreach ($linkToArray as $key => $value) {

            if ($key > 0) {
                $stmt->bindParam(':' . $value, $searchArray[$key], PDO::PARAM_STR); //MANDAMOS LOS PARAMETROS DE FORMA DINAMICA.-
            }
        }

        try {

            $stmt->execute();
        } catch (PDOException $Exception) {

            return null;
        }

        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }

    /*====================
    Petisiones GET para el buscador entre tablas relacionadas
    =================== */
    static public function getRelDataSearch($rel, $type, $select, $linkTo, $search, $orderBy, $orderMode, $startAt, $endAt)
    {

        /*====================
        Organizamos las Petisiones con varios filtros
        =================== */
        $linkToArray = explode(",", $linkTo); //SEPARAMOS LOS PARAMETROS PARA BUSCAR LAS COLUMNAS POR COMAS.-

        $searchArray = explode(",", $search); //SEPARAMOS LOS VALORES POR BARRA_PISO PARA PARAR EL VALOR A BUSCAR.-

        $linkToText = ""; //CREAMOS UNA CADENA DE TEXTO PARA QUE SE GENERE LA CONSULTA DE MANERA DINAMICA.-

        if (count($linkToArray) > 1) { //CONTAMOS SI EL LA CONSULTA SE PASA MAS DE UN PARAMETRO DE BUSQUEDA.-

            foreach ($linkToArray as $key => $value) { //RECORREMOS LA CADENA DE TEXTO PARA IR FORMANDO LA CONSULTA.-

                if ($key > 0) { //INICIAMOS EN EL INDICE 0 DEL ARRAY.-

                    $linkToText .= "AND " . $value . " = :" . $value . " "; //CREAMOS LAS CONSULTAS DINAMICAS.-

                }

            }

        }

        /*====================
        EXTRAMOS LOS VALORES O RUTAS DE LOS PARAMETROS, MOTIVO POR EL QUE VIENE COOMO UNA CADENA SEPARADA POR COMAS
        =================== */
        $relArray = explode(",", $rel);
        $typeArray = explode(",", $type);
        $innnerJoinText = "";

        if (count($relArray) > 1) {

            /*====================
            CREAMOS LA RELACION EN UNA CADENA DE TEXTO PARA QUE LA CONSULTA SEA MAS DINAMICA
            =================== */
            foreach ($relArray as $key => $value) {

                /*====================
                Validar existencia de la tabla
                =================== */
                if (empty(Connection::getColumnsData($value, ["*"]))) {
                    return null;
                }

                if ($key > 0) {
                    $innnerJoinText .= "INNER JOIN " . $value . " ON " . $relArray[0] . ".id_" . $typeArray[$key] . "_" . $typeArray[0] . " = " . $value . ".id_" . $typeArray[$key] . " ";
                }
            }

            /*====================
            Sin ordenada y sin limitar datos
            =================== */
            $sql = "SELECT $select 
                        FROM $relArray[0] $innnerJoinText
                        WHERE $linkToArray[0]
                        LIKE '%$searchArray[0]%' $linkToText";

            /*====================
            Ordenar datos sin limites
            =================== */
            if ($orderBy != null && $orderMode != null  && $startAt == null  && $endAt == null) {
                $sql = "SELECT $select 
                            FROM $relArray[0] $innnerJoinText
                            WHERE $linkToArray[0]
                            LIKE '%$searchArray[0]%' $linkToText
                            ORDER BY $orderBy $orderMode";
            }

            /*====================
            Ordenar y limitar datos
            =================== */
            if ($orderBy != null && $orderMode != null  && $startAt != null  && $endAt != null) {
                $sql = "SELECT $select 
                            FROM $relArray[0] $innnerJoinText
                            WHERE $linkToArray[0]
                            LIKE '%$searchArray[0]%' $linkToText
                            ORDER BY $orderBy $orderMode
                            LIMIT $startAt, $endAt";
            }

            /*====================
            Limitar datos sin ordenar
            =================== */
            if ($orderBy == null && $orderMode == null  && $startAt != null  && $endAt != null) {
                $sql = "SELECT $select 
                            FROM $relArray[0] $innnerJoinText
                            WHERE $linkToArray[0]
                            LIKE '%$searchArray[0]%' $linkToText
                            LIMIT $startAt, $endAt";
            }

            $stmt = Connection::connect()->prepare($sql);

            foreach ($linkToArray as $key => $value) {

                if ($key > 0) {
                    $stmt->bindParam(':' . $value, $searchArray[$key], PDO::PARAM_STR); //MANDAMOS LOS PARAMETROS DE FORMA DINAMICA.-
                }
            }

            try {

                $stmt->execute();
            } catch (PDOException $Exception) {

                return null;
            }

            return $stmt->fetchAll(PDO::FETCH_CLASS);
        } else {
            return null;
        }
    }

    /*====================
    Petisiones GET de seleccion de rangos
    =================== */
    static public function getDataRange($table, $select, $linkTo, $between1, $between2, $filterTo, $inTo, $orderBy, $orderMode, $startAt, $endAt)
    {

        /*====================
        Validar existencia de la tabla y de las columnas
        =================== */
        $linkToArray = explode(",", $linkTo); //SEPARAMOS LOS PARAMETROS PARA BUSCAR LAS COLUMNAS POR COMAS.-

        if ($filterTo != null) {

            $filterToArray = explode(",", $filterTo);

        } else {

            $filterToArray = array();

        }

        $selectArray = explode(",", $select);

        foreach ($linkToArray as $key => $value) {
            array_push($selectArray, $value);
        }

        foreach ($filterToArray as $key => $value) {
            array_push($selectArray, $value);
        }

        $selectArray = array_unique($selectArray);

        if (empty(Connection::getColumnsData($table, $selectArray))) {
            return null;
        }

        /*====================
        Preparamos el filtro 
        =================== */
        $filter = "";

        if ($filterTo != null && $inTo != null) {
            $filter = 'AND ' . $filterTo . ' IN (' . $inTo . ')';
        }

        /*====================
        Sin ordenada y sin limitar datos
        =================== */
        $sql = "SELECT $select 
                    FROM $table
                    WHERE $linkTo
                    BETWEEN '$between1' AND '$between2'
                    $filter";

        /*====================
        Ordenar datos sin limites
        =================== */
        if ($orderBy != null && $orderMode != null  && $startAt == null  && $endAt == null) {
            $sql = "SELECT $select 
                    FROM $table
                    WHERE $linkTo
                    BETWEEN '$between1' AND '$between2'
                    $filter
                    ORDER BY $orderBy $orderMode";
        }

        /*====================
        Ordenar y limitar datos
        =================== */
        if ($orderBy != null && $orderMode != null  && $startAt != null  && $endAt != null) {
            $sql = "SELECT $select 
                    FROM $table
                    WHERE $linkTo
                    BETWEEN '$between1' AND '$between2'
                    $filter
                    ORDER BY $orderBy $orderMode
                    LIMIT $startAt, $endAt";
        }

        /*====================
        Limitar datos sin ordenar
        =================== */
        if ($orderBy == null && $orderMode == null  && $startAt != null  && $endAt != null) {
            $sql = "SELECT $select 
                    FROM $table
                    WHERE $linkTo
                    BETWEEN '$between1' AND '$between2'
                    $filter
                    LIMIT $startAt, $endAt";
        }

        $stmt = Connection::connect()->prepare($sql);

        try {

            $stmt->execute();
        } catch (PDOException $Exception) {

            return null;
        }

        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }

    /*====================
    Petisiones GET de seleccionde rango con tablas relacionadas
    =================== */
    static public function getRelDataRange($rel, $type, $select, $linkTo, $between1, $between2, $filterTo, $inTo, $orderBy, $orderMode, $startAt, $endAt)
    {

        /*====================
        Preparamos el filtro 
        =================== */
        $filter = "";

        if ($filterTo != null && $inTo != null) {
            $filter = 'AND ' . $filterTo . ' IN (' . $inTo . ')';
        }

        /*====================
        EXTRAMOS LOS VALORES O RUTAS DE LOS PARAMETROS, MOTIVO POR EL QUE VIENE COOMO UNA CADENA SEPARADA POR COMAS
        =================== */
        $relArray = explode(",", $rel);
        $typeArray = explode(",", $type);
        $innnerJoinText = "";

        if (count($relArray) > 1) {

            /*====================
            CREAMOS LA RELACION EN UNA CADENA DE TEXTO PARA QUE LA CONSULTA SEA MAS DINAMICA
            =================== */
            foreach ($relArray as $key => $value) {

                /*====================
                Validar existencia de la tabla
                =================== */
                if (empty(Connection::getColumnsData($value, ["*"]))) {
                    return null;
                }

                if ($key > 0) {
                    $innnerJoinText .= "INNER JOIN " . $value . " ON " . $relArray[0] . ".id_" . $typeArray[$key] . "_" . $typeArray[0] . " = " . $value . ".id_" . $typeArray[$key] . " ";
                }
            }

            /*====================
            Sin ordenada y sin limitar datos
            =================== */
            $sql = "SELECT $select 
                        FROM $relArray[0] $innnerJoinText
                        WHERE $linkTo
                        BETWEEN '$between1' AND '$between2'
                        $filter";

            /*====================
            Ordenar datos sin limites
            =================== */
            if ($orderBy != null && $orderMode != null  && $startAt == null  && $endAt == null) {
                $sql = "SELECT $select 
                        FROM $relArray[0] $innnerJoinText
                        WHERE $linkTo
                        BETWEEN '$between1' AND '$between2'
                        $filter
                        ORDER BY $orderBy $orderMode";
            }

            /*====================
            Ordenar y limitar datos
            =================== */
            if ($orderBy != null && $orderMode != null  && $startAt != null  && $endAt != null) {
                $sql = "SELECT $select 
                        FROM $relArray[0] $innnerJoinText
                        WHERE $linkTo
                        BETWEEN '$between1' AND '$between2'
                        $filter
                        ORDER BY $orderBy $orderMode
                        LIMIT $startAt, $endAt";
            }

            /*====================
            Limitar datos sin ordenar
            =================== */
            if ($orderBy == null && $orderMode == null  && $startAt != null  && $endAt != null) {
                $sql = "SELECT $select 
                        FROM $relArray[0] $innnerJoinText
                        WHERE $linkTo
                        BETWEEN '$between1' AND '$between2'
                        $filter
                        LIMIT $startAt, $endAt";
            }

            $stmt = Connection::connect()->prepare($sql);

            try {

                $stmt->execute();

            } catch (PDOException $Exception) {

                return null;
            }

            return $stmt->fetchAll(PDO::FETCH_CLASS);

        } else {
            return null;
        }
    }
}
