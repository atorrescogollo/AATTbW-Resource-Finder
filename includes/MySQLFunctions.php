<?php

/** ********************************************************************
 *  	COMUNIDADES AUTÓNOMAS
 ** ********************************************************************/

/**
 * oGetCCAA (hecho)
 *
 * Nivel:: (Ejemplo) FÁCIL
 * Devuelve un listado, ordenado por 'Nombre', de todas las Comunidades Autónomas o de aquellas que
 * empiezan por la condición de filtrado indicada
 *
 * @param sFiltro	Cadena de caracteres. Opcional, valor por defecto 'null'
 *			.null => la función devuelve el listado de todas las CCAA
 *			.no null => la función devuelve el listado de las CCAA cuyo nombre empieza
 *						por el valor de 'sFiltro'.
 *
 * @return Objeto de tipo mysqli_result en el que cada registro tiene la siguiente información de un
 *	 		Municipio:
 *			. Codigo => PK de la CA
 *			. Nombre => Nombre de la CA
 *
 **/
function oGetCCAA($sFiltro = null)
{
	/**
	 *  construcción de la consulta de forma incremental en función del estado definido por los
	 *  argumentos de la función.
	 *  Dos estados:
	 *   a) El argumento $sFiltro está vacío, por tanto no procede la claúsula WHERE
	 *   b) El argumento $sFiltro tiene algún valor, es necesario añadir la condición de filtrado
	 */


	/* parte de la sentencia común a los dos estados */
	$sSQL = ' SELECT sCod AS Codigo, sNombre AS Nombre' .
		' FROM Comunidad_Autonoma AS c';


	/* parte de la sentencia específica para el estado (b) */
	if (!is_null($sFiltro) && !empty($sFiltro)) {
		$sSQL .= ' WHERE sNombre LIKE "%' . $sFiltro . '%"';
	}

	/* parte de la sentencia común a los dos estados */
	$sSQL .=	 ' ORDER BY Nombre';

	return _rsExecQuery($sSQL);
}

/**
 * oGetInfoCA
 *
 * Nivel:: MEDIO - GEO (hecho)
 * Devuelve información de una CA
 *
 * @param sCod	Código de la CA. Obligatorio.
 *
 * @return
 *		- null si sCod es nulo o es una cadena vacía
 *		- en otro caso objeto de tipo mysqli_result con un máximo de un registro
 *		  con la siguiente información del Distrito con código 'sCod'.
 *			. Codigo => PK de la CA
 *			. Nombre => Nombre de la CA
 *			. Geometria => Tipo de Geometría de la CA
 *			. GeoJSON => Geometría en formato GeoJSON
 *			. NumProvincias => Número de Provincias de la CA
 *			. NumRecursos => Número de Recursos asociados a la CA
 *
 *	Funciones Geográficas a utilizar:
 *	- ST_GeometryType: Devuelve un string con el tipo de un bjeto GEOMETRY
 *	- ST_AsGeoJSON: Devuelve un string con la serialización de un objeto GEOMETRY
 *                  en formato GeoJSON.
 *  - ST_INTERSECTS (g1, g2): ST_Intersects tests to determine whether the two geometries given to it meet one of four conditions:
 *			that the geometries' interiors intersect,
 *			that their boundaries intersect,
 *			that the boundary of the first geometry intersects with the interior of the second, or
 *			that the interior of the first geometry intersects with the boundary of the second.
 *
 **/
function oGetInfoCA($sCod)
{
	/* validación de los argumentos obligatorios */
	if (is_null($sCod) or empty($sCod) or !is_numeric($sCod)) {
		return null;
	}


	$sSQL =	'
			SELECT c.sCod AS Codigo, c.sNombre AS Nombre, 
				ST_GeometryType(c.gGeometria) AS Geometria,
				ST_AsGeoJSON(c.gGeometria) AS GeoJSON, 
				( 
					SELECT COUNT(*) 
					FROM Provincia AS p 
					WHERE p.sCodCA = c.sCod 
					) AS NumProvincias, 
				( 
					SELECT COUNT(DISTINCT rp.sCodRecurso) 
					FROM Recurso_Provincia AS rp
						INNER JOIN Provincia as p
							ON p.sCod=rp.sCodProvincia
					WHERE c.sCod=p.sCodCA
				) AS NumRecursos 
			FROM Comunidad_Autonoma AS c 
			WHERE c.sCod=\'' . $sCod . '\'';

	return _rsExecQuery($sSQL);
}


/** ********************************************************************
 *  	PROVINCIAS
 ** ********************************************************************/

