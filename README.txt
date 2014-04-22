GetDim.php

Versión: 1.5
Fecha: 21 Nov 2013
Autor: Mauricio Román Rojas

Cambios:

v1.5 - Se colocó un filtro para detectar el número de columnas deseado en los archivos de entrada, y generar error si dicho número no corresponde.
Además, se filtra para que los nodos que envían consumo, y no aparecen en la dimensión emisores, generen una alerta.
También se refinó el filtro que elimina las relaciones que están en 0 (sea 
costo primario, o consumo secundario), pues PHP no estaba interpretando bien los
condicionales

Objetivo:

Este programa genera los archivos de entrada para (i) cálculo de tasas (ii) cálculo de costos secundarios y (iii) generación de trazabilidad. 

Toma como insumos: (a) Cubo y dimensiones del modelo en Jedox, (b) archivo de costos primarios, (c) archivo de consumos secundarios, y (d) archivo con niveles, asignado a elementos consolidadores, con el fin de representar los 
flujos por nivel

El programa valida la consistencia de los datos y genera Warnings en caso de encontrar problemas.

Sus salidas son: (a) archivo elementosDim con los nodos a ser usados en el modelo (sólo los que participan de relaciones de consumo primario y secundario), (b) archivo con costos primarios, llamado PrimariosValidados, (omitiendo relaciones en 0), y (c) archivo con consumos secundarios, llamado ConsumosValidados, (omitiendo relaciones en 0)

El archivo elementosDim incluye la capacidad, y el nivel, de cada nodo

Los archivos de costos primarios, y consuos secundarios, deben estar en un subdirectorio llamado "csv". Además, deben incorporar a su nombre las siglas del cliente, y el año, mes y versión que se desea correr, por ejemplo:

RBM_Primarios_2012_12_V002.csv
RBM_Consumos_2012_12_V002.csv

El año, mes, versión a correr también deben configurarse en el archivo de configuración, GetDim_config.php

Los archivos de entrada se deben colocar en un directorio llamado "csv", ubicado en el directorio donde se coloque el programa GetDim.php

Los archivos de salida de este programa sirven como archivos de entrada para el programa TRZ.exe. Para correr este programa, es necesario adicionar su directorio al PATH de Windows. Ademas, si se desean ver PDFs, es necesario instalar el programa GraphViz, e indicar la ubicacion de su directorio ejecutable en el PATH de Windows.