<?php

namespace App\Http\Controllers;

use DemeterChain\A;
use Illuminate\Http\Request;
use Carbon\Carbon;
use View;
use Response;
use App;
use DB;

use App\documento;
use App\Http\Controllers\incidenciaController;
use App\recepcion;
use App\proyecto;
use App\captura;
use App\adetalle;
use App\log;
use App\indizacion;
use App\proyecto_captura_flujo;
use App\respuesta;
use App\control_calidad;
use App\fedatario;


class indizacionController extends Controller
{

    public function __construct()
    {

        $this->middleware('auth');
        parent::__construct();

    }

    /* public function index()
     {

         $lotes = App\recepcion::
         join("cliente as c", "c.cliente_id", "recepcion.cliente_id")
             ->join("proyecto as p", "p.proyecto_id", "recepcion.proyecto_id")
             ->select(
                 "recepcion_id",
                 "recepcion_tipo",
                 "recepcion_nombre",
                 "recepcion_estado",
                 "recepcion.created_at",
                 "cliente_nombre",
                 "proyecto_nombre"

             )
             ->get();


         return view::make('captura.index.content')
             ->with("lotes", $lotes);


     }*/

    public function index()
    {

        //Instancia Documento
        $ins_documento = new documento();
        //Instancia Incidencia
        $ins_incidencia = new incidenciaController();

        $lista_documentos = $ins_documento->listar_documento();
        $incidencia = $ins_incidencia->listar_incidencia();


        return view::make('indizacion.index.content')
            ->with("lotes", $lista_documentos)
            ->with("incidencia", $incidencia);


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

        $recepcion_instancia = new recepcion();

        $recepcion_tipo = $recepcion_instancia::where("recepcion_id", $recepcion_id)->select("recepcion_tipo")->first();

        $documento_instancia = new documento();
        $contador = $documento_instancia->where("recepcion_id", $recepcion_id)->count();
        $contador2 = $documento_instancia::join("recepcion as re", "re.recepcion_id", "documento.recepcion_id")->leftjoin("adetalle as ad", "ad.adetalle_id", "documento.adetalle_id")->where("documento.recepcion_id", $recepcion_id)->count();


        //  return $contador;
        // return $contador;
        if ($recepcion_tipo['recepcion_tipo'] == "s" && $contador !== 0 && $contador2 == 0) {
            $data2 = documento::join("recepcion as re", "re.recepcion_id", "documento.recepcion_id")->where("documento.recepcion_id", $recepcion_id)->get();
        } elseif ($recepcion_tipo['recepcion_tipo'] == "s" && $contador2 !== 0) {
            $data2 = documento::join("recepcion as re", "re.recepcion_id", "documento.recepcion_id")->leftjoin("adetalle as ad", "ad.adetalle_id", "documento.adetalle_id")->where("documento.recepcion_id", $recepcion_id)->orderBy('documento_id')->get();

        } elseif ($recepcion_tipo['recepcion_tipo'] == "m" && $contador !== 0) {
            $data2 = documento::join("recepcion as re", "re.recepcion_id", "documento.recepcion_id")->where("documento.recepcion_id", $recepcion_id)->get();
        } else {
            $data2 = recepcion::where("recepcion_id", $recepcion_id)->get();
        }

        // where("recepcion_id",$recepcion_id)

        return $data2;

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

        $ida = $idadetalle;

        $documento_instancia = new documento();
        $documento_valor = $documento_instancia->where('adetalle_id', $ida)->first();
        $validador = $documento_instancia->join("recepcion as re", "re.recepcion_id", "documento.recepcion_id")->where("adetalle_id", $ida)->first();


        adetalle::where('adetalle_id', $ida)->delete();


        if ($validador['recepcion_tipo'] === "m") {
            documento::where('documento_id', $documento_valor['documento_id'])->delete();
            captura::where('captura_id', $documento_valor['captura_id'])->delete();
        }


        return 'ok';

    }


