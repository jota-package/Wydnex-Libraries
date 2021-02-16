<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\respuesta;
use App\Http\Controllers\dangoController;
use Carbon\Carbon;
use App;


class filesController extends Controller
{
	public function test(){

		$data = [
			"recepcion_id" => 1,
			"file_nombre" => "Nueva Apertura 4",
			"file_padre_id" => 6,
			"file_tipo" => 2,
			"file_usuario_id" => 1
		];
		$f = new App\files();
		//return $f->crear($data);
		//return $f->listar_desde_recepcion(1, 2);
		//return $f->mover(1, 2);
		//return $f->listar_desde_padre(1);
		//return $f->borrar(5);
		return respuesta::ok();
	}

	public function create_node(Request $request){
		// Descomente la siguiente linea en caso de prueba
		//return respuesta::ok(["file_id" => 15]);

		// Considerado que unicamente los nodos que se crean por este controlador son los nodos de tipo directorio
		$f = new App\files();
		$parent = $request->input('padre_id', 0);
		if($parent == 0){
			$data = [
				"recepcion_id" => intval($request->input('recepcion_id',0)),
				"file_nombre" => $request->input('nombre',''),
				"file_captura_estado" => intval($request->input('captura_estado',0)),
				"file_tipo" => 'd',
				"file_usuario_id" => session("usuario_id")
			];
			return $f->crear($data);
		} else {
			$parent = App\files::where('file_id', $parent)
						->where('recepcion_id', intval($request->input('recepcion_id',0)))
						->where('file_captura_estado', intval($request->input('captura_estado',0)))
						->first();
			if($parent){
				$data = [
					"recepcion_id" => $parent["recepcion_id"],
					"file_nombre" => $request->input('nombre',''),
					"file_padre_id" => $request->input('padre_id', 0),
					"file_captura_estado" => $parent["file_captura_estado"],
					"file_tipo" => 'd',
					"file_usuario_id" => session("usuario_id")
				];
				return $f->crear($data);
			} else {
				return respuesta::error("Uno de los parámetros no es correcto para poder crear el archivo o directorio.",500);
			}
		}
	}

	/**
	 * Crea un elemento file de tipo captura
	 * @param array $data Array asociativo de los elementos [recepcion_id, captura_estado, padre_id*, nombre*]
	 * @param string $nombre Nombre de la captura en caso esta no este definida en $data
	 * @return respuesta Se retorna una respuesta {estado, mensaje, status}
	 * @author Juan Ignacio Basilio Flores
	 * @version v1.00.2
	*/
	public static function create_captura($data, $nombre=""){
		// 3.2 Creando la captura file
        $f = new App\files();
        $parent = intval((!empty($data["padre_id"]))?$data["padre_id"] : 0);
        if($parent == 0){
            $file_created = $f->crear([
                "recepcion_id" => intval((!empty($data["recepcion_id"]))?$data["recepcion_id"] : 0),
                "file_nombre" => (!empty($data["nombre"]))?$data["nombre"] : $nombre,
                "file_captura_estado" => intval((!empty($data["captura_estado"]))?$data["captura_estado"] : 0),
                "file_tipo" => 'f',
                "file_usuario_id" => session()->get('usuario_id', 0)
            ]);
        } else {

            $parent = App\files::where('file_id', $parent)
                        ->where('recepcion_id', intval((!empty($data["recepcion_id"]))? $data["recepcion_id"] : 0))
                        ->where('file_captura_estado', intval((!empty($data["captura_estado"]))? $data["captura_estado"] : 0))
                        ->first();
            if($parent){
                $file_created = $f->crear([
                    "recepcion_id" => $parent["recepcion_id"],
                    "file_nombre" => (!empty($data["nombre"]))?$data["nombre"] : $nombre,
                    "file_padre_id" => intval((!empty($data["padre_id"]))?$data["padre_id"] : 0),
                    "file_captura_estado" => $parent["file_captura_estado"],
                    "file_tipo" => 'f',
                    "file_usuario_id" => session("usuario_id")
                ]);
            } else {
                return respuesta::error("Uno de los parámetros no es correcto para poder crear la captura.",500);
            }
        }

        return $file_created;
        // puede usarse lo siguiente para recibir el retorno
        if(!$file_created["estado"]){
            return response($file_created["mensaje"], 500);
        } else {
            $file_created = $file_created["payload"];
        }

	}

