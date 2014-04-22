<?php
/**
 * element_list_descendents.php
 *
 * Este archivo se compone de una clase para manejar colas,
 * y de una función para navegar la jerarquía de elementos de una dimensión,
 * con el fin de acumular, en un arreglo, los elementos base, a partir de un
 * punto de partida. Dicha función utiliza el algoritmo BFS y se apoya en la 
 * clase para manejar colas 
 *
 * @package    SAREN
 * @author     Mauricio Román Rojas <mauricio.roman.rojas@gmail.com>
 * @copyright  2013 Mauricio Román Rojas
 * @version    1.4
 * @since      7 Nov 2013
 * @deprecated 
 */

define('QUEUESIZE', '100');

class queue
{
	var $first;					/* posición del primer elemento */
	var $last;					/* posición del último elemento */
	var $count;					/* número de elementos en la cola */
	var $q = array();			/* cuerpo de la cola */

	function init_queue()
	{
		$this->first = 0;
		$this->last = QUEUESIZE-1;
		$this->count = 0;
	}

	function enqueue($x)
	{
		if ($this->count >= QUEUESIZE)
			echo("<BR>Warning: Cola se desbordó, x=".$x);
		else {
			$this->last = ($this->last+1) % QUEUESIZE;
			$this->q[ $this->last ] = $x;
			$this->count = $this->count + 1;
		}
	}

	function dequeue()
	{
		if ($this->count <= 0) echo "<BR>Warning: Cola está vacía";
		else {
			$x = $this->q[$this->first];
			$this->first = ($this->first+1) % QUEUESIZE;
			$this->count = $this->count -1;
		}
		return $x;
	}

	function is_empty()
	{
		if ($this->count <=0) return (TRUE);
		else return (FALSE);
	}

}

//Esta función utiliza el algoritmo BFS para generar la lista de
//todos los descendientes del elemento $start
function element_list_descendents ($palo_server, $receptores,$start)
{
	$q = new queue;
	$q->init_queue();
	$q->enqueue($start);
	$desc_adic = array();
	while($q->is_empty() == FALSE){
		$v = $q->dequeue();
		if (DEBUG) echo "<BR>Dequeue ".$v;
		$children = palo_element_list_children($palo_server, $receptores, $v);
		foreach($children as $x){
			if($x['type'] == 'consolidated'){
				$q->enqueue($x['name']);
				if (DEBUG) echo "<BR>Enqueue ".$x['name'];
				if (DEBUG) echo "<BR>Nodo ".$x['name']." es consolidado";
			} else {
				$desc_adic[] = $x['name'];
			}
		}
	}
	if (DEBUG) echo "<BR> Familia = ".$start;
	if (DEBUG) echo print_r($desc_adic,1);
	return $desc_adic;
}

?>