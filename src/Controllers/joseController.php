<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Excel;
use View;
use App;
use Spatie;
use Illuminate\Support\Facades\DB;
use Imagick;

Trait joseController
{
    public function index()
    {

        return View::make('pruebas.index.content');
    }

    public function test_pdf(Request $request)
    {


        $nombre_subido = request()->file('archivo')->store('public/pdf');
        $nombre_original = $request->file('archivo')->getClientOriginalName();


        $archivo = new App\archivo;
        $archivo->save();


        $ag = new App\adetalle;
        $ag->adetalle_url = $nombre_subido;
        $ag->adetalle_nombre = $nombre_original;
        $ag->archivo_id = $archivo->archivo_id;
        $ag->save();

        return 'subido';

    }

    public static function convert_auto($array)
    {
        $only_name = $array['only_name'];
        $extension = $array['extension'];
        $path_origin = $array['path_origin'];
        $full_path_name = $array['path'];
        $path_destiny = $array['path_destiny'];

        $contat = '';


        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            //Windows
            $contat = '\\';
        } else {
            //Linux
            $contat = '/';

        }

        //Windows

        $pdf = new Spatie\PdfToImage\Pdf($full_path_name);
        $numero_paginas = $pdf->getNumberOfPages();


        if ($extension == 'pdf') {
            for ($i = 1; $i <= $numero_paginas; $i++) {

                $ruta_guardado = $path_destiny . $contat . $only_name . '_' . $i . '.jpg';
                $pdf->setPage($i);
                $pdf->saveImage($ruta_guardado);
            }
        }
        return 'ok';

    }

    public static function convert_final($array)
    {
        $only_name = $array['only_name'];
        $extension = $array['extension'];
        $origin_name = $array['origin_name'];
        $full_path_name = $array['path'];
        $path_destiny = $array['path_destiny'];

        $contat = '';

        $destino = $path_destiny;

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            //Windows
            $contat = '\\';
        } else {
            //Linux
            $contat = '/';

        }

        $pdf = new Spatie\PdfToImage\Pdf($full_path_name);
        $numero_paginas = $pdf->getNumberOfPages();

        $pdf->setOutputFormat("jpg");
        $pdf->setResolution(144);
        $pdf->setCompressionQuality(50);
        $pdf->saveAllPagesAsImages($destino, $only_name);


        $elementos = array();

        for ($i = 1; $i <= $numero_paginas; $i++) {

            $adetalle_nombre = $path_destiny . '/' . $only_name . '_' . $i . '.jpg';
            $adetalle_url = $path_destiny . '/' . $origin_name . '_' . $i . '.jpg';

            $tmp = [];
            $tmp['adetalle_nombre'] = $adetalle_nombre;
            $tmp['adetalle_url'] = $adetalle_url;

            $elementos[] = $tmp;
        }
        DB::table('adetalle')->insert(
            $elementos
        );

        return 'ok';

    }

    public function convert1()
    {
        $pdf = new Spatie\PdfToImage\Pdf('D:\test1.pdf');
        //->saveImage('D:\test1.jpg');
        $a = $pdf->getNumberOfPages();


        $pdf->setPage(4);
        $pdf->saveImage('D:\test_pag4');

        return $a;
    }

    public function convert2()
    {
        $archivo_id = 5;

        $buscar_archivo = App\adetalle::where('archivo_id', '=', $archivo_id)
            ->first();
        $nombre = $buscar_archivo->adetalle_nombre;


        $pdf = new Spatie\PdfToImage\Pdf('D:\prueba.pdf');
        $pdf->saveImage('D:\prueba.jpg');
        $a = $pdf->getNumberOfPages();


        for ($i = 1; $i <= $a; $i++) {

            $nombre_imagenes = 'D:\page_' . $i . '.jpg';
            $pdf->setPage($i);
            $pdf->saveImage($nombre_imagenes);
        }
        return 'ok';

    }

    public function convert3()
    {

        $pdf = new Spatie\PdfToImage\Pdf('D:\pdferror.pdf');
        //$pdf->saveImage('D:\prueba.jpg');
        $a = $pdf->getNumberOfPages();

        $pdf->setResolution(150);
        $outputformat = 'png';
        $pdf->setOutputFormat('jpeg');

        //$pdf->setCompressionQuality(80);


        for ($i = 1; $i <= $a; $i++) {

            $nombre_imagenes = 'D:\pdferror' . $i . '.jpeg';
            $pdf->setPage($i);
            $pdf->saveImage($nombre_imagenes);

        }
        return 'ok';
    }

    public function convert4()
    {

        $pdf = new Spatie\PdfToImage\Pdf('D:\pdferror.pdf');
        // $pdf->saveImage('D:\prueba.jpg');
        $a = $pdf->getNumberOfPages();

        $pdf->setOutputFormat("jpg");
        $pdf->setResolution(144);
        $pdf->setCompressionQuality(40);
        $pdf->saveAllPagesAsImages('D:\pruebas', '144p');

        return 'ok';
    }

    public function convert5()
    {

        $pdf = new Spatie\PdfToImage\Pdf('D:\prueba.pdf');
        $pdf->saveImage('D:\prueba.jpg');
        $a = $pdf->getNumberOfPages();


        for ($i = 1; $i <= $a; $i++) {

            $nombre_imagenes = 'D:\test' . $i . '.jpg';
            $pdf->setPage($i);
            $pdf->saveImage($nombre_imagenes);
        }
        return 'ok';
    }

    public function convert_linux()
    {

        $pdf = new Spatie\PdfToImage\Pdf('/var/www/html/fedatario2/storage/app/public/pdf/prueba.pdf');
        $pdf->saveImage('/var/www/html/fedatario2/storage/app/public/test.jpg');
        $a = $pdf->getNumberOfPages();


        for ($i = 1; $i <= $a; $i++) {

            $nombre_imagenes = '/var/www/html/fedatario2/storage/app/public/pdf/t' . $i . '.jpg';
            $pdf->setPage($i);
            $pdf->saveImage($nombre_imagenes);
        }
        return 'ok';
    }

    public function pruebajose()
    {

        if (!strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            //LINUX
            return "linux";
        } else {

            return "Windowx";
        }
    }


    public function convert_pdf_tiff($array)
    {
        $only_name = $array['only_name'];
        $extension = $array['extension'];
        $origin_name = $array['origin_name'];
        $full_path_name = $array['path'];
        $path_destiny = $array['path_destiny'];
        $path_origin = $array['path_origin'];


        $explode = explode("/app/", $path_destiny);
        $path_url = $explode[count($explode) - 1];


        $nombre_guardado = $only_name . '_';

        $pdf = new Spatie\PdfToImage\Pdf($full_path_name);
        $numero_paginas = $pdf->getNumberOfPages();

        $pdf->setOutputFormat("jpg");
        $pdf->setResolution(144);
        $pdf->setCompressionQuality(50);
        $pdf->saveAllPagesAsImages($path_destiny, $nombre_guardado);


        $elementos = array();


        if ($extension == 'pdf') {

            for ($i = 1; $i <= $numero_paginas; $i++) {

                $adetalle_url = $path_url . "/" . $only_name . '_' . $i . '.jpg';
                $adetalle_nombre = $only_name . '_' . $i . '.jpg';

                $now = new DateTime();

                $array_temporal = [];
                $array_temporal['adetalle_nombre'] = $adetalle_nombre;
                $array_temporal['adetalle_url'] = $adetalle_url;
                $array_temporal['created_at'] = $now;
                $array_temporal['updated_at'] = $now;


                $elementos[] = $array_temporal;
            }
            DB::table('adetalle')->insert(
                $elementos
            );

        } elseif ($extension == 'tiff') {

            for ($i = 1; $i <= $numero_paginas; $i++) {

                $adetalle_url = $path_url . "/" . $only_name . '_' . $i . '.jpg';
                $adetalle_nombre = $only_name . '_' . $i . '.jpg';

                $now = new DateTime();

                $array_temporal = [];
                $array_temporal['adetalle_nombre'] = $adetalle_nombre;
                $array_temporal['adetalle_url'] = $adetalle_url;
                $array_temporal['created_at'] = $now;
                $array_temporal['updated_at'] = $now;


                $elementos[] = $array_temporal;
            }
            DB::table('adetalle')->insert(
                $elementos
            );


        } else {
            return "error desconocido, contacte administrador";

        }


        return 'ok';
    }

    public function test()
    {


        $numero_paginas = 10;

        $path_destiny = "test";
        $only_name = "name";
        $origin_name = "url";

        $elementos = array();

        for ($i = 1; $i <= $numero_paginas; $i++) {

            $adetalle_nombre = $path_destiny . '/' . $only_name . '_' . $i . '.jpg';
            $adetalle_url = $path_destiny . '/' . $origin_name . '_' . $i . '.jpg';

            $tmp = [];
            $tmp['adetalle_nombre'] = $adetalle_nombre;
            $tmp['adetalle_url'] = $adetalle_url;

            $elementos[] = $tmp;
        }

        return $elementos;
        DB::table('adetalle')->insert(
            $elementos
        );

    }

    public function retornourl(Request $request)
    {
        //la variable de request no es documento_id es ADETALLE_ID

        $recepcion_tipo = $request->input('recepcion_tipo');


        $path = env("APP_URL");

        if ($recepcion_tipo == "s") {


            $documento_id = $request->input('documento_id');


            $archivos = DB::select(
                "select concat(" . "'$path'" . ",imagen_url) as imagen_url,imagen_id,imagen_pagina,
                (select max(cast(imagen_pagina as integer)) from imagen where documento_id =:documento_id and imagen_estado =1)
                from imagen
                where documento_id =:documento_id and imagen_estado =1
                order by imagen_pagina::int
                ", ["documento_id" => $documento_id]);

            /* $archivos = DB::table('imagen')
                 ->where('documento_id', '=', $documento_id)
                 ->where('imagen_estado', '=', 1)
                 ->select(
                     DB::raw("CONCAT('$path',imagen_url) as imagen_url"), "imagen_id","imagen_pagina")
                 ->get();

             return $archivos;*/

        } else {
            //ID se resta 1 pues
            //$adetalle_id = 5;

            $adetalle_id = $request->input('documento_id');

            $archivo_detalle = DB::table('adetalle')->where('adetalle_id', '=', $adetalle_id)
                ->first();


            $nombre = $archivo_detalle->adetalle_url;


            $explode = explode("/", $nombre);

            $nombre_completo = $explode[count($explode) - 1];

            $explode2 = explode(".", $nombre_completo);

            $nombre_sin_extension = $explode2[0];

            //  return $nombre_sin_extension;

            $archivos = DB::select(
                "select concat(" . "'$path'" . ",imagen_url) as imagen_url,imagen_id,imagen_pagina        
                ,(select max(cast(imagen_pagina as integer))
                from imagen
                where imagen_estado =1 and imagen_nombre ilike '%" . $nombre_sin_extension . "%')
                from imagen
                where imagen_estado =1 and imagen_nombre ilike '%" . $nombre_sin_extension . "%'
                order by imagen_pagina::int
                ");

            /*$imagen = new App\imagen();
            $archivos = $imagen->where("imagen_nombre", 'ilike', '%' . $nombre_sin_extension . '%')
                ->select(
                    DB::raw("CONCAT('$path',imagen_url) as imagen_url"), "imagen_id","imagen_pagina")
                ->get();*/
        }

        return $archivos;
    }

    public function retornourl_reproceso(Request $request)
    {

        //la variable de request no es documento_id es ADETALLE_ID
        $recepcion_tipo = $request->input('recepcion_tipo');


        $path = env("APP_URL");

        if ($recepcion_tipo == "s") {


            $documento_id = $request->input('documento_id');


            $archivos = DB::select(
                "select concat(" . "'$path'" . ",imagen_url) as imagen_url,a.imagen_id,imagen_pagina,
                (select max(cast(imagen_pagina as integer)) from imagen where documento_id =:documento_id and imagen_estado =1)
                from imagen a
                join incidencia_imagen b
                on a.imagen_id = b.imagen_id
                where imagen_estado = 1 and documento_id =:documento_id and imagen_estado =1
                order by a.imagen_pagina::int
                ", ["documento_id" => $documento_id]);

            /* $archivos = DB::table('imagen')
                 ->where('documento_id', '=', $documento_id)
                 ->where('imagen_estado', '=', 1)
                 ->select(
                     DB::raw("CONCAT('$path',imagen_url) as imagen_url"), "imagen_id","imagen_pagina")
                 ->get();

             return $archivos;*/

        } else {
            //ID se resta 1 pues
            //$adetalle_id = 5;

            $adetalle_id = $request->input('documento_id');

            $archivo_detalle = DB::table('adetalle')->where('adetalle_id', '=', $adetalle_id)
                ->first();


            $nombre = $archivo_detalle->adetalle_url;


            $explode = explode("/", $nombre);

            $nombre_completo = $explode[count($explode) - 1];

            $explode2 = explode(".", $nombre_completo);

            $nombre_sin_extension = $explode2[0];

            //  return $nombre_sin_extension;

            $archivos = DB::select(
                "select concat(" . "'$path'" . ",imagen_url) as imagen_url,a.imagen_id,imagen_pagina        
                ,(select max(cast(imagen_pagina as integer))
                from imagen
                where  imagen_estado = 1 and imagen_nombre ilike '%" . $nombre_sin_extension . "%')
                from imagen a
                join incidencia_imagen b
                on a.imagen_id = b.imagen_id
                where imagen_estado = 1 and  estado = 1 and imagen_nombre ilike '%" . $nombre_sin_extension . "%'
                order by a.imagen_pagina::int
                ");


            /*$imagen = new App\imagen();
            $archivos = $imagen->where("imagen_nombre", 'ilike', '%' . $nombre_sin_extension . '%')
                ->select(
                    DB::raw("CONCAT('$path',imagen_url) as imagen_url"), "imagen_id","imagen_pagina")
                ->get();*/
        }

        return $archivos;



    }

    public function url_impresion_captura(Request $request)
    {
        //la variable de request no es documento_id es ADETALLE_ID

        $recepcion_tipo = $request->input('recepcion_tipo');


        $path = env("APP_URL");

        if ($recepcion_tipo == "s") {


            $documento_id = $request->input('documento_id');


            $documento = App\documento::where("documento_id", $documento_id)
                ->first();

            $archivo = App\adetalle::where("adetalle_id", $documento['adetalle_id'])
                ->first();

            //si es tiff o tif .. devuelo el mismo nombre pero con extension pdf ya que se ha guardado previamente en los dos formatos
            $pos_ = strrpos($archivo->adetalle_url, ".");
            $exte = substr($archivo->adetalle_url, $pos_ + 1);
            if ($exte == "tif" || $exte == "tiff") {
                return $nombre = $path . substr($archivo->adetalle_url, 0, $pos_ + 1) . "pdf";
            } else {
                return $nombre = $path . $archivo->adetalle_url;

            }


        } else {
            //ID se resta 1 pues
            //$adetalle_id = 5;

            $adetalle_id = $request->input('documento_id');

            $archivo_detalle = DB::table('adetalle')->where('adetalle_id', '=', $adetalle_id)
                ->first();

            //si es tiff o tif .. devuelo el mismo nombre pero con extension pdf ya que se ha guardado previamente en los dos formatos
            $pos_ = strrpos($archivo_detalle->adetalle_url, ".");
            $exte = substr($archivo_detalle->adetalle_url, $pos_ + 1);
            if ($exte == "tif" || $exte == "tiff") {
                return $nombre = $path . substr($archivo_detalle->adetalle_url, 0, $pos_ + 1) . "pdf";
            } else {
                return $nombre = $path . $archivo_detalle->adetalle_url;
            }


            return $nombre = $path . $archivo_detalle->adetalle_url;


            $explode = explode("/", $nombre);

            $nombre_completo = $explode[count($explode) - 1];

            $explode2 = explode(".", $nombre_completo);

            $nombre_sin_extension = $explode2[0];

            // return $nombre_sin_extension;

            $imagen = new App\imagen();
            $archivos = $imagen->where("imagen_nombre", 'ilike', '%' . $nombre_sin_extension . '%')
                ->select(
                    DB::raw("CONCAT('$path',imagen_url) as imagen_url"), "imagen_id")
                ->get();
        }

        return $archivos;
    }


    public function read_excel()
    {


        //Excel::toCollection(new App\Imports\ProyectoGuiaImport,$importFile);
        return 'ok';
    }

}
