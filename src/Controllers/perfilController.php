<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use View;
use Response;
use App;

Trait perfilController
{
    public function index()
    {

        // $id_usuario_actual = session('usuario_id');
        $id_persona_actual = session('persona_id');
        $id_perfil_actual = session('perfil_id');

        $dat = App\persona::where('persona_id', $id_persona_actual)
            ->leftjoin("adetalle as ad", "ad.archivo_id", "=", "persona.archivo_id")
            ->first();

        $cargo = App\perfil::where('perfil_id', $id_perfil_actual)
            ->first();

        return view::make('perfil.index.content')
            ->with("dat", $dat)
            ->with("cargo", $cargo);


    }

    public function actualizar_perfil(Request $request)
    {

        $nombre = $request->input('nombre-persona');
        $apellido = $request->input('apellido-persona');
        $correo = $request->input('correo-persona');
        $telefono = $request->input('telefono-persona');
        $contra = $request->input('contra-persona');
        $direccion = $request->input('direccion-persona');

        $id_usuario_actual = session('usuario_id');
        $id_persona_actual = session('persona_id');


        if ($contra !== '' && $contra !== null) {

            $errors = "";
            $valor = $this->checkPassword($contra, $errors);


            if ($valor == 1) {
                App\User::where('usuario_id', $id_usuario_actual)
                    ->update([

                        "password" => bcrypt($contra),
                        "fecha_expiracion" => Carbon::now()->addMonth(),
                        "intentos" => 0

                    ]);

            } else {
                return $this->crear_objeto("error", $valor);
            }

        }


        if (request()->file('foto_persona')) {

            $nombre_subido = request()->file('foto_persona')->store('public');
            $nombre_original = $request->file('foto_persona')->getClientOriginalName();

            $archivo = new App\archivo;
            $archivo->save();

            $ag = new App\adetalle;
            $ag->adetalle_url = $nombre_subido;
            $ag->adetalle_nombre = $nombre_original;
            $ag->archivo_id = $archivo->archivo_id;
            $ag->save();

        } else {

            $archivo = array("archivo_id" => null);

        }

        $array_usuario = [
            'persona_nombre' => $nombre,
            'persona_apellido' => $apellido,
            'persona_correo' => $correo,
            'persona_telefono' => $telefono,
            'persona_direccion' => $direccion
        ];

        if( request()->file('foto_persona') ){

            $array_usuario['archivo_id'] = $archivo['archivo_id'];

        }


        $busc = App\persona::where('persona_id', $id_persona_actual)->update($array_usuario);


        


        session()->put('persona_nombre', $nombre);
        session()->put('persona_apellido', $apellido);

        if (request()->file('foto_persona')) {

            session()->put('foto_url', $nombre_subido);

        }
        else {

            // session()->put('foto_url', "");

        }

        if ($busc) {

                return $this->crear_objeto("ok", "Completado");

        }
        else {

            return $this->crear_objeto("error", "No completado");

        }


    }


    public function checkPassword($pwd, $errors)
    {

        $sub_errors="<div>La contraseña debe contener:<div>" ;

        $contador = 0;
        if (strlen($pwd) < 6) {
            $sub_errors.="<li>Más de 6 caracteres!</li>" ;
            
            $contador++;
        }


        if (!preg_match("#[0-9]+#", $pwd)) {
            $sub_errors.= "<li>Por lo menos un número!</li>";
            
            $contador++;
        }


        if (!preg_match("#[a-zA-Z]+#", $pwd)) {
            $sub_errors.="<li>Por lo menos una letra!</li>";
            
            $contador++;
        }

        if (!preg_match("@[A-Z]@", $pwd)) {
            $sub_errors.="<li>Por lo menos una mayúscula!</li>";
            
            $contador++;
        }

        if (!preg_match("@[a-z]@", $pwd)) {
            $sub_errors.="<li>Por lo menos una minúscula!</li>";
            
            $contador++;
        }

        if ($contador == 0) {
            return 1;
        } else {


            $errors=  $errors.$sub_errors;

            return $errors;
        }


    }
}
