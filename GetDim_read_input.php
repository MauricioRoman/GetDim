<?php
/**
 * GetDim_read_input.php
 *
 * Este pedazo de código lee un archivo de costos primarios, o consumos
 * y escribe en otro archivo los valores, sustituyendo espacios por ceros
 * También verifica que existan los valores.
 * v1.4 - Descarta relaciones cuyos nodos tengan niveles no definidos 
 * v1.5 - Se revisa el número de columnas en los archivos de entrada para 
 * validar que corresponda a las expectativas del programa. Además, se filtra
 * para que los nodos que envían consumo, y no aparecen en la dimensión emisores, generen una alerta.
 * @package    SAREN
 * @author     Mauricio Román Rojas <mauricio.roman.rojas@gmail.com>
 * @copyright  2013 Mauricio Román Rojas
 * @version    1.5
 * @since      21 Nov 2013
 * @deprecated 
 */
	
	$nodos_problema = array();
	$emisores_problema = array();

	if(!($fpout=fopen($filename_out,"w")))
		error_msg("Error: No se pudo abrir el archivo".$filename_out,E_USER_ERROR);

	if ($fp=fopen($filename,"r")) {
		//$cols = fgetcsv($fp, 1000, ";");
		$line_buffer = fgets($fp,4096);
		$cols = explode(";",$line_buffer);

		if (count($cols) != $required_cols){
			error_msg( "<BR>Error: El archivo de entrada necesita ".$required_cols." columnas, pero tiene ".count($cols),E_USER_ERROR);
		}
		if ( strcmp ($cols[2], $label1)  && strcmp ($cols[3], $label2) )
			error_msg("<BR>Error: En la primera fila del archivo ".$filename." la columna 3 debe ser ".$label1." y la 4 ".$label2,E_USER_ERROR);
    	else 		// Grabar la primera línea
    		fprintf($fpout,"%s;%s;%s;%s\n",$cols[0],$cols[1],$cols[2],$cols[3]);

//    	while (($cols = fgetcsv($fp, 1000, ";")) !== FALSE) {
    	while (($line_buffer = fgets($fp, 4096)) !== FALSE) {
    		$cols = explode(";",$line_buffer);
        	foreach( $cols as $key => $val )
            	$cols[$key] = trim( $cols[$key] );

            $i=2; 
			if(!isset($nodo_kvp[$cols[0]]) || !isset($nodo_kvp[$cols[1]])){
				if(!isset($nodo_kvp[$cols[0]])){
					$nodo_encontrado=0;
					foreach($nodos_problema as $p){
						if ($cols[0] == $p)
							$nodo_encontrado=1;
					}
					if(!$nodo_encontrado){
						echo "<br>WARNING: El elemento -".$cols[0]."- no existe en Jedox (cuentas y/o receptores) y será omitido";
						$nodos_problema[]=$cols[0];
					}
				}
				if(!isset($nodo_kvp[$cols[1]])){
					$nodo_encontrado=0;
					foreach($nodos_problema as $p){
						if ($cols[1] == $p)
							$nodo_encontrado=1;
					}
					if(!$nodo_encontrado){
						echo "<br>WARNING: El elemento -".$cols[1]."- no existe en Jedox (cuentas y/o receptores) y será omitido";
						$nodos_problema[]=$cols[1];
					}
				}

			} elseif ($nodo_nivel[$nodo_kvp[$cols[0]]]<0){  //Nivel no asignado
				if($flag_consolidado[$nodo_kvp[$cols[0]]] === 1){
					error_msg("<BR>Error: El nodo -".$cols[0]."- es consolidador y envía (dinero, o consumo) a ".$cols[1],E_USER_ERROR);
				}
				echo "<br>WARNING: El elemento -".$cols[0]."- no tiene padre en archivo de niveles y será omitido";
			} elseif ($nodo_nivel[$nodo_kvp[$cols[1]]]<0){  //Nivel no asignado
				if($flag_consolidado[$nodo_kvp[$cols[1]]] === 1){
					error_msg("<BR>Error: El nodo -".$cols[1]."- es consolidador y recibe (dinero, o consumo) de ".$cols[0],E_USER_ERROR);
				}
				echo "<br>WARNING: El elemento -".$cols[1]."- no tiene padre en archivo de niveles y será omitido";
			} else {
        		if(DEBUG){
        			echo "<BR> Input Or.: ".print_r($cols, 1);
				}

				if($cols[2] == NULL){
					if(DEBUG)
						echo "<BR>REEMPLAZANDO col 3 por 0";
					$cols[2]=0.0;
				}
				if($cols[3] == NULL){
					if(DEBUG)
						echo "<BR>REEMPLAZANDO col 4 por 0";
					$cols[3]=0.0;
				}
				if($flag_consolidado[$nodo_kvp[$cols[0]]] === 1){
					error_msg("<BR>Error: El nodo -".$cols[0]."- es consolidador y envía (dinero, o consumo) a ".$cols[1],E_USER_ERROR);
				}
				if($flag_consolidado[$nodo_kvp[$cols[1]]] === 1){
					error_msg("<BR>Error: El nodo -".$cols[1]."- es consolidador y recibe (dinero, o consumo) de ".$cols[0],E_USER_ERROR);
				}
				/* Si alguno de los valores existe, lo escribimos en el archivo de carga 
				y validamos los nodos */
				//str_replace(",",".",$cols[2]);		// Reemplazamos coma por punto
				//str_replace(",",".",$cols[3]);		// Reemplazamos coma por punto				
        		if(DEBUG)
        			echo "<BR> Input Tr.: ".print_r($cols, 1);
				//if($cols[2] || $cols[3]){

        		$test_a = explode(",",$cols[2]);
        		if(count($test_a) > 1){
        			$test_a_max = max(abs($test_a[0]),abs($test_a[1]));
        		} else {
        			$test_a_max = $cols[2];
        		}
        		$test_b = explode(",",$cols[3]);
        		if(count($test_b) > 1){
        			$test_b_max = max(abs($test_b[0]),abs($test_b[1]));
        		} else {
        			$test_b_max = $cols[3];
        		}
        		if(DEBUG)
        			echo "<BR>Test:".$cols[0]." ".$test_a_max."-".$test_b_max."-".$cols[2]."-".$cols[3];
        		$test1 = $test_a_max + $test_b_max;
        		$test2 = $cols[2] + $cols[3];
        		if(DEBUG)
        			echo "<br> test 1 = ".$test1." - test2 = ".$test2;
				if($test1 <> 0 || $test2 <> 0){
					$output = $cols[0].";".$cols[1].";".$cols[2].";".$cols[3];
					fprintf($fpout,"%s\n",$output);
					if(DEBUG)
						echo "<br>Escribiendo: ".$output;
					if($check_sender){
						$emisor_ok=0;
						foreach ($emisores_lista as $em){
							if($em['name'] == $cols[0])
								$emisor_ok=1;
						}
						if(!$emisor_ok){
							$nodo_encontrado=0;
							foreach($emisores_problema as $p){
								if ($cols[0] == $p)
									$nodo_encontrado=1;
							}
							if(!$nodo_encontrado){
								echo "<BR>WARNING: El elemento -".$cols[0]."- envía consumo pero no está en la dimensión de Emisores";
								$emisores_problema[] = $cols[0];
							}
						}
					}


					while($i<$numero_nodos+2){
						if($flag_usado[$nodo_kvp[$cols[0]]] && $flag_usado[$nodo_kvp[$cols[1]]])
							break;
						if(!strcmp($nodo_etiqueta[$i],$cols[0])){
							$flag_usado[$i]=1;
							if(DEBUG)
								echo "<BR>Nodo ".$nodo_etiqueta[$i]." es utilizado";
							}
							elseif(!strcmp($nodo_etiqueta[$i],$cols[1])){
								$flag_usado[$i]=1;
								if(DEBUG)
								echo "<BR>Nodo ".$nodo_etiqueta[$i]." es utilizado";
							}
					$i++;
					} // while
				} else {
					if (DEBUG){
						echo "<BR>WARNING: Omitiendo relación ". $cols[0]. " -> ".
					$cols[1]." con valores (". $cols[2].";".$cols[3].")";
						echo "<br> test 1 = ".$test1." - test2 = ".$test2;
					}
				}
			}
    	} // while
		fclose($fp);
		fclose($fpout);
		echo "<BR><b>Se escribió el archivo ".$filename." con datos de los elementos de ".$label1." y ".$label2."</b>";

	} else {
        error_msg("Error: No se logró abrir el archivo ".$filename,E_USER_ERROR);
	}

?>