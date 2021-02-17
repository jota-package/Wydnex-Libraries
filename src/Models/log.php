<?php

namespace Fedatario\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Request;

Trait log
{

    protected $primaryKey = 'log_id';
    protected $table = 'log';

    public function create_log(
        $log_fecha_hora ,
        $log_usuario ,
        $log_estado ,
        $log_ip ,
        $log_captura_id ,
        $log_id_asociado ,
        $log_modulo_step_id ,
        $log_tabla_asociada ,
        $log_proceso ,
        $log_descripcion ,
        $log_comentario ,
        $log_archivo_id
    ){

        $data = [
            "log_fecha_hora" => $log_fecha_hora,
            "log_usuario" => $log_usuario,
            "log_estado" => $log_estado,
            "log_ip" => $log_ip,
            "log_captura_id" => $log_captura_id,
            "log_id_asociado" => $log_id_asociado,
            "log_modulo_step_id" => $log_modulo_step_id,
            "log_tabla_asociada" => $log_tabla_asociada,
            "log_proceso" => $log_proceso,
            "log_descripcion" => $log_descripcion,
            "log_comentario" => $log_comentario,
            "log_archivo_id" => $log_archivo_id
        ];

        return $this::create($data);

    }

    public function create_log_ez(
        $log_captura_id  = null,
        $log_id_asociado  = null,
        $log_modulo_step_id  = null,
        $log_tabla_asociada  = null,
        $log_proceso  = null,
        $log_descripcion  = null,
        $log_comentario = null ,
        $log_archivo_id = null
    ){


        $data = [
            "log_fecha_hora" => date('Y-m-d H:i:s'),
            "log_usuario" => session("usuario_id"),
            "log_estado" => 1,
            "log_ip" => Request::ip(),
            "log_captura_id" => $log_captura_id,
            "log_id_asociado" => $log_id_asociado,
            "log_modulo_step_id" => $log_modulo_step_id,
            "log_tabla_asociada" => $log_tabla_asociada,
            "log_proceso" => $log_proceso,
            "log_descripcion" => $log_descripcion,
            "log_comentario" => $log_comentario,
            "log_archivo_id" => $log_archivo_id
        ];

        return $this::create($data);
    }

    public function reporte_x_captura($captura_id){
        return DB::select(
            DB::raw("
            with
            log_inicial as (
                select
                    row_number() over(order by a.log_fecha_hora) as id_aux
                    ,a.log_fecha_hora
                    ,to_char(a.log_fecha_hora,'DD/MM/YYYY') as fecha
                    ,to_char(a.log_fecha_hora,'DD/MM/YYYY HH24:MI:SS') as inicio
                    ,'' as fin
                    ,a.log_proceso as proceso
                    ,a.log_descripcion as descripcion
                    ,p.persona_nombre||' '||p.persona_apellido as usuario
                from log a
                left join persona p
                on a.log_usuario = p.usuario_id
                where a.log_captura_id = :captura_id
                order by a.log_fecha_hora
            )
            select
                a.fecha
                ,a.inicio
                ,case when b.inicio is not null
                    then b.inicio
                    else 'EN PROCESO'
                end
                    as fin
                ,a.proceso
                ,a.descripcion
                ,a.usuario
            from log_inicial a
            left join log_inicial b
                on a.id_aux =b.id_aux-1
            order by a.log_fecha_hora;
            "),
            ["captura_id" => $captura_id]);

    }

    //
}
