<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use View;
use Response;
use App;

Trait dropsController
{
    public function index()
    {

        return view('drops.index');
    }

    public function dropo(Request $request)
    {

        //$file = $request->file('file');


        $subir_archivo = request()->file('file')->store('archivos'); // guarda en el servidor con un nombre modificado (HASH)
        $nombre_original = request()->file('file')->getClientOriginalName(); // Captura el nombre del archivo enviado de front-end

        //$subir_archivo = $file->store('archivos');
        //$nombre_original = $file->getClientOriginalName();

        $ag = new App\adetalle;
        $ag->adetalle_url = $subir_archivo;
        $ag->adetalle_nombre = str_replace(" ", "_", $nombre_original);
        $ag->save();
        //$file->storeAs('guardado',$file->getClientOriginalName());
        //getFilename() da el nombre temporal
        //getClientOriginalName(); da el nombre original
        return $ag->adetalle_id;
    }

    public function descargar($a)
    {


        $idarchivo = $a;

        $archivo = App\adetalle::where('adetalle_id', $idarchivo)->first();

        /*$headers = 	array(
                    	'Content-Type'=> $we
                    );*/

        $path = env("APP_URL");

        $v= "../storage/app/". $archivo->adetalle_url;


        //TODO: you have to split the file name from url
        return response()->download($v,$archivo->adetalle_nombre);
    }

    public function borrar(Request $request)
    {
        $idadetalle = $request->input('adetalle_id');

        $ida = $idadetalle;

        App\adetalle::where('adetalle_id', $ida)->delete();

        return 'ok';
    }

    public function descargar_archivo(Request $request)
    {

            $adetalle_id = request('adetalle_id');

        $archivo = App\adetalle::where('adetalle_id', $adetalle_id)->first();

        /*$headers = 	array(
                    	'Content-Type'=> $we
                    );*/

        $path = env("APP_URL");

        $v= "../storage/app/". $archivo->adetalle_url;


        //TODO: you have to split the file name from url
        return response()->download($v);
    }

}
