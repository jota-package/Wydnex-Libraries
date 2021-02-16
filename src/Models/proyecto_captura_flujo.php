<?php

namespace Fedatario\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

Trait proyecto_captura_flujo
{
    protected $primaryKey = 'proyecto_captura_flujo_id';
    protected $table = 'proyecto_captura_flujo';

    public function crear_PCF_from_PF($captura_id)
    {

        return DB::insert("
        insert into proyecto_captura_flujo
        (
            proyecto_id,
            captura_id,
            modulo_step_id,
            modulo_step_orden,
            modulo_id
        )
        select 
        --identity
        b.proyecto_id,
        a.captura_id,
        b.modulo_step_id,
        b.modulo_step_orden,
        case
            when b.modulo_step_id =1 then a.captura_id
            else null end as modulo_id
        from captura a
        left join proyecto_flujo b 
            on a.proyecto_id = b.proyecto_id
        where a.captura_id = :captura_id;
            ", [ "captura_id" => $captura_id]);
    }


    public function consultar_orden($captura_id)
    {

        return DB::select("
            select 
                a.captura_id,
                b.modulo_step_id,
                b.modulo_step_orden,
                b.modulo_id
                from captura a
                left join proyecto_captura_flujo b 
                    on a.captura_id = b.captura_id
                where a.captura_id = :captura_id --captura_id
                    and b.modulo_step_id > a.flujo_id_actual
                order by b.modulo_step_orden
                limit 1;
            ", ["captura_id" => $captura_id]);

    }

    public function consultar_orden_recepcion($recepcion_id)
    {

        return DB::select("
            select 
                a.captura_id,
                b.modulo_step_id,
                b.modulo_step_orden,
                b.modulo_id
                from captura a
                left join proyecto_captura_flujo b 
                    on a.captura_id = b.captura_id
                where a.recepcion_id = :recepcion_id --captura_id
                    and b.modulo_step_id > a.flujo_id_actual
                order by b.modulo_step_orden
                limit 1;
            ", ["recepcion_id" => $recepcion_id]);

    }

}
