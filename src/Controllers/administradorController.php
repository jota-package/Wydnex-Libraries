<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\respuesta;
use App\Http\Controllers\utils;
use App\Http\Controllers\incidenciaController;
use App\Http\Controllers\tipo_calibradorController;
use App\captura;
use App\documento;
use App\equipo;
use App\files;
use View;
use DB;
use Response;

Trait administradorController
{

    public function index(){

        //Instancia Documento
        $ins_documento = new documento();
        //Instancia Incidencia
        $ins_incidencia = new incidenciaController();
        $ins_tipo_calibrador = new tipo_calibradorController();

        $equipo = equipo::join('persona as p','p.usuario_id','equipo.usuario_id')
            ->select('p.persona_nombre','p.persona_apellido','equipo.usuario_id')
            ->distinct()
            ->get();

        $equipo_captura = equipo::join('persona as p','p.usuario_id','equipo.usuario_id')
            ->select('p.persona_nombre','p.persona_apellido','equipo.usuario_id')
            ->distinct()
            ->where('equipo.perfil_id',2)
            ->get();

        $equipo_indizacion = equipo::join('persona as p','p.usuario_id','equipo.usuario_id')
            ->select('p.persona_nombre','p.persona_apellido','equipo.usuario_id','equipo.perfil_id')
            ->distinct()
            ->where('equipo.perfil_id',3)
            ->get();

        $equipo_control_calidad = equipo::join('persona as p','p.usuario_id','equipo.usuario_id')
            ->select('p.persona_nombre','p.persona_apellido','equipo.usuario_id','equipo.perfil_id')
            ->distinct()
            ->where('equipo.perfil_id',4)
            ->get();

        $equipo_fedatario = equipo::join('persona as p','p.usuario_id','equipo.usuario_id')
            ->select('p.persona_nombre','p.persona_apellido','equipo.usuario_id')
            ->distinct()
            ->where('equipo.perfil_id',5)
            ->get();

        $equipo_reproceso = equipo::join('persona as p','p.usuario_id','equipo.usuario_id')
            ->select('p.persona_nombre','p.persona_apellido','equipo.usuario_id')
            ->distinct()
            ->get();


        $lista_documentos = $ins_documento->listar_documento();
        $incidencia = $ins_incidencia->listar_incidencia();
        $tipo_calibrador = $ins_tipo_calibrador->listar_tipo_calibrador();

        return view::make('administrador.index.content')
            ->with("lotes", $lista_documentos)
            ->with("incidencia", $incidencia)
            ->with("usuarios_captura", $equipo_captura)
            ->with("usuarios_reproceso", $equipo_reproceso)
            ->with("usuarios_indizacion", $equipo_indizacion)
            ->with("usuarios_control_calidad", $equipo_control_calidad)
            ->with("usuarios_fedatario", $equipo_fedatario)
            ->with("usuarios", $equipo)
            ->with("tipo_calibrador", $tipo_calibrador);

    }

    public function mantenimiento_admin(){

        $documento_id = request("documento_id");

        $is_captura = new captura();
        $array_total = $is_captura->mantenimiento($documento_id);

        return $array_total;

    }

    public function modificar_glb_admin(){


        $captura_id = request('captura_id');
        //Recibir estados
        $estado_captura = request('estado_captura');
        $estado_indizacion = request('estado_indizacion');
        $estado_control_calidad = request('estado_control_calidad');
        $estado_fed_normal = request('estado_fed_normal');
        $estado_fed_firmar = request('estado_fed_firmar');
        $estado_gen_medio = request('estado_generar');

        //Recibir usuarios
        $usuario_captura = request('usuario_captura');
        $usuario_captura = (is_null ( $usuario_captura )) ? 0 : $usuario_captura;

        $usuario_indizacion = request('usuario_indizacion');
        $usuario_indizacion = (is_null ( $usuario_indizacion )) ? 0 : $usuario_indizacion;

        $usuario_cc = request('usuario_cc');
        $usuario_cc = (is_null ( $usuario_cc )) ? 0 : $usuario_cc;

        $usuario_fa = request('usuario_fa');
        $usuario_fa = (is_null ( $usuario_fa )) ? 0 : $usuario_fa;

        $usuario_feda = request('usuario_feda');
        $usuario_feda = (is_null ( $usuario_feda )) ? 0 : $usuario_feda;

        $usuario_firmado = request('usuario_firmado');
        $usuario_firmado = (is_null ( $usuario_firmado )) ? 0 : $usuario_firmado;

        $usuario_reproceso = request('usuario_reproceso');
        $usuario_reproceso = (is_null ( $usuario_reproceso )) ? 0 : $usuario_reproceso;

        $is_captura = new captura();
        $ok_update_captura = $is_captura-> super_squery_upd_mantenimiento(
            $captura_id
            ,$estado_captura
            ,$estado_indizacion
            ,$estado_control_calidad
            ,$estado_fed_normal
            ,$estado_fed_firmar
            ,$estado_gen_medio

            ,$usuario_captura
            ,$usuario_indizacion
            ,$usuario_cc
            ,$usuario_fa
            ,$usuario_feda
            ,$usuario_firmado
            ,$usuario_reproceso
        );

        return 'ok';

    }

    public function cambio_est(){

        $captura_id = request('captura_id');
        //Recibir estados
        $estado_captura = request('estado_captura');
        $estado_indizacion = request('estado_indizacion');
        $estado_control_calidad = request('estado_control_calidad');
        $estado_fed_normal = request('estado_fed_normal');
        $estado_fed_firmar = request('estado_fed_firmar');
        $estado_gen_generar  = request('estado_generar');

        $is_captura = new captura();
        $change_validador = $is_captura -> modulo_cambio_flujo(
            $estado_captura,
            $estado_indizacion,
            $estado_control_calidad,
            $estado_fed_normal,
            $estado_fed_firmar,
            $estado_gen_generar
        );

        return $change_validador;

    }

    public function obtener_indices(Request $request){
        $lista_capturas = $request->input('lista_capturas', []);
        if(count($lista_capturas) == 0){
            return respuesta::error("No se ha recibido ningun indices.");
        }
        $lista_query = "{".implode(",", $lista_capturas)."}";
        return captura::obtener_indices_capturas($lista_query);
    }

    public function borrar_captura(Request $request){
        $file_id = $request->input("file_id", 0);
        return files::borrar_file_contenido($file_id);
    }
}
