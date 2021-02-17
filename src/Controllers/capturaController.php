<?php

namespace Fedatario\Controllers;

use DemeterChain\A;
use Illuminate\Http\Request;
use Carbon\Carbon;
use View;
use Response;
use App;
use App\documento;
use App\captura;
use App\control_calidad;
use App\recepcion;
use App\proyecto;
use App\adetalle;
use App\files;
use App\log;
use App\indizacion;
use App\ocr;
use App\fedatario;
use App\fedatario_firmar;
use App\proyecto_captura_flujo;
use App\Http\Controllers\respuesta;
use App\Http\Controllers\incidenciaController;
use App\Http\Controllers\tipo_calibradorController;
use App\Http\Controllers\capturafileController;
use App\Http\Controllers\OCRController;
use DB;
use DateTime;

Trait capturaController
{

    public function index()
    {

        //Instancia Documento
        $ins_documento = new documento();
        //Instancia Incidencia
        $ins_incidencia = new incidenciaController();
        $ins_tipo_calibrador = new tipo_calibradorController();

        $lista_documentos = $ins_documento->listar_documento();
        $incidencia = $ins_incidencia->listar_incidencia();
        $tipo_calibrador = $ins_tipo_calibrador->listar_tipo_calibrador();

        return view::make('captura.index.content')
            ->with("lotes", $lista_documentos)
            ->with("incidencia", $incidencia)
            ->with("tipo_calibrador", $tipo_calibrador);

    }

    public function retornar_url()
    {

        $documento_id = request("documento_id");
        //Instancia Documento
        $ins_documento = new documento();
        return $lista_url_imagenes = $ins_documento->listar_url($documento_id);

    }

    public function listar_recepcion_captura()
    {

        $recepcion_id = request("recepcion_id");
        $captura_estado = request("captura_estado");
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        if ($captura_estado == 1) {

            $captura_validacion = new captura();

            $acta = $captura_validacion::where("recepcion_id", $recepcion_id)
                ->where("captura_estado", 2)
                ->whereBetween("created_at", [$today, $tomorrow])
                ->count();

            $tarjeta = $captura_validacion::where("recepcion_id", $recepcion_id)
                ->where("captura_estado", 3)
                ->whereBetween("created_at", [$today, $tomorrow])
                ->count();

            $obj_captura = $captura_validacion::where("recepcion_id", $recepcion_id)
                ->where("captura_estado", 3)
                ->whereBetween("created_at", [$today, $tomorrow])
                ->first();

            $tc_id = $obj_captura['tc_id'];


            if ($tc_id == null || $tc_id == -1) {

                return $this->crear_objeto("Error", "Seleccione medio en el registro de calibradoras.");

            } else {
                if (!($acta > 0 && $tarjeta > 0)) {

                    return $this->crear_objeto("Error", "Compruebe si cuenta con acta y tarjeta validadora.");

                }
            }


        }

        if ($captura_estado == 2) {

            $captura_validacion = new captura();

            $tarjeta = $captura_validacion::where("recepcion_id", $recepcion_id)
                ->where("captura_estado", 3)
                ->whereBetween("created_at", [$today, $tomorrow])
                ->count();
            //return $recepcion_id;
            $obj_captura = $captura_validacion::where("recepcion_id", $recepcion_id)
                ->where("captura_estado", 3)
                ->whereBetween("created_at", [$today, $tomorrow])
                ->first();

            $tc_id = $obj_captura['tc_id'];


            //  return $tarjeta;
            if ($tc_id == null || $tc_id == -1) {

                return $this->crear_objeto("Error", "Seleccione medio en el registro de calibradoras.");

            } else {
                if (!($tarjeta > 0)) {

                    return $this->crear_objeto("Error", "Compruebe si cuenta con tarjeta calibradora.");

                }
            }
        }

        $recepcion_instancia = new recepcion();

        $data = DB::select(
            "select * from recepcion a
        left join captura b on a.recepcion_id = b.recepcion_id
        left join documento c on c.captura_id = b.captura_id
        left join adetalle d on d.adetalle_id = c.adetalle_id
        where a.recepcion_id = :recepcion_id
        and b.captura_estado = :captura_estado
        and (b.captura_estado_glb = 'cap'
        or b.captura_estado_glb= 'ind'
        or b.captura_estado_glb ='cc')
        order by
        case when b.captura_orden is null then b.captura_id else b.captura_orden end;"
            , ['captura_estado' => $captura_estado, 'recepcion_id' => $recepcion_id]);

        return $data;

    }

    public static function validar_listar_captura($recepcion_id, $captura_estado)
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        if ($captura_estado == 1) {

            $captura_validacion = new captura();

            $acta = $captura_validacion::where("recepcion_id", $recepcion_id)
                ->where("captura_estado", 2)
                ->whereBetween("created_at", [$today, $tomorrow])
                ->count();

            $tarjeta = $captura_validacion::where("recepcion_id", $recepcion_id)
                ->where("captura_estado", 3)
                ->whereBetween("created_at", [$today, $tomorrow])
                ->count();

            $obj_captura = $captura_validacion::where("recepcion_id", $recepcion_id)
                ->where("captura_estado", 3)
                ->whereBetween("created_at", [$today, $tomorrow])
                ->first();

            $tc_id = $obj_captura['tc_id'];

            if ($tc_id == null || $tc_id == -1) {
                return respuesta::error("Seleccione medio en el registro de calibradoras.", 500);
            } else {
                if (!($acta > 0 && $tarjeta > 0)) {
                    return respuesta::error("Compruebe si cuenta con acta y tarjeta validadora.", 500);
                }
            }
        }

        if ($captura_estado == 2) {

            $captura_validacion = new captura();
            $tarjeta = $captura_validacion::where("recepcion_id", $recepcion_id)
                ->where("captura_estado", 3)
                ->whereBetween("created_at", [$today, $tomorrow])
                ->count();
            //return $recepcion_id;
            $obj_captura = $captura_validacion::where("recepcion_id", $recepcion_id)
                ->where("captura_estado", 3)
                ->whereBetween("created_at", [$today, $tomorrow])
                ->first();

            $tc_id = $obj_captura['tc_id'];

            //  return $tarjeta;
            if ($tc_id == null || $tc_id == -1) {
                return respuesta::error("Seleccione medio en el registro de calibradoras.", 500);
            } else {
                if (!($tarjeta > 0)) {
                    return respuesta::error("Compruebe si cuenta con tarjeta calibradora.", 500);
                }
            }
        }

        return respuesta::ok();
    }

    public function ver_inicial_captura()
    {

        $recepcion_id = request("recepcion_actual");

        $recepcion = recepcion::where("recepcion_id", $recepcion_id)
            ->first();

        $proyectos = proyecto::where("cliente_id", $recepcion->cliente_id)
            ->first();

        $documentos = documento::where("recepcion_id", $recepcion_id)
            ->get();

        $recepcion['documentos'] = $documentos;
        $recepcion['proyectos'] = $proyectos;

        //prueba para captura
        if ($recepcion['recepcion_tipo'] === "s") {
            session()->put('recepcion_tipo', "s");
        } else {
            session()->put('recepcion_tipo', "m");
        }

        return $recepcion;

    }

    public function guardar_captura()
    {

        $captura = request('elementos');
        $recepcion_tipo = request('tipo');
        $recepcion_id = request('recepcion_id');
        // $obj_captura = json_decode($captura, true);

        $inst_recepcion = new recepcion();
        $recepcion_valores = $inst_recepcion->where("recepcion_id", $recepcion_id)->first();


        $inst_captura = new captura();
        $array_limpiar = [];
        $array = [];


        if ($recepcion_tipo === "m") {

            foreach ($captura as $key => $value) {

                $documento_valores = new documento();
                $valor_documento = $documento_valores->where("documento_id", $value['documento_id'])->first();


                if ($value['documento_id'] === null || $recepcion_tipo === "m") {

                    $array_limpiar[] = $value['documento_id'];

                    $now = date('Y-m-d H:i:s');

                    $objeto = [
                        "recepcion_id" => $recepcion_id,
                        "proyecto_id" => $recepcion_valores['proyecto_id'],
                        "cliente_id" => $recepcion_valores['cliente_id'],
                        "adetalle_id" => $value['adetalle_id'],
                        "documento_nombre" => $value['documento_nombre'],
                        "captura_estado" => 1,
                        "captura_estado_glb" => "cap",
                        "created_at" => $now,
                        "updated_at" => $now];

                    $array[] = $objeto;

                }
            }
            $contador_array = count($array);

            if ($contador_array !== 0) {
                $captura = new captura();
                $captura->where('recepcion_id', $recepcion_id)->delete();
                $documento = new documento();
                $documento->where('recepcion_id', $recepcion_id)->delete();

                $captura->crear_lista_captura($array, false);

                if ($captura) {

                    return $this->crear_objeto("ok", "Capturas registradas.");

                } else {

                    return $this->crear_objeto("Error", "Hubo un problema con la inserción de datos, inténtelo más tarde.");

                }
            } else {
                return $this->crear_objeto("ok", "Capturas actualizadas.");
            }

        } else {

            foreach ($captura as $key => $value) {

                $documento_valores = new documento();
                $documento_valores->editar_documento_adetalle($value['documento_id'], $value['adetalle_id']);
            }

        }

        $tipo = 'ok';
        $mensaje = 'Registro de plantilla correcto';

        return Controller::crear_objeto($tipo, $mensaje);

    }

    public function listar_captura()
    {

        $recepcion_id = request('recepcion_actual');
        $inst_captura = new captura();
        $captura_lista = $inst_captura->listar_captura($recepcion_id);

        return $captura_lista;

    }

    public function borrar_archivo_captura(Request $request)
    {
        $idadetalle = $request->input('adetalle_id');

        return self::borrar_archivo_captura_impl($idadetalle);

    }

    public function borrar_archivo_captura_impl($idadetalle)
    {
        // $idadetalle = $request->input('adetalle_id');

        $ida = $idadetalle;

        $documento_instancia = new documento();
        $documento_valor = $documento_instancia->where('adetalle_id', $ida)->first();
        $validador = $documento_instancia->join("recepcion as re", "re.recepcion_id", "documento.recepcion_id")->where("adetalle_id", $ida)->first();
        $captura_valor = captura::where('captura_id', $documento_valor['captura_id'])->first();

        adetalle::where('adetalle_id', $ida)->delete();

        $f = new files();

        if ($validador['recepcion_tipo'] === "m") {
            documento::where('documento_id', $documento_valor['documento_id'])->delete();
            captura::where('captura_id', $documento_valor['captura_id'])->delete();
            $f->borrar_archivo($captura_valor["captura_file_id"]);
        } elseif ($captura_valor['captura_estado'] != 1) {
            documento::where('documento_id', $documento_valor['documento_id'])->delete();
            captura::where('captura_id', $documento_valor['captura_id'])->delete();
            $f->borrar_archivo($captura_valor["captura_file_id"]);
        }


        return 'ok';

    }

    public function reemplazar_captura_multiple(Request $request, $input_file_name = "file", $fileTiff = null)
    {

        $idadetalle = $request->input('adetalle_id');
        //1.-capturo la fecha de creacion del archivo a reemplazar
        $documento = DB::table('documento')->where('adetalle_id', '=', $idadetalle)
            ->first();
        $captura = DB::table('captura')->where('captura_id', '=', $documento->captura_id)
            ->first();
        $captura_orden = $captura->captura_orden;

        //2.-borro el archivo a reemplazar
        $resultado_borrar = self::borrar_archivo_captura_impl($idadetalle);

        //3.-inserto el nuevo archivo
        $capturaFileController = new capturafileController();
        if (!empty($fileTiff)) {
            $adetalle_id_nuevo = $capturaFileController->registrar_archivo_multiple($request, "", $fileTiff);
        } else {
            $adetalle_id_nuevo = $capturaFileController->registrar_archivo_multiple($request, $input_file_name);
        }

        //4.-conseguimos el captura_id para actualizar la fecha de creacion
        $documento_nuevo = DB::table('documento')->where('adetalle_id', '=', $adetalle_id_nuevo)
            ->first();
        //5.-actualizamos la fecha de creación
        //en captura orden inserto el orden anterior y si no tuviese, le inserto el id captura del anterior
        $orden = ($captura_orden != null ? $captura_orden : $captura->captura_id);
        DB::table('captura')
            ->where('captura_id', '=', $documento_nuevo->captura_id)
            ->update(
                ['captura_orden' => $orden]
            );

        return $adetalle_id_nuevo;

    }

    /**
     * Realiza un artificio para el caso de capturas de recepcion múltiple y obtener el id de la captura
     * @param Request $request Informacion recibida a través del botón "finalizar-registro" del "modal_mantenimiento.blade.php"
     * @author El juaquer Bueno (ง •̀_•́)ง
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function finalizar_registro_incidencia_captura()
    {
        $usuario_creador = session("usuario_id");
        $recepcion_tipo = request("recepcion_tipo");
        $tipo_asociado = request("tipo_asociado");
        $captura_id = request("captura_id");

        // $usuario_creador = "1";//session("usuario_id");
        // $recepcion_tipo = "m";//request("recepcion_tipo");
        // $tipo_asociado = "cap";//request("tipo_asociado");
        // $captura_id = "13";//request("captura_id");
        //En caso de que captura sea múltiple obtiene el id de captura a través del adetalle_id
        if ($recepcion_tipo === "m") {
            $is_captura = (new adetalle())
                ->join("documento as doc", "doc.adetalle_id", "adetalle.adetalle_id")
                ->where("doc.adetalle_id", $captura_id)
                ->select("documento_id")->first();

            $captura_id = $is_captura["documento_id"];

        }

        $id_asociado = $captura_id;

        //Enviado a la funcion global de incidencia
        (new incidenciaController())->finalizar_registro_incidencia_glb($id_asociado, $usuario_creador, $tipo_asociado,
            //funcion que se ejecutará dentro de finalizar_registro_incidencia_glb pero al final
            function ($id_asociado, $usuario_creador, $count, $request) {
                $cap_est_glb_0 = 'rep';
                $cap_est_glb_1 = 'ind';

                if ($count > 0) {
                    //grabamos log de captura
                    $log = new log();
                    $log->create_log_ez(
                        $id_asociado,//$log_captura_id  ,
                        $id_asociado,//$log_id_asociado  ,
                        1,//$log_modulo_step_id  ,
                        'captura',//$log_tabla_asociada  ,
                        'CAP-FIN',//$log_proceso  ,
                        'Finalizar Registro de Captura - Reproceso',//$log_descripcion  ,
                        '',//$log_comentario  ,
                        null//$log_archivo_id
                    );
                    //mandar captura a estado reproceso id_asociado = captura_id
                    $this->estado_captura_glb($id_asociado, $cap_est_glb_0);
                    return 'ok';
                } else {
                    //para flujo dinámico cambiar aquí

                    $this->registrar_avance_flujo_from_captura_to($id_asociado, $usuario_creador);

                    //grabamos log de captura
                    $log = new log();
                    $log->create_log_ez(
                        $id_asociado,//$log_captura_id  ,
                        $id_asociado,//$log_id_asociado  ,
                        1,//$log_modulo_step_id  ,
                        'captura',//$log_tabla_asociada  ,
                        'CAP-FIN',//$log_proceso  ,
                        'Finalizar Registro de Captura',//$log_descripcion  ,
                        '',//$log_comentario  ,
                        null//$log_archivo_id
                    );
                    //mandar captura a indizacion id_asociado = captura_id
                    $this->estado_captura_glb($id_asociado, $cap_est_glb_1);


                    //Obtenemos la ruta del documento y el proyecto id para validar
                    $obj_query_ruta= DB::select("
                    select adetalle_url,documento_id,proyecto_id from documento d
                    join adetalle ad on d.adetalle_id = ad.adetalle_id
                        where documento_id = :documento_id;
                    ", ["documento_id"=>$id_asociado]);

                    $proyecto_id = $obj_query_ruta[0]->proyecto_id;

                    $obj_query_validador= DB::select("
                    select proyecto_ocr from proyecto
                        where proyecto_id = :proyecto_id;
                    ", ["proyecto_id"=>$proyecto_id]);

                    $proyecto_ocr = $obj_query_validador[0]->proyecto_ocr;

                    if($proyecto_ocr == '1'){
                        $path = $obj_query_ruta[0]->adetalle_url;

                        //Concatenamos la ruta completa
                        $ruta =storage_path() . '/app/'.$path ;
                        //Enviamos la ruta al WS de OCR
                        $is_OCR = new OCRController();
                        $ws_OCR = $is_OCR->path_file_ws_ocr($ruta);
                        
                        if(!$ws_OCR["estado"]){
                            $log->create_log_ez(
                                $id_asociado,//$log_captura_id  ,
                                $id_asociado,//$log_id_asociado  ,
                                7,//$log_modulo_step_id  ,
                                'ocr',//$log_tabla_asociada  ,
                                'OCR-ERROR-CURL',//$log_proceso  ,
                                $ws_OCR["mensaje"],//$log_descripcion  ,
                                '',//$log_comentario  ,
                                null//$log_archivo_id
                            );
                            return dd($ws_OCR);
                        }

                        $ws_OCR = $ws_OCR["payload"];
                        if(!$ws_OCR["estado"]){
                            $log->create_log_ez(
                                $id_asociado,//$log_captura_id  ,
                                $id_asociado,//$log_id_asociado  ,
                                7,//$log_modulo_step_id  ,
                                'ocr',//$log_tabla_asociada  ,
                                'OCR-ERROR',//$log_proceso  ,
                                $ws_OCR["mensaje"],//$log_descripcion  ,
                                '',//$log_comentario  ,
                                null//$log_archivo_id
                            );
                            return dd($ws_OCR);
                        }

                        $log->create_log_ez(
                            $id_asociado,//$log_captura_id  ,
                            $id_asociado,//$log_id_asociado  ,
                            7,//$log_modulo_step_id  ,
                            'ocr',//$log_tabla_asociada  ,
                            'OCR-FIN',//$log_proceso  ,
                            'Archivo con proceso de OCR - captura',//$log_descripcion  ,
                            '',//$log_comentario  ,
                            null//$log_archivo_id
                        );
                        //Guardar los registros por página en la base de datos
                        //$validador = self::guardar_paginas($ws_OCR,$id_asociado,$usuario_creador);
                        return dd($ws_OCR);
                    }

                    return 'ok';

                }

            });

        return 'ok';
    }



    public function guardar_paginas($ws_OCR,$id_asociado,$usuario_creador)
    {

        $texto_glb = '';
        $arr_pagina = [];
        $now = date('Y-m-d H:i:s');

        if (touch($ws_OCR)) {

            $archivoID = fopen($ws_OCR, "r");

            while (!feof($archivoID)) {
                //$linea = fgets($archivoID, 1024);
                $linea = fgets($archivoID);
                $break_page = chr(12);
                $texto_glb = $texto_glb . $linea;
                $arr_pagina = explode($break_page, $texto_glb);
            }
            fclose($archivoID);
        }

        foreach ($arr_pagina as $key => $value) {

            $imagen_id = 1;
            $captura_id = $id_asociado;
            $ocr_contenido = $value;
            $ocr_estado = 1;
            $ocr_usuario_id = $usuario_creador;
            $ocr_fecha = $now;
            $ocr_pagina = $key + 1;
            $ocr_total_paginas = count($arr_pagina);

            $is_OCR_model = new ocr();
            $OCR_save = $is_OCR_model->crear_ocr($imagen_id, $captura_id,
                $ocr_contenido, $ocr_estado,
                $ocr_usuario_id, $ocr_fecha,
                $ocr_pagina, $ocr_total_paginas
            );

        }

        return 'ok';

    }

    /**
     * Consulta el siguiente módulo donde debe continuar la captura y continua su respectivo registro, actualizando también las tablas respectivas
     * @param integer $captura_id código de captura
     * @param integer $usuario_creador usuario logeado en el momento
     * @return void no retorna valor
     * @author Christian Fernando Condori Soto
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    function registrar_avance_flujo_from_captura_to($captura_id, $usuario_creador)
    {
        $pro_cap_flujo = (new proyecto_captura_flujo())->consultar_orden($captura_id);
        $modulo_step_id = 0;
        if (count($pro_cap_flujo) > 0) {
            $modulo_step_id = $pro_cap_flujo[0]->modulo_step_id;
            $id_generado_nuevo_modulo = 0;
            //dependiendo del módulo generamos los registros correspondientes
            switch ($modulo_step_id) {
                case 1:
                    //pasa captura


                    break;
                case 2:
                    //pasa a indizacion

                    //crear registro inicial de indizacion
                    $id_generado_nuevo_modulo = (new indizacion())->crear_indizacion_inicial_from_captura($captura_id,$usuario_creador);
                    break;
                case 3:
                    //pasa a control calidad

                    //crear registro inicial de control de calidad
                    $id_generado_nuevo_modulo = (new control_calidad())->crear_control_calidad_inicial_from_captura($captura_id, $usuario_creador);
                    // (new App\indizacion())->estado_indizacion_glb($id_asociado, $indizacion_estado_finalizado);
                    // $this->estado_captura_glb($id_asociado, $cap_est_glb_1);

                    break;
                case 4:
                    //pasa a fedatario revisar
                    $id_generado_nuevo_modulo = (new fedatario())->crear_fedatario_inicial_from_captura($captura_id, $usuario_creador);
                    break;
                case 5:
                    //pasa a fedatario firmar
                    $id_generado_nuevo_modulo = (new fedatario_firmar())->crear_fedatario_firmar_inicial_from_captura($captura_id, $usuario_creador);
                    break;

                default:

                    break;
            }
            //actualizamos captura y proyecto_captura_flujo
            $this->actualizar_captura_pcf_nuevo_modulo($captura_id, $modulo_step_id, $id_generado_nuevo_modulo);


        }


    }

    function chuchona($gmd_id){
        //Obtenemos la ruta del documento y el proyecto id para validar


        $obj_query_ruta= DB::select("
        select
            row_number() over(order by o.documento_id) as nro,
            ad.adetalle_url
            ,o.captura_id as documento_id
            from documento o
            join adetalle ad on ad.adetalle_id = o.adetalle_id
            where o.captura_id = :captura_id
            order by o.documento_id;
        ",["captura_id"=>$gmd_id]);

        /*
           $obj_query_ruta= DB::select("
           select
           row_number() over(order by a.gmdc_id) as nro,
           adetalle_url,documento_id
           from generacion_medio_detalle_captura a
           left join captura c on c.captura_id = a.captura_id
           left join documento d on d.captura_id = c.captura_id
           left join adetalle ad on ad.adetalle_id = d.adetalle_id
           where  c.captura_estado = 1
           and
           a.captura_id =:captura_id
           order by a.gmdc_id
           ", ["captura_id"=>$gmd_id]);

           $obj_query_ruta= DB::select("
           select
           adetalle_url,documento_id
           from documento d
           left join adetalle ad on ad.adetalle_id = d.adetalle_id
           where
           d.captura_id = :captura_id
           ", ["captura_id"=>$gmd_id]);
       */
        $cont = 0;
        foreach ($obj_query_ruta as $key => $fila) {
            $path="";
            $path = $fila->adetalle_url;




            //Concatenamos la ruta completa
            $ruta =storage_path() . '/app/'.$path ;
            var_dump($cont."      ".$path);

            //echo $cont." ".$path."\n";


            //Enviamos la ruta al WS de OCR
            $is_OCR = new OCRController();
            $ws_OCR = $is_OCR->path_file_ws_ocr($ruta);

            var_dump($cont.".......OK");

            $cont++;

        }


        return 'ok2';


    }



    public function finalizar_registro_incidencia_captura_solo_ocr()
    {
        $usuario_creador = session("usuario_id");
        $recepcion_tipo = request("recepcion_tipo");
        $tipo_asociado = request("tipo_asociado");
        $captura_id = request("captura_id");

        return self::chuchona($captura_id);
    }

    public function prueba_ocr_docs(){

        $path = storage_path() . "/app/documentos/";
        $path_documentos = "documentos/";


        $ruta_inicio = $path;
        $ruta_destino = env('FOLDER_GENERADO');
        $ruta_destino = env('FOLDER_GENERADO');

        $res_gm = DB::select(
            DB::raw("
            with
            datos_input as (
                select
                :path::varchar(5000) as path
                ,:path_documento::varchar(5000) as path_documento
            )
            ,datos as (
                select
                    replace(ad_ca.adetalle_url,a.path_documento,'') as ruta
                    ,ad_ca.adetalle_peso as peso
                    ,0
                    --ruta destino
                    ,'PROCESADOS'||'/'||ad_ca.adetalle_nombre as ruta_destino
                    --,c.*
                    ,row_number() over(order by o.captura_id) as correlativo
                    ,count(*) over() as total
                    --from ocr4

                --select *


                    from ocr4 o
                    --left join generacion_medio_detalle b on a.gmd_id = b.gmd_id
                    --left join generacion_medio_detalle_captura c on b.gmd_id = c.gmd_id
                    cross join datos_input a
                    --para archivos calibradoras y aperturas

                    left join adetalle ad_ca on ad_ca.adetalle_id = o.adetalle_id
                    where o.adetalle_id is not null
                order by o.captura_id
            )
            --select * from datos;
            ,datos_recursivos as (

                select
                    a.ruta||'****'||a.ruta_destino as ruta
                    ,0 as peso_acumulado
                    ,a.correlativo
                    ,a.peso
                from datos a
            )
            select ruta
            from datos_recursivos
            order by correlativo;
            "),
        ["path" => $path,"path_documento" => $path_documentos]);

        $fp = fopen($ruta_inicio . "pruebaOCR2.txt", 'w');



        foreach ($res_gm as $res_gm_value) {

            fwrite($fp, $res_gm_value->ruta . "\n");

        }

        fclose($fp);

        //Aquí se consume el WS de juanchel 😎
        $url_put = "http://localhost:3000/mover";

        //Objeto que se enviará para el Update
        $data_put = [
            "id" => 1,
            "ruta_inicio" => $ruta_inicio,
            "ruta_destino" => $ruta_destino,
            "ruta_lista" => $ruta_inicio . "pruebaOCR2.txt",
        ];


        //Cambiando formato de objeto
        $var = json_encode($data_put, true);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => "3000",
            CURLOPT_URL => $url_put,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 3000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $var,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Postman-Token:  f6383e44-afc1-47c7-a62d-cbe4820c6fb7",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);

        $error = json_decode($response, true);
        $contador_error = 0;
        if ($error['estado'] == false) {
            $contador_error++;
        }


        $err = curl_error($curl);

        curl_close($curl);
        return $response;
        return $res_gm;

    }

}
