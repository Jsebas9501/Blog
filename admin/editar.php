<?php session_start();
require 'config.php';
require '../funciones.php';

// 1.- Conectamos a la base de datos
$conexion = conexion($bd_config);
if (!$conexion) {
    header('Location: ../error.php');
}

// Comprobamos si la session esta iniciada, sino, redirigimos.
comprobarSesion();

// Determinamos si se estan enviado datos por el metodo POST o GET
# Si se envian por POST significa que el usuario los ha enviado desde el formulario
# por lo que tomamos los datos y los cambiamos en la base de datos.

# De otra forma significa que hay datos enviados por el metodo GET
# es decir, el ID que pasamos por la URL, si es asi entonces traemos los 
# datos de la base de datos a pantalla para que el usuario los pueda modificar.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Limpiamos los datos para evitar que el usuario inyecte codigo.
    $titulo = limpiarDatos($_POST['titulo']);
    $extracto = limpiarDatos($_POST['extracto']);
    // Con la funcion nl2br podemos transformar los saltos de linea en etiquetas <br>
    // $texto = nl2br($_POST['texto']);
    $texto = $_POST['texto'];
    $id = limpiarDatos($_POST['id']);
    $thump_guardada = $_POST['thump_guardada'];
    $thump = $_FILES['thump'];

    # Comprobamos que el nombre del thumb no este vacio, si lo esta
    # significa que el usuario no agrego una nueva thumb y entonces utilizamos la thumb anterior.
    if (empty($thump['name'])) {
        $thump = $thump_guardada;
    } else {
        // De otra forma cargamos la nueva thumb
        // Direccion final del archivo incluyendo el nombre
        # Importante recordar que este archivo se encuentra dentro de la carpeta admin, asi que
        # tenemos que concatenar al inicio '../' para bajar a la raiz de nuestro proyecto.
        $archivo_subido = '../' . $blog_config['carpeta_imagenes'] . $_FILES['thump']['name'];

        move_uploaded_file($_FILES['thump']['tmp_name'], $archivo_subido);
        $thump = $_FILES['thump']['name'];
    }

    $statement = $conexion->prepare('UPDATE articulos SET titulo = :titulo, extracto = :extracto, texto = :texto, thump = :thump WHERE id = :id');
    $statement->execute(array(
        ':titulo' => $titulo,
        ':extracto' => $extracto,
        ':texto' => $texto,
        ':thump' => $thump,
        ':id' => $id
    ));

    header("Location: " . RUTA . '/admin');
} else {
    $id_articulo = id_articulo($_GET['id']);

    if (empty($id_articulo)) {
        header('Location: ' . RUTA . '/admin');
    }

    // Obtenemos el post por id
    $post = obtener_post_por_id($conexion, $id_articulo);

    // Si no hay post en el ID entonces redirigimos.
    if (!$post) {
        header('Location: ' . RUTA . '/admin/index.php');
    }
    $post = $post[0];
}

require '../views/editar.views.php';
