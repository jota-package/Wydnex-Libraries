<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class fedatario_firmarController extends Controller
{
    public function __construct()
    {

        $this->middleware('auth');
        parent::__construct();

    }

    public function index()
    {

        return view::make('fedatario_firmar.index.content');

    }

    public function arbol_fedatario()
    {

        $data = (new App\fedatario_firmar())->arbol_fedatario_firmar();

        $array_proyecto = array();
        $array_recepcion = array();
        $array_captura = array();
        $recepcion_old = "0";
        $recepcion_nombre_old = "";
        $recepcion_tipo_old = "";
        $proyecto_old = "0";
        $proyecto_nombre_old = "";
        $prefijo_id_captura = "captura_";
        foreach ($data as $fila) {
            if ($fila->recepcion_id != $recepcion_old && $recepcion_old != "0") {
                $array_recepcion[] = [
                    "id_recepcion" => $recepcion_old,//$fila->recepcion_id,
                    "recepcion_tipo" => $recepcion_tipo_old,//$fila->recepcion_id,
                    "text" => $recepcion_nombre_old,
//                    "children" => $array_captura
                ];
                $array_captura = array();

                if ($fila->proyecto_id != $proyecto_old && $proyecto_old != "0") {
                    $array_proyecto[] = [
                        "id_proyecto" => $proyecto_old,//$fila->proyecto_id,
                        "text" => $proyecto_nombre_old,
                        "children" => $array_recepcion
                    ];
                    $array_recepcion = array();
                }
            }
           /* $array_captura[] = [
                "id" => $prefijo_id_captura . $fila->captura_id,
                "text" => $fila->documento_nombre,
                "id_captura" => $fila->captura_id,
                "indizacion_id" => $fila->indizacion_id,
                "cc_id" => $fila->cc_id,
                "id_documento" => $fila->documento_id,
                "adetalle_id" => $fila->adetalle_id,
                "recepcion_tipo" => $fila->recepcion_tipo,
                "cliente_id" => $fila->cliente_id,
                "usuario_creador" => $fila->usuario_creador,
                "proyecto_id" => $fila->proyecto_id,
                "recepcion_id" => $fila->recepcion_id
            ];*/
            $recepcion_old = $fila->recepcion_id;
            $recepcion_tipo_old = $fila->recepcion_tipo;
            $recepcion_nombre_old = $fila->recepcion_nombre;

            $proyecto_old = $fila->proyecto_id;
            $proyecto_nombre_old = $fila->proyecto_nombre;
        }
        if($recepcion_old!="0" && $proyecto_old!="0"){
            $array_recepcion[] = [
                "id_recepcion" => $recepcion_old,
                "recepcion_tipo" => $recepcion_tipo_old,
                "text" => $recepcion_nombre_old,
                //"children" => $array_captura
            ];
            $array_proyecto[] = [
                "id_proyecto" => $proyecto_old,
                "text" => $proyecto_nombre_old,
                "children" => $array_recepcion
            ];
        }

        return $array_proyecto;


    }

    public function listar_documentos_fedatario()
    {

        $recepcion_id = request("recepcion_id");
        $captura_estado = request("captura_estado");

        $recepcion_instancia = new App\recepcion();

        $data = DB::select(
            "
            with documento_imagen AS (
                select
                     c.documento_id,
                     max(cast(imagen_pagina as integer)) as total_pag,
                     c.documento_estado,
                     c.documento_nombre,
                     c.captura_id,
                     c.adetalle_id
                from documento c
                join imagen i on c.documento_id = i.documento_id
                group by c.documento_id),
            esquery AS (
              select d.adetalle_id,
                  d.adetalle_nombre,
                  d.adetalle_peso,
                  d.adetalle_url,
                  b.captura_estado,
                  b.captura_estado_glb,
                  b.captura_file_id,
                  b.captura_id,
                  b.cliente_id,
                  c.documento_estado,
                  c.documento_id,
                  c.documento_nombre,
                  c.total_pag,
                  e.fedatario_firmar_estado,
                  e.fedatario_firmar_id,
                  e.fedatario_id,
                  b.flujo_id_actual,
                  a.proyecto_id,
                  a.recepcion_id,
                  a.recepcion_tipo
              from recepcion a
              left join captura b on a.recepcion_id = b.recepcion_id
              left join documento_imagen c on b.captura_id = c.captura_id
              left join adetalle d on d.adetalle_id = c.adetalle_id
              join fedatario_firmar e on e.captura_id = b.captura_id
              where a.recepcion_id = :recepcion_id
                and b.captura_estado = :captura_estado
                and b.captura_estado_glb = 'fed_fir'
                order by
                case when b.captura_orden is null then b.captura_id else b.captura_orden end
              )
        select *
        from esquery;"
            , ['captura_estado' => $captura_estado, 'recepcion_id' => $recepcion_id]);

        return $data;

    }

    public function validador_archivos_firmados(){

        //Variables necesarias
        $recepcion_id = 4;
        $flag_validador_primercaso = '';
        $flag_validador_segundocaso = '';
        $flag_validador_tercercaso = '';
        $flag_validador_general = '';

        //Array contenedores
        $archivos_firmados = [];

        //Query de la lista de archivos
        //Corregir query devolver sin extensión
        $archivos_guardados = DB::select(
            "select documento_nombre from recepcion a
                left join captura b on a.recepcion_id = b.recepcion_id
                left join documento c on c.captura_id = b.captura_id
                left join adetalle d on d.adetalle_id = c.adetalle_id
                where a.recepcion_id = :recepcion_id
                  and b.captura_estado = 1
                  and ( b.captura_estado_glb ='fed')"
            , ['recepcion_id' => $recepcion_id]);

        //Obtener el nombre de los archivos dentro de un array
        $files = File::Files(storage_path() . "/app/documentos/proyecto 001/Simple/");

        //Guardar archivos del directorio
        foreach ($files as $file){
            //Array de archivos dentro de un directorio
            $archivos_firmados[] = $file->getFilename();
        }

        //Primer caso cuando los nombres de los archivos son iguales
        $flag_validador_primercaso = self::validar_primer_caso($archivos_firmados,$archivos_guardados);

        //Segundo caso cuando los nombres de los archivos son .zip .esig
        $flag_validador_segundocaso = self::validar_segundo_caso($archivos_firmados,$archivos_guardados);

        //Tercer caso cuando los nombres de los archivos tienen un archivo adicional .esig
        $flag_validador_tercercaso = self::validar_tercer_caso($archivos_firmados,$archivos_guardados);

        if($flag_validador_primercaso || $flag_validador_segundocaso || $flag_validador_tercercaso){
            $flag_validador_general = true;
        }

        if($flag_validador_general){
            //Pendiente query actualizar firmados para el semáforo
        }

        //si vuelve true al frontend entonces recargar rama para que se pinten el semáforo según corresponda
        return $flag_validador_general;

    }

    public function validar_primer_caso($archivos_firmados,$archivos_guardados){

        $archivos_firmados_match = [];
        $array_firmados_pendientes = [];

        //Guardar nombre de los archivos
        foreach ($archivos_guardados as $archivo){
            //Array de archivos dentro de un directorio
            $archivos_firmados_match[] = $archivo->documento_nombre;
        }

        // Los archivos que faltan por subir
        $array_firmados_pendientes = array_diff($archivos_firmados_match,$archivos_firmados);

        //Flag validador
        return $flag_validador_primercaso = (empty($array_firmados_pendientes))?true:false;

    }

    public function validar_segundo_caso($archivos_firmados,$archivos_guardados){

        $array_firmados_pendientes = [];
        // Los archivos que faltan por subir

        $archivos_firmados_match = [];
        $array_firmados_pendientes = [];

        //Guardar nombre de los archivos
        foreach ($archivos_guardados as $archivo){
            //Array de archivos dentro de un directorio
            $archivos_firmados_match[] = ($archivo->documento_nombre).".rar.esig";
            $archivos_firmados_match[] = ($archivo->documento_nombre).".esig";
        }


        $array_firmados_pendientes = array_diff($archivos_firmados_match,$archivos_firmados);

        //Flag validador
        return $flag_validador_segundocaso = (empty($array_firmados_pendientes))?true:false;


    }

    public function validar_tercer_caso($archivos_firmados,$archivos_guardados){

        $array_firmados_pendientes = [];
        // Los archivos que faltan por subir

        $archivos_firmados_match = [];
        $array_firmados_pendientes = [];

        //Guardar nombre de los archivos
        foreach ($archivos_guardados as $archivo){
            //Array de archivos dentro de un directorio
            $archivos_firmados_match[] = ($archivo->documento_nombre).".esig";
        }


        $array_firmados_pendientes = array_diff($archivos_firmados_match,$archivos_firmados);

        //Flag validador
        return $flag_validador_tercercaso = (empty($array_firmados_pendientes))?true:false;

    }

    public function firmar_registro_fed_fir(){

        $array_check = request("array_check");
        $extension = request("extension");
        $out  = env('FOLDER_X_FIRMAR');
        //$out =storage_path() . "/app/rayos/";

        $is_inc_ima = new App\fedatario_firmar();
        $is_inc_ima -> iniciar_fedatario_firmar($array_check,3, $out);

    }

    public function validar_firmar_fed_fir(){

        //$out =storage_path() . "/app/rayos/";

        $is_inc_ima = new App\fedatario_firmar();

        $ids = request("array_check");
        $extension  = request("extension");
        $out = env('FOLDER_FIRMADO');
        $ruta = env('FOLDER_X_FIRMAR');

        $is_inc_ima = new App\fedatario_firmar();
        return $is_inc_ima ->registrar_documentos($ids, $out, $ruta,".".$extension);


    }

}
