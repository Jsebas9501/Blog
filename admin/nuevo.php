<<?php session_start();
require 'config.php';
require '../funciones.php';

// Comprobamos si la session esta iniciada, sino, redirigimos.
comprobarSesion();

// Conectamos a la base de datos
$conexion = conexion($bd_config);
if(!$conexion){
	header('Location: ../error.php');
}

// Comprobamos si los datos han sido enviados
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$titulo = limpiarDatos($_POST['titulo']);
	$extracto = limpiarDatos($_POST['extracto']);
	// Con la funcion nl2br podemos transformar los saltos de linea en etiquetas <br>
	$texto = $_POST['texto'];
	$thump = $_FILES['thump']['tmp_name'];

	// Direccion final del archivo incluyendo el nombre
	# Importante recordar que este archivo se encuentra dentro de la carpeta admin, asi que
	# tenemos que concatenar al inicio '../' para bajar a la raiz de nuestro proyecto.
	$archivo_subido = '../' . $blog_config['carpeta_imagenes'] . $_FILES['thump']['name'];

	// Subimos el archivo
	move_uploaded_file($thump, $archivo_subido);

	$statement = $conexion->prepare(
		'INSERT INTO articulos (id, titulo, extracto, texto, thump) 
		VALUES (null, :titulo, :extracto, :texto, :thump)'
	);

	$statement->execute(array(
		':titulo' => $titulo,
		':extracto' => $extracto,
		':texto' => $texto,
		':thump' => $_FILES['thump']['name']
	));

	header('Location: ' . RUTA . '/admin');
}


require '../views/nuevo.views.php';

?>