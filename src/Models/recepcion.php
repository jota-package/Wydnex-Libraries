<?php

namespace Fedatario\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use DB;

Trait recepcion

{
    use Notifiable;

    protected $primaryKey = "recepcion_id";
    protected $table = "recepcion";

    public function proyecto()
    {
        return $this->belongsTo('App\proyecto',"proyecto_id","proyecto_id");
    }

    public function children()//Todas las recepciones
    {
        $usuario_id = session('usuario_id');
        return $this
                    ->hasMany('App\captura',"recepcion_id")
                    ->select(
                        "captura_id",
                        "recepcion_id"
                    )

                    ->where("captura_estado",1)
                    ->whereNull('usuario_asignado_indizacion')
                    ->orWhere('usuario_asignado_indizacion',$usuario_id)

                    ->with("children")//Esto nos permite concatenar un nivel mas de profundidad
                    ->with("indizacion")
                    ->orderBy("captura_id")
                    ;
    }

    public function children_control_calidad()//Todas las recepciones
    {
        $usuario_id = session('usuario_id');
        return $this
            ->hasMany('App\captura',"recepcion_id")
            ->select(
                "captura_id",
                "recepcion_id",
                'usuario_asignado_control_calidad'
            )

            ->where("captura_estado",1)
            ->whereNull('usuario_asignado_control_calidad')
            ->orWhere('usuario_asignado_control_calidad',$usuario_id)

            ->with("children")//Esto nos permite concatenar un nivel mas de profundidad
            ->with("indizacion")
            ;
    }


    public function children_captura()//Todas las recepciones
    {
        $usuario_id = session('usuario_id');
        return $this
            ->hasMany('App\captura',"recepcion_id")
            ->select(
                "captura_id",
                "recepcion_id"
            )

            ->whereNull('usuario_asignado_indizacion')
            ->orWhere('usuario_asignado_indizacion',$usuario_id)

            //->with("children_captura")//Esto nos permite concatenar un nivel mas de profundidad

            ;
    }



    public function crear_recepcion($cliente_id, $proyecto_id, $recepcion_estado,$recepcion_nombre)
    {

        $recepcion = new recepcion();
            $recepcion->cliente_id = $cliente_id;
            $recepcion->proyecto_id = $proyecto_id;
            $recepcion->recepcion_estado = $recepcion_estado;
            $recepcion->recepcion_nombre = $recepcion_nombre;
        $recepcion->save();


        return $recepcion;

    }
    public function validar_recepcion_existe($recepcion_nombre)
    {
        $recepcion = $this::where('recepcion_nombre', '=', $recepcion_nombre)->first();

        if ($recepcion === null) {
            return 'no';
        }else{
            return 'existe';
        }
    }

    public function recepcion_x_proyecto($proyecto_id){

        return $elementos =  DB::select(
            "
            select
            distinct recepcion_id,recepcion_nombre
            from proyecto p
            left join recepcion r
                on p.proyecto_id = r.proyecto_id
            where p.proyecto_id = any(:proyecto_id::int[])
            ;
            ", ['proyecto_id' => $proyecto_id]);

    }

}