	public function rename_node(Request $request){
		// Descomentar la siguiente linea en caso de test rapido para front-end
		// return respuesta::ok($request->all());

		$f = new App\files();
		return $f->renombrar($request->input('file_id',0),$request->input('nombre',''));
	}

	public function delete_node(Request $request){
		// Descomentar la siguiente linea en caso de test rapido para front-end
		// return respuesta::ok($request->all());
		//return respuesta::error("No se pudo renombrar pa pa.",500);
		$f = new App\files();
		return $f->borrar_directorio($request->input('file_id',0));
	}

	public function move_node(Request $request){
		$f = new App\files();
		$file_id = intval($request->input('file_id', 0));
		$padre_id = intval($request->input('new_padre_id', 0));
		$recepcion_id = intval($request->input('recepcion_id', 0));
		$captura_estado = intval($request->input('captura_estado', 0));
		if($padre_id == 0){
			return $f->mover_a_recepcion($file_id, $recepcion_id, $captura_estado);
		} else {
			return $f->mover_a_directorio($file_id, $padre_id);
		}
	}

	public function load_tree(Request $request){
		$file_id = request('file_id');

		if($file_id == "#"){
			return $this->load_main_tree();
		} else {
			$captura_estado = intval($request->input('captura_estado',0));
			$recepcion_id = $request->input('recepcion_id',0);
			$file_id = intval($file_id);
			return $this->load_node($recepcion_id, $file_id, $captura_estado);
		}
	}

    public function load_tree_admin(Request $request){
        $file_id = request('file_id');

        if($file_id == "#"){
            return $this->load_main_tree_admin();
        } else {
            $captura_estado = intval($request->input('captura_estado',0));
            $recepcion_id = $request->input('recepcion_id',0);
            $file_id = intval($file_id);
            return $this->load_node($recepcion_id, $file_id, $captura_estado);
        }
    }

    public function load_tree_documento(Request $request){
        $file_id = request('file_id');

        if($file_id == "#"){
            return $this->load_main_tree_documento();
        } else {
            $captura_estado = intval($request->input('captura_estado',0));
            $recepcion_id = $request->input('recepcion_id',0);
            $file_id = intval($file_id);
            return $this->load_node_documento($recepcion_id, $file_id, $captura_estado);
        }
    }

	public function load_node($recepcion_id, $file_id, $captura_estado){
		$f = new App\files();
		if($file_id == 0){
			$lista = $f->listar_desde_recepcion($recepcion_id, $captura_estado);
		} else {
			$lista = $f->listar_desde_padre($recepcion_id, $file_id);
		}

		$nodo = [];
		if($lista["estado"]){
			$lista = $lista["payload"];
			foreach ($lista as $i => $elem) {
				if($elem->file_tipo == "d"){
					array_push($nodo, [
						"text" => $elem->file_nombre,
						"file_id" => $elem->file_id,
						"file_tipo" => $elem->file_tipo,
						"recepcion_id" => $elem->recepcion_id,
						"recepcion_tipo" => $elem->recepcion_tipo,
						"captura_estado" => $elem->file_captura_estado,
						"captura_id" => $elem->captura_id,
						"proyecto_id" => $elem->proyecto_id,
						"cliente_id" => $elem->cliente_id,
						"children" => true,
					]);
				} else if ($elem->file_tipo == "f") {
					if($elem->recepcion_tipo == "s" && !empty($elem->adetalle_id)){
						array_push($nodo, [
							"text" => $elem->documento_nombre,
							"icon" => $this->obtener_icon_file($elem->adetalle_nombre),
							"file_id" => $elem->file_id,
							"file_tipo" => $elem->file_tipo,
							"recepcion_id" => $elem->recepcion_id,
							"recepcion_tipo" => $elem->recepcion_tipo,
							"captura_estado" => $elem->file_captura_estado,
							"adetalle_id" => $elem->adetalle_id,
							"documento_id" => $elem->documento_id,
							"cliente_id" => $elem->cliente_id,
							"captura_id" => $elem->captura_id,
							"captura_estado_glb" => $elem->captura_estado_glb,
							"proyecto_id" => $elem->proyecto_id,
							"documento_nombre" => $elem->documento_nombre,
							"padre_id" => ((!empty($elem->file_padre_id))? $elem->file_padre_id : 0)

						]);
					} else if ($elem->recepcion_tipo == "m"){
						array_push($nodo, [
							"text" => $elem->documento_nombre,
							"icon" => $this->obtener_icon_file($elem->adetalle_nombre),
							"file_id" => $elem->file_id,
							"file_tipo" => $elem->file_tipo,
							"recepcion_id" => $elem->recepcion_id,
							"recepcion_tipo" => $elem->recepcion_tipo,
							"captura_estado" => $elem->file_captura_estado,
							"adetalle_id" => $elem->adetalle_id,
							"documento_id" => $elem->documento_id,
							"cliente_id" => $elem->cliente_id,
							"captura_id" => $elem->captura_id,
							"captura_estado_glb" => $elem->captura_estado_glb,
							"proyecto_id" => $elem->proyecto_id,
							"documento_nombre" => $elem->documento_nombre,
							"padre_id" => ((!empty($elem->file_padre_id))? $elem->file_padre_id : 0)
						]);
					}
				} else {
				}
			}
			return $nodo;
		} else {
			return [];
		}
	}