    /**
     * Finaliza
     * @param Request $request Informacion recibida a través del botón "finalizar-registro" del "modal_mantenimiento.blade.php"
     * @author El juaquer Bueno (ง •̀_•́)ง
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function finalizar_registro_incidencia_indizacion()
    {
        $usuario_creador = session("usuario_id");
        $tipo_asociado = "ind";//request("tipo_asociado");
        $indizacion_id = request("indizacion_id");
        $id_asociado = $indizacion_id;



        //Enviado a la funcion global de incidencia
        $obj = (new incidenciaController())->finalizar_registro_incidencia_glb($id_asociado, $usuario_creador, $tipo_asociado,
            function ($id_asociado, $usuario_creador, $count, $request) {

                $indizacion_estado_reproceso = "1";
                $indizacion_estado_finalizado = "2";
                $cap_est_glb_0 = 'rep';
                $cap_est_glb_1 = 'cc';
                $recepcion_id = request('recepcion_id');
                $captura_id = request('captura_id');
                $indizacion_tipo = request('indizacion_tipo');
                $proyecto_id = request('proyecto_id');
                $flag_confirmacion = request('flag_confirmacion');
                $cliente_id = request('cliente_id');
                $indizacion_id = request('indizacion_id');
                $indizacion_anterior_id = request('indizacion_anterior_id');
                $array_respuesta = request('array_respuesta');

                if ($count > 0) {
                    //grabamos log de captura
                    $log = new log();
                    $log->create_log_ez(
                                $captura_id,//$log_captura_id  ,
                                $id_asociado,//$log_id_asociado  ,
                                2,//$log_modulo_step_id  ,
                                'indizacion',//$log_tabla_asociada  ,
                                'IND-FIN',//$log_proceso  ,
                                'Finalizar Registro de Indización - Reproceso',//$log_descripcion  ,
                                '',//$log_comentario  ,
                                null//$log_archivo_id
                            );
                    //mandar captura a estado reproceso id_asociado = indizacion_id
                    $this->estado_captura_glb($captura_id, $cap_est_glb_0);
                    (new indizacion())->estado_indizacion_glb($id_asociado, $indizacion_estado_reproceso);
                    self::guardar_respuesta($indizacion_id, $array_respuesta);
                    //cambiar indizacion a estado reproceso

                } else {
                    //Version Inicial
                    if ($indizacion_tipo == 'VI') {

                        self::guardar_respuesta($indizacion_id, $array_respuesta);
                        //crear registro inicial de indizacion VF
                        $indizacion_nueva = (new indizacion())->crear_indizacion_inicial_from_indizacion($indizacion_id,$usuario_creador);
                        (new indizacion())->estado_indizacion_glb($id_asociado, $indizacion_estado_finalizado);
                        //devolver a null el usuario asignado indizacion de la captura para la segunda validación
                        $captura = captura::where('captura_id', $captura_id)
                        ->update(['usuario_asignado_indizacion' => null
                        ]);

                        $proyecto_captura_flujo=  proyecto_captura_flujo::where('captura_id',$captura_id)
                            ->where('modulo_step_id',2)
                            ->update(['modulo_id' => $indizacion_nueva]);

                        //grabamos log de captura
                        $log = new log();
                        $log->create_log_ez(
                                    $captura_id,//$log_captura_id  ,
                                    $id_asociado,//$log_id_asociado  ,
                                    2,//$log_modulo_step_id  ,
                                    'indizacion',//$log_tabla_asociada  ,
                                    'IND-FIN',//$log_proceso  ,
                                    'Finalizar Registro de Indización - Primera Validación',//$log_descripcion  ,
                                    '',//$log_comentario  ,
                                    null//$log_archivo_id
                                );

                    } else {

                        //VF
                        $valor_sql = "";

                        foreach ($array_respuesta as $key => $elemento) {
                            $valor = $elemento['valor'];
                            $valor = str_replace(["'"], "''", $valor);
                            $valor_sql .= ("'" . (string)$elemento['elemento_id'] . "%%%" . $valor . "',");
                        }

                        $valor_sql = substr($valor_sql, 0, strlen($valor_sql) - 1);
                        $r = (array)DB::select(
                            "SELECT elemento_id FROM respuesta
                                     where
                                    indizacion_id = :indizacion_id and
                                    cast(elemento_id as varchar)||'%%%'||valor not in
                                        (" . $valor_sql . ");"

                            , ['indizacion_id' => $indizacion_anterior_id]);


                        if (count($r) > 0) {
                            //cuando hay resultados es por que no hay diferencias en la comparacion
                            if ($flag_confirmacion == '1') {

                                self::guardar_respuesta($indizacion_id, $array_respuesta);
                                //crear registro inicial de control de calidad
                                //(new App\control_calidad())->crear_control_calidad_inicial_from_indizacion($captura_id, $usuario_creador, $recepcion_id, $proyecto_id, $indizacion_id, $cliente_id);
                                $this->registrar_avance_flujo_from_indizacion_to($captura_id,$usuario_creador);
                                (new indizacion())->estado_indizacion_glb($id_asociado, $indizacion_estado_finalizado);
                                $this->estado_captura_glb($captura_id, $cap_est_glb_1);

                            } else {

                                return 'conf';
                            }


                        } else {

                            self::guardar_respuesta($indizacion_id, $array_respuesta);
                            //crear registro inicial de control de calidad
                            //(new App\control_calidad())->crear_control_calidad_inicial_from_indizacion($captura_id, $usuario_creador, $recepcion_id, $proyecto_id, $indizacion_id, $cliente_id);
                            $this->registrar_avance_flujo_from_indizacion_to($captura_id,$usuario_creador);
                            (new indizacion())->estado_indizacion_glb($id_asociado, $indizacion_estado_finalizado);
                            $this->estado_captura_glb($captura_id, $cap_est_glb_1);

                        }
                        //grabamos log de captura
                        $log = new log();
                        $log->create_log_ez(
                                    $captura_id,//$log_captura_id  ,
                                    $id_asociado,//$log_id_asociado  ,
                                    2,//$log_modulo_step_id  ,
                                    'indizacion',//$log_tabla_asociada  ,
                                    'IND-FIN',//$log_proceso  ,
                                    'Finalizar Registro de Indización - Validación Final',//$log_descripcion  ,
                                    '',//$log_comentario  ,
                                    null//$log_archivo_id
                                );

                    }
                }

                return $this->retorna_autoasignacion_nueva_captura($proyecto_id,$recepcion_id,$captura_id,$usuario_creador);

            });

        return $obj;
    }


    public function guardar_respuesta($indizacion_id, $array_respuesta)
    {

        $inst_respuesta = new respuesta();
        $inst_respuesta -> eliminar_respuesta($indizacion_id);

        foreach ($array_respuesta as $key => $elemento) {
            $inst_respuesta = new respuesta();

            $opcion_id = $elemento['opcion_id'];
            $combo_id = $elemento['combo_id'];
            $elemento_id = $elemento['elemento_id'];
            $elemento_tipo = $elemento['elemento_tipo'];
            $plantilla_id = $elemento['plantilla_id'];
            $valor = $elemento['valor'];

            $wa = $inst_respuesta->crear_respuesta($opcion_id, $combo_id, $elemento_id, $elemento_tipo, $plantilla_id, $indizacion_id, $valor);

        }
    }

    public function arbol_indizacion(){

        $data=(new indizacion())->arbol_indizacion();
        $array_proyecto= array();
        $array_recepcion= array();
        $array_captura= array();
        $recepcion_old="0";
        $recepcion_nombre_old = "";
        $proyecto_old = "0";
        $proyecto_nombre_old = "";
        $prefijo_id_captura = "captura_";
        foreach ($data as $fila) {
            if($fila->recepcion_id != $recepcion_old && $recepcion_old!="0"){
                $array_recepcion[]=[
                    "id_recepcion" => $recepcion_old,//$fila->recepcion_id,
                    "text" => $recepcion_nombre_old,
                    "children" => $array_captura
                ];
                $array_captura= array();

                if($fila->proyecto_id != $proyecto_old && $proyecto_old!="0"){
                    $array_proyecto[]=[
                        "id_proyecto" => $proyecto_old,//$fila->proyecto_id,
                        "text" => $proyecto_nombre_old,
                        "children" => $array_recepcion
                    ];
                    $array_recepcion= array();
                }
            }
            $array_captura[]=[
                "id" =>$prefijo_id_captura.$fila->captura_id,
                "text" => $fila->documento_nombre,
                "icon" => "fa fa-file",
                "id_captura" => $fila->captura_id,
                "indizacion_id" => $fila->indizacion_id,
                "id_documento"=>$fila->documento_id,
                "adetalle_id"=>$fila->adetalle_id,
                "recepcion_tipo"=>$fila->recepcion_tipo,
                "proyecto_id"=>$fila->proyecto_id,
                "recepcion_id"=>$fila->recepcion_id,
                "indizacion_tipo" => $fila->indizacion_tipo,
                "indizacion_anterior_id" => $fila->indizacion_anterior_id
            ];
            $recepcion_old=$fila->recepcion_id;
            $recepcion_nombre_old= $fila->recepcion_nombre;

            $proyecto_old = $fila->proyecto_id;
            $proyecto_nombre_old = $fila->proyecto_nombre;
        }
        if($recepcion_old!="0" && $proyecto_old!="0"){
            $array_recepcion[]=[
                "id_recepcion" => $recepcion_old,
                "text" => $recepcion_nombre_old,
                "children" => $array_captura
            ];
            $array_proyecto[]=[
                "id_proyecto" => $proyecto_old,
                "text" => $proyecto_nombre_old,
                "children" => $array_recepcion
            ];

        }

        return $array_proyecto;

    }
    function retorna_autoasignacion_nueva_captura($proyecto_id,$recepcion_id,$captura_id,$usuario_id){
        $data = (array)DB::select(
            "select a.captura_id,
            case when a.recepcion_id=:recepcion_id then 0
            else a.recepcion_id end as recepcion_id,
            case when a.proyecto_id=:proyecto_id then 0
            else a.proyecto_id end as proyecto_id,
            case when a.indizacion_tipo ='VF' then 1
            else 0 end as orden_captura
            from indizacion a
            left join captura b on a.captura_id = b.captura_id
            where (b.usuario_asignado_indizacion is null or b.usuario_asignado_indizacion=:user)
            and (a.indizacion_estado = 0)
            order by proyecto_id,recepcion_id,orden_captura,captura_id;"
            , ['recepcion_id' => $recepcion_id,'proyecto_id' => $proyecto_id, 'user' => $usuario_id]);//session("usuario_id")

        //return $data;

        $nueva_captura_id = '0';
        if (count($data) > 0) {
            $nueva_captura_id = ((array)$data[0])['captura_id'];
        }
        return $nueva_captura_id;
    }

    function autoasignar_captura_inicial(){
        $usuario_creador = session("usuario_id");
        return $this->retorna_autoasignacion_nueva_captura(0,0,0,$usuario_creador);
    }

    function aprobar_todos_recepcion(){
        $usuario_creador = session("usuario_id");
        $recepcion_id = request('recepcion_id');

        $indizacion_estado_finalizado='2';
        $cap_est_glb_1 = 'cc';
        $modulo_step_id_actual = 2; //2 => indizacion
        //self::guardar_respuesta($indizacion_id, $array_respuesta);
        //crear registro inicial de control de calidad
        (new control_calidad())->crear_control_calidad_inicial_from_indizacion_masivo($usuario_creador, $recepcion_id);
        (new indizacion())->estado_indizacion_glb_masivo($recepcion_id, $indizacion_estado_finalizado);
        //Si está en captura va a control de calidad pendiente

        $is_captura = new captura();
        $contador_cc = $is_captura->modulo_step_glb_validador($recepcion_id,3);

        $contador_fed = $is_captura->modulo_step_glb_validador($recepcion_id,4);

        $contador_fed_fir = $is_captura->modulo_step_glb_validador($recepcion_id,5);

        if($contador_cc [0]->modulo_step != 0){
            $this->estado_captura_glb_masivo($recepcion_id, $cap_est_glb_1);
        }else if($contador_fed [0]->modulo_step != 0){
            $this->estado_captura_glb_masivo($recepcion_id, 'fed');
        }else if($contador_fed_fir [0]->modulo_step != 0){
            $this->estado_captura_glb_masivo($recepcion_id, 'fed_fir');
        }else{
            $this->estado_captura_glb_masivo($recepcion_id, 'fin');
        }

        //falta los cambios por el flujo dinámico

        return $this->retorna_autoasignacion_nueva_captura(0,0,0,$usuario_creador);



        //1.- encontrar el siguiente modulo flujo al que debe de ir

        //2.- switch dependiendo del módulo crear registros

        //3.-

    }

    /**
     * Consulta el siguiente módulo donde debe continuar la indizacion y continua su respectivo registro, actualizando también las tablas respectivas
     * @param integer $captura_id código de captura
     * @param integer $usuario_creador usuario logeado en el momento
     * @return void no retorna valor
     * @author Christian Fernando Condori Soto
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    function registrar_avance_flujo_from_indizacion_to($captura_id,$usuario_creador){
        $pro_cap_flujo = (new proyecto_captura_flujo())->consultar_orden($captura_id);
        $modulo_step_id =0;
        if(count($pro_cap_flujo)>0){
            $modulo_step_id = $pro_cap_flujo[0]->modulo_step_id;
            $id_generado_nuevo_modulo=0;
            //dependiendo del módulo generamos los registros correspondientes
            switch ($modulo_step_id) {
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
                    break;

                default:

                    break;
            }
            //actualizamos captura y proyecto_captura_flujo
            $this->actualizar_captura_pcf_nuevo_modulo($captura_id,$modulo_step_id,$id_generado_nuevo_modulo);


        }
    }

}

