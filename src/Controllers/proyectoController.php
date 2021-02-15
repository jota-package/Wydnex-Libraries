<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use App;
use View;
use Carbon\carbon;
use Illuminate\Support\Facades\Storage;
use DB;

Trait proyectoController
{
    public function index()
    {

        $proyectos = App\proyecto::
        orderBy('proyecto_id', 'ASC')
            ->get();

        $personal = App\persona::join("usuario_perfil as up", "up.usuario_id", "persona.usuario_id")
            ->where("up.perfil_id", "!=", 1)
            ->get();

        $clientes = App\cliente::
        where("cliente_estado", 1)
            ->get();

        $formatos = App\archivo_formato::
        where("af_estado", 1)
            ->get();

        $plantillas = App\plantilla::
        join("elemento as e", "e.plantilla_id", "plantilla.plantilla_id")
            ->orderBy("plantilla.plantilla_id", "asc")
            ->select(
                "plantilla.plantilla_id",
                "plantilla.plantilla_nombre"

            )
            ->distinct()
            ->get();

        return view::make('proyecto.index.content')
            ->with('proyectos', $proyectos)
            ->with("personal", $personal)
            ->with("clientes", $clientes)
            ->with("formatos", $formatos)
            ->with("plantillas", $plantillas);

    }

    public function proyecto_activo()
    {

        $proyectos = App\proyecto::
        orderBy('proyecto_id', 'ASC')
            ->where("proyecto_estado", 1)
            ->get();

        $personal = App\persona::join("usuario_perfil as up", "up.usuario_id", "persona.usuario_id")
            ->where("up.perfil_id", "!=", 1)
            ->get();

        $clientes = App\cliente::
        where("cliente_estado", 1)
            ->get();

        $formatos = App\archivo_formato::
        where("af_estado", 1)
            ->get();

        $plantillas = App\plantilla::
        join("elemento as e", "e.plantilla_id", "plantilla.plantilla_id")
            ->orderBy("plantilla.plantilla_id", "asc")
            ->select(
                "plantilla.plantilla_id",
                "plantilla.plantilla_nombre"

            )
            ->distinct()
            ->get();

        return view::make('proyecto.index.content')
            ->with('proyectos', $proyectos)
            ->with("personal", $personal)
            ->with("clientes", $clientes)
            ->with("formatos", $formatos)
            ->with("plantillas", $plantillas);

    }

    public function proyecto_crear(Request $request)
    {

        //Definimos variable que traemos del post
        $nombre_proyecto = strtolower(request('nombre-proyecto'));
        $prioridad_proyecto = request('prioridad-proyecto');
        $cliente_id = request('cliente');
        $fecha_string = request('fecha-inicio-proyecto');
        $duracion_proyecto = request('duracion-proyecto');
        $comentario_proyecto = request("comentario-proyecto");

        $tipo_medio_proyecto = request('tipo-medio-proyecto');
        $validacion_proyecto = request('validacion-proyecto');
        $multipagina_proyecto = request('multipagina-proyecto');
        $ocr_proyecto = request('ocr-proyecto');

        //Tipos de Medio
        $jpg_proyecto = request('validacion-formato-jpg');
        $png_proyecto = request('validacion-formato-png');
        $tiff_proyecto = request('validacion-formato-tiff');
        $pdf_proyecto = request('validacion-formato-pdf');
        $gif_proyecto = request('validacion-formato-gif');
        $bmp_proyecto = request('validacion-formato-bmp');
        $word_proyecto = request('validacion-formato-docx');
        $otro_proyecto = request('validacion-formato-otro');

        //Modulos permitidos
        $validacion_indizacion = request('validacion-indizacion');
        $validacion_control_calidad = request('validacion-control-calidad');
        $validacion_fedatario_firmar = request('validacion-fedatario-firmar');
        $validacion_fedatario_revisar = request('validacion-fedatario-revisar');

        //Asistente fedatario
        $validacion_fedatario_revisar_asistente = request('validacion-fedatario-revisar-asistente');

        //Validamos que los campos esten completos
        if ($nombre_proyecto == '' || $fecha_string == '' || $prioridad_proyecto == '' || $cliente_id == null || $duracion_proyecto == '' || $tipo_medio_proyecto == '' ) {

            return response('Llene todos los Campos', 200);

        }


            //Tratamos la fecha inicial y Fecha Final
            $date_string = $this->formato_fecha($fecha_string);

            $fecha_inicio_proyecto = Carbon::parse($date_string);

            $fecha_fin_proyecto = Carbon::parse($date_string)->addDays($duracion_proyecto);





        //Validamos los valores de los checks y asignamos que sean 0 en caso no estar checkeados

        $validacion_proyecto = $validacion_proyecto == '' ? 0 : 1;
        $multipagina_proyecto = $multipagina_proyecto == '' ? 0 : 1;
        $ocr_proyecto = $ocr_proyecto == '' ? 0 : 1;
        $proyecto_fedatario_asistente = $validacion_fedatario_revisar_asistente == '' ? 0 : 1;

        $jpg_proyecto = $jpg_proyecto == '' ? 0 : 1;
        $png_proyecto = $png_proyecto == '' ? 0 : 1;
        $tiff_proyecto = $tiff_proyecto == '' ? 0 : 1;
        $pdf_proyecto = $pdf_proyecto == '' ? 0 : 1;
        $gif_proyecto = $gif_proyecto == '' ? 0 : 1;
        $bmp_proyecto = $bmp_proyecto == '' ? 0 : 1;
        $word_proyecto = $word_proyecto == '' ? 0 : 1;
        $otro_proyecto = $otro_proyecto == '' ? 0 : 1;

        //Validamos los valores de los módulos que sean necesarios:

        $validacion_indizacion =  $validacion_indizacion == '' ? 0 : 1;
        $validacion_control_calidad =  $validacion_control_calidad == '' ? 0 : 1;
        $validacion_fedatario_firmar =  $validacion_fedatario_firmar == '' ? 0 : 1;
        $validacion_fedatario_revisar =  $validacion_fedatario_revisar == '' ? 0 : 1;


        //Definimos escritura en la BD


        $proyecto = new App\proyecto;

        $existe = $proyecto->validar_proyecto_existe($nombre_proyecto,$cliente_id);

        if ($existe == 'no') {

            $proyecto->proyecto_nombre = $nombre_proyecto;
            $proyecto->proyecto_prioridad = $prioridad_proyecto;
            $proyecto->proyecto_fecha_inicio = $fecha_inicio_proyecto;
            $proyecto->proyecto_fecha_fin = $fecha_fin_proyecto;
            $proyecto->proyecto_duracion = $duracion_proyecto;
            $proyecto->cliente_id = $cliente_id;
            $proyecto->proyecto_estado = 1;
            $proyecto->proyecto_comentario = $comentario_proyecto;

            $proyecto->proyecto_tipo_medio = $tipo_medio_proyecto;
            $proyecto->proyecto_validacion = $validacion_proyecto;
            $proyecto->proyecto_multipagina = $multipagina_proyecto;
            $proyecto->proyecto_ocr = $ocr_proyecto;
            $proyecto->proyecto_fedatario_asistente = $proyecto_fedatario_asistente;

            $proyecto->save();

            $formato = new App\proyecto_formato;

            $formato->proyecto_id = $proyecto->proyecto_id;
            $formato->proyecto_jpg = $jpg_proyecto;
            $formato->proyecto_png = $png_proyecto;
            $formato->proyecto_tiff = $tiff_proyecto;
            $formato->proyecto_tif = $tiff_proyecto;
            $formato->proyecto_gif = $gif_proyecto;
            $formato->proyecto_pdf = $pdf_proyecto;
            $formato->proyecto_bmp = $bmp_proyecto;
            $formato->proyecto_docx = $word_proyecto;
            $formato->proyecto_otro = $otro_proyecto;

            $formato->save();

            $proyecto_flujo = new App\proyecto_flujo();

            $proyecto_flujo -> insertar_proyecto_flujo($proyecto->proyecto_id,
                $validacion_indizacion,
                $validacion_control_calidad,
                $validacion_fedatario_revisar,
                $validacion_fedatario_firmar);


            //Si no se ejecuto los query devolvemos error
            if (!$proyecto) {
                App::abort(500, 'Error');
            }


            $directorio = $this->crear_directorio($nombre_proyecto);

            if ($directorio == 'existe') {

                return $this->crear_objeto("error", "El directorio ya existe");

            } else {
                return response('ok', 200);
            }


        } else {
            return $this->crear_objeto("error", "El proyecto ya existe");
        }


    }

    public function proyecto_ver_datos(Request $request)
    {

        $proyecto_actual = request('proyecto_actual');

       /* $datos = App\proyecto::
        join("proyecto_formato as pf", "pf.proyecto_id", "proyecto.proyecto_id")
            ->where('proyecto.proyecto_id', $proyecto_actual)
            ->first();*/

        $datos = DB::select("
            select
               a.proyecto_id,
               array(select modulo_step_id from proyecto_flujo where proyecto_id =:proyecto_id ) as flujo,
               cliente_id,
               a.created_at,
               equipo_id,
               pf_estado,
               pf_id,
               plantilla_id,
               proyecto_bmp,
               proyecto_comentario,
               proyecto_duracion,
               proyecto_estado,
               proyecto_fecha_fin,
               proyecto_fecha_inicio,
               proyecto_gif,
               proyecto_jpg,
               proyecto_multipagina,
               proyecto_ocr,
               proyecto_nombre,
               proyecto_pdf,
               proyecto_png,
               proyecto_prioridad,
               proyecto_tiff,
               proyecto_tif,
               proyecto_docx,
               proyecto_otro,
               proyecto_tipo_medio,
               proyecto_validacion,
               proyecto_fedatario_asistente,
               a.updated_at,
               usuario_creador
        from
             proyecto a
        left join proyecto_formato b on a.proyecto_id= b.proyecto_id

        where a.proyecto_id = :proyecto_id
        ", ["proyecto_id"=>$proyecto_actual]);

        return $datos;

    }


    public function proyecto_editar(Request $request)
    {

        //Definimos variable que traemos del post
        $proyecto_actual = request('proyecto_actual');

        $nombre_proyecto = strtolower(request('nombre-proyecto'));
        $prioridad_proyecto = request('prioridad-proyecto');
        $cliente_id = request('cliente');
        $fecha_string = request('fecha-inicio-proyecto');
        $duracion_proyecto = request('duracion-proyecto');
        $comentario_proyecto = request('comentario-proyecto');

        $tipo_medio_proyecto = request('tipo-medio-proyecto');
        $validacion_proyecto = request('validacion-proyecto');
        $multipagina_proyecto = request('multipagina-proyecto');
        $ocr_proyecto = request('ocr-proyecto');

        //Asistente fedatario
        // $validacion_fedatario_revisar_asistente = request('validacion-fedatario-revisar-asistente');


        //Tipos de Medio
        $jpg_proyecto = request('validacion-formato-jpg');
        $png_proyecto = request('validacion-formato-png');
        $tiff_proyecto = request('validacion-formato-tiff');
        $pdf_proyecto = request('validacion-formato-pdf');
        $gif_proyecto = request('validacion-formato-gif');
        $bmp_proyecto = request('validacion-formato-bmp');
        $word_proyecto = request('validacion-formato-docx');
        $otro_proyecto = request('validacion-formato-otro');

        //Validamos que los campos esten completos
        if ($nombre_proyecto == '' || $fecha_string == '' || $prioridad_proyecto == '' || $cliente_id == null || $duracion_proyecto == '' || $tipo_medio_proyecto == '' ) {

            return response('Llene todos los Campos', 200);

        }


            //Tratamos la fecha inicial y Fecha Final
            $date_string = $this->formato_fecha($fecha_string);

            $fecha_inicio_proyecto = Carbon::parse($date_string);

            $fecha_fin_proyecto = Carbon::parse($date_string)->addDays($duracion_proyecto);

            // $proyecto_fedatario_asistente = $validacion_fedatario_revisar_asistente == '' ? 0 : 1;

        //Validamos los valores de los checks y asignamos que sean 0 en caso no estar checkeados

        $validacion_proyecto = $validacion_proyecto == '' ? 0 : 1;
        $multipagina_proyecto = $multipagina_proyecto == '' ? 0 : 1;

        $jpg_proyecto = $jpg_proyecto == '' ? 0 : 1;
        $png_proyecto = $png_proyecto == '' ? 0 : 1;
        $tiff_proyecto = $tiff_proyecto == '' ? 0 : 1;
        $pdf_proyecto = $pdf_proyecto == '' ? 0 : 1;
        $gif_proyecto = $gif_proyecto == '' ? 0 : 1;
        $bmp_proyecto = $bmp_proyecto == '' ? 0 : 1;
        $word_proyecto = $word_proyecto == '' ? 0 : 1;
        $otro_proyecto = $otro_proyecto == '' ? 0 : 1;

        //Escribimos en las tablas
        $save = App\proyecto::where('proyecto_id', $proyecto_actual)->first();

        $save->proyecto_nombre = $nombre_proyecto;
        $save->proyecto_prioridad = $prioridad_proyecto;
        $save->proyecto_fecha_inicio = $fecha_inicio_proyecto;
        $save->proyecto_fecha_fin = $fecha_fin_proyecto;
        $save->proyecto_duracion = $duracion_proyecto;
        $save->cliente_id = $cliente_id;
        $save->proyecto_comentario = $comentario_proyecto;

        $save->proyecto_tipo_medio = $tipo_medio_proyecto;
        $save->proyecto_validacion = $validacion_proyecto;
        $save->proyecto_multipagina = $multipagina_proyecto;
        $save->proyecto_ocr = $ocr_proyecto;

        // $save->proyecto_fedatario_asistente = $proyecto_fedatario_asistente;

        $save->push();

        $formato = App\proyecto_formato::where("proyecto_id", $proyecto_actual)->delete();

        $formato = new App\proyecto_formato;

        $formato->proyecto_id = $save->proyecto_id;
        $formato->proyecto_jpg = $jpg_proyecto;
        $formato->proyecto_png = $png_proyecto;
        $formato->proyecto_tiff = $tiff_proyecto;
        $formato->proyecto_tif = $tiff_proyecto;
        $formato->proyecto_gif = $gif_proyecto;
        $formato->proyecto_pdf = $pdf_proyecto;
        $formato->proyecto_bmp = $bmp_proyecto;
        $formato->proyecto_docx = $word_proyecto;
        $formato->proyecto_otro = $otro_proyecto;

        $formato->save();


        //Si no se ejecuto los query devolvemos error
        if (!$save) {

            App::abort(500, 'Error');

        }

        return response('ok', 200);

    }

    public function proyecto_estado(Request $request)
    {

        $proyecto_actual = request('proyecto_actual');
        $estado = request('estado');


        $ins_proyecto = new App\proyecto();

        $validador = $ins_proyecto->validador_proyecto_recepcion($proyecto_actual);

        // return $proyecto_actual;

        if ($validador < 1) {

            $save = App\proyecto::where('proyecto_id', $proyecto_actual)
                ->update(['proyecto_estado' => $estado]);

            //Si no se ejecuto los query devolvemos error
            if (!$save) {

                App::abort(500, 'Error');

            }

            return response('ok', 200);

        } else {
            return $this->crear_objeto("Error", "Este proyecto esta asignado a una recepcion");
        }

    }

    public function plantilla_validacion_proyecto()
    {

        $proyecto_id = request("proyecto_id");

        $proyecto = App\proyecto::
        where("proyecto_id", $proyecto_id)
            ->select(
                "proyecto_id",
                "proyecto_nombre",
                "plantilla_id",
                "proyecto_validacion"
            )
            ->first();

        return $proyecto;


    }

    public function asignar_plantilla()
    {

        $plantilla_id = request("plantilla_id");
        $proyecto_actual = request("proyecto_actual");

        $proyecto = App\proyecto::where("proyecto_id", $proyecto_actual)
            ->update(["plantilla_id" => $plantilla_id]);

        if ($proyecto) {

            return $this->crear_objeto("ok", "Plantilla Asignada Correctamente");

        } else {

            return $this->crear_objeto("Error", "Hubo un error en la asignacion, pruebe mas tarde");

        }


    }

    public function listar_proyectos()
    {

        $cliente_id = request("cliente_id");

        $proyectos = App\proyecto::where("cliente_id", $cliente_id)
            ->where("proyecto_estado", 1)
            ->select("proyecto_id", "proyecto_nombre", "cliente_id")
            ->get();

        if (count($proyectos) > 0) {

            return $this->crear_objeto("ok", $proyectos);

        } else {

            return $this->crear_objeto("error", "No se encontraron proyectos para este cliente");

        }

    }

    public function listar_proyectos_global()
    {

        $valores_busqueda = request("term");

        $respuesta = App\proyecto::where("proyecto_estado", 1)
            ->where("proyecto_nombre","ILIKE","%".$valores_busqueda."%")
            ->select("proyecto_id as id", "proyecto_nombre as value")
            ->take(30)
            ->get();

        $array_preparado = [
            "results" => $respuesta
        ];

        return $array_preparado;

    }

    public function crear_directorio($nombre_proyecto)
    {

        $existe = Storage::disk('local')->exists('documentos/' . $nombre_proyecto);

        if ($existe) {
            return "existe";

        } else {

            Storage::disk('local')->makeDirectory('documentos/' . $nombre_proyecto);

            return 'ok';
        }

    }

    public function listar_archivos_cargados_proyecto()
    {

        $proyecto_actual = request("proyecto_actual");

        $plantilla = App\proyecto::
                            where("proyecto_id",$proyecto_actual)
                            ->select(
                                "proyecto_id",
                                "proyecto_ultimo_archivo_pantilla"
                            )
                            ->first();


        return $plantilla;


    }

    public function select2_proyectos()
    {

        $proyectos = App\proyecto::where("proyecto_estado", 1)
            ->select("proyecto_id", "proyecto_nombre", "cliente_id")
            ->orderBy("proyecto_nombre")
            ->get();

        if (count($proyectos) > 0) {

            return $this->crear_objeto("ok", $proyectos);

        } else {

            return $this->crear_objeto("error", "No se encontraron proyectos para este cliente");

        }

    }

    public function select2_usuarios()
    {

        $usuarios = App\User::join('persona as p','p.usuario_id','usuario.usuario_id')
            ->select("usuario.usuario_id", "p.persona_nombre")
            ->orderBy("persona_nombre")
            ->get();

        if (count($usuarios) > 0) {

            return $this->crear_objeto("ok", $usuarios);

        } else {

            return $this->crear_objeto("error", "No se encontraron proyectos para este cliente");

        }

    }

}