/**
 * oGetProvincias (por hacer)
 *
 * Nivel:: FÁCIL
 * Devuelve un listado, ordenado por 'Nombre', de todas las Provincias o de aquellas que
 * empiezan por la condición de filtrado indicada y/o pertenecen a una Comunidad Autónoma de la
 * que se conoce su código
 *
 * @param sFiltro	Cadena de caracteres. Opcional, valor por defecto 'null'
 *			.null => la función devuelve el listado de las Provincias que satisfagan la condición derivada del argumento 'sCodCA'
 *			.no null => El nombre de las Provincias pertinentes deberán empiezar
 *						por el valor de 'sFiltro'.
 *
 * @param sCodCA	Cadena de caracteres. Opcional, valor por defecto 'null'
 *			.null => la función devuelve el listado de todas las Provincias que satisfagan la condición determinada por 'sFiltro'
 *			.no null => Las Provincias pertinentes deberán pertenecer a la
 *						Comunidad Autónoma determinada por el código 'sCodCA'

 * @return Objeto de tipo mysqli_result en el que cada registro tiene la siguiente información de una Provincia:
 *			. Codigo => PK de la Provincia
 *			. Nombre => Nombre de la Provincia
 *			. CodigoCA => PK de la Comunidad Autónoma a la que pertenece
 *			. NombreCA => Nombre de la Comunidad Autónoma a la que pertenece
 *
 **/
function oGetProvincias($sFiltro = null, $sCodCA = null)
{
	$sSQL = '
		SELECT p.sCod as Codigo, p.sNombre as Nombre, c.sCod as CodigoCA, c.sNombre as NombreCA 
		FROM Provincia as p INNER JOIN Comunidad_Autonoma as c 
		ON p.sCodCA=c.sCod
		';
	$bFiltro = !is_null($sFiltro) && !empty($sFiltro);
	$bCodCA = !is_null($sCodCA) && !empty($sCodCA) && is_numeric($sCodCA);
	if ($bFiltro && $bCodCA) {
		$sSQL .= 'WHERE c.sCod=' . $sCodCA . ' AND p.sNombre LIKE "' . $sFiltro . '%" ';
	} else if ($bFiltro) {
		$sSQL .= 'WHERE p.sNombre LIKE "' . $sFiltro . '%" ';
	} else if ($bCodCA) {
		$sSQL .= 'WHERE c.sCod=' . $sCodCA . ' ';
	}
	$sSQL .= 'ORDER BY p.sNombre ASC';
	return _rsExecQuery($sSQL);
}

/**
 * oGetInfoProvincia
 *
 * Nivel:: MEDIO - GEO (por hacer)
 * Devuelve información de una Provincia
 *
 * @param sCod	Código de la Provincia. Obligatorio.
 *
 * @return
 *		- null si sCod es nulo o es una cadena vacía
 *		- en otro caso objeto de tipo mysqli_result con un máximo de un registro
 *		  con la siguiente información del Distrito con código 'sCod'.
 *			. Codigo => PK de la Provincia
 *			. Nombre => Nombre de la Provincia
 *			. CodigoCA => PK de la Comunidad Autónoma a la que pertenece
 *			. NombreCA => Nombre de la Comunidad Autónoma a la que pertenece
 *			. Geometria => Tipo de Geometría de la Provincia
 *			. GeoJSON => Geometría en formato GeoJSON
 *			. NumRecursos => Número de Recursos asociados a la Provincia
 *
 *	Funciones Geográficas a utilizar:
 *	- ST_GeometryType: Devuelve un string con el tipo de un bjeto GEOMETRY
 *	- ST_AsGeoJSON: Devuelve un string con la serialización de un objeto GEOMETRY
 *                  en formato GeoJSON.
 *  - ST_INTERSECTS (g1, g2): ST_Intersects tests to determine whether the two geometries given to it meet one of four conditions:
 *			that the geometries' interiors intersect,
 *			that their boundaries intersect,
 *			that the boundary of the first geometry intersects with the interior of the second, or
 *			that the interior of the first geometry intersects with the boundary of the second.
 *
 **/
function oGetInfoProvincia($sCod)
{
	if (is_null($sCod) or empty($sCod) or !is_numeric($sCod)) {
		return null;
	}

	$sSQL = 'SELECT p.sCod as Codigo, p.sNombre as Nombre, 
				c.sCod as CodigoCA , c.sNombre as NombreCA,
				ST_GeometryType(p.gGeometria) as Geometria,
				ST_AsGeoJSON(p.gGeometria) as GeoJSON,
				(
					SELECT COUNT(DISTINCT rp.sCodRecurso) 
					FROM Recurso_Provincia AS rp
					WHERE rp.sCodProvincia=p.sCod
				) as NumRecursos
				FROM Provincia as p INNER JOIN Comunidad_Autonoma as c
				ON p.sCodCA=c.sCod
				WHERE p.sCod=' . $sCod;

	return _rsExecQuery($sSQL);
}


/** ********************************************************************
 *  	CATEGORÍAS
 ** ********************************************************************/
