<?php

/**
 * GetDim.php
 *
 * Obtiene la lista de elementos en las dimensiones de Cuenta Contable y Receptores
 * para ingresarlas al motor en C que calcula tasas, secundarios, caminos, etc.
 *
 * Para cada elemento de Cuentas Contables, obtiene el ID numérico utilizado en Jedox
 * y lo graba en un archivo plano, usando el formato: ID, Etiqueta
 *
 * Para cada elemento de Receptores, obtiene el ID numérico utilizado en Jedox
 * y le suma el último ID de Receptores, para crear una lista contínua
 * Luego lo graba usando el formato: ID,Etiqueta,flag_capacity
 * donde flag_capacity denota si el elemento posee capacidad limitante, o no
 *
 * V1.1 - Carga los archivos de primarios y secundarios, e identifica los nodos que
 * utilizan, de forma tal que el maestro de nodos, sólo contenga los nodos activos
 * V1.2 - Corrige algunos BUGs
 * V1.3 - Carga archivo de niveles (por familias) para generar una columna adicional
 * con el nivel, en el archivo de elementos de la dimension
 *
 * @package    SAREN
 * @author     Mauricio Román Rojas <mauricio.roman.rojas@gmail.com>
 * @copyright  2013 Mauricio Román Rojas
 * @version    1.5
 * @since      21 Nov 2013
 * @deprecated 
 */

