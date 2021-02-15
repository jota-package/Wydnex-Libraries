<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use App;

Trait elementoController
{

    public function crear_elemento($te_id,$elemento_nombre ,$plantilla_id){



        if (($elemento_nombre == "" || $elemento_nombre == null) ||
            ($te_id == "" || $te_id == null) ||
            ($plantilla_id == "" || $plantilla_id == null)) {

            $tipo = 'error';
            $mensaje = 'Ingrese el nombre del elemento';
            return;
            return Controller::crear_objeto($tipo, $mensaje);

        } else {
            $inst_elemento = new App\elemento();
            return $inst_elemento-> crear_elemento($te_id,$elemento_nombre ,$plantilla_id);

            $tipo = 'ok';
            $mensaje = 'Registro de elemento correcto';

            return Controller::crear_objeto($tipo, $mensaje);
        }

    }

    public function actualizar_elemento(){

    }

    public function eliminar_elemento($plantilla_id){

        // $plantilla_id = request("plantilla_id");

        $elemento = App\elemento::where("plantilla_id",$plantilla_id)->delete();

    }

    public function listar_elemento(){

    }


}