/**
 * oGetTodasCategorias
 *
 * Nivel:: FÁCIL (por hacer)
 * Devuelve un listado, ordenado por nombre, de todas las Categorías
 *
 *
 * @return
 *		- objeto de tipo mysqli_result en que que cada registro tiene la
 *		 la siguiente información de una Categoría:
 *			. Codigo => PK de la Categoría
 *			. Nombre => Nombre de la Categoría
 *			. NumCD => Número de Conjuntos de Datos asociados a la Categoría
 *			. NumRecursos => Números de Recursos de los Conjuntos de Datos asociados a la Categoría
 **/

function oGetTodasCategorias()
{
	$sSQL = '
		SELECT c.sCod as Codigo, 
			c.sNombre as Nombre, 
			COUNT(DISTINCT g.sCodCD) as NumCD, 
			COUNT(DISTINCT r.sCod) as NumRecursos
		FROM Categoria as c 
			LEFT JOIN ConjuntoDatos_Categoria as g 
				ON c.sCod=g.sCodCat 
			LEFT JOIN Recurso as r
				ON g.sCodCD=r.sCodCD
		GROUP BY c.sCod, c.sNombre
		';
	return _rsExecQuery($sSQL);
}

/**
 * oGetCategorias
 *
 * Nivel:: DIFÍCIL (hecho)
 * Devuelve un listado, ordenado por nombre, de las Categorías que satisfacen las condiciones de filtrado
 *
 * @param aCodCat	array con códigos de Categorías existentes. Opcional, valor por defecto 'null'
 *		.null => la función devuelve las Categorías que satisfagan la condición derivada del argumento 'aCodPC'
 *		.no null => La función devuelve las Categorías asociadas a Conjuntos de Datos asociados a las Categorías
 *					indicadas en el array excluyendo éstas, y que satisfagan la condición derivada del argumento 'aCodPC'.
 *
 * @param aCodPC	array con códigos de Palabras Clave existentes. Opcional, valor por defecto 'null'
 *		.null => la función devuelve las Categorías que satisfagan la condición derivada del argumento 'aCodCat'
 *		.no null => La función devuelve las Categorías asociadas a Conjuntos de Datos asociados a las Palabras Clave
 *					indicadas en el array excluyendo éstas, y que satisfagan la condición derivada del argumento 'aCodCat'.
 *
 * @return
 *		- objeto de tipo mysqli_result en que que cada registro tiene la
 *		  la siguiente información de una Categoría:
 *			. Codigo => PK de la Categoría
 *			. Nombre => Nombre de la Categoría
 *			. NumCD => Número de Conjuntos de Datos asociados a la Categoría
 *			. NumRecursos => Números de Recursos de los Conjuntos de Datos asociados a la Categoría
 **/

