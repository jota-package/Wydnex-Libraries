<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;

Trait utils
{
    static public function rmdir_recursive($dir_path){
    	if (is_dir($dir_path)) {
			$objects = scandir($dir_path);
			$estado_proceso = true;
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (is_dir($dir_path. DIRECTORY_SEPARATOR .$object) && !is_link($dir_path."/".$object)) {
						self::rmdir_recursive($dir_path. DIRECTORY_SEPARATOR .$object);
					} else {
						unlink($dir_path. DIRECTORY_SEPARATOR .$object);
					}
				}
			}
			return rmdir($dir_path);
		} else {
			return false;
		}
    }

    static public function eliminar_lista_rutas($lista, $base=""){
        
        if(!is_array($lista)){
            return respuesta::error("La data es incorrecta.");
        }

        if(count($lista) == 0){
            return respuesta::ok();
        }

        $estado_proceso = true;
        foreach ($lista as $i => $ruta) {
        	if(is_dir($base.$ruta)){
        		if(!self::rmdir_recursive($base.$ruta)){
        			$estado_proceso = false;
        		}
        	} else if (file_exists($base.$ruta)) {
            	if(!unlink($base.$ruta)){
            		$estado_proceso = false;
            	}
            }
        }

        if(!$estado_proceso){
        	return respuesta::error("Uno o mas archivos y/o directorios no han podido ser borrados.");
        } else {
        	return respuesta::ok();
        }
    }

}
