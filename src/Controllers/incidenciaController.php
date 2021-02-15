<?php

namespace Fedatario\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\respuesta;
use App;
use DB;
use View;

Trait incidenciaController
{

    public function index()
    {

        $incidencias = App\incidencia::
        orderBy('incidencia_id','ASC')
            ->get();

        return view::make('incidencia.index.content')
            ->with('incidencias',$incidencias)
            ;

    }

    public function incidencia_crear(Request $request)
    {

        // $incidencia_id = $request->input('incidencia_id', 0);
        $data_incidencia = $request->all();
        return App\incidencia::crear($data_incidencia);

    }

    public function incidencia_ver_datos(Request $request)
    {

        $incidencia_actual = request('incidencia_actual');

        return App\incidencia::obtener($incidencia_actual);
    }

    public function incidencia_editar(Request $request)
    {

        $incidencia_id = $request->input('incidencia_id', 0);
        $data_incidencia = $request->all();
        return App\incidencia::modificar($incidencia_id, $data_incidencia);
    }

    public function dar_baja_incidencia(Request $request){
        $incidencia_id = $request->input('incidencia_id', 0);
        return App\incidencia::dar_baja($incidencia_id);
    }

    public function dar_alta_incidencia(Request $request){
        $incidencia_id = $request->input('incidencia_id', 0);
        return App\incidencia::dar_alta($incidencia_id);
    }

    public function incidencia_estado(Request $request)
    {

        $incidencia_actual = request('incidencia_actual');
        $estado = request('estado');

        if( $estado == 0 ){

            $proyecto_asociado = App\proyecto::where("incidencia_id",$incidencia_actual)
                ->count();

            if( $proyecto_asociado > 0 ){

                return "Este incidencia se encuentra asociado a un proyecto, no se le puede dar de baja";

            }

        }

        $save = App\incidencia::where('incidencia_id',$incidencia_actual)
            ->update(['incidencia_estado' => $estado]);

        //Si no se ejecuto los query devolvemos error
        if(!$save){

            App::abort(500, 'Error');

        }

        return response('ok', 200);

    }

    public function listar_incidencia()
    {
        $incidencia = new App\incidencia();
        $lista_incidencias = $incidencia->listar();

        return $lista_incidencias;
    }

    public function registrar_incidencia()
    {

        $imagen_id = request('imagen_id');
        $incidente_id = request('incidente_id');
        $tipo_asociado = request('tipo_asociado');

        $proceso = 'CAP';
        $modulo_step_id = 1;

        switch ($tipo_asociado) {
            case 'cap':
                $is_captura = new App\imagen();
                $obj_captura = $is_captura->where('imagen_id', $imagen_id)
                    ->first();
                $this->incidencia_imagen_nuevo($incidente_id, $imagen_id, $tipo_asociado, $obj_captura['captura_id']);
                break;
            case 'ind':
                $indizacion_id = request('indizacion_id');
                $this->incidencia_imagen_nuevo($incidente_id, $imagen_id, $tipo_asociado, $indizacion_id);
                $proceso = 'IND';
                $modulo_step_id = 2;
                break;
            case 'cal':
                $control_calidad_id = request('cc_id');
                $this->incidencia_imagen_nuevo($incidente_id, $imagen_id, $tipo_asociado, $control_calidad_id);
                $proceso = 'CAL';
                $modulo_step_id = 3;
                break;
            case 'fed':
                $fedatario_id = request('fed_id');
                $this->incidencia_imagen_nuevo($incidente_id, $imagen_id, $tipo_asociado, $fedatario_id);
                $proceso = 'FED';
                $modulo_step_id = 4;
                break;
            default:
                return $this->crear_objeto("error", "Que haces prro!");
                break;
        }

        $is_captura = new App\imagen();
        $obj_captura = $is_captura->where('imagen_id', $imagen_id)->first();
        //grabamos log de captura
        $log = new App\log();
        $log->create_log_ez(
                    $obj_captura['captura_id'],//$id_asociado,//$log_captura_id  ,
                    $imagen_id,//$log_id_asociado  ,
                    $modulo_step_id,//$log_modulo_step_id  ,
                    'incidencia_imagen',//$log_tabla_asociada  ,
                    $proceso,//$log_proceso  ,
                    'Registro de incidencia',//$log_descripcion  ,
                    '',//$log_comentario  ,
                    null//$log_archivo_id
                );

        // if ($tipo_asociado == "cap" || $tipo_asociado == "ind" || $tipo_asociado == "cal") {

        //     $is_captura = new App\imagen();
        //     $obj_captura = $is_captura->where('imagen_id', $imagen_id)
        //         ->first();
        //     $this->incidencia_imagen_nuevo($incidente_id, $imagen_id, $tipo_asociado, $obj_captura['captura_id']);

        // } else {
        //     return $this->crear_objeto("error", "Que haces prro!");
        // }

    }

    public function finalizar_registro_incidencia_captura()
    {
        $usuario_creador = session("usuario_id");
        $recepcion_tipo = request("recepcion_tipo");
        $tipo_asociado = request("tipo_asociado");
        $captura_id = request("captura_id");

        //En caso de que captura sea múltiple obtiene el id de captura a través del adetalle_id
        if ($recepcion_tipo === "m") {
            $is_captura = (new App\adetalle())
                ->join("documento as doc", "doc.adetalle_id", "adetalle.adetalle_id")
                ->where("doc.adetalle_id", $captura_id)
                ->select("documento_id")->first();

            $captura_id = $is_captura->documento_id;

        }

        $id_asociado = $captura_id;


        $this->finalizar_registro_incidencia_glb($id_asociado, $usuario_creador, $tipo_asociado,
            function($id_asociado, $usuario_creador,$count,$request){
                $cap_est_glb_0 = 'rep';
                $cap_est_glb_1 = 'ind';

                if ($count > 0) {
                    //mandar captura a estado reproceso id_asociado = captura_id
                    $this->estado_captura_glb($id_asociado, $cap_est_glb_0);
                } else {
                    //crear registro inicial de indizacion
                    $indizacion_nueva = (new App\indizacion())->crear_indizacion_inicial_from_captura($usuario_creador, $id_asociado);
                    //mandar captura a indizacion id_asociado = captura_id
                    $this->estado_captura_glb($id_asociado, $cap_est_glb_1);
                }

            });

    }


    /**
     * Función global que registra todas las incidencias y las funciones particulares de cada flujo
     * @param $id_asociado(captura_id,indizacion_id,control_calidad_id) --- $tipo_asociado ('ca','in','cc')
     * @author El juaquer Bueno (ง •̀_•́)ง
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function finalizar_registro_incidencia_glb($id_asociado, $usuario_creador, $tipo_asociado,$funcion )
    {
        $request = request();
        //actualizar tabla incidencia imagen
        //validar si hay alguna imagen que pasó a reproceso
        $img_reproceso = $this->actualizar_incidencia_imagen($id_asociado, $tipo_asociado);
        $count = count($img_reproceso);

//      $funcion($id_asociado, $usuario_creador,$count,$request);
        return $funcion($id_asociado, $usuario_creador,$count,$request);

    }

    public function incidencia_valor()
    {

        $imagen_id = request('imagen_id');


        $incidencia_imagen = new App\incidencia_imagen();

        $incidencia_id = $incidencia_imagen->select('incidencia_id','created_at')
            ->orderBy('created_at', 'DESC')
            ->where('imagen_id', $imagen_id)
            ->where('estado',0)
            ->first();

        if (isset($incidencia_id)) {
            return $incidencia_id['incidencia_id'];
        } else {
            return "-1";
        }

    }

    public function incidencia_valor_administrador()
    {
        $imagen_id = request('imagen_id');
        $incidencia_imagen = new App\incidencia_imagen();
        $incidencia = $incidencia_imagen->incidencia_imagen($imagen_id);

        return $incidencia;
    }

    public function incidencia_valor_reproceso()
    {

        $imagen_id = request('imagen_id');



        $incidencia_imagen = new App\incidencia_imagen();

        $incidencia = $incidencia_imagen
            ->join('incidencia','incidencia.incidencia_id','incidencia_imagen.incidencia_id')
            ->select('incidencia_imagen.incidencia_id as incidencia_id','incidencia_imagen.created_at','incidencia_nombre')
            ->orderBy('incidencia_imagen.created_at', 'DESC')
            ->where('imagen_id', $imagen_id)
            ->where('estado',1)
            ->first();

        if (isset($incidencia)) {
            return $incidencia;
        } else {
            return "-1";
        }

    }


}
