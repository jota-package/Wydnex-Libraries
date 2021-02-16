<?php

namespace Fedatario\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\User;

Trait control_calidad
{

    protected $primaryKey = "cc_id";
    protected $table = "control_calidad";


    public function crear_control_calidad_inicial_from_indizacion($captura_id,$usuario_creador,$recepcion_id,$proyecto_id,$indizacion_id,$cliente_id){

        return DB::insert("
            insert into control_calidad(
                captura_id
                ,indizacion_id
                ,proyecto_id
                ,recepcion_id
                ,cliente_id
                ,cc_estado
                ,usuario_creador
                ,created_at
                ,updated_at
                )
                values (:captura_id,
                :indizacion_id,
                :proyecto_id,
                :recepcion_id,
                :cliente_id,
                0,
                :usuario_creador,
                now(),
                now()
                )", ["captura_id"=>$captura_id
            , "recepcion_id"=>$recepcion_id
            , "proyecto_id"=>$proyecto_id
            , "indizacion_id"=>$indizacion_id
            , "cliente_id"=>$cliente_id
            , "usuario_creador"=>$usuario_creador]);
    }
    public function crear_control_calidad_inicial_from_captura($captura_id, $usuario_creador)
    {

        return DB::select(
                    DB::raw("
                    insert into control_calidad(
                        captura_id
                        ,proyecto_id
                        ,recepcion_id
                        ,cliente_id
                        ,cc_estado
                        ,usuario_creador
                        ,created_at
                        ,updated_at
                        )
                        select
                            a.captura_id,
                            a.proyecto_id,
                            a.recepcion_id,
                            a.cliente_id,
                            0,
                            :usuario_creador,
                            now(),
                            now()
                        from captura a
                        left join proyecto b on a.proyecto_id = b.proyecto_id
                        where a.captura_id=:captura_id
                        RETURNING cc_id;
                        "),["usuario_creador" => $usuario_creador, "captura_id" => $captura_id]
        )[0]->cc_id;
    }


    public function arbol_controlcalidad()
    {
        $is_admin = User::is_admin();
        if($is_admin){
            return DB::select("
            select
            a.proyecto_id, a.recepcion_id, a.captura_id, a.cc_id, a.indizacion_id,a.cliente_id,a.usuario_creador,
            d.documento_id,
            b.proyecto_nombre,
            c.recepcion_nombre,
            d.documento_nombre,
            d.adetalle_id,
            c.recepcion_tipo,
            f.indizacion_id
            from control_calidad a
            left join proyecto b on a.proyecto_id = b.proyecto_id
            left join recepcion c on c.recepcion_id = a.recepcion_id
            left join documento d on d.captura_id = a.captura_id
            left join proyecto_captura_flujo e on a.captura_id = e.captura_id and e.modulo_step_id =2
            left join indizacion f  on e.modulo_id = f.indizacion_id
            where a.cc_estado = 0
            order by a.proyecto_id, a.recepcion_id, a.captura_id, a.indizacion_id;
            ");
        }else{
            $usuario_id = session('usuario_id');
            return DB::select("
            select
            a.proyecto_id, a.recepcion_id, a.captura_id, a.cc_id, a.indizacion_id,a.cliente_id,a.usuario_creador,
            d.documento_id,
            b.proyecto_nombre,
            c.recepcion_nombre,
            d.documento_nombre,
            d.adetalle_id,
            c.recepcion_tipo,
            f.indizacion_id
            from control_calidad a
            left join proyecto b on a.proyecto_id = b.proyecto_id
            left join recepcion c on c.recepcion_id = a.recepcion_id
            left join documento d on d.captura_id = a.captura_id
            left join proyecto_captura_flujo e on a.captura_id = e.captura_id and e.modulo_step_id =2
            left join indizacion f  on e.modulo_id = f.indizacion_id
            left join equipo eq on b.proyecto_id = eq.proyecto_id
            where a.cc_estado = 0
            and eq.usuario_id = :usuario_id
            order by a.proyecto_id, a.recepcion_id, a.captura_id, a.indizacion_id;
            ", ["usuario_id"=>$usuario_id]);
        }

    }

    public function update_estado_cc($cc_id,$cc_estado)
    {

        return DB::update("
            UPDATE control_calidad
            SET cc_estado = :cc_estado
            WHERE cc_id = :cc_id", ["cc_estado"=>$cc_estado
            , "cc_id"=>$cc_id]);



    }

    public function update_estado_cc_masivo($recepcion_id,$cc_estado)
    {

        return DB::update("
            UPDATE control_calidad
            SET cc_estado = :cc_estado
            WHERE recepcion_id = :recepcion_id", ["cc_estado"=>$cc_estado
            , "recepcion_id"=>$recepcion_id]);



    }
    public function crear_control_calidad_inicial_from_indizacion_masivo($usuario_creador,$recepcion_id){

        return DB::insert("
            insert into control_calidad(
                cliente_id
                ,proyecto_id
                ,recepcion_id
                ,indizacion_id
                ,captura_id
                ,usuario_creador
                ,cc_estado
                ,created_at
                ,updated_at)
            select
                cliente_id,
                proyecto_id,
                recepcion_id,
                indizacion_id,--cambiar por null
                captura_id,
                :usuario_creador,--usuario_creador
                0,--estado inicial en duro
                now(),
                now()
                from indizacion
                where recepcion_id = :recepcion_id
                and indizacion_estado=0
                and indizacion_tipo='VF';",
                ["recepcion_id"=>$recepcion_id
            , "usuario_creador"=>$usuario_creador]);
    }

}