    public function load_node_admin($recepcion_id, $file_id, $captura_estado){
        $f = new App\files();
        if($file_id == 0){
            $lista = $f->listar_todo_desde_recepcion_admin($recepcion_id, $captura_estado);
        } else {
            $lista = $f->listar_desde_padre_admin($recepcion_id, $file_id);
        }

        $nodo = [];
        if($lista["estado"]){
            $lista = $lista["payload"];
            foreach ($lista as $i => $elem) {
                if($elem->file_tipo == "d"){
                    array_push($nodo, [
                        "text" => $elem->file_nombre,
                        "file_id" => $elem->file_id,
                        "file_tipo" => $elem->file_tipo,
                        "recepcion_id" => $elem->recepcion_id,
                        "recepcion_tipo" => $elem->recepcion_tipo,
                        "captura_estado" => $elem->file_captura_estado,
                        "captura_id" => $elem->captura_id,
                        "proyecto_id" => $elem->proyecto_id,
                        "cliente_id" => $elem->cliente_id,
                        "children" => true,
                    ]);
                } else if ($elem->file_tipo == "f") {
                    if($elem->recepcion_tipo == "s" && !empty($elem->adetalle_id)){
                        array_push($nodo, [
                            "text" => $elem->documento_nombre,
                            "icon" => $this->obtener_icon_file($elem->adetalle_nombre),
                            "file_id" => $elem->file_id,
                            "file_tipo" => $elem->file_tipo,
                            "recepcion_id" => $elem->recepcion_id,
                            "recepcion_tipo" => $elem->recepcion_tipo,
                            "captura_estado" => $elem->file_captura_estado,
                            "adetalle_id" => $elem->adetalle_id,
                            "documento_id" => $elem->documento_id,
                            "cliente_id" => $elem->cliente_id,
                            "captura_id" => $elem->captura_id,
                            "captura_estado_glb" => $elem->captura_estado_glb,
                            "proyecto_id" => $elem->proyecto_id,
                            "documento_nombre" => $elem->documento_nombre,
                            "padre_id" => ((!empty($elem->file_padre_id))? $elem->file_padre_id : 0)

                        ]);
                    } else if ($elem->recepcion_tipo == "m"){
                        array_push($nodo, [
                            "text" => $elem->documento_nombre,
                            "icon" => $this->obtener_icon_file($elem->adetalle_nombre),
                            "file_id" => $elem->file_id,
                            "file_tipo" => $elem->file_tipo,
                            "recepcion_id" => $elem->recepcion_id,
                            "recepcion_tipo" => $elem->recepcion_tipo,
                            "captura_estado" => $elem->file_captura_estado,
                            "adetalle_id" => $elem->adetalle_id,
                            "documento_id" => $elem->documento_id,
                            "cliente_id" => $elem->cliente_id,
                            "captura_id" => $elem->captura_id,
                            "captura_estado_glb" => $elem->captura_estado_glb,
                            "proyecto_id" => $elem->proyecto_id,
                            "documento_nombre" => $elem->documento_nombre,
                            "padre_id" => ((!empty($elem->file_padre_id))? $elem->file_padre_id : 0)
                        ]);
                    }
                } else {
                }
            }
            return $nodo;
        } else {
            return [];
        }
    }


