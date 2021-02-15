<?php

namespace Fedatario\Controllers;

use App;
use Illuminate\Http\Request;
use View;


Trait recepcionController
{

    public function __construct()
    {

        $this->middleware('auth');
        parent::__construct();

    }

    public function index()
    {

        $lotes = App\recepcion::
        join("cliente as c", "c.cliente_id", "recepcion.cliente_id")
            ->join("proyecto as p", "p.proyecto_id", "recepcion.proyecto_id")
            ->select(
                "recepcion_id",
                "recepcion_nombre",
                "recepcion_estado",
                "recepcion.created_at",
                "cliente_nombre",
                "proyecto_nombre"

            )
            ->orderBy("recepcion.created_at", 'DESC')
            ->get();
        $clientes = App\cliente::
        where("cliente_estado", 1)
            ->get();

        $incidencia_informativa = App\incidencia::where("incidencia_control",1)->get();


        return view::make('recepcion.index.content')
            ->with("lotes", $lotes)
            ->with("clientes", $clientes)
            ->with("incidencia_informativa", $incidencia_informativa);

    }

    //Creamos la lista de documentos solicitados asociados a un cliente y un proyecto
    public function crear_recepcion(Request $request)
    {

        $capturas = json_decode(request("capturas"));
        $nombre = request("nombre-recepcion");
        $tipo = request("tipo-recepcion");
        $cliente_id = request("cliente-recepcion");
        $proyecto_id = request("proyecto-recepcion");



        if ($nombre != null && $cliente_id != null && $proyecto_id != null && $tipo != null) {
            if ($tipo == "s") {

                if (count($capturas) == 0) {

                    return $this->crear_objeto("Error", "Ingrese algunos documentos.");

                }

                //Creación de la recepcion
                // $recepcion = new App\recepcion();
                // $recepcion->crear_recepcion($cliente_id, $proyecto_id, 1,$nombre);

                $recepcion = new App\recepcion();
                $recepcion->cliente_id = $cliente_id;
                $recepcion->proyecto_id = $proyecto_id;
                $recepcion->recepcion_estado = 1;
                $recepcion->recepcion_nombre = $nombre;
                $recepcion->recepcion_tipo = $tipo;
                $recepcion->save();

                $recepcion_id = $recepcion->recepcion_id;


                //Creamos los documentos
                //$save = $this->crear_documentos($documentos, $cliente_id, $proyecto_id, $recepcion_id);
                $save_captura = $this->crear_captura($proyecto_id, $recepcion_id, $cliente_id, $capturas);

                //Si guarda retornamos ok
                if ($save_captura) {

                    return $this->crear_objeto("ok", "Documentos registrado.");

                } else {

                    return $this->crear_objeto("Error", "Hubo un problema con la inserción de datos, inténtelo más tarde.");

                }

            } elseif ($tipo == "m") {

                //Creación de la recepcion
                // $recepcion = new App\recepcion();
                // $recepcion->crear_recepcion($cliente_id, $proyecto_id, 1,$nombre);

                $recepcion = new App\recepcion();
                $recepcion->cliente_id = $cliente_id;
                $recepcion->proyecto_id = $proyecto_id;
                $recepcion->recepcion_estado = 1;
                $recepcion->recepcion_nombre = $nombre;
                $recepcion->recepcion_tipo = $tipo;
                $recepcion->save();

                if ($recepcion) {

                    return $this->crear_objeto("ok", "Recepción Registrada.");

                } else {

                    return $this->crear_objeto("Error", "Hubo un problema con la inserción de datos, inténtelo más tarde.");

                }


            }
        } else {

            return $this->crear_objeto("Error", "Ingrese todos los campos requeridos");
        }

    }

    public function ver_recepcion()
    {

        $recepcion_id = request("recepcion_actual");

        $recepcion = App\recepcion::where("recepcion_id", $recepcion_id)
            ->first();

        $proyectos = App\proyecto::join('recepcion as rec','proyecto.proyecto_id','rec.proyecto_id')
                            ->where("rec.proyecto_id", $recepcion->proyecto_id)
                            ->get();

        $documentos = App\documento::join("captura as cap","cap.captura_id","documento.captura_id")
                ->leftjoin("incidencia_captura as i_c","i_c.captura_id","cap.captura_id")
                ->where("documento.recepcion_id", $recepcion_id)
                ->where("captura_estado", 1)
            ->get();

        $capturas = App\captura::where("recepcion_id", $recepcion_id)
            ->get();


        $recepcion['capturas'] = $capturas;
        $recepcion['proyectos'] = $proyectos;
        $recepcion['documentos'] = $documentos;

        //prueba para captura
        if ($recepcion['recepcion_tipo'] === "s") {
            session()->put('recepcion_tipo', "s");
        } else {
            session()->put('recepcion_tipo', "m");
        }

        return $recepcion;

    }

    public function editar_principal()
    {

        $capturas = json_decode(request("capturas"));


        $nombre = request("nombre-recepcion");
        $tipo = request("tipo-recepcion");
        $cliente_id = request("cliente-recepcion");
        $proyecto_id = request("proyecto-recepcion");
        $recepcion_id = request("recepcion_actual");


        if ($tipo == "s") {


            if (count($capturas) == 0) {

                return $this->crear_objeto("Error", "Ingrese algunos documentos.");

            }

            //Creamos los documentos
            // return $documentos;

            return $save = $this->crear_captura($proyecto_id, $recepcion_id, $cliente_id, $capturas);

        }

    }


    public function crear_documentos($proyecto_id, $recepcion_id, $captura_id, $cliente_id, $capturas)
    {

        return $captura_id;

        $now = date('Y-m-d H:i:s');

        $array = [];


        //Iteramos el elemento para agregar los campos de timestamp
        foreach ($capturas as $key => $value) {

            // $pos = strpos($value->documento_nombre, '_streservado_');
            $doc_mas_id = explode("_streservado_", $value->captura_nombre);


            //return $doc_mas_id;
            $contador = sizeof($doc_mas_id);
            if ($contador == 2) {

                $doc_nombre = $doc_mas_id[0];
                $doc_id = $doc_mas_id[1];

                $documento2 = new App\documento();

                $documento2->editar_documento($doc_id, $doc_nombre);

            } else {

                $objeto = [
                    "proyecto_id" => $proyecto_id,
                    "recepcion_id" => $recepcion_id,
                    "captura_id" => $captura_id,
                    "cliente_id" => $cliente_id,
                    "documento_estado" => 1,
                    "documento_nombre" => $value->captura_nombre,
                    "created_at" => $now,
                    "updated_at" => $now,

                ];

                $array[] = $objeto;

            }

        }

        $contador_array = count($array);
        //Creación de los documentos que le pertenecen a un grupo de recepción


        if ($contador_array !== 0) {
            $documento = new App\documento();
            $documento->crear_lista_documento($array);

            if ($documento) {

                return $this->crear_objeto("ok", "Documentos registrado.");

            } else {

                return $this->crear_objeto("Error", "Hubo un problema con la inserción de datos, inténtelo más tarde.");

            }
        } else {
            return $this->crear_objeto("ok", "Documentos actualizados.");
        }


    }


    public function crear_captura($proyecto_id, $recepcion_id, $cliente_id, $capturas)
    {


        $now = date('Y-m-d H:i:s');

        $array = [];
        $array_limpiar = [];

        //Iteramos el elemento para agregar los campos de timestamp
        foreach ($capturas as $key => $value) {

            // $pos = strpos($value->documento_nombre, '_streservado_');
            $cap_mas_id = explode("_streservado_", $value->captura_nombre);
            $cap_incidencia = explode("_strincidencia_", $value->captura_nombre);

            $contador = sizeof($cap_mas_id);
            if ($contador == 2) {

                $doc_nombre_temporal = $cap_mas_id[0];
                $doc_nombre_array = explode("_strincidencia_", $doc_nombre_temporal);
                $doc_nombre = $doc_nombre_array[0];
                $doc_id_temporal = $cap_mas_id[1];
                $doc_id_array = explode("_strincidencia_", $doc_id_temporal);;
                $doc_id=$doc_id_array[0];

                $captura_incidencia_informativa = $cap_incidencia[1];
                $array_limpiar[] = $doc_id;


                if ($doc_id == null) {

                    $objeto = [
                        "recepcion_id" => $recepcion_id,
                        "proyecto_id" => $proyecto_id,
                        "cliente_id" => $cliente_id,
                        "documento_nombre" => $doc_nombre,
                        "captura_estado" => 1,
                        "created_at" => $now,
                        "updated_at" => $now,

                    ];
                } else {

                    $objeto = [
                        "recepcion_id" => $recepcion_id,
                        "proyecto_id" => $proyecto_id,
                        "documento_id" => $doc_id,
                        "cliente_id" => $cliente_id,
                        "documento_nombre" => $doc_nombre,
                        "captura_estado" => 1,
                        "captura_incidencia_informativa" => $captura_incidencia_informativa,
                        "created_at" => $now,
                        "updated_at" => $now,

                    ];


                }


                $array[] = $objeto;

            } else {
                $doc_nombre_temporal = $cap_mas_id[0];
                $doc_nombre_array = explode("_strincidencia_", $doc_nombre_temporal);
                $doc_nombre = $doc_nombre_array[0];
                $captura_incidencia_informativa = $cap_incidencia[1];

                $objeto = [
                    "recepcion_id" => $recepcion_id,
                    "proyecto_id" => $proyecto_id,
                    "cliente_id" => $cliente_id,
                    "documento_nombre" => $doc_nombre,
                    "captura_estado" => 1,
                    "captura_incidencia_informativa" => $captura_incidencia_informativa,
                    "created_at" => $now,
                    "updated_at" => $now];


                $array[] = $objeto;

            }

        }

        $contador_array = count($array);
        //Creación de los documentos que le pertenecen a un grupo de recepción



        if ($contador_array !== 0) {
            $captura = new App\captura();
            $aper_cal=$captura->whereIn('captura_estado',[2,3])->select('captura_id')->get();

            foreach ($aper_cal as $key => $value) {
                $array_limpiar[] = $value->captura_id;
            }

            $captura->where('recepcion_id', $recepcion_id)->whereNotIn('captura_id', $array_limpiar)->delete();
            $documento = new App\documento();
            $documento->where('recepcion_id', $recepcion_id)->whereNotIn('documento_id', $array_limpiar)->delete();

            $captura->crear_lista_captura($array, false);

            if ($captura) {

                return $this->crear_objeto("ok", "Capturas registradas.");

            } else {

                return $this->crear_objeto("Error", "Hubo un problema con la inserción de datos, inténtelo más tarde.");

            }
        } else {
            return $this->crear_objeto("ok", "Capturas actualizadas.");
        }


    }


    public function estado_recepcion(Request $request)
    {


        $recepcion_actual = request('recepcion_actual');
        $estado = request('estado');

        $ins_captura = new App\captura();

        $validador = $ins_captura->validador($recepcion_actual);

        if ($validador < 1) {

            $save = App\recepcion::where('recepcion_id', $recepcion_actual)
                ->update(['recepcion_estado' => $estado]);

            //Si se ejecuto los query devolvemos ok
            if ($save) {

                return $this->crear_objeto("ok", "Estado Actualizado");

            }

            return $this->crear_objeto("error", "Hubo un error en la operación, inténtelo nuevamente");


        } else {

            return $this->crear_objeto("error", "Existen documento anexados a esta recepcion");
        }


    }

    public function desactivar_recepcion(Request $request)
    {

        $recepcion_id = request('recepcion_actual');

        $documento = new App\documento;
        $count = $documento->where('recepcion_id', '=', $recepcion_id)
            ->whereNotNull('adetalle_id')
            ->count();
        if ($count == 0) {

            $recepcion = new App\recepcion();

            $recepcion->where('recepcion_id', '=', $recepcion_id)
                ->update([
                    'recepcion_estado' => 0]);


            return $this->crear_objeto('ok', 'Eliminado');


        } else {

            return $this->crear_objeto('error', 'Existen documentos asociados');

        }


    }

    public function activar_recepcion(Request $request)
    {

        $recepcion_id = request('recepcion_actual');


        $recepcion = new App\recepcion();

        $recepcion->where('recepcion_id', '=', $recepcion_id)
            ->update([
                'recepcion_estado' => 1]);


        return $this->crear_objeto('ok', 'Activado');


    }

}