function oGetCategorias($aCodCat = null, $aCodPC = null)
{
	$sSQL = '
			SELECT c.sCod AS Codigo, c.sNombre AS Nombre, 
					COUNT(DISTINCT cdc.sCodCD) AS NumCD, COUNT(DISTINCT r.sCod) AS NumRecursos 
			FROM Categoria AS c INNER JOIN ConjuntoDatos_Categoria AS cdc 
				ON cdc.sCodCat = c.sCod LEFT JOIN Recurso AS r
				ON r.sCodCD = cdc.sCodCD';

	$sSQLWhere = '';
	/* condición derivada de $aCodCat */
	if (!is_null($aCodCat) && is_array($aCodCat) && count($aCodCat) > 0) {
		$sAux = '';
		$sAux2 = '';
		foreach ($aCodCat as $sCod) {
			$sAux .= (empty($sAux)) ? '' : ' AND ';
			$sAux2 .= (empty($sAux2)) ? '' : ', ';
			$sAux .= '
					cdc.sCodCD IN ( SELECT DISTINCT sCodCD
									FROM ConjuntoDatos_Categoria
									WHERE sCodCat =\'' . $sCod . '\'
									)';
			$sAux2 .=	"'$sCod'";
		}
		$sSQLWhere .= '
					WHERE ' . $sAux . '
					AND c.sCod NOT IN (' . $sAux2 . ') ';
	}

	/* condición derivada de $aCodPC */
	if (!is_null($aCodPC) && is_array($aCodPC) && count($aCodPC) > 0) {
		$sAux = '';
		foreach ($aCodPC as $sCod) {
			$sAux .= (empty($sAux)) ? '' : ' AND ';
			$sAux .= '
					cdc.sCodCD IN ( SELECT DISTINCT sCodCD
									FROM ConjuntoDatos_PalabrasClave
									WHERE sCodPC =\'' . $sCod . '\'
									)';
		}
		$sSQLWhere .= ((empty($sSQLWhere)) ? '
						WHERE ' : '
						AND ') . $sAux;
	}


	/* parte de la sentencia común a todos los estados */
	$sSQL .=	$sSQLWhere . '
			GROUP BY c.sCod, c.sNombre
			ORDER BY Nombre';


	//echo PHP_EOL.$sSQL.PHP_EOL;
	return _rsExecQuery($sSQL);
}



/** ********************************************************************
 *  	PALABRAS CLAVE
 ** ********************************************************************/
/**
 * oGetTodasPalabrasClave
 *
 * Nivel:: FÁCIL (por hacer)
 * Devuelve un listado, ordenado por nombre, de todas las Palabras Clave
 *
 * @return
 *		- objeto de tipo mysqli_result en que que cada registro tiene la
 *		 la siguiente información de una Palabra Clave:
 *			. Codigo => PK de la Palabra Clave
 *			. Nombre => Palabra Clave
 *			. NumCD => Número de Conjuntos de Datos asociados a la Palabra Clave
 *			. NumRecursos => Números de Recursos de los Conjuntos de Datos asociados a la Palabra Clave
 **/

function oGetTodasPalabrasClave()
{
	$sSQL = 'SELECT pc.sCod as Codigo, pc.sNombre as Nombre, COUNT(DISTINCT cd.sCod) as NumCD, COUNT(DISTINCT r.sCod) as NumRecursos
				FROM Palabras_Clave as pc 
					LEFT JOIN ConjuntoDatos_PalabrasClave as cdpc 
						ON pc.sCod=cdpc.sCodPC 
					LEFT JOIN Conjunto_Datos as cd 
						ON cd.sCod=cdpc.sCodCD 
					LEFT JOIN Recurso as r 
						ON r.sCodCD=cd.sCod 
					GROUP BY pc.sCod 
					ORDER BY pc.sNombre ASC
				';
	return _rsExecQuery($sSQL);
}

/**
 * oGetPalabrasClave
 *
 * Nivel:: DIFÍCIL (por hacer)
 * Devuelve un listado, ordenado por nombre, de las Palabras Clave que satisfacen las condiciones de filtrado
 *
 * @param aCodCat	array con códigos de Categorías existentes. Opcional, valor por defecto 'null'
 *		.null => la función devuelve las Palabras Clave que satisfagan la condición derivada del argumento 'aCodPC'
 *		.no null => La función devuelve las Palabras Clave asociadas a Conjuntos de Datos asociados a las Categorías
 *					indicadas en el array excluyendo éstas, y que satisfagan la condición derivada del argumento 'aCodPC'.
 *
 * @param aCodPC	array con códigos de Palabras Clave existentes. Opcional, valor por defecto 'null'
 *		.null => la función devuelve las Palabras Clave que satisfagan la condición derivada del argumento 'aCodCat'
 *		.no null => La función devuelve las Palabras Clave asociadas a Conjuntos de Datos asociados a las Palabras Clave
 *					indicadas en el array excluyendo éstas, y que satisfagan la condición derivada del argumento 'aCodCat'.
 *
 * @return
 *		- objeto de tipo mysqli_result en que que cada registro tiene la
 *		 la siguiente información de una Palabra Clave:
 *			. Codigo => PK de la Palabra Clave
 *			. Nombre => Palabra Clave
 *			. NumCD => Número de Conjuntos de Datos asociados a la Palabra Clave
 *			. NumRecursos => Números de Recursos de los Conjuntos de Datos asociados a la Palabra Clave
 **/

function oGetPalabrasClave($aCodCat = null, $aCodPC = null)
{
	$sSQL = 'SELECT pc.sCod as Codigo, pc.sNombre as Nombre, COUNT(DISTINCT cd.sCod) as NumCD, COUNT(DISTINCT r.sCod) as NumRecursos
	FROM Palabras_Clave as pc 
		INNER JOIN ConjuntoDatos_PalabrasClave as cdpc 
			ON pc.sCod=cdpc.sCodPC 
		INNER JOIN Conjunto_Datos as cd 
			ON cd.sCod=cdpc.sCodCD 
		LEFT JOIN Recurso as r 
			ON r.sCodCD=cd.sCod
		';

	$sSQLWhereClause = '';

	$bCodPC = isset($aCodPC) && is_array($aCodPC) && count($aCodPC) > 0;
	$bCodCat = isset($aCodCat) && is_array($aCodCat) && count($aCodCat) > 0;
	if ($bCodPC) {
		$aux = '';
		$aux2 = '';
		foreach ($aCodPC as $i) {
			$aux .= (empty($aux) ? '' : ' AND ');
			$aux2 .= (empty($aux2) ? '' : ' , ');
			$aux .= '
				cdpc.sCodCD IN (
					SELECT DISTINCT sCodCD
					FROM ConjuntoDatos_PalabrasClave
					WHERE sCodPC = \'' . $i . '\'
				)
			';
			$aux2 .= '\'' . $i . '\'';
		}
		$sSQLWhereClause .= '
			WHERE ' . $aux . ' AND pc.sCod NOT IN (' . $aux2 . ')
		';
	}
	if ($bCodCat) {
		$aux = '';
		foreach ($aCodCat as $i) {
			$aux .= (empty($aux)) ? '' : ' AND ';
			$aux .= '
				cdpc.sCodCD IN (
					SELECT DISTINCT sCodCD
					FROM ConjuntoDatos_Categoria
					WHERE sCodCat = \'' . $i . '\'
				)
			';
		}
		$sSQLWhereClause .= ' ' . ((empty($sSQLWhereClause)) ? ' WHERE ' : ' AND ') . $aux . ' ';
	}

	$sSQL .= $sSQLWhereClause . '
		GROUP BY pc.sCod , pc.sNombre
		ORDER BY pc.sNombre ASC
	';
	return _rsExecQuery($sSQL);
}

/** ********************************************************************
 *  	CONJUNTOS DE DATOS
 ** ********************************************************************/
/**
 * oGetTodosConjuntosDatos
 *
 * Nivel:: FÁCIL (por hacer)
 * Devuelve un listado, ordenado por nombre, de todos los Conjuntos de Datos
 *
 *
 * @return
 *		- objeto de tipo mysqli_result en que que cada registro tiene la
 *		 la siguiente información de un Conjunto de Datos:
 *			. Codigo => PK del Conjunto de Datos
 *			. Nombre => Nombre del Conjunto de Datos
 *			. Descripción => Descripción del Conjunto de Datos
 *			. Categorias => Concatenación, separada por ';', del nombre de las Categorías a las
 *						que se encuentra asociado el Conjunto de Datos
 *			. PalabrasClave => Concatenación, separada por ';', de las Palabras Clave
 *						del Conjunto de Datos
 *			. NumRecursos => Números de Recursos de los Conjuntos de Datos asociados a la Palabra Clave
 **/

function oGetTodosConjuntosDatos()
{
	$sSQL = '
		SELECT 
			cd.sCod as Codigo, 
			cd.sNombre as Nombre, 
			cd.tDescripcion as Descripcion, 
			GROUP_CONCAT(DISTINCT c.sNombre ORDER BY c.sNombre SEPARATOR\';\') as Categorias, 
			GROUP_CONCAT(DISTINCT pc.sNombre ORDER BY pc.sNombre SEPARATOR\';\') as PalabrasClave, 
			COUNT(DISTINCT r.sCod) as NumRecursos
		FROM Conjunto_Datos as cd
			INNER JOIN ConjuntoDatos_Categoria as cdc
				ON cdc.sCodCD=cd.sCod
				LEFT JOIN Categoria as c
				ON c.sCod=cdc.sCodCat
			INNER JOIN ConjuntoDatos_PalabrasClave as cdpc
				ON cdpc.sCodCD=cd.sCod
			INNER JOIN Palabras_Clave as pc
				ON pc.sCod=cdpc.sCodPC
			LEFT JOIN Recurso as r
				ON r.sCodCD=cd.sCod
			GROUP BY cd.sCod
			ORDER BY cd.sNombre ASC
	';
	return _rsExecQuery($sSQL);
}

/**
 * oGetConjuntosDatos
 *
 * Nivel:: DIFÍCIL (por hacer)
 * Devuelve un listado, ordenado por nombre, de los Conjuntos de Datos que satisfacen las condiciones de filtrado
 *
 * @param aCodCat	array con códigos de Categorías existentes. Opcional, valor por defecto 'null'
 *		.null => la función devuelve los Conjuntos de Datos que satisfagan la condición derivada del argumento 'aCodPC'
 *		.no null => La función devuelve los Conjuntos de Datos asociados a las Categorías
 *					indicadas en el array, y que satisfagan la condición derivada del argumento 'aCodPC'.
 *
 * @param aCodPC	array con códigos de Palabras Clave existentes. Opcional, valor por defecto 'null'
 *		.null => la función devuelve los Conjuntos de Datos que satisfagan la condición derivada del argumento 'aCodCat'
 *		.no null => La función devuelve los Conjuntos de Datos asociados a las Palabras Clave
 *					indicadas en el array, y que satisfagan la condición derivada del argumento 'aCodCat'.
 *
 *	@param sCod  código del Conjunto de Datos del que se desea información. Opcional, valor por defecto 'null'.
 *		.null => la función devuelve los conjuntos de datos pertinenten a los otros argumentos
 *		.not null => la función devuelve información del Conjunto de Datos con ese valor de código
 *					IGNORANDO el resto de argumentos.
 *
 * @return
 *		- objeto de tipo mysqli_result en que que cada registro tiene la
 *		 la siguiente información de un Conjunto de Datos:
 *			. Codigo => PK del Conjunto de Datos
 *			. Nombre => Nombre del Conjunto de Datos
 *			. Descripción => Descripción del Conjunto de Datos
 *			. Categorias => Concatenación, separada por ';', del nombre de las Categorías a las
 *						que se encuentra asociado el Conjunto de Datos
 *			. PalabrasClave => Concatenación, separada por ';', de las Palabras Clave
 *						del Conjunto de Datos
 *			. NumRecursos => Números de Recursos de los Conjuntos de Datos asociados a la Palabra Clave
 **/

function oGetConjuntosDatos($aCodCat = null, $aCodPC = null, $sCod = null)
{
	$sSQL = 'SELECT 
			cd.sCod as Codigo, 
			cd.sNombre as Nombre, 
			cd.tDescripcion as Descripcion, 
			GROUP_CONCAT(DISTINCT c.sNombre ORDER BY c.sNombre SEPARATOR\';\') as Categorias, 
			GROUP_CONCAT(DISTINCT pc.sNombre ORDER BY pc.sNombre SEPARATOR\';\') as PalabrasClave, 
			COUNT(DISTINCT r.sCod) as NumRecursos
		FROM Conjunto_Datos as cd
		INNER JOIN ConjuntoDatos_Categoria as cdc
			ON cdc.sCodCD=cd.sCod
		INNER JOIN Categoria as c
			ON c.sCod=cdc.sCodCat
		INNER JOIN ConjuntoDatos_PalabrasClave as cdpc
			ON cdpc.sCodCD=cd.sCod
		INNER JOIN Palabras_Clave as pc
			ON pc.sCod=cdpc.sCodPC
		LEFT JOIN Recurso as r
			ON r.sCodCD=cd.sCod
	';

	$bCod = !is_null($sCod);
	if ($bCod) {
		$sSQL .= " WHERE cd.sCod='" . $sCod . "' ";
	} else {
		$aux = array();
		$bCodCat = !is_null($aCodCat) && is_array($aCodCat) && count($aCodCat) > 0;
		if ($bCodCat) {
			$aux['Cat'] = '
				SELECT cd.sCod as sCodCD
				FROM Conjunto_Datos as cd
				INNER JOIN ConjuntoDatos_Categoria as cdc
					ON cdc.sCodCD=cd.sCod
				WHERE cdc.sCodCat IN (\'' . implode('\',\'', $aCodCat) . '\')
				GROUP BY cd.sCod
				HAVING COUNT(DISTINCT cdc.sCodCat)=' . count($aCodCat) . '
			';
		}

		$bCodPC = !is_null($aCodPC) && is_array($aCodPC) && count($aCodPC) > 0;
		if ($bCodPC) {
			$aux['PC'] = '
				SELECT cdpc.sCodCD
				FROM Conjunto_Datos as cd
				INNER JOIN ConjuntoDatos_PalabrasClave as cdpc
					ON cd.sCod=cdpc.sCodCD
				WHERE cdpc.sCodPC IN (\'' . implode('\',\'', $aCodPC) . '\')
				GROUP BY cdpc.sCodCD
				HAVING COUNT(DISTINCT cdpc.sCodPC)=' . count($aCodPC) . '
			';
		}

		switch (count($aux)) { // How many filters to apply?
			case 2: // Merge tables
				$filtertable = '
					SELECT cat.sCodCD
					FROM (
						' . $aux['Cat'] . '
					) as cat
					INNER JOIN (
						' . $aux['PC'] . '
					) as pc
					ON pc.sCodCD=cat.sCodCD
				';
				break;
			case 1: // Get first (unique) filter
				$filtertable = reset($aux);
				break;
		}
		if (isset($filtertable)) {
			$sSQL .= '
			WHERE cd.sCod IN (
				' . $filtertable . '
			)';
		}
	}

	$sSQL .= '
		GROUP BY cd.sCod
		ORDER BY cd.sNombre ASC
	';

	return _rsExecQuery($sSQL);
}


/** ********************************************************************
 *  	RECURSOS
 ** ********************************************************************/
/**
 * oGetInfoRecurso
 *
 * Nivel:: FÁCIL (por hacer)
 * Devuelve información de un Recurso
 *
 * @param sCod	Código del Recurso. Obligatorio
 *
 * @return
 *		- null si sCod es nulo o es una cadena vacía
 *		- en otro caso objeto de tipo mysqli_result con un máximo de un registro
 *		  con la siguiente información del Recurso con código 'sCod'.
 *			. Codigo => PK del Recurso
 *			. Nombre => Nombre del Recurso
 *			. CodigoCD => PK del Conjunto de Datos al que se encuentra  asociado el Recurso
 *			. NombreCD	=> Nombre del Conjunto de Datos al que se encuentra  asociado el Recurso
 *			. Provincias => Concatenación, separada por ';', del nombre de las Provincias en
 *						las que se encuentra ubicado el Recurso
 *			. Geometria => Tipo de Geometría de la CA
 *			. GeoJSON => Geometría en formato GeoJSON
 *			. NumRasgos => Número de Rasgos que caracterizan al Recurso
 *
 *	Función de agregación a utilizar:
 * 	GROUP_CONCAT
 *
 *	Funciones Geográficas a utilizar:
 *	- ST_GeometryType: Devuelve un string con el tipo de un bjeto GEOMETRY
 *	- ST_AsGeoJSON: Devuelve un string con la serialización de un objeto GEOMETRY
 *                  en formato GeoJSON.
 *  - ST_INTERSECTS (g1, g2): ST_Intersects tests to determine whether the two geometries given to it meet one of four conditions:
 *			that the geometries' interiors intersect,
 *			that their boundaries intersect,
 *			that the boundary of the first geometry intersects with the interior of the second, or
 *			that the interior of the first geometry intersects with the boundary of the second.
 *
 **/
function oGetInfoRecurso($sCod)
{
	$sSQL = null;
	if (!is_null($sCod)) {
		$sSQL = 'SELECT 
			r.sCod as Codigo, r.sNombre as Nombre,
			cd.sCod as CodigoCD, cd.sNombre as NombreCD,
			GROUP_CONCAT(DISTINCT p.sNombre SEPARATOR \';\') as Provincias,
			ST_GeometryType(r.gGeometria) as Geometria,
			ST_AsGeoJSON(r.gGeometria) as GeoJSON,
			COUNT(DISTINCT rr.sCodRasgo) as NumRasgos
			FROM Recurso r
				INNER JOIN Conjunto_Datos as cd
					ON cd.sCod=r.sCodCD
				INNER JOIN Recurso_Provincia as rp
					ON rp.sCodRecurso=r.sCod
				INNER JOIN Provincia as p
					ON p.sCod=rp.sCodProvincia
				INNER JOIN Recurso_Rasgo as rr
					ON rr.sCodRecurso=r.sCod 
			WHERE r.sCod=\'' . $sCod . '\'
			GROUP BY r.sCod
		';
	}
	return _rsExecQuery($sSQL);
}

/**
 * oGetRasgosRecurso
 *
 * Nivel:: FÁCIL (por hacer)
 * Devuelve información sobre los Rasgos asociados a un Recurso
 *
 * @param sCod	Código del Recurso. Obligatorio
 *
 * @return
 *		- null si sCod es nulo o es una cadena vacía
 *		- en otro caso objeto de tipo mysqli_result con la siguiente información
 * 		  de los Rasgos del Recurso con código 'sCod':
 *			. Codigo => PK del Rasgo
 *			. Nombre => Nombre del Rasgo
 *			. Valor => Valor del Rasgo
 *
 **/
function oGetRasgosRecurso($sCod)
{
	$sSQL = null;
	if (!is_null($sCod)) {
		$sSQL = 'SELECT rg.sCod as Codigo, rg.sNombre as Nombre, rr.sValor as Valor
			FROM Rasgo as rg
				INNER JOIN Recurso_Rasgo as rr
					ON rr.sCodRasgo=rg.sCod
			WHERE rr.sCodRecurso=\'' . $sCod . '\'
		';
	}
	return _rsExecQuery($sSQL);
}


/**
 * oGetRecursos
 *
 * Nivel:: FÁCIL (por hacer)
 * Devuelve un listado, ordenado por 'Nombre', de los Recursos pertenecientes a un Conjunto de Datos y/o cuyo nombre contiene una determinada cadena de caracteres
 *
 * @param sCodCD	Código de un Conjunto de Datos. Opcional, valor por defecto null.
 *			.null => la función devuelve todos los Recurso cuyo nombre contiene
 *					el valor de 'sFiltro'
 *			.not null => la función devuelve los Recursos del Conjunto de Datos con código 'sCodCD' y que satisfagan la condición derivada de 'sFiltro'
 *
 * @param sFiltro	Cadena de caracteres. Opcional, valor por defecto 'null'
 *			.null => la función devuelve todos los Recurso del Conjunto de Datos determinado por 'sCodCD'
 *			.no null => la función devuelve los Recursos del Conjunto de Datos cuyo nombre contiene
 *						el valor de 'sFiltro'
 * @param sCodPR Cadena de caracteres. Codigo de Provincia
 * @param sCodCA Cadena de caracteres. Codigo de Comunidad Autonoma
 * @return
 *		- null si sCodCD y sFiltro son nulos o cadena vacía
 *		- en otro caso objeto de tipo mysqli_result
 *		  con la siguiente información de los Recursos pertinentes:
 *			. Codigo => PK del Recurso
 *			. Nombre => Nombre del Recurso
 *			. CodigoCD => PK del Conjunto de Datos al que se encuentra  asociado el Recurso
 *			. NombreCD	=> Nombre del Conjunto de Datos al que se encuentra  asociado el Recurso
 *			. Provincias => Concatenación, separada por ';', del nombre de las Provincias en
 *						las que se encuentra ubicado el Recurso
 *			. Geometria => Tipo de Geometría de la CA
 *			. GeoJSON => Geometría en formato GeoJSON
 *			. NumRasgos => Número de Rasgos que caracterizan al Recurso
 *
 *	Función de agregación a utilizar:
 * 	GROUP_CONCAT
 *
 *	Funciones Geográficas a utilizar:
 *	- ST_GeometryType: Devuelve un string con el tipo de un bjeto GEOMETRY
 *	- ST_AsGeoJSON: Devuelve un string con la serialización de un objeto GEOMETRY
 *                  en formato GeoJSON.
 *  - ST_INTERSECTS (g1, g2): ST_Intersects tests to determine whether the two geometries given to it meet one of four conditions:
 *			that the geometries' interiors intersect,
 *			that their boundaries intersect,
 *			that the boundary of the first geometry intersects with the interior of the second, or
 *			that the interior of the first geometry intersects with the boundary of the second.
 **/

function oGetRecursos($sCodCD = null, $sFiltro = null, $sCodPR = null, $sCodCA = null)
{
	$sSQL = null;
	$sSQLWhereClause = '';
	$bCodCD = !is_null($sCodCD);
	$bFiltro = !is_null($sFiltro);
	$bCodPR = !is_null($sCodPR);
	$bCodCA = !is_null($sCodCA);
	if ($bCodCD) {
		$sSQLWhereClause .= ' WHERE r.sCodCD=\'' . $sCodCD . '\' ';
	}
	if ($bFiltro) {
		$sSQLWhereClause .= (empty($sSQLWhereClause)) ? ' WHERE ' : ' AND ';
		$sSQLWhereClause .= 'r.sNombre LIKE \'%' . $sFiltro . '%\' ';
	}
	if ($bCodPR) {
		$sSQLWhereClause .= (empty($sSQLWhereClause)) ? ' WHERE ' : ' AND ';
		$sSQLWhereClause .= 'p.sCod=\'' . $sCodPR . '\' ';
	}
	if ($bCodCA) {
		$sSQLWhereClause .= (empty($sSQLWhereClause)) ? ' WHERE ' : ' AND ';
		$sSQLWhereClause .= 'p.sCodCA=\'' . $sCodCA . '\' ';
	}

	if (!empty($sSQLWhereClause)) {
		$sSQL = 'SELECT 
				r.sCod as Codigo, r.sNombre as Nombre,
				cd.sCod as CodigoCD, cd.sNombre as NombreCD,
				GROUP_CONCAT(DISTINCT p.sNombre SEPARATOR \';\') as Provincias,
				ST_GeometryType(r.gGeometria) as Geometria,
				ST_AsGeoJSON(r.gGeometria) as GeoJSON,
				COUNT(DISTINCT rr.sCodRasgo) as NumRasgos
				FROM Recurso r
				INNER JOIN Conjunto_Datos as cd
						ON cd.sCod=r.sCodCD
				INNER JOIN Recurso_Provincia as rp
					ON rp.sCodRecurso=r.sCod
				INNER JOIN Provincia as p
					ON p.sCod=rp.sCodProvincia
				INNER JOIN Recurso_Rasgo as rr
					ON rr.sCodRecurso=r.sCod 
				'. $sSQLWhereClause . '
				GROUP BY r.sCod
				ORDER BY r.sNombre ASC
			';
	}

	return _rsExecQuery($sSQL);
}

/** ********************************************************************
 *  	AUXILIARES (no modificar)
 ** ********************************************************************/

/**
 * Ejecuta la query que se pasa como parámetro
 * @param sSQL: sentencia SQL (de selección) a ejecutar
 * @return objeto de tipo mysqli_result con el resultado de la consulta o null si ésta no se ha podido ejecutar
 **/
function _rsExecQuery($sSQL)
{
	global $oMysqli;

	$rs = null;
	if (!empty($sSQL)) {
		if (($rs = $oMysqli->query($sSQL)) === false) {
			printf("Consulta incorrecta: %s\nQuery: %s\n", $oMysqli->error, $sSQL);
			$rs = null;
		}
	}
	return $rs;
}


/**
 * Establece una conexión con el sistema gestor de base de datos atendiendo a los parámetors
 * de conexión definidos por las constantes MAQUINAMYSQL, USUARIOMYSQL, CLAVEMYSQL y BASEDEDATOS
 * @return objeto de tipo mysqli
 **/
function oAbrirBaseDeDatos()
{
	global $dbServer, $dbPort, $dbDatabase, $dbUser, $dbPass;
	$oMysqli = new mysqli($dbServer, $dbUser, $dbPass, $dbDatabase, $dbPort);

	if ($oMysqli->connect_error) {
		die('Error de conexión (' . $oMysqli->connect_errno . ') ' . $oMysqli->connect_error);
	}
	if (!$oMysqli->set_charset("utf8")) {
		printf("Error cargando charset utf8: %s\n", $oMysqli->error);
	}
	/* configuracion del idioma */
	$oMysqli->query("SET lc_time_names = 'es_ES'");
	return $oMysqli;
} // abrirBaseDeDatos

/**
 * Cierra la conexión asociada a un objeto de tipo mysqli
 * @param oMysqli: objeto con información de la conexión a cerrar
 **/
function cerrarBaseDeDatos($oMysqli)
{
	if (is_object($oMysqli) && get_class($oMysqli) == 'mysqli') {
		$oMysqli->close();
	}
} // cerrarBaseDeDatos
