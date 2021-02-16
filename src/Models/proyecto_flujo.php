<?php

namespace Fedatario\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

Trait proyecto_flujo
{

    protected $primaryKey = 'proyecto_flujo_id';
    protected $table = 'proyecto_flujo';

    public function insertar_proyecto_flujo($proyecto_id,$validacion_indizacion,
                                            $validacion_control_calidad,
                                            $validacion_fedatario_revisar,
                                            $validacion_fedatario_firmar){



        $str_flujo = '1-1,2-'.$validacion_indizacion.',3-'
            .$validacion_control_calidad.',4-'
            .$validacion_fedatario_revisar.',5-'
            .$validacion_fedatario_firmar
        ;

        return DB::insert("
            insert into proyecto_flujo(
                proyecto_id,
                modulo_step_id,
                modulo_step_orden,
                created_at,
                updated_at
                )
                select 
                :proyecto_id,--proyecto_id
                b.modulo_step_id,
                row_number() over(order by b.modulo_step_orden) as orden,
                now(),
                now()
                from regexp_split_to_table(:str_flujo, ',') a
                left join modulo_step b 
                    on b.modulo_step_id::varchar = left(a,strpos(a,'-')-1)
                where right(a,strpos(a,'-')-1)='1'
            ", ["proyecto_id" => $proyecto_id, "str_flujo" => $str_flujo]);

    }

}
