<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use View;
use Response;
use App;

Trait usuarioController
{

    public function __construct()
    {

         $this->middleware('auth');
        parent::__construct();

    }

    public function index()
    {

        $usuarios = App\User::join('persona as p', 'p.usuario_id', '=', 'usuario.usuario_id')
            ->join('usuario_perfil as up', 'up.usuario_id', '=', 'p.usuario_id')
            ->join('perfil as per', 'per.perfil_id', '=', 'up.perfil_id')
            ->select(
                'usuario.name',
                'p.usuario_id',
                'persona_id',
                'persona_nombre',
                'persona_apellido',
                'persona_correo',
                'estado'

            )
            ->distinct()
            ->orderBy('p.usuario_id', 'ASC')
            ->get();

        foreach ($usuarios as $key => $value) {

            $perfiles = App\usuario_perfil::join("perfil as p", "p.perfil_id", "usuario_perfil.perfil_id")
                ->where("usuario_id", $value->usuario_id)
                ->select("p.perfil_id", "p.perfil_nombre")
                ->get();

            $value['perfiles'] = $perfiles;


        }

        $perfiles = App\perfil::get();


        return view::make('usuario.index.content')
            ->with('usuarios', $usuarios)
            ->with("perfiles", $perfiles);

    }

    public function usuario_activo()
    {

        $usuarios = App\User::join('persona as p', 'p.usuario_id', '=', 'usuario.usuario_id')
            ->join('usuario_perfil as up', 'up.usuario_id', '=', 'p.usuario_id')
            ->join('perfil as per', 'per.perfil_id', '=', 'up.perfil_id')
            ->where('usuario.estado',1)
            ->select(
                'usuario.name',
                'p.usuario_id',
                'persona_id',
                'persona_nombre',
                'persona_apellido',
                'persona_correo',
                'estado'

            )
            ->distinct()
            ->orderBy('p.usuario_id', 'ASC')
            ->get();

        foreach ($usuarios as $key => $value) {

            $perfiles = App\usuario_perfil::join("perfil as p", "p.perfil_id", "usuario_perfil.perfil_id")
                ->where("usuario_id", $value->usuario_id)
                ->select("p.perfil_id", "p.perfil_nombre")
                ->get();

            $value['perfiles'] = $perfiles;


        }

        $perfiles = App\perfil::get();


        return view::make('usuario.index.content')
            ->with('usuarios', $usuarios)
            ->with("perfiles", $perfiles);


    }


    public function usuario_crear(Request $request)
    {

        //Definimos variable que traemos del post
        $password_usuario = request('password-usuario');
        $nombre_usuario = request('nombre-usuario');
        $apellido_usuario = request('apellido-usuario');
        $dni_usuario = request('dni-usuario');
        $telefono_usuario = request('telefono-usuario');
        $correo_usuario = strtolower(request('correo-usuario'));
        $direccion_usuario = strtolower(request('direccion-usuario'));

        $perfil_usuario = request('perfil-usuario');


        //Validamos que los campos esten completos
        if ($password_usuario == '' || $nombre_usuario == '' || $apellido_usuario == '' || $dni_usuario == '' || $perfil_usuario == '') {

            return $this->crear_objeto('error','Llene todos los Campos');

        }

        $validar_dni = App\User::where("name", $dni_usuario)->count();

        if ($validar_dni >= 1) {


            return $this->crear_objeto('error','Este DNI ya se encuentra registrado');

        }

        if ($password_usuario !== '' && $password_usuario !== null) {

            $errors = "";
            $instancia = new perfilController();
            $valor = $instancia->checkPassword($password_usuario, $errors);

            if ($valor == 1) {
                //Definimos escritura en la BD

                $usuario = new App\User;

                $usuario->name = $dni_usuario;
                $usuario->password = bcrypt($password_usuario);
                $usuario->estado = 1;
                $usuario->fecha_expiracion = Carbon::now()->addMonth();
                $usuario->intentos = 0;

                $usuario->save();

                $persona = new App\persona;

                $persona->usuario_id = $usuario->usuario_id;
                $persona->documento_id = 1;//dni
                $persona->persona_nombre = $nombre_usuario;
                $persona->persona_apellido = $apellido_usuario;
                $persona->persona_telefono = $telefono_usuario;
                $persona->persona_correo = $correo_usuario;
                $persona->persona_documento = $dni_usuario;
                $persona->persona_direccion = $direccion_usuario;

                $save = $persona->save();

                $per = new App\usuario_perfil;

                $per->usuario_id = $usuario->usuario_id;
                $per->perfil_id = $perfil_usuario;
                $per->usuario_asignador = session("perfil_id");

                $per->save();


                //Si no se ejecuto los query devolvemos error
                if (!$save) {
                    App::abort(500, 'Error');
                }

                return $this->crear_objeto("ok", "Completado");
            } else {
                return $this->crear_objeto("error", $valor);
            }
        }
    }

    public function usuario_ver_datos(Request $request)
    {

        $usuario_actual = request('usuario_actual');

        $datos = App\persona::join('usuario as u', 'u.usuario_id', '=', 'persona.usuario_id')
            ->join('usuario_perfil as up', 'up.usuario_id', '=', 'persona.usuario_id')
            ->join('perfil as p', 'p.perfil_id', '=', 'up.perfil_id')
            ->where('persona.usuario_id', $usuario_actual)
            ->first();

        return $datos;


    }


    public function usuario_editar(Request $request)
    {

        //Definimos variable que traemos del post
        $usuario_actual = request('usuario_actual');

        $username_usuario = request('dni-usuario');
        $password_usuario = request('password-usuario');
        $nombre_usuario = request('nombre-usuario');
        $apellido_usuario = request('apellido-usuario');
        $dni_usuario = request('dni-usuario');
        $telefono_usuario = request('telefono-usuario');
        $correo_usuario = strtolower(request('correo-usuario'));
        $direccion_usuario = strtolower(request('direccion-usuario'));

        // $perfil_usuario = request('perfil-usuario');
        $perfil_usuario = request('perfil_actual');


        if ($username_usuario == '' || $nombre_usuario == '' || $apellido_usuario == '' || $dni_usuario == '' || $perfil_usuario == '') {

            return $this->crear_objeto('error','llene todos los campos');

        }

        $validar_dni = App\User::where("name", $dni_usuario)->count();

        //Escribimos en las tablas
        $save = App\User::where('usuario_id', $usuario_actual)->first();

        if ($validar_dni >= 1 && $save->persona['persona_documento'] != $dni_usuario) {


            return $this->crear_objeto('error','Este DNI ya se encuentra registrado');

        }


        $save->name = $username_usuario;

        if ($password_usuario != '' && $password_usuario != null) {

            $errors = "";
            $instancia = new perfilController();

            $valor = $instancia->checkPassword($password_usuario, $errors);

            if( $valor != 1 ){

                return $this->crear_objeto("error",$valor);

            }

            $save->password = bcrypt($password_usuario);

            $save->fecha_expiracion = Carbon::now()->addMonth();
            $save->intentos = 0;

        }

        $save->persona->persona_nombre = $nombre_usuario;
        $save->persona->persona_apellido = $apellido_usuario;
        $save->persona->persona_telefono = $telefono_usuario;
        $save->persona->persona_correo = $correo_usuario;
        $save->persona->persona_documento = $dni_usuario;
        $save->persona->persona_direccion = $direccion_usuario;

        $save->push();

        //Validamos notificacion

        $wa = App\notificacion::where("usuario_id", $save['usuario_id'])
            ->where("cn_id", 1);

        if ($wa->count() > 0) {

            $wa->update([
                "notificacion_estado" => 2
            ]);

        }


        //Si no se ejecuto los query devolvemos error
        if (!$save) {

            return $this->crear_objeto("error","Hubo un problema con el registro, intentelo luego");

        }

        return $this->crear_objeto("ok","Usuario Actualizado");

    }

    public function usuario_estado(Request $request)
    {

        $usuario_actual = request("usuario_actual");
        $estado = request("estado");
        $sesion_id = session("sesion_id");

        if( $estado == 1 ){

            $save = App\User::where('usuario_id',$usuario_actual)
                            ->update([
                                'estado' => $estado,
                                'intentos' => 0
                                ]);

                //Si no se ejecuto los query devolvemos error
                if(!$save){

                    App::abort(500, 'Error');

                }

                return response('ok', 200);
            
        }
        else if( $estado == 0 ){

            //Validamos el equipo
            $equipo = App\equipo::where("usuario_id",$usuario_actual)
                    ->count();

            //Si tiene un equipo asociado no se le puede dar de baja
            if( $equipo > 0 ){

                return "Este usuario esta asignado a un proyecto, desasignelo antes de darle de baja";

            }




            //Validamos la sesion
            $validar_sesion = App\sesion::where("usuario_id",$usuario_actual)
                        ->latest()
                        ->first();

            //Si el usuario tiene una sesion registrada
            if( $validar_sesion != null && $validar_sesion != '' ){

                //Si el usuario tiene una sesion activa
                if( $validar_sesion['sesion_estado'] == 1 ){

                    return "Este usuario tiene una sesión activa";

                }
                else{

                    $save = App\User::where('usuario_id',$usuario_actual)
                                ->update(['estado' => $estado]);

                    //Si no se ejecuto los query devolvemos error
                    if(!$save){

                        App::abort(500, 'Error');

                    }

                    return response('ok', 200);

                }

                


                }
            //Si el usuario no ha logeado y por ende no tiene una sesion registrada, se puede cambiar el estado
            else{

                $save = App\User::where('usuario_id',$usuario_actual)
                                ->update(['estado' => $estado]);

                //Si no se ejecuto los query devolvemos error
                if(!$save){

                    App::abort(500, 'Error');

                }

                return response('ok', 200);

            }


        }

    }

    public function usuario_perfiles_asignados()
    {


        $usuario_actual = request("usuario_actual");


        $perfiles = App\usuario_perfil::
        join("perfil as p", "p.perfil_id", "usuario_perfil.perfil_id")
            ->where("usuario_id", $usuario_actual)
            ->select(
                "p.perfil_id",
                "p.perfil_nombre"
            )
            ->get();

        return $perfiles;


    }

    public function usuario_asignar_perfil()
    {

        $perfiles_elegidos = request("perfiles_elegidos");
        $usuario_actual = request("usuario_actual");

        //Iteramos el array enviado desde el frontend para convertirlo en una solo sentencia SQL
        $array = [];
        $array_total1 = [];//
        $array_total2 = [];
        $array_total3 = [];

        $validar_equipo = App\equipo::join("usuario_perfil as up", "up.usuario_id", "equipo.usuario_id")
            ->where("equipo.usuario_id", $usuario_actual)

            ->select("equipo.perfil_id")
            ->distinct("equipo.perfil_id")
            ->get();

        foreach ($perfiles_elegidos as $key => $value) {
            $array_total1[] = intval($value);

        }

        foreach ($validar_equipo as $key => $value) {
            $array_total2[] = $value['perfil_id'];

        }

        foreach ($array_total2 as $key => $value) {
            if (in_array($value, $array_total1)) {

            } else {
                $array_total3[] = $value;
            }
        }

        $perfiles_lista = App\perfil::whereIn("perfil_id", $array_total3)->get();

        $respuesta="";

        foreach ($perfiles_lista as $key => $value) {

            $respuesta .=$value['perfil_nombre'].", ";

        }

        $sesion_activa = App\sesion::where("usuario_id", $usuario_actual)->orderBy('created_at', 'DESC')->first();


        if ($sesion_activa['sesion_estado'] == 0) {
            if ($perfiles_lista->count() == 0) {


                foreach ($perfiles_elegidos as $key => $value) {

                    $objeto = [];

                    $objeto['perfil_id'] = $value;
                    $objeto['usuario_id'] = $usuario_actual;
                    $objeto['usuario_asignador'] = session("usuario_id");

                    $array[] = $objeto;

                }
                App\usuario_perfil::where("usuario_id", $usuario_actual)->delete();

                App\usuario_perfil::insert($array);


                if (session("usuario_id") == $usuario_actual) {

                    session()->put("perfiles", $perfiles_elegidos);

                }

                return $this->crear_objeto("ok", ' Asignación de perfiles completada'  );;

            } else {

                return $this->crear_objeto("error", 'El usuario tiene 
                los siguientes perfiles agregados a un proyecto: ' .$respuesta );
            }

        } else {
            return $this->crear_objeto("error", "El usuario se encuentra conectado actualmente");
        }
    }

    public function cambiar_perfil()
    {

        $perfil_id = request("id");

        $datos = App\User::join('usuario_perfil as up', 'up.usuario_id', '=', 'usuario.usuario_id')
            ->join('perfil as per', 'per.perfil_id', '=', 'up.perfil_id')
            ->where("usuario.usuario_id", session("usuario_id"))
            ->where("per.perfil_id", $perfil_id)
            ->first();

        session()->put("datos_personales", $datos);

        switch ($perfil_id) {

            case '1':

                return redirect()
                    ->action('usuarioController@perfil_admin');

                break;

            case '2':
                session()->put('cons', '1');
                session()->put('cons_n', 'Consultorio');
                return redirect()
                    ->action('usuarioController@perfil_medico');

                break;

            case '3':

                return redirect()
                    ->action('usuarioController@perfil_laboratorista');

                break;
            case '4':

                return redirect()
                    ->action('usuarioController@perfil_farmaceutico');

                break;
            case '5':

                return redirect()
                    ->action('usuarioController@perfil_asistente');

                break;
            case '6':

                return redirect()
                    ->action('usuarioController@perfil_vendedor');

                break;
            case '7':

                return redirect()
                    ->action('usuarioController@perfil_paciente');

                break;
            case '9':
                session()->put('cons', 1);
                session()->put('cons_n', "Consultorio");
                return redirect()
                    ->action('usuarioController@perfil_clinica');

                break;

            default:
                # code...
                break;
        }

        return redirect()
            ->action('usuarioController@perfil_admin');

    }

    public function perfil_admin()
    {

        $datos = session("datos_personales");

        $datos_persona = App\persona::where("usuario_id", $datos['usuario_id'])
            ->leftjoin("adetalle as ad", "ad.archivo_id", "=", "persona.archivo_id")
            ->first();

        session()->put('usuario_id', $datos['usuario_id']);
        session()->put('perfil_id', $datos['perfil_id']);
        session()->put('perfil_nombre', $datos['perfil_nombre']);
        session()->put('persona_id', $datos_persona['persona_id']);
        session()->put('persona_nombre', $datos_persona['persona_nombre']);
        session()->put('persona_apellido', $datos_persona['persona_apellido']);
        session()->put('persona_correo', $datos_persona['persona_correo']);
        session()->put('foto_url', $datos_persona['adetalle_url']);

        return redirect()->route('dashboard');

    }

    public function perfil_indizador()
    {

        $datos = session("datos_personales");

        $datos_persona = App\persona::where("usuario_id", $datos['usuario_id'])
            ->leftjoin("adetalle as ad", "ad.archivo_id", "=", "persona.archivo_id")
            ->first();

        session()->put('usuario_id', $datos['usuario_id']);
        session()->put('perfil_id', $datos['perfil_id']);
        session()->put('perfil_nombre', $datos['perfil_nombre']);
        session()->put('persona_id', $datos_persona['persona_id']);
        session()->put('persona_nombre', $datos_persona['persona_nombre']);
        session()->put('persona_apellido', $datos_persona['persona_apellido']);
        session()->put('persona_correo', $datos_persona['persona_correo']);
        session()->put('foto_url', $datos_persona['adetalle_url']);

        return redirect()->route('dashboard');

    }

    public function perfil_control()
    {

        $datos = session("datos_personales");

        $datos_persona = App\persona::where("usuario_id", $datos['usuario_id'])
            ->leftjoin("adetalle as ad", "ad.archivo_id", "=", "persona.archivo_id")
            ->first();

        session()->put('usuario_id', $datos['usuario_id']);
        session()->put('perfil_id', $datos['perfil_id']);
        session()->put('perfil_nombre', $datos['perfil_nombre']);
        session()->put('persona_id', $datos_persona['persona_id']);
        session()->put('persona_nombre', $datos_persona['persona_nombre']);
        session()->put('persona_apellido', $datos_persona['persona_apellido']);
        session()->put('persona_correo', $datos_persona['persona_correo']);
        session()->put('foto_url', $datos_persona['adetalle_url']);

        return redirect()->route('dashboard');

    }

    public function perfil_reproceso()
    {

        $datos = session("datos_personales");

        $datos_persona = App\persona::where("usuario_id", $datos['usuario_id'])
            ->leftjoin("adetalle as ad", "ad.archivo_id", "=", "persona.archivo_id")
            ->first();

        session()->put('usuario_id', $datos['usuario_id']);
        session()->put('perfil_id', $datos['perfil_id']);
        session()->put('perfil_nombre', $datos['perfil_nombre']);
        session()->put('persona_id', $datos_persona['persona_id']);
        session()->put('persona_nombre', $datos_persona['persona_nombre']);
        session()->put('persona_apellido', $datos_persona['persona_apellido']);
        session()->put('persona_correo', $datos_persona['persona_correo']);
        session()->put('foto_url', $datos_persona['adetalle_url']);

        return redirect()->route('dashboard');

    }

    public function perfil_fedatario()
    {

        $datos = session("datos_personales");

        $datos_persona = App\persona::where("usuario_id", $datos['usuario_id'])
            ->leftjoin("adetalle as ad", "ad.archivo_id", "=", "persona.archivo_id")
            ->first();

        session()->put('usuario_id', $datos['usuario_id']);
        session()->put('perfil_id', $datos['perfil_id']);
        session()->put('perfil_nombre', $datos['perfil_nombre']);
        session()->put('persona_id', $datos_persona['persona_id']);
        session()->put('persona_nombre', $datos_persona['persona_nombre']);
        session()->put('persona_apellido', $datos_persona['persona_apellido']);
        session()->put('persona_correo', $datos_persona['persona_correo']);
        session()->put('foto_url', $datos_persona['adetalle_url']);

        return redirect()->route('dashboard');

    }

    public function perfil_generador()
    {

        $datos = session("datos_personales");

        $datos_persona = App\persona::where("usuario_id", $datos['usuario_id'])
            ->leftjoin("adetalle as ad", "ad.archivo_id", "=", "persona.archivo_id")
            ->first();

        session()->put('usuario_id', $datos['usuario_id']);
        session()->put('perfil_id', $datos['perfil_id']);
        session()->put('perfil_nombre', $datos['perfil_nombre']);
        session()->put('persona_id', $datos_persona['persona_id']);
        session()->put('persona_nombre', $datos_persona['persona_nombre']);
        session()->put('persona_apellido', $datos_persona['persona_apellido']);
        session()->put('persona_correo', $datos_persona['persona_correo']);
        session()->put('foto_url', $datos_persona['adetalle_url']);

        return redirect()->route('dashboard');

    }


}
