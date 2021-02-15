<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use View;
use Response;
use App;

Trait wakaController
{
    public function lista_proyecto_recepcion()
    {

        $data2 = App\proyecto::
                    select(
                        /*"proyecto_id as id",*/
                        "proyecto_nombre as text",
                        "proyecto_id as id_proyecto"
                        )
                    ->get();

        foreach ($data2 as $key => $value) {

            $hijos = App\recepcion::
                    where("proyecto_id",$value->id_proyecto)
                    ->select(
                    /*"recepcion_id as id",*/
                    "recepcion_nombre as text",
                    "recepcion_id as id_recepcion"
                    )
                    ->get();

            $value['children'] = $hijos;

        }

        // $data = App\cliente::
        //             select(
        //                 "cliente_id",
        //                 "cliente_nombre"
        //             )
        //             ->with("proyectos")
        //             // ->where("cliente_id",1)
        //             ->get();

        return $data2;

    }

    public function listar_recepcion_captura(){

        $recepcion_id = request("recepcion_id");

        $data2 = App\documento::join("recepcion as re","re.documento_id","documento.documento_id")->where("recepcion_id",$recepcion_id)->get();

        // where("recepcion_id",$recepcion_id)

        return $data2;

    }

    public function waka()
    {

        $data = App\cliente::
                    select(
                        "cliente_id",
                        "cliente_nombre"
                    )
                    ->with("children")
                    // ->where("cliente_id",1)
                    ->get();

        return $data;


    }

    public function iniciar_escaneo()
    {

        $usuario_id = session("usuario_id");

        $tiempo_escaneo_inicio = Carbon::now();

        $save = new App\tiempo_escaneo;

        $save->usuario_id = $usuario_id;
        $save->tiempo_escaneo_inicio = $tiempo_escaneo_inicio;
        $save->tiempo_escaneo_estado = 1;

        $save->save();

        if( $save ){

            return $save->tiempo_escaneo_id;

        }
        else{

            return "";

        }



    }

    public function finalizar_escaneo()
    {

        $tiempo_escaneo_id = request("tiempo_escaneo_id");

        $tiempo_escaneo_fin = Carbon::now();

        $save = App\tiempo_escaneo::where("tiempo_escaneo_id",$tiempo_escaneo_id)
                        ->update([
                            "tiempo_escaneo_fin" => $tiempo_escaneo_fin
                        ]);

        if( $save ){

            return "termino el escaneo backend";

        }

        else{

            return "";

        }



    }

    public function descargar_instalador_twain()
    {
        //Antiguo
        return response()->download('../storage/app/archivos/Instalador.msi');
        // return response()->download('../storage/app/documentos/beiORHbc2cJ23oO5vhOmD3KLUFD8AXQXPPFLfym5.jpeg');

    }

    public function descargar_instalador_twain2()
    {
        //Nuevo
        return response()->download('../storage/app/archivos/Instalador2.msi');
        // return response()->download('../storage/app/documentos/beiORHbc2cJ23oO5vhOmD3KLUFD8AXQXPPFLfym5.jpeg');

    }

    public function descargar_instalador_twain3()
    {
        //Nuevo
        return response()->download('../storage/app/archivos/ScannerApp_v8.exe');
        // return response()->download('../storage/app/documentos/beiORHbc2cJ23oO5vhOmD3KLUFD8AXQXPPFLfym5.jpeg');

    }


}
