<?php

/* These parameters must be specified in a web page */
    	define('DEBUG','0');
        define('ANO','2013');
        define('MES', '1');

        define('CLIENTE','RBM');
        define('VERSION','V002');
        define('DBNAME', 'SAREN_COSTOS_RBM');
        define('VERSION_LOCAL','USA');              //ING o ESP
        define('CUBO_COSTO_PRIMARIO','5');
        define('CUBO_TASA_CAPACIDAD','0'); 
        define('CUBO_COSTO_SECUNDARIO','4');  
        define('DIM_CUENTAS','3');
        define('DIM_EMISOR','7');
        define('DIM_RECEPTOR','6');
        define('ATTR_CUENTA','7');
        define('ATTR_RECEPTOR','10');

//Para versión de Colombia
        //define('DBNAME', 'SAREN_Costos_Positiva');
        //define('CUBO_COSTO_PRIMARIO','0');
        //define('CUBO_TASA_CAPACIDAD','1'); 
        //define('CUBO_COSTO_SECUNDARIO','3');
        //define('DIM_CUENTAS','7');
        //define('DIM_EMISOR','10');
        //define('DIM_RECEPTOR','9'); 

        define('CONN', 'LOCALHOST');
        define('HOST', '127.0.0.1');
        define('PORT', '7777');
        define('USER', 'DB_Connect');
        define('PASS', 'saren');

        define('CUENTAS_RAIZ','0');
        define('RECEPTOR_RAIZ','0');
        define('MAX_RELACIONES','15');

?>