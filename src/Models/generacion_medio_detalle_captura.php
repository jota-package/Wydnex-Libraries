<?php

namespace Fedatario\Models;

use function GuzzleHttp\Psr7\str;
use Illuminate\Database\Eloquent\Model;
use DB;

Trait generacion_medio_detalle_captura
{
    protected $primaryKey = "gmdc_id";
    protected $table = "generacion_medio_detalle_captura";

    public function captura_estado($gmd_id){

        $files = DB::select(
            DB::raw('
               select
                   a.gmd_id,
                   c.documento_id,
                   c.documento_nombre,
                   c.documento_estado,
                   b.captura_estado
            from generacion_medio_detalle_captura a
            join captura b on a.captura_id = b.captura_id
            join documento c on c.captura_id = b.captura_id
            where a.gmd_id = :gmd_id'),
            ['gmd_id' => $gmd_id]
        );
        return $files;
    }

     public function captura_estado_test($recepcion_id){

        $files = DB::select(
            DB::raw('
               select   b.captura_id,
                         c.documento_id,
                         c.documento_nombre,
                         c.documento_estado,
                         b.captura_estado
                from  captura b
                left join documento c on c.captura_id = b.captura_id
                where b.recepcion_id = :recepcion_id
                and b.captura_estado = 4'),
            ['recepcion_id' => $recepcion_id]
        );
        return $files;
    }


    public function captura_estado_test_calibradora($recepcion_id){

        $files = DB::select(
            DB::raw('
               select   b.captura_id,
                         c.documento_id,
                         c.documento_nombre,
                         c.documento_estado,
                         b.captura_estado
                from  captura b
                left join documento c on c.captura_id = b.captura_id
                where b.recepcion_id = :recepcion_id
                and b.captura_estado = 5'),
            ['recepcion_id' => $recepcion_id]);

    }

    public function insert_calibradora_acta_generacion_medio_detalle_captura($gmd_id,$adetalle_id){

        $files = DB::select(
            DB::raw('
            insert into generacion_medio_detalle_captura(
                gmd_id, gm_id, gmd_grupo, captura_id, created_at, updated_at)
                select 
                b.gmd_id,
                b.gm_id,
                b.gmd_grupo,
                a.captura_id,
                now(),
                now()
                from documento a
                join generacion_medio_detalle b on b.gmd_id = :gmd_id
                where a.adetalle_id = :adetalle_id
            returning gmdc_id;'),
            ['gmd_id' => $gmd_id,'adetalle_id' => $adetalle_id]

        );
        return $files;
    }

    public function captura_listar_acta_cierre($gmd_id){

        $files = DB::select(
            DB::raw('
            select 
                b.captura_id,
                c.documento_id,
                c.documento_nombre,
                c.documento_estado,
                c.adetalle_id,
                b.captura_estado
            from generacion_medio_detalle_captura a
            join captura b on a.captura_id = b.captura_id
            join documento c on c.captura_id = b.captura_id
            where a.gmd_id = :gmd_id and b.captura_estado = 4;'),
            ['gmd_id' => $gmd_id]
        );
        return $files;
    }


    public function captura_listar_calibradora_cierre($gmd_id){

        $files = DB::select(
            DB::raw('
            select 
                b.captura_id,
                c.documento_id,
                c.documento_nombre,
                c.documento_estado,
                c.adetalle_id,
                b.captura_estado
            from generacion_medio_detalle_captura a
            join captura b on a.captura_id = b.captura_id
            join documento c on c.captura_id = b.captura_id
            where a.gmd_id = :gmd_id and b.captura_estado = 5;'),
            ['gmd_id' => $gmd_id]);

        return $files;

    }

    public function validador_captura_listar_ac($gmd_id,$captura_estado){

        $files = DB::select(
            DB::raw("
            select 
                b.captura_id,
                c.documento_id,
                c.documento_nombre,
                c.documento_estado,
                c.adetalle_id,
                b.captura_estado
            from generacion_medio_detalle_captura a
            join captura b on a.captura_id = b.captura_id
            join documento c on c.captura_id = b.captura_id
            where a.gmd_id = :gmd_id and b.captura_estado = :captura_estado
              and  b.created_at >= now() - INTERVAL '1 DAY'
            ;"),
            ['gmd_id' => $gmd_id,'captura_estado'=>$captura_estado]
        );
        return $files;

    }


}
