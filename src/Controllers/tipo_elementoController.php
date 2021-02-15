<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use App;

Trait tipo_elementoController
{
    public function crear_tipo_elemento($te_nombre,$te_abreviacion,
                                        $elemento_id, $plantilla_id){



        if ($te_nombre == "" || $te_nombre == null ||
            $te_abreviacion == "" || $te_abreviacion == null ||
            $elemento_id == "" || $elemento_id == null ||
            $plantilla_id == "" || $plantilla_id == null) {

            $tipo = 'error';
            $mensaje = 'Ingrese el nombre del tipo de elemento';

            return Controller::crear_objeto($tipo, $mensaje);

        } else {
            $inst_tipo_elemento = new App\tipo_elemento();
            $inst_tipo_elemento->crear_tipo_elemento($te_nombre,$te_abreviacion, $elemento_id, $plantilla_id);

            $tipo = 'ok';
            $mensaje = 'Registro de tipo de elemento correcto';

            return Controller::crear_objeto($tipo, $mensaje);
        }

    }

    public function actualizar_tipo_elemento(){

    }

    public function eliminar_tipo_elemento(){

    }

    public function listar_tipo_elemento(){

    }
}