    public function load_node_documento($recepcion_id, $file_id, $captura_estado){
        $f = new App\files();
        if($file_id == 0){
            $lista = $f->listar_todo_desde_recepcion_documento($recepcion_id, $captura_estado);
        } else {
            $lista = $f->listar_desde_padre_documento($recepcion_id, $file_id);
        }

        $nodo = [];
        if($lista["estado"]){
            $lista = $lista["payload"];
            foreach ($lista as $i => $elem) {
                if($elem->file_tipo == "d"){
                    array_push($nodo, [
                        "text" => $elem->file_nombre,
                        "file_id" => $elem->file_id,
                        "file_tipo" => $elem->file_tipo,
                        "recepcion_id" => $elem->recepcion_id,
                        "recepcion_tipo" => $elem->recepcion_tipo,
                        "captura_estado" => $elem->file_captura_estado,
                        "captura_id" => $elem->captura_id,
                        "proyecto_id" => $elem->proyecto_id,
                        "cliente_id" => $elem->cliente_id,
                        "children" => true,
                    ]);
                } else if ($elem->file_tipo == "f") {
                    if($elem->recepcion_tipo == "s" && !empty($elem->adetalle_id)){
                        array_push($nodo, [
                            "text" => $elem->documento_nombre,
                            "icon" => $this->obtener_icon_file($elem->adetalle_nombre),
                            "file_id" => $elem->file_id,
                            "file_tipo" => $elem->file_tipo,
                            "recepcion_id" => $elem->recepcion_id,
                            "recepcion_tipo" => $elem->recepcion_tipo,
                            "captura_estado" => $elem->file_captura_estado,
                            "adetalle_id" => $elem->adetalle_id,
                            "documento_id" => $elem->documento_id,
                            "cliente_id" => $elem->cliente_id,
                            "captura_id" => $elem->captura_id,
                            "captura_estado_glb" => $elem->captura_estado_glb,
                            "proyecto_id" => $elem->proyecto_id,
                            "documento_nombre" => $elem->documento_nombre,
                            "padre_id" => ((!empty($elem->file_padre_id))? $elem->file_padre_id : 0)

                        ]);
                    } else if ($elem->recepcion_tipo == "m"){
                        array_push($nodo, [
                            "text" => $elem->documento_nombre,
                            "icon" => $this->obtener_icon_file($elem->adetalle_nombre),
                            "file_id" => $elem->file_id,
                            "file_tipo" => $elem->file_tipo,
                            "recepcion_id" => $elem->recepcion_id,
                            "recepcion_tipo" => $elem->recepcion_tipo,
                            "captura_estado" => $elem->file_captura_estado,
                            "adetalle_id" => $elem->adetalle_id,
                            "documento_id" => $elem->documento_id,
                            "cliente_id" => $elem->cliente_id,
                            "captura_id" => $elem->captura_id,
                            "captura_estado_glb" => $elem->captura_estado_glb,
                            "proyecto_id" => $elem->proyecto_id,
                            "documento_nombre" => $elem->documento_nombre,
                            "padre_id" => ((!empty($elem->file_padre_id))? $elem->file_padre_id : 0)
                        ]);
                    }
                } else {
                }
            }
            return $nodo;
        } else {
            return [];
        }
    }

	public function obtener_icon_file($file_name){
		$name = explode(".", $file_name);
		if(sizeof($name) > 1){
			switch ($name[sizeof($name)-1]) {
				case 'jpg':
				case 'jpeg':
				case 'JPG':
				case 'JPEG':
					return "fa fa-file-image";
					break;
				case 'pdf':
				case 'PDF':
					return "fa fa-file-pdf";
					break;
				default:
					return "fa fa-file";
					break;
			}
		} else {
			return "fa fa-file";
		}
	}

