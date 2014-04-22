<?php
/**
 * GetDim_read_levels.php
 *
 * Este pedazo de código lee un archivo de niveles, en el cual se indican
 * los niveles a que pertenece cada familia de elementos de la dimensión de
 * receptores
 *
 * @package    SAREN
 * @author     Mauricio Román Rojas <mauricio.roman.rojas@gmail.com>
 * @copyright  2013 Mauricio Román Rojas
 * @version    1.4
 * @since      7 Nov 2013
 * @deprecated 
 */

	if ($fp=fopen($filename,"r")) {
		$cols = fgetcsv($fp, 1000, ";");
		if ( strcmp ($cols[0], $label1)  && strcmp ($cols[1], $label2) )
			error_msg("En la primera fila del archivo ".$filename." la columna 1 debe ser ".$label1." y la 2 ".$label2,E_USER_ERROR);

    	while (($cols = fgetcsv($fp, 1000, ";")) !== FALSE) {
        	foreach( $cols as $key => $val )
            	$cols[$key] = trim( $cols[$key] );
 
			if(!isset($nodo_kvp[$cols[0]])){
				echo "<br>WARNING: El elemento ".$cols[0]." no existe en Jedox y será omitido";
			} else {
        		if(DEBUG){
        			echo "<BR> Input Or.: ".print_r($cols, 1);
				}

				if($cols[1] == NULL || $cols[1] == 0){
					echo "<br>WARNING: Al elemento ".$cols[0]." no se le ha asignado un nivel (> 1)";
				} else {
					$familia[]=$cols[0];
					$nivel[]=$cols[1];
					//echo "<BR> Nivel: ".print_r($nivel, 1);
				}
			}
    	} // while
		fclose($fp);
	} else {
        error_msg("No se logró abrir el archivo ".$filename,E_USER_ERROR);
	}

?>