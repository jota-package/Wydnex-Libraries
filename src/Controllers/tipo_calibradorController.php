<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use App;

Trait tipo_calibradorController
{
    public function listar_tipo_calibrador()
    {
        $is_tc = new App\tipo_calibrador();
        $lista_tipo_calibrador = $is_tc->listar();

        return $lista_tipo_calibrador;
    }

    public function registrar_tipo_calibrador()
    {

        $tc_id = request('tc_id');
        $adetalle_id_sinformato = request('adetalle_id');

        $adetalle_id = str_replace("d","",$adetalle_id_sinformato);


        $is_bloque = App\documento::join("adetalle as ad","ad.adetalle_id","documento.adetalle_id")
            ->join("captura as cap","cap.captura_id","documento.captura_id")
            ->where('ad.adetalle_id', $adetalle_id)->first();



        if($tc_id==8){
            $tc_descripcion = request('tc_descripcion');
            $is_captura = App\captura::where('captura_id', $is_bloque->captura_id)->update([
                'tc_id' => $tc_id,
                'tc_descripcion' => $tc_descripcion
            ]);

        }else{
            $is_captura = App\captura::where('captura_id', $is_bloque->captura_id)->update([
                'tc_id' => $tc_id,
            ]);
        }




        return $is_captura;

    }

    public function tipo_calibrador_valor()
    {

        $adetalle_id = request('adetalle_id');

        $is_documento = new App\documento();

        $tipo_calibrador = $is_documento->join("adetalle as ad","ad.adetalle_id","documento.adetalle_id")
            ->join("captura as cap","cap.captura_id","documento.captura_id")
            ->where('ad.adetalle_id', $adetalle_id)->first();

       // return $tipo_calibrador;
        if (isset($tipo_calibrador['tc_id'])) {
            return [$tipo_calibrador['tc_id'],$tipo_calibrador['tc_descripcion']];
        } else {
            return -1;
        }

    }

}