/*
 * Definiciones y archivos a incluir
 */

    include "GetDim_config.php";
	include "palo_connect.php";
	include "GetDim_bfs.php";

	function error_msg($msg, $code)
	{
		die($msg);
	}

	//Permitimos que corra durante 2 minutos
	set_time_limit(120);
	ini_set('max_execution_time', 120);

	// Notificar E_NOTICE también puede ser bueno (para informar de variables
	// no inicializadas o capturar errores en nombres de variables ...)
	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

    //1. Conexión al servidor
    $palo_server = CONN . '/' . DBNAME;		;

    RCA_palo_connect();
    echo "<BR>Programa GetDim.php";
    echo "<BR>Versión 1.5";
    echo "<BR>Creada por Mauricio Román Rojas";
    echo "<BR><BR>Usando Base de Datos: ".DBNAME;

    echo "<BR><BR><b>OBS: El programa sólo acepta relaciones entre elementos base</b></br>";

	if(DEBUG>=2)
		echo "<BR> @Usando la estructura de cubos de la versión ".VERSION_LOCAL;
	
    $cubos = array();
    $cubos_attr = array();
	$dimensiones = array();

	//2. Leer datos de cubos y dimensiones       
    $cubos = palo_database_list_cubes($palo_server, 0);

    if(DEBUG>=2)
    	echo "<BR><BR>@Lista de CUBOS: ".json_encode($cubos); 
	
	if ($cubos[0]=="#")
        	error_msg("Error: No está disponible la base de datos ".DBNAME,E_USER_ERROR);

    $cubos_attr = palo_database_list_cubes($palo_server, 2);

    if(DEBUG>=2)
    	echo "<BR><BR>@Lista de CUBOS de ATRIBUTOS: ".json_encode($cubos_attr); 
	if ($cubos_attr[0]=="#")
       	error_msg("Error: No está disponible la base de datos ".DBNAME,E_USER_ERROR);

	$dimensiones=palo_database_list_dimensions($palo_server, 0);
	$dimensiones_attr=palo_database_list_dimensions($palo_server, 2);
	foreach ($dimensiones as &$val)
		$val = utf8_encode($val);

    if(DEBUG>=2)
        echo "<BR><BR>@Lista de DIMENSIONES: ".json_encode($dimensiones);
    if(DEBUG>=2)
        echo "<BR><BR>@Lista de DIMENSIONES de ATRIBUTOS: ".json_encode($dimensiones_attr);
	
	//Contar todos los elementos en cada dimensión y obtener su nombre 
	
	$cuentas_numero = palo_ecount($palo_server, $dimensiones[DIM_CUENTAS]);
	$cuentas_info 	= palo_dimension_info($palo_server, $dimensiones[DIM_CUENTAS]);
	$cuentas_nombre = $cuentas_info[1];

	$receptores_numero 	 = 	palo_ecount($palo_server, $dimensiones[DIM_RECEPTOR]);
	$receptores_info 	 =	palo_dimension_info($palo_server, $dimensiones[DIM_RECEPTOR]);
	$receptores_nombre   =  $receptores_info[1];

	$emisores_numero = palo_ecount($palo_server, $dimensiones[DIM_EMISOR]);
	$emisores_info =palo_dimension_info($palo_server, $dimensiones[DIM_EMISOR]);
	$emisores_nombre = $emisores_info[1];
	
	//Revisar la estructura del cubo de atributos para el receptor

	$receptores_attr_nombre = $cubos_attr[ATTR_RECEPTOR];
	$cuentas_receiver_attr = array();
	$cuentas_receiver_attr = palo_cube_list_dimensions($palo_server, $receptores_attr_nombre);

	if (DEBUG>=2)
		echo "<BR><BR>@Lista de Dimensiones en Cubo de Atributos de Receptor ".$receptores_attr_nombre.": ".json_encode($cuentas_receiver_attr); 			

	unset($dimensiones);

    /*************************************************************************************/

    // Dimensionar el arreglo de nodos 
	$numero_nodos = $receptores_numero+$cuentas_numero;	//MR 16102013

	// Armar lista de NODOS CUENTA
	$cuentas_lista = array($cuentas_numero);
	$cuentas_lista = palo_dimension_list_elements($palo_server, $cuentas_nombre);
	if (DEBUG>=3)
		echo "<BR><BR>@Lista de NODOS CUENTA: ".json_encode($cuentas_lista); 

	// Armar lista de NODOS RECEPTORES
	$receptores_lista = array($receptores_numero);
	$receptores_lista = palo_dimension_list_elements($palo_server, $receptores_nombre);
	if (DEBUG>=3)
		echo "<BR><BR>@Lista de NODOS RECEPTORES: ".json_encode($receptores_lista); 

	// Armar lista de NODOS EMISORES
	$emisores_lista = array($emisores_numero);
	$emisores_lista = palo_dimension_list_elements($palo_server, $emisores_nombre);
	if (DEBUG>=3)
		echo "<BR><BR>@Lista de NODOS EMISORES: ".json_encode($emisores_lista); 

	// Emitir mensaje indicando cubo, dimensiones y número de nodos
	echo "<BR>Se encontraron ". $cuentas_numero ." nodos cuentas (base + consolidados) en la dimensión ".$cuentas_nombre;
	echo "<BR>Se encontraron ". $receptores_numero ." nodos receptores (base + consolidados) en la dimensión ".$receptores_nombre;
	echo "<BR>Se encontraron ". $emisores_numero ." nodos emisores (base + consolidados) en la dimensión ".$emisores_nombre;
	echo "<BR>Se obtendrá bandera de capacidad del cubo de atributos ".$cubos_attr[ATTR_RECEPTOR];
	echo "<BR>El sistema se dimensionará para ".$numero_nodos." nodos";
	echo "<BR>El sistema usará el cubo ".$cubos[CUBO_TASA_CAPACIDAD]." para obtener información de capacidad";
	
	// Definir arreglos con número de nodos para guardar valores correspondientes a los nodos
	$nodo_id   		  = new SplFixedArray($numero_nodos+2);
	$nodo_id_nuevo    = new SplFixedArray($numero_nodos+2);
	$flag_capacidad   = new SplFixedArray($numero_nodos+2);
	//$nodo_etiqueta    = new SplFixedArray($numero_nodos+2);
	$flag_consolidado = new SplFixedArray($numero_nodos+2);
	$flag_usado		  = new SplFixedArray($numero_nodos+2);
	$nodo_capacidad   = new SplFixedArray($numero_nodos+2);		
	$nodo_nivel   	  = new SplFixedArray($numero_nodos+2);	 
	//4. Inicializar valores
	for ($i=0; $i<$numero_nodos+2;$i++) {
		$flag_capacidad[$i] = 0;					//1=tiene capacidad limitante
		$nodo_etiqueta[$i] = "";
		$flag_consolidado[$i]=-1;					//1=Base,2=Consolidado
		$nodo_capacidad[$i]=-1.0;					//1=Base,2=Consolidado
		$flag_usado[$i]=0;
		$nodo_nivel[$i]=-1;
	}

	/********************************************************************************/

	$i=2; //iniciamos el contador en 2, para guardar en los primeros espacios datos 
		  //requeridos por Jedox

	// Leer datos de cada nodo Receptor
	foreach($receptores_lista as $x){
		if($x['type'] == 'numeric')
			$flag_consolidado[$i] = 0;
		elseif($x['type'] == 'consolidated')
			$flag_consolidado[$i] = 1;

		$nodo_etiqueta[$i] = $x['name'];
		$nodo_kvp[$nodo_etiqueta[$i]] = $i;

		$i++;
	}

	// Preparamos las etiquetas que le enviaremos a Jedox, para consulta
	$nodo_etiqueta[0]=1;
	$nodo_etiqueta[1]=$receptores_numero;
	//Leemos el cubo de atributos para receptores
    echo "<BR><BR>***************** Leyendo cubo de atributos para receptores **********************";
	$nodo_flag_capacidad = palo_dataav($palo_server, $cubos_attr[ATTR_RECEPTOR],array('Capacidad', $nodo_etiqueta));
	if($nodo_flag_capacidad[0]=='#')
		error_msg("Error: No se pudo leer datos de cubo",E_USER_ERROR);

	if(DEBUG>=2)
		echo "<BR><BR>@Bandera de capacidad para cada NODO RECEPTOR: ".json_encode($nodo_flag_capacidad);

    /*********************************************************************************/
    // Cargar archivo con niveles
    echo "<BR><BR>***************** Cargando Archivo con Niveles **********************";
	chdir("csv"); 
    $filename = CLIENTE."_Niveles_".ANO."_".MES."_".VERSION.".csv";
    $label1="Familia";
    $label2="Nivel";
    $nivel=array();
    $familia=array();
 	require "GetDim_read_levels.php"; 
	chdir(".."); 

	//v1.4 Averiguar nivel, con base en la familia
    echo "<BR><BR>***************** Relacionando Niveles con Nodos **********************";
	$no_niveles = sizeof($nivel);
	$m=2;
	
	for($j=0;$j<$no_niveles;$j++){
		$hijos[$j] = array();
		$hijos[$j] = element_list_descendents($palo_server, $receptores_nombre, $familia[$j]);
	}

	foreach($receptores_lista as $x){
		if($x['type'] == 'numeric'){
			$es_hijo=0;
			$id_padre=0;
			$j=0;

			while($j<$no_niveles && $es_hijo==0){
			//Revisar si el nodo está en la lista de niveles
				if($familia[$j]== $nodo_etiqueta[$m]){
					$es_hijo=1;
					$id_padre=$j;
					$nodo_nivel[$m]=$nivel[$j];
				} else {
					foreach($hijos[$j] as $y){
						if($y == $nodo_etiqueta[$m]){
							$es_hijo=1;
							$id_padre=$j;
							$nodo_nivel[$m]=$nivel[$j];
						}
					}
				}
				$j++;
			}
			if($es_hijo){
				if (DEBUG) echo "<BR> ".$nodo_etiqueta[$m]." es hijo de ".$familia[$id_padre];
				if (DEBUG) echo "<BR> Su nivel es ".$nodo_nivel[$m];
 					//if(palo_eischild($palo_server, $receptores_nombre, $familia[$id_padre], $nodo_etiqueta[$m]))
 					//	echo "<BR> Palo_Eischild lo confirma";
			} else {
				echo "<BR>WARNING: El receptor ".$nodo_etiqueta[$m]." no tiene padre directo en el archivo de niveles y será ignorado.";				
 					//if(palo_eischild($palo_server, $receptores_nombre, $familia[$id_padre], $nodo_etiqueta[$m]))
 					//	echo "<BR> Palo_Eischild lo confirma";
			}
		}
		$m++;
	}

	// Luego leemos datos de cada nodo Cuenta y los apilamos sobre los nodos receptores
    echo "<BR><BR>***************** Leyendo Nodos Cuenta **********************";
	foreach($cuentas_lista as $x){
		$is_unique=1;
		for($j=0;$j<$i;$j++)
			if($x['name']==$nodo_etiqueta[$j])
				$is_unique=0;

		if($is_unique){
			if($x['type'] == 'numeric')
				$flag_consolidado[$i] = 0;
			elseif($x['type'] == 'consolidated')
				$flag_consolidado[$i] = 1;
			$nodo_etiqueta[$i] = $x['name'];
			//Todas las cuentas son nivel 0
			$nodo_nivel[$i] = 0;
			$nodo_kvp[$nodo_etiqueta[$i]] = $i;
			$i++;
		}
	}

	// Imprimimos la lista de nodos
	if(DEBUG>=3)
		for($i=0;$i<$numero_nodos+2;$i++)
			echo "<BR>i ".$i." Nodo_etiqueta ".$nodo_etiqueta[$i]." nodo kvp ".$nodo_kvp[$nodo_etiqueta[$i]];

    /*********************************************************************************/
	
	//Leer Capacidad para cada nodo Emisor
    echo "<BR><BR>***************** Leyendo Capacidad para cada Nodo Emisor **********************";
  	$emisores_base_lista=array();
	$emisores_base_lista[]=1;
	$emisores_base_lista[]=0;
	
	foreach($emisores_lista as $nodo)
			$emisores_base_lista[] = $nodo['name'];

	$emisores_base_lista[1] = $emisores_numero;

	if(DEBUG>=3)
		echo "<BR><BR>@Lista de NODOS EMISORES: ".json_encode($emisores_base_lista);

	if(DEBUG>=2)
		echo "<BR><BR>Leyendo capacidad, para ".$emisores_numero." nodos emisores.<BR>";

	if(VERSION_LOCAL=='USA')
			$capacidad = palo_dataav($palo_server, $cubos[CUBO_TASA_CAPACIDAD], array($emisores_base_lista,MES,ANO,VERSION,'Y001'));
	elseif(VERSION_LOCAL=='COL')
			$capacidad = palo_dataav($palo_server, $cubos[CUBO_TASA_CAPACIDAD], array(VERSION, ANO,MES,'Y001',$emisores_base_lista));	

	if($capacidad[0]=='#')
		error_msg("Error: No se pudo leer datos de cubo",E_USER_ERROR);
     	
    if (DEBUG>=2) 		
		echo "<BR><BR>@Capacidad (Y001) para cada NODO EMISOR:".json_encode($capacidad);

    /*********************************************************************************/

	// Integrar arreglos de nodos (receptores y cuentas) con datos de capacidad de emisores
    echo "<BR><BR>***************** Integrando Nodos **********************";

    for($i=2;$i<$emisores_numero+2;$i++){
    	$llave = $emisores_base_lista[$i];
    	//if($indice >= $numero_nodos)
		//	error_msg("Error: Indice es mayor al número de nodos",E_USER_ERROR);
    	//if(array_key_exists($llave,$nodo_kvp)){
    	IF(isset($nodo_kvp[$llave])){
    		if(DEBUG>=2)
    			echo "<BR> i ".$i." Llave ".$llave." Valor ".$nodo_kvp[$llave]." Capacidad ".$capacidad[$i];
    		if($nodo_kvp[$llave]>=0 && $nodo_kvp[$llave])
    			$nodo_capacidad[$nodo_kvp[$llave]] = $capacidad[$i];
    	} else {
    		echo "<BR>WARNING: Elemento emisor ".$llave." no existe en los receptores y los valores relacionados serán ignorados";
    	}
    }
	for($i=2;$i<$numero_nodos+2;$i++)
    	if($nodo_capacidad[$i]<0)
    		$nodo_capacidad[$i]=0.0;
    /*********************************************************************************/

    // Cargar archivos de primarios 
    echo "<BR><BR>***************** Cargando Archivo de Primarios **********************";

	chdir("csv"); 

    $filename = CLIENTE."_Primarios_".ANO."_".MES."_".VERSION.".csv";
    $filename_out = CLIENTE."_PrimariosValidados_".ANO."_".MES."_".VERSION.".csv";
    $label1="C001";
    $label2="C002";
    $required_cols = 4;
    $check_sender=0;
	require "GetDim_read_input.php";
	
    // Cargar archivos de secundarios
    echo "<BR><BR>***************** Cargando Archivo de Secundarios **********************";

    $filename = CLIENTE."_Consumos_".ANO."_".MES."_".VERSION.".csv";
    $filename_out = CLIENTE."_ConsumosValidados_".ANO."_".MES."_".VERSION.".csv";
    $label1="Q001";
    $label2="Q002";
    $required_cols = 4;
    $check_sender=1;
	require "GetDim_read_input.php";

    /*********************************************************************************/

    // Imprimir archivo de salida con elementos (nodos)
    echo "<BR><BR>***************** Imprimiendo Archivo de Salida (Nodos) **********************";

	$myFile = CLIENTE."_elementosDim_".ANO."_".MES."_".VERSION.".csv";
	$fh = fopen($myFile, 'w') or die("No se pudo abrir archivo de salida");
	fwrite($fh, "ID_Nodo;Etiqueta_Nodo;Flag_Consolidado;Flag_Capacidad;Y001;Nivel\n");
	for($i=2;$i<$numero_nodos+2;$i++){
		$flag_capacity=0;
		if($nodo_flag_capacidad[$i] == 'Y')
			$flag_capacity=1;
		else
			$flag_capacity=0;
		if($nodo_etiqueta[$i] && $flag_usado[$i]===1 && $nodo_nivel[$i]>=0){
			if($flag_capacity === 0 && $nodo_capacidad[$i] > 0.0){
				echo "<BR>WARNING: El elemento ".$nodo_etiqueta[$i]." tiene capacidad limitante (".$nodo_capacidad[$i].") pero no está indicada como atributo (Y). Se asumirá que dicho atributo es (Y) en el archivo de salida!";
				$flag_capacity=1;
			}
			fprintf($fh,"%d;%s;%d;%d;%4f;%d\n",$i,$nodo_etiqueta[$i],$flag_consolidado[$i],$flag_capacity,$nodo_capacidad[$i],$nodo_nivel[$i]);
		}
	}
	echo "<BR><b>Se escribió el archivo ".$myFile." con datos de los elementos de las dimensiones de Cuenta y Receptor</b>";
	fclose($fh);
	chdir(".."); 
	echo "<BR><b>El programa finalizó con éxito.";
?>