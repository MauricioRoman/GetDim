<?php
    	define('DEBUG','0');

        define('ANO','2013');
        define('MES', '5');
        define('CLIENTE','POS');

        define('VERSION','V002');
        define('DBNAME', 'SAREN_Costos');
        define('VERSION_LOCAL','COL');              //ING o ESP
      //  define('CUBO_COSTO_PRIMARIO','5');
      //  define('CUBO_TASA_CAPACIDAD','0'); 
      //  define('CUBO_COSTO_SECUNDARIO','4');  
      //  define('DIM_CUENTAS','3');
      //  define('DIM_EMISOR','7');
      //  define('DIM_RECEPTOR','6');
      //  define('ATTR_CUENTA','7');
      //  define('ATTR_RECEPTOR','10');

//Para versión de Colombia
       
        define('CUBO_COSTO_PRIMARIO','0');
        define('CUBO_TASA_CAPACIDAD','1'); 
        define('CUBO_COSTO_SECUNDARIO','3');
        define('DIM_CUENTAS','7');
        define('DIM_EMISOR','10');
        define('DIM_RECEPTOR','9'); 
        define('ATTR_CUENTA','11');
        define('ATTR_RECEPTOR','13');

        define('CONN', 'LOCALHOST');
        define('HOST', '127.0.0.1');
        define('PORT', '7777');
        define('USER', 'DB_Connect');
        define('PASS', 'saren');

        define('CUENTAS_RAIZ','0');
        define('RECEPTOR_RAIZ','0');
        define('MAX_RELACIONES','15');

?>