<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use View;
use Response;
use App;

Trait dashboardController
{

    public function __construct()
    {

        $this->middleware('auth');

    }


    public function index()
    {
        // return session("perfiles");
        return view::make('dashboard.admin.content');

    }


    public function listar_cita()
    {
        $usuario_id_actual = session("usuario_id");
        // $usuario_id_actual = 1;

        $id_perfil_actual = session("perfil_id");
        // $id_perfil_actual = 1;

        $tabla_cita= App\cita::ver_cita($id_perfil_actual, $usuario_id_actual)->get();

        return $tabla_cita;

    }

}