	/**
	 * Crea una lista de las capturas creadas en la recepción o en la carpeta
	 * @param Request $request Datos desde el cliente [recepcion_id, file_id, captura_estado]
	 * @return respuesta Se retorna una respuesta {estado, mensaje, status}
	 * @author Juan Ignacio Basilio Flores
	 * @version v1.00.2
	*/

	public function listar_capturas(Request $request){
		$recepcion_id = $request->input('recepcion_id',0);
		$file_id = $request->input('file_id',0);
		$captura_estado = $request->input('captura_estado',0);

		$f = new App\files();
		if($file_id == 0){
			$lista = $f->listar_todo_desde_recepcion($recepcion_id, $captura_estado);
		} else {
			$lista = $f->listar_desde_padre($recepcion_id, $file_id);
		}

		$nodo = [];
		if($lista["estado"]){
			$lista = $lista["payload"];
			foreach ($lista as $i => $elem) {
				if ($elem->file_tipo == "f") {
					array_push($nodo, $elem);
				} else {
				}
			}
			return respuesta::ok($nodo);
		} else {
			return $lista;
		}
	}


    public function listar_captura_admin(Request $request){
        $recepcion_id = $request->input('recepcion_id',0);
        $file_id = $request->input('file_id',0);
        $captura_estado = $request->input('captura_estado',0);

        $f = new App\files();
        if($file_id == 0){
            $lista = $f->listar_todo_desde_recepcion_admin($recepcion_id, $captura_estado);
        } else {
            $lista = $f->listar_desde_padre_admin($recepcion_id, $file_id);
        }

        $nodo = [];
        if($lista["estado"]){
            $lista = $lista["payload"];
            foreach ($lista as $i => $elem) {
                if ($elem->file_tipo == "f") {
                    array_push($nodo, $elem);
                } else {
                }
            }
            return respuesta::ok($nodo);
        } else {
            return $lista;
        }
    }

    public function listar_captura_documento(Request $request){
        $recepcion_id = $request->input('recepcion_id',0);
        $file_id = $request->input('file_id',0);
        $captura_estado = $request->input('captura_estado',0);

        $f = new App\files();
        if($file_id == 0){
            $lista = $f->listar_todo_desde_recepcion_documento($recepcion_id, $captura_estado);
        } else {
            $lista = $f->listar_desde_padre_documento($recepcion_id, $file_id);
        }

        $nodo = [];
        if($lista["estado"]){
            $lista = $lista["payload"];
            foreach ($lista as $i => $elem) {
                if ($elem->file_tipo == "f") {
                    array_push($nodo, $elem);
                } else {
                }
            }
            return respuesta::ok($nodo);
        } else {
            return $lista;
        }
    }

	public function load_main_tree(){

	    $is_admin = App\user::is_admin();

	    if($is_admin){
            $data = App\proyecto::
            select(
                "proyecto_id",
                "proyecto_nombre as text"
            )
                ->with("children_captura")
                // ->where("cliente_id",1)
                ->orderBy('proyecto_id')
                ->get();
        }else{
            $usuario_id = session('usuario_id');
            $data = App\proyecto::
            select(
                "proyecto.proyecto_id",
                "proyecto.proyecto_nombre as text",
                "equipo.usuario_id"
            )
                ->leftJoin('equipo','equipo.proyecto_id','proyecto.proyecto_id')
                ->with("children_captura")
                ->where('usuario_id',$usuario_id)
                // ->where("cliente_id",1)
                ->orderBy('proyecto_id')
                ->get();
        }

        return dangoController::verifyDirectoryTreeClienteCaptura($data, true);
	}

    public function load_main_tree_admin(){
        $data = App\proyecto::
        select(
            "proyecto_nombre as text",
            "proyecto_id"
        )
            ->with("children_captura")
            ->orderBy('proyecto_id')
            ->get();

        return dangoController::verifyDirectoryTreeClienteCapturaAdmin($data, true);
    }

    public function load_main_tree_documento(){
        $data = App\proyecto::
        select(
            "proyecto_nombre as text",
            "proyecto_id"
        )
            ->with("children_captura")
            // ->where("cliente_id",1)
            ->orderBy('proyecto_id')
            ->get();

        return dangoController::verifyDirectoryTreeClienteCapturaDocumento($data, true);
    }

}
