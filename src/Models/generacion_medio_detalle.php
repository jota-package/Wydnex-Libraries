<?php

namespace Fedatario\Controllers;

use Illuminate\Database\Eloquent\Model;
use DB;

Trait generacion_medio_detalle
{
    protected $primaryKey = "gmd_id";
    protected $table = "generacion_medio_detalle";

    public function confirmar_gmd($gmd_id){

        return  $res_gmd = $this->join("generacion_medio_detalle_captura as gmdc","gmdc.gmd_id","generacion_medio_detalle.gmd_id")
            ->leftjoin("captura as cap","cap.captura_id","gmdc.captura_id")
            ->leftjoin("documento as doc","doc.captura_id","cap.captura_id")
            ->leftjoin("adetalle as ad","ad.adetalle_id","doc.adetalle_id")
            ->select("generacion_medio_detalle.gmd_id","gmdc_id","adetalle_url")
            ->where("generacion_medio_detalle.gmd_id",$gmd_id)
            ->get();
    }

}
