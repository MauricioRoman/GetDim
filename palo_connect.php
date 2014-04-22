        <?php
/**
 * GetDim_palo_connect.php
 *
 * Funciones para conectarse via SDK a Jedox OLAP
 *
 * @package    SAREN
 * @author     Mauricio Román Rojas <mauricio.roman.rojas@gmail.com>
 * @copyright  2013 Mauricio Román Rojas
 * @version    1.2
 * @since      5 Nov 2013
 * @deprecated 
 */
        function RCA_palo_connect()
        {
        
        	// registers a name for a PALO server
		
			$conn_status = @palo_ping(CONN);
			if(DEBUG)
				echo $conn_status;

			if (is_string($conn_status) || $conn_status[0] == '#')
				$estado="INACTIVA";
			elseif ($conn_status==1)
				$estado="ACTIVA";

			echo "La conexión: ".CONN."/".HOST." para usuario ".USER." está ".$estado."<BR>";

			if ($estado == "INACTIVA"){
        		//Intentamos registrar el servidor...
				if(DEBUG)
        			echo "<BR>Intentando registrar el servidor";
        		$conn = @palo_register_server(CONN, HOST, PORT, USER, PASS);
        		if(DEBUG)	
        			print_r($conn);
   				if (!is_string($conn) || $conn[0] == '#'){
     				error_msg("Conexión al servidor Jedox no está disponible!!",E_USER_ERROR);
     			} else {
     				echo "La conexión con el servidor Jedox Molap fue registrada exitosamente<BR>";
     			}
     		}
			//print_r(palo_server_info(CONN));
			
		}