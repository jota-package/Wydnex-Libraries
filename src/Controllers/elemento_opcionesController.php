<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use App;

Trait elemento_opcionesController
{
    public function crear_elemento_opciones($eo_incremental,
                                            $eo_obligatorio, $eo_multipagina,
                                            $eo_guia, $elemento_id, $plantilla_id_1){




        if ($eo_incremental === "" || $eo_incremental === null ||
            $eo_obligatorio === "" || $eo_obligatorio === null ||
            $eo_multipagina === "" || $eo_multipagina === null ||
            $eo_guia === "" || $eo_guia === null
        ) {

            $tipo = 'error';
            $mensaje = 'Ingrese el nombre del elemento opcion';

            return Controller::crear_objeto($tipo, $mensaje);

        } else {
            $inst_elemento_opciones = new App\elemento_opciones();
            $ol= $inst_elemento_opciones->crear_elemento_opciones($eo_incremental,
                $eo_obligatorio, $eo_multipagina,
                $eo_guia, $elemento_id, $plantilla_id_1);

            $tipo = 'ok';
            $mensaje = 'Registro de elemento opcion correcto';

            return Controller::crear_objeto($tipo, $mensaje);
        }
    }

    public function actualizar_elemento_opciones(){

    }

    public function eliminar_elemento_opciones(){

    }

    public function listar_elemento_opciones(){

    }
}
