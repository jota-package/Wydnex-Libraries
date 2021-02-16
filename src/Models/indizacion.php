<?php

namespace Fedatario\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

Trait indizacion
{
    protected $primaryKey = 'indizacion_id';
    protected $table = 'indizacion';

    public function crear_indizacion($captura_id, $proyecto_id, $recepcion_id, $cliente_id, $indizacion_estado, $usuario_creador)
    {

        $indizacion = new indizacion();
        $indizacion->captura_id = $captura_id;
        $indizacion->proyecto_id = $proyecto_id;
        $indizacion->recepcion_id = $recepcion_id;
        $indizacion->cliente_id = $cliente_id;
        $indizacion->indizacion_estado = $indizacion_estado;
        $indizacion->usuario_creador = $usuario_creador;
        // $respuesta->conca_id = $simple_tipo_formato;

        $indizacion->save();


        return $indizacion;

    }

    public function listar_indizacion()
    {
        return $this::all();
    }

    public function crear_indizacion_inicial_from_captura($captura_id, $usuario_creador)
    {
        // DB::select( DB::raw("SELECT * FROM some_table WHERE some_col = :somevariable"), array(
        //     'somevariable' => $someVariable,
        //   ));

        return DB::select(
                    DB::raw("
                        insert into indizacion(
                            captura_id
                            ,proyecto_id
                            ,recepcion_id
                            ,cliente_id
                            ,indizacion_estado
                            ,indizacion_tipo
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
                                case when b.proyecto_validacion = 1 then 'VI'
                                    else 'VF' end,
                                :usuario_creador,
                                now(),
                                now()
                            from captura a
                            left join proyecto b on a.proyecto_id = b.proyecto_id
                            where a.captura_id=:captura_id
                            RETURNING indizacion_id;
                        "),["usuario_creador" => $usuario_creador, "captura_id" => $captura_id]
        )[0]->indizacion_id;
    }


    public function crear_indizacion_inicial_from_indizacion($indizacion_id, $usuario_creador)
    {

            return DB::select(
                DB::raw("
                insert into indizacion(
                    captura_id
                    ,proyecto_id
                    ,recepcion_id
                    ,cliente_id
                    ,indizacion_estado
                    ,indizacion_tipo
                    ,usuario_creador
                    ,indizacion_anterior_id
                    ,created_at
                    ,updated_at
                    )
                    select
                    captura_id
                    ,proyecto_id
                    ,recepcion_id
                    ,cliente_id
                    ,indizacion_estado
                    ,'VF'
                    ,:usuario_creador
                    ,:indizacion_id
                    ,now()
                    ,now()
                    from indizacion
                    where indizacion_id=:indizacion_id
                RETURNING indizacion_id;
                    "), ["indizacion_id" => $indizacion_id,"usuario_creador" => $usuario_creador]
    )[0]->indizacion_id;
    }


    public function arbol_indizacion()
    {

        $is_admin = user::is_admin();
        if($is_admin){
            return DB::select("
            select
            a.proyecto_id, a.recepcion_id, a.captura_id, a.indizacion_id,
            d.documento_id,
            b.proyecto_nombre,
            c.recepcion_nombre,
            d.documento_nombre,
            d.adetalle_id,
            c.recepcion_tipo,
            a.indizacion_tipo,
            a.indizacion_anterior_id
            from indizacion a
            left join proyecto b on a.proyecto_id = b.proyecto_id
            left join recepcion c on c.recepcion_id = a.recepcion_id
            left join documento d on d.captura_id = a.captura_id
            where a.indizacion_estado = 0
            order by a.proyecto_id, a.recepcion_id, a.captura_id, a.indizacion_id;
            ");
        }else{
            $usuario_id = session('usuario_id');

            return DB::select("
            select
            a.proyecto_id, a.recepcion_id, a.captura_id, a.indizacion_id,
            d.documento_id,
            b.proyecto_nombre,
            c.recepcion_nombre,
            d.documento_nombre,
            d.adetalle_id,
            c.recepcion_tipo,
            a.indizacion_tipo,
            a.indizacion_anterior_id,
            e.usuario_id
            from indizacion a
            left join proyecto b on a.proyecto_id = b.proyecto_id
            left join recepcion c on c.recepcion_id = a.recepcion_id
            left join documento d on d.captura_id = a.captura_id
            left join equipo e on b.proyecto_id = e.proyecto_id
            where a.indizacion_estado = 0
            and e.usuario_id = :usuario_id
            order by a.proyecto_id, a.recepcion_id, a.captura_id, a.indizacion_id;
            ", ["usuario_id"=>$usuario_id]);
        }

    }

    public function estado_indizacion_glb($indizacion_id,$indizacion_estado_glb){

        $is_indizacion = new indizacion();

        $is_indizacion->where('indizacion_id', $indizacion_id)
            ->update(['indizacion_estado' => $indizacion_estado_glb]);



    }

    public function estado_indizacion_glb_masivo($recepcion_id,$indizacion_estado_glb){
        $is_indizacion = new indizacion();
        $is_indizacion->where('recepcion_id', $recepcion_id)
            ->where('indizacion_estado', 0)
            ->where('indizacion_tipo', 'VF')
            ->update(['indizacion_estado' => $indizacion_estado_glb]);
    }

}
