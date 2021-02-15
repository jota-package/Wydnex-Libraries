<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use App;
use View;
use Carbon\carbon;
use DB;

Trait ivanController
{

    public function retornar_lista()
    {

        //Instancia Documento
        $ins_documento = new App\documento();

        $lista_documentos = $ins_documento->listar_documento();

        return $lista_documentos;

    }

    public function retornar_url($documento_id){

        //Instancia Documento
        $ins_documento = new App\documento();
        return $lista_url_imagenes= $ins_documento->listar_url($documento_id);

    }

    public function prueba(){

        $gmd_id = 86;
        $elementos =  DB::select(
            '
               select a.gmd_id,
                c.captura_id,
                c.captura_estado,
                ade.adetalle_nombre,
                e.cant_paginas
                from generacion_medio_detalle a
                join generacion_medio_detalle_captura b on a.gmd_id = b.gmd_id
                join captura c  on c.captura_id = b.captura_id
                join documento d on d.captura_id = c.captura_id
                join adetalle ade on ade.adetalle_id = d.adetalle_id
                left join (select distinct max(imagen_pagina::int)over(partition by documento_id) as cant_paginas,documento_id from imagen) e
                         on e.documento_id = d.documento_id
                where a.gmd_id = :gmd_id and c.captura_estado = 1
                order by c.captura_id
            ', ['gmd_id' => $gmd_id]);

        $elementos_calibradora =  DB::select(
            '
            select
                   b.captura_id,
                   c.documento_id,
                   c.documento_nombre,
                   c.documento_estado,
                   e.cant_paginas,
                   b.captura_estado
            from generacion_medio_detalle_captura a
                   join captura b on a.captura_id = b.captura_id
                   join documento c on c.captura_id = b.captura_id
                   join adetalle ade on ade.adetalle_id = c.adetalle_id
                   left join (select distinct max(imagen_pagina::int)over(partition by documento_id) as cant_paginas,documento_id from imagen) e
                     on e.documento_id = c.documento_id
            where a.gmd_id = :gmd_id and b.captura_estado in (2,4)
            ', ['gmd_id' =>$gmd_id]);

        $elementos_cierre =  DB::select(
            '
              select
                   b.captura_id,
                   c.documento_id,
                   c.documento_nombre,
                   c.documento_estado,
                   e.cant_paginas,
                   b.captura_estado
            from generacion_medio_detalle_captura a
                   join captura b on a.captura_id = b.captura_id
                   join documento c on c.captura_id = b.captura_id
                   join adetalle ade on ade.adetalle_id = c.adetalle_id
                   left join (select distinct max(imagen_pagina::int)over(partition by documento_id) as cant_paginas,documento_id from imagen) e
                     on e.documento_id = c.documento_id
            where a.gmd_id = :gmd_id and b.captura_estado in (3,5)
            ', ['gmd_id' => $gmd_id]);

        $array_temporal = [];
        $array_temporal['correlativo_1'] = "";
        $array_temporal['adetalle_nombre'] = "";
        $array_temporal['cant_imagenes'] = "";
        $array_temporal['correlativo_2'] = "";
        $array_temporal['calibradora'] ="";
        $array_temporal['cant_imagenes_2'] = "";
        $array_temporal['correlativo_3'] = "";
        $array_temporal['cierre'] = "";
        $array_temporal['cant_imagenes_3'] = "";
        $bloques[] = $array_temporal;

        foreach ($elementos as $key => $cap) {

            $bloques[$key]['correlativo_1'] = $key+1;
            $bloques[$key]['adetalle_nombre'] = $cap->adetalle_nombre;
            $bloques[$key]['cant_imagenes'] = $cap->cant_paginas;
            if(!(isset($bloques[$key]['correlativo_2']))){
                $bloques[$key]['correlativo_2'] = "";
            }
            if(!(isset($bloques[$key]['calibradora']))){
                $bloques[$key]['calibradora'] = "";
            }
            if(!(isset($bloques[$key]['cant_imagenes_2']))){
                $bloques[$key]['cant_imagenes_2'] = "";
            }
            if(!(isset($bloques[$key]['correlativo_3']))){
                $bloques[$key]['correlativo_3'] = "";
            }
            if(!(isset($bloques[$key]['cierre']))){
                $bloques[$key]['cierre'] = "";
            }
            if(!(isset($bloques[$key]['cant_imagenes_3']))){
                $bloques[$key]['cant_imagenes_3'] = "";
            }

        }

        foreach ($elementos_calibradora as $key2 => $val2){

            if(!(isset($bloques[$key2]['correlativo_1']))){
                $bloques[$key2]['correlativo_1'] = "";
            }
            if(!(isset($bloques[$key2]['adetalle_nombre']))){
                $bloques[$key2]['adetalle_nombre'] = "";
            }
            if(!(isset($bloques[$key2]['cant_imagenes']))){
                $bloques[$key2]['cant_imagenes'] = "";
            }

            $bloques[$key2]['correlativo_2'] = $key2+1;
            $bloques[$key2]['calibradora'] = $val2->documento_nombre;
            $bloques[$key2]['cant_imagenes_2'] = $val2->cant_paginas;

            if(!(isset($bloques[$key2]['correlativo_3']))){
                $bloques[$key2]['correlativo_3'] = "";
            }
            if(!(isset($bloques[$key2]['cierre']))){
                $bloques[$key2]['cierre'] = "";
            }
            if(!(isset($bloques[$key2]['cant_imagenes_3']))){
                $bloques[$key2]['cant_imagenes_3'] = "";
            }

        }

        foreach ($elementos_cierre as $key3 => $val3){

            if(!(isset($bloques[$key3]['correlativo_1']))){
                $bloques[$key3]['correlativo_1'] = "";
            }
            if(!(isset($bloques[$key3]['adetalle_nombre']))){
                $bloques[$key3]['adetalle_nombre'] = "";
            }
            if(!(isset($bloques[$key3]['cant_imagenes']))){
                $bloques[$key3]['cant_imagenes'] = "";
            }
            if(!(isset($bloques[$key3]['correlativo_2']))){
                $bloques[$key3]['correlativo_2'] = "";
            }
            if(!(isset($bloques[$key3]['calibradora']))){
                $bloques[$key3]['calibradora'] = "";
            }
            if(!(isset($bloques[$key3]['cant_imagenes_2']))){
                $bloques[$key3]['cant_imagenes_2'] = "";
            }
            $bloques[$key3]['correlativo_3'] = $key3+1;
            $bloques[$key3]['cierre'] = $val3->documento_nombre;
            $bloques[$key3]['cant_imagenes_3'] = $val3->cant_paginas;
        }

        return $bloques;
    }


}
