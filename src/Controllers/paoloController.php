<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

Trait paoloController
{

    public function wservices(){

        //$subir_archivo = request()->file('file')->store('archivos'); // guarda en el servidor con un nombre modificado (HASH)
        //$nombre_original = request()->file('file')->getClientOriginalName(); // Captura el nombre del archivo enviado de front-end
        $param1 = request("recepcion_id");
        $param2 = request("captura_id");
        $param3 = request("tipo");
        $param4 = request("nombre");
        $imagen = request("img");

        //$imagen = str_replace(" ","+",$imagen);

        // //Decodificamos $imagen codificada en base64.
        //$subir_archivo =  base64_decode($imagen)->store('archivos');
        //Storage::disk('public')->put('imagen.jpeg', base64_decode($imagen));

        //list(, $imagen) = explode(';', $imagen);
        //list(, $imagen) = explode(',', $imagen);
        Storage::disk('public')->put($param4,base64_decode($imagen));
        // //escribimos la información obtenida en un archivo llamado
        // //unodepiera.png para que se cree la imagen correctamente
        // //file_put_contents('imagen1.jpg', $imagen);
        // $subir_archivo = file_put_contents('imagen1.jpg', $imagen)->file('file')->store('archivos');
        return $param1."-".$param2."-".$param3."-".$param4;

    }
    public function index(){

    //     $f = [
    //         "cliente_nombre" => "123",
    //         "tipo-institucion-cliente" => "Peru"
    //     ];
    // return $f;
    $root='../storage/app/documentos/';
    $_POST['dir'] = urldecode($_POST['dir']);

if( file_exists($root . $_POST['dir']) ) {
	$files = scandir($root . $_POST['dir']);
	natcasesort($files);
	if( count($files) > 2 ) { /* The 2 accounts for . and .. */
		echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
		// All dirs
		foreach( $files as $file ) {
			if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && is_dir($root . $_POST['dir'] . $file) ) {
				echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "/\">" . htmlentities($file) . "</a></li>";
			}
		}
		// All files
		foreach( $files as $file ) {
			if( file_exists($root . $_POST['dir'] . $file) && $file != '.' && $file != '..' && !is_dir($root . $_POST['dir'] . $file) ) {
				$ext = preg_replace('/^.*\./', '', $file);
				echo "<li class=\"file ext_$ext\"><a id='dd' href=\"#\" rel=\"" . htmlentities($_POST['dir'] . $file) . "\">" . htmlentities($file) . "</a></li>";
			}
		}
		echo "</ul>";
	}
}

    }


    public function crear_recepcion(){
        $recepcion_identificador = request("identificador-recepcion");
        $recepcion_proyecto = request("proyecto-recepcion");
        $recepcion_documento = request("documento-recepcion");

        if( $recepcion_identificador == '' || $recepcion_proyecto == '' || $recepcion_documento =''){
            return response('Llene todos los Campos', 200);
        }

        $arreglo = [];
        $arreglo["recepcion_identificador"] = $recepcion_identificador;
        $arreglo["recepcion_proyecto"] = $recepcion_proyecto;
        $arreglo["recepcion_opcion"] = $recepcion_documento;

       return $arreglo;
    }

    public function editar_recepcion(){
        $recepcion_id = request("recepcion_actual");
        $recepcion_identificador = request("identificador-recepcion");
        $recepcion_proyecto = request("proyecto-recepcion");
        $recepcion_documento = request("documento-recepcion");

        if( $recepcion_id == '' || $recepcion_identificador == '' || $recepcion_proyecto == '' || $recepcion_documento =''){
            return response('Llene todos los Campos', 200);
        }

        $arreglo = [];
        $arreglo["recepcion_id"] = $recepcion_id;
        $arreglo["recepcion_identificador"] = $recepcion_identificador;
        $arreglo["recepcion_proyecto"] = $recepcion_proyecto;
        $arreglo["recepcion_opcion"] = $recepcion_documento;

       return $arreglo;
     }
}


