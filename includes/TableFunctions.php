<?php
	require_once ('MySQLFunctions.php');
		
	
	/* elementos de primer nivel */
	DEFINE('T_DATOS','td');
	DEFINE('T_DETALLE','tt');
	
	/* elementos de T_DATOS */	
	DEFINE('T_DATOS_CAMPOS','td_c');
	DEFINE('T_DATOS_INFO','td_i');

	/* elementos de T_DETALLE */	
	DEFINE('T_DETALLE_NOMBRE','tt_n');	
	DEFINE('T_DETALLE_GEO','tt_g');	
	DEFINE('T_DETALLE_RASGOS','tt_r');
	
	DEFINE('T_DETALLE_GEO_GEOMETRIA','tt_g_g');
	DEFINE('T_DETALLE_GEO_COOR','tt_g_c');

	/* campos que no deberán formar parte de T_DATOS_CAMPOS */
	$aCamposExcluidos = array('Codigo','Geometria','GeoJSON');
	$aCamposGeometria = array('Geometria','GeoJSON');

	/* tipos soportados */
	$aTipos = array('CA','PR','RE');
	
    /**
	 * aGetTable  
	 * 
	 * @param oRS	Objeto de tipo mysqli_result con con la tabla de resultados
	 *				de una consulta a la base de datos. Obligatorio
	 *
	 * @param sTipo Cadena de caracteres. Obligatorio. Indica el tipo de registros que contiene oRS.
	 *			Puede tomar uno de los siguientes valores:
	 *				- CA => registros con información de Comunidades Autónomas
	 *				- PR => registros con información de Provincias
	 *				- RE => registros con información de Recursos	 
	 *
	 * @return	
	 *	- array vacío si alguno de los argumentos no es correcto o si oRS no tiene datos
	 *	- en otro caso array asociativo con la siguiente estructura
	 * [T_DATOS] => array(
	 *		 [T_DATOS_CAMPOS] = array(<campo1>,<campo2>,.....)
	 *		 [T_DATOS_INFO] = array(
	 *						[<Codigo>] = array(<valor1>,<valor2>,....)
	 *								.............
	 *						[<Codigo>] = array(<valor1>,<valor2>,....)	 
	 *						)
	 *		)
	 * [T_DETALLE] => array(
	 *			[<Codigo>]  => array(
	 *					[T_DETALLE_NOMBRE] = string
	 *					[T_DETALLE_GEO] = array(
	 *							[T_DETALLE_GEO_GEOMETRIA] = String
	 *							[T_DETALLE_GEO_COOR] = GeoJson String 
	 *							)
	 *					[T_DETALLE_RASGOS] = array(
	 *							[<Rasgo>] = String
	 *								.............
	 *							[<Rasgo>] = String
     *							)	 
	 *						)
	 *					.............
	 *			[<Codigo>]  => array(
	 *					.............	 
	 *
	 *						)
	 *	)
	 */
	function aGetTable($oRS, $sTipo){
		global $aTipos;
		global $aCamposExcluidos;
		global $aCamposGeometria;
		$aOut = array();

		/* validación de parámetros */
		if(in_array($sTipo, $aTipos) && $oRS!=null && $oRS->num_rows>0){		
			$aOut[T_DATOS] = array();
			$aOut[T_DETALLE] = array();

			$columns = array_column($oRS->fetch_fields(),'name');
			$columns_T_DATOS_CAMPOS = array_diff($columns, $aCamposExcluidos);
			
			$requireExtraInfo = count(array_intersect($aCamposGeometria, $columns))!=count($aCamposGeometria); // Si no tiene los datos de geometria
			$columnsAll=array();
			while($row = $oRS->fetch_assoc()){
				$codigo = $row["Codigo"];
				if($requireExtraInfo){ // Si necesita llamar a Info
					switch($sTipo){
						case "PR":
							$rowRS = oGetInfoProvincia($codigo);
							break;
						case "CA":
							$rowRS = oGetInfoCA($codigo);
							break;
						case "RE":
							$rowRS = oGetInfoRecurso($codigo);
							break;
					}
					
					$columnsAll = $columns + array_column($rowRS->fetch_fields(),'name');
					$row = $rowRS->fetch_assoc();
				}
				else{
					$columnsAll=$columns;
				}

				$rasgosAdicionales=array();
				if($sTipo=="RE"){
					$rasgosAdicionales = oGetRasgosRecurso($codigo)->fetch_all();
				}

				// DATOS
				// - Campos
				$aOut[T_DATOS][T_DATOS_CAMPOS]=$columns_T_DATOS_CAMPOS;
				// - Info
				foreach($columns_T_DATOS_CAMPOS as $c){
					$aOut[T_DATOS][T_DATOS_INFO][$codigo][] = $row[$c];
				} 
				
				// DETALLE
				// - Nombre
				$nombre=$row["Nombre"];
				$aOut[T_DETALLE][$codigo][T_DETALLE_NOMBRE] = (!empty($nombre))?$nombre:$codigo;
				if(array_key_exists("NumRecursos",$row)){
					$aOut[T_DETALLE][$codigo][T_DETALLE_NOMBRE] .= " (".$row["NumRecursos"].") ";
				}

				// - Rasgos
				foreach(array_diff($columnsAll,array('Codigo','Nombre'),$aCamposGeometria) as $c){
					$aOut[T_DETALLE][$codigo][T_DETALLE_RASGOS][$c] = $row[$c];
				}
				foreach($rasgosAdicionales as $rasgo){
					$aOut[T_DETALLE][$codigo][T_DETALLE_RASGOS]['@'.$rasgo[1]] = $rasgo[2];
				}
				// - Geometria
				$aOut[T_DETALLE][$codigo][T_DETALLE_GEO][T_DETALLE_GEO_GEOMETRIA] = $row['Geometria'];
				$aOut[T_DETALLE][$codigo][T_DETALLE_GEO][T_DETALLE_GEO_COOR] = $row['GeoJSON'];

			}
		}		
		return ($aOut);
	}

	

?>