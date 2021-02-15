<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use App;
use View;

Trait clienteController
{
    public function index()
    {

    	$clientes = App\cliente::
    			    orderBy('cliente_id','ASC')
					->get();

    	return view::make('cliente.index.content')
					->with('clientes',$clientes)
					;

    }

    public function cliente_crear(Request $request)
    {

    	//Definimos variable que traemos del post
    	//$nombre_cliente = strtolower( request('nombre-cliente') );
    	$nombre_cliente = request('nombre-cliente');
    	$ruc_cliente = request('ruc-cliente');
        $correo_cliente = strtolower(request('correo-cliente'));
    	$telefono_cliente = request('telefono-cliente');
		$direccion_cliente = strtolower(request('direccion-cliente'));
		$tipo_institucion_cliente = request('tipo-institucion-cliente');


		$representante = request('representante-cliente');
		$memorandum = request('memorandum-cliente');
		$codigo = request('codigo-cliente');





		//Validamos que los campos esten completos
		// if( $nombre_cliente == '' || $ruc_cliente == '' || $correo_cliente == '' || $telefono_cliente == '' || $direccion_cliente == '' || $representante == '' || $memorandum == '' || $codigo == '' ){
			if( $nombre_cliente == '' ){

			return response('Llene todos los Campos', 200);

		}

    	//Definimos escritura en la BD

		$save = new App\cliente;

			$save->cliente_ruc = $ruc_cliente;
			$save->cliente_nombre = $nombre_cliente;
			$save->cliente_correo = $correo_cliente;
			$save->cliente_telefono = $telefono_cliente;
			$save->cliente_direccion = $direccion_cliente;
			$save->cliente_tipo_institucion = $tipo_institucion_cliente;
			$save->cliente_estado = 1;
			$save->cliente_representante = $representante;
			$save->cliente_memorandum = $memorandum;
			$save->cliente_codigo = $codigo;

        $save->save();


		//Si no se ejecuto los query devolvemos error
		if(!$save){
			App::abort(500, 'Error');
		}

		return response('ok', 200);

    }

    public function cliente_ver_datos(Request $request)
    {

    	$cliente_actual = request('cliente_actual');

    	$datos = App\cliente::
                    where('cliente_id',$cliente_actual)
					->first();

    	return $datos;


    }



    public function cliente_editar(Request $request)
    {

    	//Definimos variable que traemos del post
    	$cliente_actual = request('cliente_actual');

    	$nombre_cliente = strtolower( request('nombre-cliente') );
    	$ruc_cliente = request('ruc-cliente');
        $correo_cliente = strtolower(request('correo-cliente'));
    	$telefono_cliente = request('telefono-cliente');
        $direccion_cliente = strtolower(request('direccion-cliente'));
		$tipo_institucion_cliente = request('tipo-institucion-cliente');

		$representante = request('representante-cliente');
		$memorandum = request('memorandum-cliente');
		$codigo = request('codigo-cliente');


		// if( $nombre_cliente == '' || $ruc_cliente == '' || $correo_cliente == '' || $telefono_cliente == '' || $direccion_cliente == '' || $representante == '' || $memorandum == '' || $codigo == '' ){
			if( $nombre_cliente == '' ){

				return response('Llene todos los Campos', 200);

		}

		//Escribimos en las tablas
		$save = App\cliente::where('cliente_id',$cliente_actual)->first();

			$save->cliente_ruc = $ruc_cliente;
			$save->cliente_nombre = $nombre_cliente;
			$save->cliente_correo = $correo_cliente;
			$save->cliente_telefono = $telefono_cliente;
			$save->cliente_direccion = $direccion_cliente;
			$save->cliente_tipo_institucion = $tipo_institucion_cliente;
			$save->cliente_representante = $representante;
			$save->cliente_memorandum = $memorandum;
			$save->cliente_codigo = $codigo;

		$save->push();


		//Si no se ejecuto los query devolvemos error
		if(!$save){

			App::abort(500, 'Error');

		}

		return response('ok', 200);

    }

   	public function cliente_estado(Request $request)
   	{

   		$cliente_actual = request('cliente_actual');
		$estado = request('estado');
		
			if( $estado == 0 ){

				$proyecto_asociado = App\proyecto::where("cliente_id",$cliente_actual)
										->count();

				if( $proyecto_asociado > 0 ){

					return "Este Cliente se encuentra asociado a un proyecto, no se le puede dar de baja";

				}

			}		

		

   		$save = App\cliente::where('cliente_id',$cliente_actual)
   				->update(['cliente_estado' => $estado]);

   		//Si no se ejecuto los query devolvemos error
		if(!$save){

			App::abort(500, 'Error');

		}

		return response('ok', 200);

	}
}
