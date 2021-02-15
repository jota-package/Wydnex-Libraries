<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use View;
use Response;
use App;

Trait cronController
{
    public function liberar_bloqueo()
    {
        $instancia = new App\User();

        $actualizar = $instancia->where("estado", 2)
            ->update([
                "estado" => 1,
                "intentos"=> 0
            ]);

    }
}
