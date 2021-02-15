<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use View;
use Response;
use App;
use App\Http\Controllers\filesController;
use Spatie;
use Illuminate\Support\Facades\DB;
use DateTime;
use Illuminate\Support\Facades\Storage;
use App\Services\PayUService\Exception;
use Imagick;
use finfo;

Trait capturafileController
{
    private $tiempo_de_ejecucion = 7200;

    public function recibir_archivo(Request $request){
        ini_set('max_execution_time', $this->tiempo_de_ejecucion);
        return $this->registrar_archivo_simple($request, "file");
    }

    public function recibir_archivo_simple(Request $request){
        ini_set('max_execution_time', $this->tiempo_de_ejecucion);
        return $this->registrar_archivo_simple($request, "file");
    }

    public function recibir_scanner_simple(Request $request){
        ini_set('max_execution_time', $this->tiempo_de_ejecucion);
        return $this->registrar_archivo_simple($request, "archivo");
    }

    public function recibir_archivo_multiple(Request $request){
        ini_set('max_execution_time', $this->tiempo_de_ejecucion);
        return $this->registrar_archivo_multiple($request, "file");
    }
    /**
     * funcion para ingresar las calibradoras y actas de cierre en generacion medios
     */
    public function recibir_archivo_multiple_gmd(Request $request){
        ini_set('max_execution_time', $this->tiempo_de_ejecucion);
        $adetalle_id = $this->registrar_archivo_multiple($request, "file");
        $gmdc_model = new App\generacion_medio_detalle_captura();
        $gmd_id = request('gmd_id');
        $gmdc_model->insert_calibradora_acta_generacion_medio_detalle_captura($gmd_id,$adetalle_id);
        return $adetalle_id;
    }

    public function recibir_scanner_multiple(Request $request){
        ini_set('max_execution_time', $this->tiempo_de_ejecucion);
        return $this->registrar_archivo_multiple($request, "archivo");
    }

    /**
     * funcion para ingresar las calibradoras y actas de cierre en generacion medios
     */
    public function recibir_scanner_multiple_gmd(Request $request){
        ini_set('max_execution_time', $this->tiempo_de_ejecucion);
        $adetalle_id= $this->registrar_archivo_multiple($request, "archivo");
        $gmdc_model = new App\generacion_medio_detalle_captura();
        $gmd_id = request('gmd_id');
        $gmdc_model->insert_calibradora_acta_generacion_medio_detalle_captura($gmd_id,$adetalle_id);
        return $adetalle_id;
    }

    public function reemplazar_scanner_multiple(Request $request){
        ini_set('max_execution_time', $this->tiempo_de_ejecucion);
        $capturaController = new App\Http\Controllers\capturaController();
        return $capturaController->reemplazar_captura_multiple($request, "archivo");
    }

    public function recibir_tiff_simple(Request $request){
        ini_set('max_execution_time', $this->tiempo_de_ejecucion);
        $finalFile = self::procesar_pdf_a_tiff($request);
        $adetalle_id_tif = self::registrar_archivo_simple($request,"",$finalFile);
        return $adetalle_id_tif;
    }

    public function recibir_tiff_multiple(Request $request){
        ini_set('max_execution_time', $this->tiempo_de_ejecucion);
        $finalFile = self::procesar_pdf_a_tiff($request);
        $adetalle_id_tif = self::registrar_archivo_multiple($request,"",$finalFile);
        return $adetalle_id_tif;
    }

    /**
     * funcion para ingresar las calibradoras y actas de cierre en generacion medios
     */
    public function recibir_tiff_multiple_gmd(Request $request){
        ini_set('max_execution_time', $this->tiempo_de_ejecucion);
        $finalFile = self::procesar_pdf_a_tiff($request);
        $adetalle_id_tif = self::registrar_archivo_multiple($request,"",$finalFile);
        $gmdc_model = new App\generacion_medio_detalle_captura();
        $gmd_id = request('gmd_id');
        $gmdc_model->insert_calibradora_acta_generacion_medio_detalle_captura($gmd_id,$adetalle_id_tif);
        return $adetalle_id_tif;
    }

    public function reemplazar_tiff_multiple(Request $request){
        ini_set('max_execution_time', $this->tiempo_de_ejecucion);
        $finalFile = self::procesar_pdf_a_tiff($request);
        $capturaController = new App\Http\Controllers\capturaController();
        $adetalle_id_tif = $capturaController->reemplazar_captura_multiple($request,"",$finalFile);
        return $adetalle_id_tif;
    }

    public function procesar_pdf_a_tiff(Request $request){
        $base64 = request("base64");
        $flag_base64 = (empty($base64))?false:true;
        if(!$flag_base64){
            $root = storage_path() . "/app/";
            $file = request()->file("archivo"); //solo puede haber sido enviado por scanner
            $nombre_original = $file->getClientOriginalName();
            $prePath = storage_path() . "/app/public/";
            $subir_archivo = $file->store($prePath); // guarda en el servidor con un nombre modificado (HASH)
            return self::cambiar_formato_archivo($root.$subir_archivo, "tiff");
        } else {
            $nombre_original = request("nombre");
            //grabamos el archivo en base 64
            Storage::disk('public')->put($nombre_original, base64_decode($base64));
            $prePath = storage_path() . "/app/public/";
            //cambiamos el formato a tiff
            return self::cambiar_formato_archivo($prePath.$nombre_original, "tiff");
        }
    }

    public function registrar_archivo_simple(Request $request, $input_file_name, $fileTiff = null){

    // public function recibir_archivo(Request $request,$fileTiff = null){
        //SIMPLE
        //$input_file_name = "file";
        $root = storage_path() . "/app/";
        $recepcion_tipo = request('recepcion_tipo');
        $recepcion_id = request('recepcion_id');
        $proyecto_id = request('proyecto_id');
        $captura_id = request('captura_id');
        $cliente_id = request('cliente_id');
        $documento_nombre = request('documento_nombre');
        $captura_estado = 1;
        $valido = capturaController::validar_listar_captura($recepcion_id, $captura_estado);

        if(!$valido["estado"]){
            return self::crear_objeto("error",$valido["mensaje"]);
        }

        //por scanner
        $base64 = request("base64");
        $flag_base64 = (empty($base64))?false:true;
        if(!$flag_base64){
            if(empty($fileTiff)){
                $file = request()->file($input_file_name);
            } else {
                $file = $fileTiff;
            }
        }

        //TODO ES SIMPLE
        //1.-Obtenemos el documento_id
        $documento_id = request('captura_id');

        //2.-Obtenemos recepcion

        //3.-Obtenemos la extensión del archivo
        if($flag_base64){
            $nombre_original = request("nombre");

            $nombre_limpio =  substr($nombre_original,0,strrpos($nombre_original,"."));
            $extension_archivo =substr($nombre_original,strlen($nombre_limpio)+1);
            $extension = self::get_mime_from_extension($extension_archivo);

        }else{
            $nombre_original = (!empty($fileTiff)) ? request("nombre") : $file->getClientOriginalName();
            $nombre_limpio =  substr($nombre_original,0,strrpos($nombre_original,"."));
            $extension_archivo =substr($nombre_original,strlen($nombre_limpio)+1);
            $extension = self::get_mime_from_extension($extension_archivo);
            //$extension = $file->getClientMimeType();
        }

        //3.1 Validamos la extension
        if(!self::validar_extension($extension,$proyecto_id)){
            return self::crear_objeto("error","Extension de archivo no permitido");
        }



        //4.-Conseguimos el path donde irán los archivos
        $path = self::verify_path_capturas($recepcion_id);

        if($extension == "image/jpg" || $extension == "image/jpeg" || $extension == "image/bmp" || $extension == "image/png" || $extension == "image/gif") {
            if($flag_base64){
                Storage::disk('public')->put($nombre_original, base64_decode($base64));
                Storage::move("public/" . $nombre_original, $path . '/' . $nombre_original);
                $subir_archivo = $path . "/" . $nombre_original;
            }else{
                $subir_archivo = $file->store($path); // guarda en el servidor con un nombre modificado (HASH)
                $nombre_original = $file->getClientOriginalName(); // Captura el nombre del archivo enviado de front-end
            }
            //5.-Guardo adetalle del archivo
            $ag_p= new App\adetalle();
            $ag_p->insertar_adetalle($subir_archivo,str_replace(" ", "_", $nombre_original));

            //6.-Convertir archivo a jpg y obtener nombre original y ruta directorio dentro de app
            $nueva_ruta = self::convert_to_jpeg($root . $subir_archivo, $root . $path . "/imagenes");
            $explode = explode("/", $nueva_ruta);
            $nombre_original = $explode[count($explode) - 1];
            $explode = explode("/app/", $nueva_ruta);
            $subir_archivo = $explode[count($explode) - 1];
            //7.-Guardo la imagen convertida a jpg en adetalle
            $ag= new App\adetalle();
            $ag->insertar_adetalle($subir_archivo,str_replace(" ", "_", $nombre_original));
            //8.- llamar crear_lista_captura para hacer update de nombre y adetalle en documento
            $retorno = array($nueva_ruta, $nombre_original, $subir_archivo, $ag->adetalle_id);
            $now = date('Y-m-d H:i:s');
            $objeto = [
                "recepcion_id" => $recepcion_id,
                "proyecto_id" => $proyecto_id,
                "cliente_id" => $cliente_id,
                "adetalle_id" => $ag_p->adetalle_id,
                "documento_nombre" => $documento_nombre,
                "documento_id" => $documento_id,
                "captura_estado" => 1,
                "captura_estado_glb" => "cap",
                "created_at" => $now,
                "updated_at" => $now];
            $array[] = $objeto;
            $captura = new  App\captura();
            $captura->crear_lista_captura($array, false);
            //9.-Guardar imagen
            $imagen = new App\imagen();
            $imagen->insertar_imagen($recepcion_id,
                    $captura_id ,
                    $documento_id ,
                    $nombre_original ,
                    1 ,
                    $subir_archivo ,
                    1);
            //10.-Update Validación
            self::validar_update_estado_imagen([$imagen->imagen_id],$documento_id);
            return $ag_p->adetalle_id;
        }else if($extension == "image/tiff" || $extension == "application/pdf" || $extension == "image/tif"){

            if($flag_base64){
                Storage::disk('public')->put($nombre_original, base64_decode($base64));
                Storage::move("public/" . $nombre_original, $path . '/' . $nombre_original);
                $subir_archivo = $path . "/" . $nombre_original;
                $pathfile_info["origin_name"] = $nombre_original;

                $pathfile_info = self::informacion_ruta_archivo($root . $subir_archivo);

                $ag = new App\adetalle();
                $ag->insertar_adetalle($subir_archivo, str_replace(" ", "_", $nombre_original));
            } else {
                $subir_archivo = $file->store($path);
                $pathfile_info = self::informacion_ruta_archivo($root . $subir_archivo);
                $pathfile_info["origin_name"] = $nombre_original; // Captura el nombre del archivo enviado de front-end

                $ag = new App\adetalle();
                $ag->insertar_adetalle($subir_archivo, str_replace(" ", "_", $pathfile_info["origin_name"]));

            }

            //Si es tiff-> convierto a pdf y lo guardo con el mismo nombre pero extension pdf
            $pos_ = strrpos($subir_archivo, ".");
            $exte = substr($subir_archivo, $pos_ + 1);
            if ($exte == "tif" || $exte == "tiff") {
                // $prePath = env("APP_URL");
                // $prePath = storage_path('app\\');
                $prePath = storage_path() . "/app/";
                $docu = new Imagick($prePath . $subir_archivo);
                $docu->setimageformat("pdf");
                $docu->writeimages($prePath . substr($subir_archivo, 0, $pos_ + 1) . "pdf", true);

            }

            //8.- llamar crear_lista_captura para hacer update de nombre y adetalle en documento
            //$retorno = array($nueva_ruta, $nombre_original, $subir_archivo, $ag->adetalle_id);
            $now = date('Y-m-d H:i:s');
            $objeto = [
                "recepcion_id" => $recepcion_id,
                "proyecto_id" => $proyecto_id,
                "cliente_id" => $cliente_id,
                "adetalle_id" => $ag->adetalle_id,
                "documento_nombre" => $documento_nombre,
                "documento_id" => $documento_id,
                "captura_estado" => 1,
                "captura_estado_glb" => "cap",
                "created_at" => $now,
                "updated_at" => $now];
            $array[] = $objeto;
            $captura = new  App\captura();
            $captura->crear_lista_captura($array, false);

            //9.- Convertir PDF/TIFF a imagenes
            $array_imagen = [
                "recepcion_id" => $recepcion_id,
                "captura_id" => $captura_id,
                "documento_id" => $documento_id,
            ];
            try {
                $this->convertir_simple($pathfile_info, $array_imagen);
            } catch (Exception  $e) {
                return $e->getMessage();
            }
            //
            $adetalle_id = $ag->adetalle_id;

            $archivo_detalle = DB::table('adetalle')->where('adetalle_id', '=', $adetalle_id)
                ->first();

            $nombre = $archivo_detalle->adetalle_url;
            $explode = explode("/", $nombre);
            $nombre_completo = $explode[count($explode) - 1];
            $explode2 = explode(".", $nombre_completo);
            $nombre_sin_extension = $explode2[0];

            $ids_imagenes = DB::table('imagen')
                ->where('documento_id', '=', $documento_id)
                ->where('imagen_nombre', 'ilike', '%' . $nombre_sin_extension . '%')
                ->select('imagen_id')
                ->get();

            $array_ids = array();

            foreach ($ids_imagenes as $i) {
                $array_ids[] = $i->imagen_id;
            }

            self::validar_update_estado_imagen($array_ids, $documento_id);
            return $ag->adetalle_id;
        } else {

            $subir_archivo = $file->store($path);
            $pathfile_info = self::informacion_ruta_archivo($root . $subir_archivo);
            $pathfile_info["origin_name"] = $file->getClientOriginalName(); // Captura el nombre del archivo enviado de front-end

            $ag_p = new App\adetalle();
            $ag_p->insertar_adetalle($subir_archivo, str_replace(" ", "_", $pathfile_info["origin_name"]));

            $nueva_ruta = $root . $path;
            $explode = explode("/", $nueva_ruta);
            $nombre_original = $explode[count($explode) - 1];
            $explode = explode("/app/", $nueva_ruta);
            $subir_archivo = $explode[count($explode) - 1];

            $retorno = array($nueva_ruta, $nombre_original, $subir_archivo);

            //Guardado automático de captura

            $now = date('Y-m-d H:i:s');

            $objeto = [
                "recepcion_id" => $recepcion_id,
                "proyecto_id" => $proyecto_id,
                "cliente_id" => $cliente_id,
                "adetalle_id" => $ag_p->adetalle_id,
                "documento_nombre" => $documento_nombre,
                "documento_id" => $documento_id,
                "captura_estado" => 1,
                "captura_estado_glb" => "cap",
                "created_at" => $now,
                "updated_at" => $now];

            $array[] = $objeto;


            $captura = new  App\captura();

            $captura->crear_lista_captura($array, false);

            return $ag_p->adetalle_id;

        }

    }

    public function cambiar_formato_archivo( $pathFile, $formatoFinal){

        $pos_ = strrpos($pathFile, ".");
        $pathFileFinal = substr($pathFile, 0, $pos_ + 1) . $formatoFinal;
        $docu = new Imagick($pathFile);
        $docu->setimageformat($formatoFinal);
        $docu->writeimages($pathFileFinal, true);

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return new UploadedFile(
            $pathFileFinal,
            substr($pathFileFinal,strrpos($pathFileFinal, "/")+1),
            $finfo->file($pathFileFinal),
            filesize($pathFileFinal),
            0,
            false
        );
    }

    public function registrar_archivo_multiple(Request $request, $input_file_name, $fileTiff = null){
    // public function recibir_archivo_multiple(Request $request,$fileTiff = null) {

        //MULTIPLE
        //$input_file_name = "file";
        $root = storage_path() . "/app/";
        $recepcion_tipo = request('recepcion_tipo');
        $recepcion_id = request('recepcion_id');
        $captura_estado = request('captura_estado');

        $valido = capturaController::validar_listar_captura($recepcion_id, $captura_estado);
        if ($valido["estado"]) {
            // $proyecto_id = request('proyecto_id');
            // $captura_id = request('captura_id');

            // $cliente_id = request('cliente_id');
            // $documento_nombre = request('documento_nombre');

            //por scanner
            $base64 = request("base64");
            $flag_base64 = (empty($base64)) ? false : true;
            if (!$flag_base64) {
                if (empty($fileTiff)) {
                    $file = request()->file($input_file_name);
                } else {
                    $file = $fileTiff;
                }
            }

            //TODO ES SIMPLE
            //1.-Obtenemos el documento_id
            $documento_id = request('captura_id');

            $recepcion = DB::table('recepcion')->where('recepcion_id', '=', $recepcion_id)
                ->first();


            //2.-Obtenemos recepcion

            //3.-Obtenemos la extensión del archivo
            if ($flag_base64) {

                $nombre_original = request("nombre");
                $nombre_limpio = substr($nombre_original, 0, strrpos($nombre_original, "."));
                $extension_archivo = substr($nombre_original, strlen($nombre_limpio) + 1);
                $extension = self::get_mime_from_extension($extension_archivo);
            } else {

                $nombre_original = (!empty($fileTiff)) ? request("nombre") : $file->getClientOriginalName();
                $nombre_limpio = substr($nombre_original, 0, strrpos($nombre_original, "."));
                $extension_archivo = substr($nombre_original, strlen($nombre_limpio) + 1);
                $extension = self::get_mime_from_extension($extension_archivo);
                //$extension = $file->getClientMimeType();
            }


            //3.1 Validamos la extension
            if (!self::validar_extension($extension, $recepcion->proyecto_id, $captura_estado)) {
                return self::crear_objeto("error", "Extension de archivo no permitido");
            }

            //4.-Conseguimos el path donde irán los archivos
            $path = self::verify_path_capturas($recepcion_id);

            if ($extension == "image/jpeg" || $extension == "image/bmp" || $extension == "image/png" || $extension == "image/gif") {

                if ($flag_base64) {
                    $nombre_original = request("nombre");
                    $nombre_original_guardado = request("nombre");
                    Storage::disk('public')->put($nombre_original, base64_decode($base64));
                    Storage::move("public/" . $nombre_original, $path . '/' . $nombre_original);
                    $subir_archivo = $path . "/" . $nombre_original;
                } else {
                    $subir_archivo = $file->store($path); // guarda en el servidor con un nombre modificado (HASH)
                    $nombre_original = $file->getClientOriginalName(); // Captura el nombre del archivo enviado de front-end
                    $nombre_original_guardado = $file->getClientOriginalName(); // Captura el nombre del archivo enviado de front-end
                }

                $getext = substr(strrchr($nombre_original, '.'), 1);

                //Para ajustar el nombre cuando no es captura estado 1 solo en sirve para multiple
                switch ($captura_estado) {
                    case '2':
                        //$nombre_original= ('Acta_apertura_'.date('Y-m-d H-i-s').$nombre_original);
                        $nombre_original = ('Acta_apertura_' . date('Y-m-d H-i-s') . '.' . $getext);
                        //$nombre_original_guardado= ('Acta_apertura_'.date('Y-m-d H-i-s').$nombre_original_guardado);
                        $nombre_original_guardado = ('Acta_apertura_' . date('Y-m-d H-i-s') . '.' . $getext);
                        break;
                    case '3':
                        //$nombre_original= ('Calibradora_inicial_'.date('Y-m-d H-i-s').$nombre_original);
                        $nombre_original = ('Calibradora_inicial_' . date('Y-m-d H-i-s') . '.' . $getext);
                        //$nombre_original_guardado= ('Calibradora_inicial_'.date('Y-m-d H-i-s').$nombre_original_guardado);
                        $nombre_original_guardado = ('Calibradora_inicial_' . date('Y-m-d H-i-s') . '.' . $getext);
                        break;
                    case '4':
                        //$nombre_original= ('Acta_cierre_'.date('Y-m-d H-i-s').'_'.$nombre_original);
                        $nombre_original = ('Acta_cierre_' . date('Y-m-d H-i-s') . '.' . $getext);
                        //$nombre_original_guardado= ('Acta_cierre_'.date('Y-m-d H-i-s').'_'.$nombre_original_guardado);
                        $nombre_original_guardado = ('Acta_cierre_' . date('Y-m-d H-i-s') . '.' . $getext);
                        break;
                    case '5':
                        //$nombre_original= ('Calibradora_final_'.date('Y-m-d H-i-s').'_'.$nombre_original);
                        $nombre_original = ('Calibradora_final_' . date('Y-m-d H-i-s') . '.' . $getext);
                        //$nombre_original_guardado= ('Calibradora_final_'.date('Y-m-d H-i-s').'_'.$nombre_original_guardado);
                        $nombre_original_guardado = ('Calibradora_final_' . date('Y-m-d H-i-s') . '.' . $getext);
                        break;
                    default:
                        # code...
                        break;
                }

                //5.-Guardo adetalle del archivo
                $ag_p = new App\adetalle();
                $ag_p->insertar_adetalle($subir_archivo, str_replace(" ", "_", $nombre_original));
                //6.-Convertir archivo a jpg y obtener nombre original y ruta directorio dentro de app
                $nueva_ruta = self::convert_to_jpeg($root . $subir_archivo, $root . $path . "/imagenes");
                $explode = explode("/", $nueva_ruta);
                $nombre_original = $explode[count($explode) - 1];
                $explode = explode("/app/", $nueva_ruta);
                $subir_archivo = $explode[count($explode) - 1];
                //7.-Guardo la imagen convertida a jpg en adetalle
                $ag = new App\adetalle();
                $ag->insertar_adetalle($subir_archivo, str_replace(" ", "_", $nombre_original));
                //8.- llamar crear_lista_captura para hacer update de nombre y adetalle en documento
                $retorno = array($nueva_ruta, $nombre_original, $subir_archivo, $ag->adetalle_id);
                $now = date('Y-m-d H:i:s');
                $objeto = [
                    "recepcion_id" => $recepcion_id,
                    "proyecto_id" => $recepcion->proyecto_id,
                    "cliente_id" => $recepcion->cliente_id,
                    "adetalle_id" => $ag_p->adetalle_id,
                    "documento_nombre" => $nombre_original_guardado,
                    "captura_estado" => $captura_estado,
                    "captura_estado_glb" => "cap",
                    "imagen_nombre" => $nombre_original,
                    "imagen_pagina" => 1,
                    "imagen_url" => $subir_archivo,
                    "padre_id" => $request->input('padre_id', 0),
                    "created_at" => $now,
                    "updated_at" => $now];
                $array[] = $objeto;
                $captura = new  App\captura();
                $captura->crear_lista_captura($array, true);
                return $ag_p->adetalle_id;


            } else if ($extension == "image/tiff" || $extension == "application/pdf" || $extension == "image/tif") {

                if ($flag_base64) {
                    Storage::disk('public')->put($nombre_original, base64_decode($base64));
                    Storage::move("public/" . $nombre_original, $path . '/' . $nombre_original);
                    $subir_archivo = $path . "/" . $nombre_original;
                    $pathfile_info = self::informacion_ruta_archivo($root . $subir_archivo);
                    //$pathfile_info["origin_name"] = $nombre_original;
                } else {
                    try {
                        $subir_archivo = $file->store($path);
                        $pathfile_info = self::informacion_ruta_archivo($root . $subir_archivo);
                    } catch (\RuntimeException $e) {
                        dd('Whoops: ' . $e->getMessage());
                    }
                }

                $nombre_original_guardado = $nombre_original;
                $getext = substr(strrchr($nombre_original, '.'), 1);
                //Para ajustar el nombre cuando no es captura estado 1 solo en sirve para multiple
                switch ($captura_estado) {
                    case '2':
                        //$nombre_original= ('Acta_apertura_'.date('Y-m-d H-i-s').$nombre_original);
                        $nombre_original = ('Acta_apertura_' . date('Y-m-d H-i-s') . '.' . $getext);
                        //$nombre_original_guardado= ('Acta_apertura_'.date('Y-m-d H-i-s').$nombre_original_guardado);
                        $nombre_original_guardado = ('Acta_apertura_' . date('Y-m-d H-i-s') . '.' . $getext);
                        break;
                    case '3':
                        //$nombre_original= ('Calibradora_inicial_'.date('Y-m-d H-i-s').$nombre_original);
                        $nombre_original = ('Calibradora_inicial_' . date('Y-m-d H-i-s') . '.' . $getext);
                        //$nombre_original_guardado= ('Calibradora_inicial_'.date('Y-m-d H-i-s').$nombre_original_guardado);
                        $nombre_original_guardado = ('Calibradora_inicial_' . date('Y-m-d H-i-s') . '.' . $getext);
                        break;
                    case '4':
                        //$nombre_original= ('Acta_cierre_'.date('Y-m-d H-i-s').'_'.$nombre_original);
                        $nombre_original = ('Acta_cierre_' . date('Y-m-d H-i-s') . '.' . $getext);
                        //$nombre_original_guardado= ('Acta_cierre_'.date('Y-m-d H-i-s').'_'.$nombre_original_guardado);
                        $nombre_original_guardado = ('Acta_cierre_' . date('Y-m-d H-i-s') . '.' . $getext);
                        break;
                    case '5':
                        //$nombre_original= ('Calibradora_final_'.date('Y-m-d H-i-s').'_'.$nombre_original);
                        $nombre_original = ('Calibradora_final_' . date('Y-m-d H-i-s') . '.' . $getext);
                        //$nombre_original_guardado= ('Calibradora_final_'.date('Y-m-d H-i-s').'_'.$nombre_original_guardado);
                        $nombre_original_guardado = ('Calibradora_final_' . date('Y-m-d H-i-s') . '.' . $getext);
                        break;
                    default:
                        # code...
                        break;
                }

                $pathfile_info["origin_name"] = $nombre_original;

                $ag = new App\adetalle();
                $ag->insertar_adetalle($subir_archivo, str_replace(" ", "_", $pathfile_info["origin_name"]));


                //Si es tiff-> convierto a pdf y lo guardo con el mismo nombre pero extension pdf
                $pos_ = strrpos($subir_archivo, ".");
                $exte = substr($subir_archivo, $pos_ + 1);

                if ($exte == "tif" || $exte == "tiff" || $exte == "application/msword" || $exte == "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {
                    // $prePath = env("APP_URL");
                    $prePath = storage_path() . "/app/";
                    $docu = new Imagick($prePath . $subir_archivo);
                    $docu->setimageformat("pdf");
                    $docu->writeimages($prePath . substr($subir_archivo, 0, $pos_ + 1) . "pdf", true);

                }


                //Guardado automático de captura
                $captura = new App\captura();

                $now = date('Y-m-d H:i:s');

                $objeto_datos = [
                    "recepcion_id" => $recepcion_id,
                    "proyecto_id" => $recepcion->proyecto_id,
                    "cliente_id" => $recepcion->cliente_id,
                    "adetalle_id" => $ag->adetalle_id,
                    "captura_estado" => $captura_estado,
                    "padre_id" => $request->input('padre_id', 0),
                    "captura_estado_glb" => "cap",
                    "created_at" => $now,
                    "updated_at" => $now
                ];

                $array_datos[] = $objeto_datos;
                //  $captura->crear_lista_captura($array);

                //joseController::convert_auto($pathfile_info);
                //  $this->convertir_multiple($pathfile_info, $array_datos);
                try {
                    $this->convertir_multiple($pathfile_info, $array_datos);
                } catch (Exception  $e) {
                    return $e->getMessage();
                }
                return $ag->adetalle_id;

            } else {


                $subir_archivo = $file->store($path);
                $pathfile_info = self::informacion_ruta_archivo($root . $subir_archivo);
                //$pathfile_info["origin_name"] = $file->getClientOriginalName(); // Captura el nombre del archivo enviado de front-end
                $nombre_original = $file->getClientOriginalName();
                $getext = substr(strrchr($nombre_original, '.'), 1);
                //Para ajustar el nombre cuando no es captura estado 1 solo en sirve para multiple
                switch ($captura_estado) {
                    case '2':
                        //$nombre_original= ('Acta_apertura_'.date('Y-m-d H-i-s').$nombre_original);
                        $nombre_original = ('Acta_apertura_' . date('Y-m-d H-i-s') . '.' . $getext);
                        //$nombre_original_guardado= ('Acta_apertura_'.date('Y-m-d H-i-s').$nombre_original_guardado);
                        $nombre_original_guardado = ('Acta_apertura_' . date('Y-m-d H-i-s') . '.' . $getext);
                        break;
                    case '3':
                        //$nombre_original= ('Calibradora_inicial_'.date('Y-m-d H-i-s').$nombre_original);
                        $nombre_original = ('Calibradora_inicial_' . date('Y-m-d H-i-s') . '.' . $getext);
                        //$nombre_original_guardado= ('Calibradora_inicial_'.date('Y-m-d H-i-s').$nombre_original_guardado);
                        $nombre_original_guardado = ('Calibradora_inicial_' . date('Y-m-d H-i-s') . '.' . $getext);
                        break;
                    case '4':
                        //$nombre_original= ('Acta_cierre_'.date('Y-m-d H-i-s').'_'.$nombre_original);
                        $nombre_original = ('Acta_cierre_' . date('Y-m-d H-i-s') . '.' . $getext);
                        //$nombre_original_guardado= ('Acta_cierre_'.date('Y-m-d H-i-s').'_'.$nombre_original_guardado);
                        $nombre_original_guardado = ('Acta_cierre_' . date('Y-m-d H-i-s') . '.' . $getext);
                        break;
                    case '5':
                        //$nombre_original= ('Calibradora_final_'.date('Y-m-d H-i-s').'_'.$nombre_original);
                        $nombre_original = ('Calibradora_final_' . date('Y-m-d H-i-s') . '.' . $getext);
                        //$nombre_original_guardado= ('Calibradora_final_'.date('Y-m-d H-i-s').'_'.$nombre_original_guardado);
                        $nombre_original_guardado = ('Calibradora_final_' . date('Y-m-d H-i-s') . '.' . $getext);
                        break;
                    default:
                        # code...
                        break;
                }
                $pathfile_info["origin_name"] = $nombre_original;


                $ag_p = new App\adetalle();
                $ag_p->insertar_adetalle($subir_archivo, str_replace(" ", "_", $pathfile_info["origin_name"]));

                $nueva_ruta = $root . $path;

                $explode = explode("/", $nueva_ruta);
                $nombre_original = $explode[count($explode) - 1];
                $explode = explode("/app/", $nueva_ruta);
                $subir_archivo = $explode[count($explode) - 1];

                $retorno = array($nueva_ruta, $nombre_original, $subir_archivo);

                //Guardado automático de captura
                $captura = new App\captura();

                $now = date('Y-m-d H:i:s');

                $objeto = [
                    "recepcion_id" => $recepcion_id,
                    "proyecto_id" => $recepcion->proyecto_id,
                    "cliente_id" => $recepcion->cliente_id,
                    "adetalle_id" => $ag_p->adetalle_id,
                    "documento_nombre" => $pathfile_info["origin_name"],
                    "captura_estado" => $captura_estado,
                    "captura_estado_glb" => "cap",
                    "padre_id" => $request->input('padre_id', 0),
                    "imagen_nombre" => $nombre_original,
                    "imagen_pagina" => 1,
                    "imagen_url" => $subir_archivo,
                    "created_at" => $now,
                    "updated_at" => $now];

                $array[] = $objeto;

                $captura->crear_lista_captura($array, true);

                return $ag_p->adetalle_id;
            }

        }else{
            return self::crear_objeto("error",$valido["mensaje"]);
        }
    }

    public function validar_update_estado_imagen($array_imagen_id,$documento_id){
        $val = DB::table('imagen')
            ->where('documento_id', '=', $documento_id)
            ->whereNotIn('imagen_id', $array_imagen_id)
            ->update(
                ['imagen_estado' => 0]
            );
    }

    public function get_mime_from_extension($extension)
    {
        $mime = "";
        $extension = strtolower($extension);

        switch ($extension) {
            case "jpg":
            case "jpeg":
                $mime = "image/jpeg";
                break;
            case "png":
                $mime = "image/png";
                break;
            case "pdf":
                $mime = "application/pdf";
                break;
            case "tiff":
                $mime = "image/tiff";
                break;
            case "tif":
                $mime = "image/tif";
                break;
            case "doc":
                $mime = "application/msword";
                break;
            case "docx":
                $mime = "application/msword";
                break;
            default:
                break;
        }
        return $mime;
    }

    public function validar_extension($extension,$proyecto_id, $estado = "1")
    {
        //Query para los tipos de formato para el proyecto
        $extensiones_query= DB::select("
        SELECT ARRAY [jpg,png,tiff,tif,pdf,gif,bmp,word,otro] as vista
            from (select
                   case when proyecto_jpg=1 then 'image/jpeg'
                         end as jpg,
                   case when proyecto_png=1  then 'image/png'
                          end as png,
                   case when proyecto_tiff=1  then 'image/tiff'
                          end as tiff,
                   case when proyecto_tif=1  then 'image/tif'
                          end as tif,
                   case when proyecto_pdf=1 then 'application/pdf'
                          end as pdf,
                   case when proyecto_gif=1  then 'image/gif'
                          end as gif,
                   case when proyecto_bmp=1 then 'image/bmp'
                          end as bmp,
                   case when proyecto_docx=1 then 'application/msword'
                         end as word,
                   case when proyecto_otro=1 then 'otro'
                         end as otro
            from proyecto_formato
            where proyecto_id = :proyecto_id) as extensiones;
        ", ["proyecto_id"=>$proyecto_id]);

        //Limpiar dato obtenido en el query
        $extensiones_query_string=substr($extensiones_query[0]->vista, 1, -1);
        //Generación del array
        $array_extensiones_permitidas = explode(",", $extensiones_query_string);
        //Limpiar array
        $respuesta = str_replace(array("NULL"),'',$array_extensiones_permitidas);

        $bloque_array = array_values(array_filter($respuesta));

        $archivos_permitidos_captura = $bloque_array;
        $archivos_permitidos_calibradora =
            array("image/jpeg", "image/png", "image/jpg", "application/pdf","image/tiff","image/tif");
        $archivos_permitidos_apertura =
            array("image/jpeg", "image/png", "image/jpg", "application/pdf","image/tiff", "image/tif", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        $archivos_permitidos_cierre =
            array("image/jpeg", "image/png", "image/jpg", "application/pdf","image/tiff", "image/tif", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document");
        $archivos_permitidos_cierre_calibradora =
            array("image/jpeg", "image/png", "image/jpg", "application/pdf","image/tiff", "image/tif", "application/msword", "application/vnd.openxmlformats-officedocument.wordprocessingml.document");


        $resultado = false;

        if(!(in_array('otro',$bloque_array))){

            switch ($estado) {
                case "1":
                    $resultado = in_array($extension, $archivos_permitidos_captura);
                    break;
                case "2":
                    $resultado = in_array($extension, $archivos_permitidos_apertura);
                    break;
                case "3":
                    $resultado = in_array($extension, $archivos_permitidos_calibradora);
                    break;
                case "4":
                    $resultado = in_array($extension, $archivos_permitidos_cierre);
                    break;
                case "5":
                    $resultado = in_array($extension, $archivos_permitidos_cierre_calibradora);
                    break;
                default:
                    $resultado = false;
                    break;
            }

        }else{

            $resultado = true;

        }

        return $resultado;

    }




    /**
     * Recibe un archivo y verifica su extension antes de guardarlo
     * Cambio de parametro $ag por $ag_p
     * @param Request $request Informacion recibida en la consulta
     * @return boolean resultado del guardado
     * @author Juan Ignacio Basilio Flores
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    /*
    public function recibir_archivo(Request $request)
    {


        $input_file_name = "file";
        $root = storage_path() . "/app/";

        $recepcion_tipo = request('recepcion_tipo');

        //por scanner
        $imagen = request("img");

        if ($imagen == "" || $imagen == null) {
            //por dropzone
            $file = request()->file($input_file_name);
        }

        if ($recepcion_tipo == "s") {

            if ($imagen == "" || $imagen == null) {
                $documento_id = request('documento_id');
            } else {
                $documento_id = request('captura_id');
                $file = "lala";
            }

            $documento = DB::table('documento')->where('documento_id', '=', $documento_id)
                ->first();


            $recepcion_id = $documento->recepcion_id;

            if (!is_null($file)) {//no hay problema wili

                if ($imagen == "" || $imagen == null) {
                    //NORMAL
                    $extension = $file->getClientMimeType();
                } else {
                    //SCANNER
                    //  $extension = "image/jpg";
                    $nombre_original = request("nombre");

                    $a=  explode(".",$nombre_original);
                    $contador = count($a);
                    $extension_archivo = $a[$contador -1 ];
                    $nombre_limpio=str_replace(".".$extension_archivo,"",$nombre_original);

                    if ($extension_archivo == "jpg" || $extension_archivo == "jpeg") {
                        $extension = "image/jpg";
                    } elseif ($extension_archivo == "pdf" || $extension_archivo == "tiff") {
                        $extension = "application/pdf";
                    } else {

                    }
                }

                if ($extension == "image/jpg" || $extension == "image/jpeg" || $extension == "image/bmp" || $extension == "image/png" || $extension == "image/gif") {

                    $path = self::verify_path_capturas($recepcion_id);


                    if ($imagen == "" || $imagen == null) {
                        //NORMAL
                        $subir_archivo = $file->store($path); // guarda en el servidor con un nombre modificado (HASH)
                        $nombre_original = $file->getClientOriginalName(); // Captura el nombre del archivo enviado de front-end


                    } else {
                        //SCANNER

                        Storage::disk('public')->put($nombre_original, base64_decode($imagen));

                        Storage::move("public/" . $nombre_original, $path . '/' . $nombre_original);


                        $subir_archivo = $path . "/" . $nombre_original;


                    }


                    $ag_p = new App\adetalle;
                    $ag_p->adetalle_url = $subir_archivo;
                    $ag_p->adetalle_nombre = str_replace(" ", "_", $nombre_original);
                    $ag_p->save();
                    $nueva_ruta = self::convert_to_jpeg($root . $subir_archivo, $root . $path . "/imagenes");
                    $explode = explode("/", $nueva_ruta);
                    $nombre_original = $explode[count($explode) - 1];
                    $explode = explode("/app/", $nueva_ruta);
                    $subir_archivo = $explode[count($explode) - 1];
                    $ag = new App\adetalle;
                    $ag->adetalle_url = $subir_archivo;
                    $ag->adetalle_nombre = str_replace(" ", "_", $nombre_original);
                    $ag->save();
                    $retorno = array($nueva_ruta, $nombre_original, $subir_archivo, $ag->adetalle_id);

                    //Guardado automático de captura

                    $now = date('Y-m-d H:i:s');

                    $objeto = [
                        "recepcion_id" => $recepcion_id,
                        "proyecto_id" => $documento->proyecto_id,
                        "cliente_id" => $documento->cliente_id,
                        "adetalle_id" => $ag_p->adetalle_id,
                        "documento_nombre" => $documento->documento_nombre,
                        "documento_id" => $documento->documento_id,
                        "captura_estado" => 1,
                        "captura_estado_glb" => "cap",
                        "created_at" => $now,
                        "updated_at" => $now];

                    $array[] = $objeto;


                    $captura = new  App\captura();

                    //actualiza el adetalle de documento y el nombre del documento
                    $captura->crear_lista_captura($array, false);

                    //creando Imagen

                    $captura_id = $documento->captura_id;


                    $imagen = new App\imagen();
                    $imagen->recepcion_id = $recepcion_id;
                    $imagen->captura_id = $captura_id;
                    $imagen->documento_id = $documento_id;
                    $imagen->imagen_nombre = $nombre_original;
                    $imagen->imagen_pagina = 1;
                    $imagen->imagen_url = $subir_archivo;
                    $imagen->imagen_estado = 1;
                    $imagen->save();


                    $imagen_id = $imagen->imagen_id;

                    $val = DB::table('imagen')
                        ->where('documento_id', '=', $documento_id)
                        ->whereNotIn('imagen_id', [$imagen_id])
                        ->count();

                    if ($val > 0) {
                        $val = DB::table('imagen')
                            ->where('documento_id', '=', $documento_id)
                            ->whereNotIn('imagen_id', [$imagen_id])
                            ->update(
                                ['imagen_estado' => 0]
                            );

                    }

                    return $ag_p->adetalle_id;
                } else if ($extension == "image/tiff" || $extension == "application/pdf" || $extension == "image/tif") {

                    $path = self::verify_path_capturas($recepcion_id);

                    if ($imagen == "" || $imagen == null) {
                        //NORMAL
                        $subir_archivo = $file->store($path);
                        $pathfile_info = self::informacion_ruta_archivo($root . $subir_archivo);
                        $pathfile_info["origin_name"] = $file->getClientOriginalName(); // Captura el nombre del archivo enviado de front-end

                        $ag = new App\adetalle;
                        $ag->adetalle_url = $subir_archivo;
                        $ag->adetalle_nombre = str_replace(" ", "_", $pathfile_info["origin_name"]);
                        $ag->save();

                    }else{
                        //SCANNER
                        Storage::disk('public')->put($nombre_original, base64_decode($imagen));

                        Storage::move("public/" . $nombre_original, $path . '/' . $nombre_original);

                        $subir_archivo = $path . "/" . $nombre_original;
                        $pathfile_info["origin_name"] = $nombre_original;


                        $pathfile_info = self::informacion_ruta_archivo($root . $subir_archivo);



                        $ag = new App\adetalle;
                        $ag->adetalle_url = $subir_archivo;
                        $ag->adetalle_nombre = str_replace(" ", "_", $nombre_original);
                        $ag->save();

                    }

                    //Guardado automático de captura
                    $captura = new App\captura();

                    $now = date('Y-m-d H:i:s');


                    $proyecto_id = $documento->proyecto_id;
                    $cliente_id = $documento->cliente_id;
                    $adetalle_id = $ag->adetalle_id;
                    $documento_nombre = $documento->documento_nombre;
                    $documento_id = $documento->documento_id;

                    $objeto = [
                        "recepcion_id" => $recepcion_id,
                        "proyecto_id" => $proyecto_id,
                        "cliente_id" => $cliente_id,
                        "adetalle_id" => $adetalle_id,
                        "documento_nombre" => $documento_nombre,
                        "documento_id" => $documento_id,
                        "captura_estado" => 1,
                        "captura_estado_glb" => "cap",
                        "created_at" => $now,
                        "updated_at" => $now];

                    $array[] = $objeto;


                    //hace update en este caso
                    $captura->crear_lista_captura($array, true);


                    $captura_id = $documento->captura_id;

                    $array_imagen = [
                        "recepcion_id" => $recepcion_id,
                        "captura_id" => $captura_id,
                        "documento_id" => $documento_id,
                    ];

                    //joseController::convert_auto($pathfile_info);
                    //return $array_imagen;
                    try
                    {
                        $this->convertir_simple($pathfile_info, $array_imagen);
                    }catch(Exception  $e){
                        return $e->getMessage();
                    }



                    $adetalle_id = $ag->adetalle_id;

                    $archivo_detalle = DB::table('adetalle')->where('adetalle_id', '=', $adetalle_id)
                        ->first();

                    $nombre = $archivo_detalle->adetalle_url;


                    $explode = explode("/", $nombre);

                    $nombre_completo = $explode[count($explode) - 1];

                    $explode2 = explode(".", $nombre_completo);

                    $nombre_sin_extension = $explode2[0];

                    $ids_imagenes = DB::table('imagen')
                        ->where('documento_id', '=', $documento_id)
                        ->where('imagen_nombre', 'ilike', '%' . $nombre_sin_extension . '%')
                        ->select('imagen_id')
                        ->get();

                    $array_ids = array();

                    foreach ($ids_imagenes as $i) {


                        $array_ids[] = $i->imagen_id;

                    }


                    $val = DB::table('imagen')
                        ->where('documento_id', '=', $documento_id)
                        ->whereNotIn('imagen_id', $array_ids)
                        ->update(
                            ['imagen_estado' => 0]
                        );


                    return $ag->adetalle_id;

                } else {

                    $path = self::verify_path_capturas($recepcion_id);
                    $subir_archivo = $file->store($path); // guarda en el servidor con un nombre modificado (HASH)
                    $nombre_original = $file->getClientOriginalName(); // Captura el nombre del archivo enviado de front-end
                    $ag_p = new App\adetalle;
                    $ag_p->adetalle_url = $subir_archivo;
                    $ag_p->adetalle_nombre = str_replace(" ", "_", $nombre_original);
                    $ag_p->save();
                    $nueva_ruta = $root . $path;
                    $explode = explode("/", $nueva_ruta);
                    $nombre_original = $explode[count($explode) - 1];
                    $explode = explode("/app/", $nueva_ruta);
                    $subir_archivo = $explode[count($explode) - 1];

                    $retorno = array($nueva_ruta, $nombre_original, $subir_archivo);

                    //Guardado automático de captura

                    $now = date('Y-m-d H:i:s');

                    $objeto = [
                        "recepcion_id" => $recepcion_id,
                        "proyecto_id" => $documento->proyecto_id,
                        "cliente_id" => $documento->cliente_id,
                        "adetalle_id" => $ag_p->adetalle_id,
                        "documento_nombre" => $documento->documento_nombre,
                        "documento_id" => $documento->documento_id,
                        "captura_estado" => 1,
                        "captura_estado_glb" => "cap",
                        "created_at" => $now,
                        "updated_at" => $now];

                    $array[] = $objeto;


                    $captura = new  App\captura();

                    $captura->crear_lista_captura($array, false);

                    //creando Imagen

                    $captura_id = $documento->captura_id;


                    return $ag_p->adetalle_id;
                }


                return $extension;

            }

        } elseif ($recepcion_tipo == 'm') {

            $recepcion_id = request('recepcion_id');

            $captura_estado = request('captura_estado');



            $recepcion = DB::table('recepcion')->where('recepcion_id', '=', $recepcion_id)
                ->first();

            if ($imagen != "" || $imagen != null) {
                $file = "lala";
            }


            if (!is_null($file)) {


                if ($imagen == "" || $imagen == null) {
                    $extension = $file->getClientMimeType();
                } else {
                    //  $extension = "image/jpg";
                    $nombre_original = request("nombre");

                    $a=  explode(".",$nombre_original);
                    $contador = count($a);
                    $extension_archivo = $a[$contador -1 ];

                    if ($extension_archivo == "jpg" || $extension_archivo == "jpeg") {
                        $extension = "image/jpg";
                    } elseif ($extension_archivo == "pdf" || $extension_archivo == "tiff") {
                        $extension = "application/pdf";
                    } else {

                    }
                }

                if ($extension == "image/jpg" || $extension == "image/jpeg" || $extension == "image/bmp" || $extension == "image/png" || $extension == "image/gif") {


                    $path = self::verify_path_capturas($recepcion_id);

                    if ($imagen == "" || $imagen == null) {

                        $subir_archivo = $file->store($path); // guarda en el servidor con un nombre modificado (HASH)
                        $nombre_original = $file->getClientOriginalName(); // Captura el nombre del archivo enviado de front-end
                        $nombre_original_guardado = $file->getClientOriginalName(); // Captura el nombre del archivo enviado de front-end

                    } else {

                        $nombre_original = request("nombre");
                        $nombre_original_guardado = request("nombre");

                        Storage::disk('public')->put($nombre_original, base64_decode($imagen));

                        Storage::move("public/" . $nombre_original, $path . '/' . $nombre_original);


                        $subir_archivo = $path . "/" . $nombre_original;

                    }

                    $ag_p = new App\adetalle;
                    $ag_p->adetalle_url = $subir_archivo;
                    $ag_p->adetalle_nombre = str_replace(" ", "_", $nombre_original);
                    $ag_p->save();
                    $nueva_ruta = self::convert_to_jpeg($root . $subir_archivo, $root . $path . "/imagenes");
                    $explode = explode("/", $nueva_ruta);
                    $nombre_original = $explode[count($explode) - 1];
                    $explode = explode("/app/", $nueva_ruta);
                    $subir_archivo = $explode[count($explode) - 1];
                    $ag = new App\adetalle;
                    $ag->adetalle_url = $subir_archivo;
                    $ag->adetalle_nombre = str_replace(" ", "_", $nombre_original);
                    $ag->save();
                    $retorno = array($nueva_ruta, $nombre_original, $subir_archivo, $ag->adetalle_id);

                    //Guardado automático de captura
                    $captura = new App\captura();

                    $now = date('Y-m-d H:i:s');

                    $objeto = [
                        "recepcion_id" => $recepcion_id,
                        "proyecto_id" => $recepcion->proyecto_id,
                        "cliente_id" => $recepcion->cliente_id,
                        "adetalle_id" => $ag_p->adetalle_id,
                        "documento_nombre" => $nombre_original_guardado,
                        "captura_estado" => $captura_estado,
                        "captura_estado_glb" => "cap",
                        "imagen_nombre" => $nombre_original,
                        "imagen_pagina" => 1,
                        "imagen_url" => $subir_archivo,
                        "created_at" => $now,
                        "updated_at" => $now];

                    $array[] = $objeto;

                    $captura->crear_lista_captura($array, true);


                    return $ag_p->adetalle_id;


                } else if ($extension == "image/tiff" || $extension == "application/pdf" || $extension == "image/tif") {

                    $path = self::verify_path_capturas($recepcion_id);

                    if ($imagen == "" || $imagen == null) {
                        $subir_archivo = $file->store($path);
                        $pathfile_info = self::informacion_ruta_archivo($root . $subir_archivo);
                        $pathfile_info["origin_name"] = $file->getClientOriginalName(); // Captura el nombre del archivo enviado de front-end

                        $ag = new App\adetalle;
                        $ag->adetalle_url = $subir_archivo;
                        $ag->adetalle_nombre = str_replace(" ", "_", $pathfile_info["origin_name"]);
                        $ag->save();

                    }else{

                        Storage::disk('public')->put($nombre_original, base64_decode($imagen));

                        Storage::move("public/" . $nombre_original, $path . '/' . $nombre_original);

                        $subir_archivo = $path . "/" . $nombre_original;


                        $pathfile_info = self::informacion_ruta_archivo($root . $subir_archivo);

                        $pathfile_info["origin_name"] = $nombre_original;


                        $ag = new App\adetalle;
                        $ag->adetalle_url = $subir_archivo;
                        $ag->adetalle_nombre = str_replace(" ", "_", $pathfile_info["origin_name"]);
                        $ag->save();

                        //return "lala";
                    }

                    //Guardado automático de captura
                    $captura = new App\captura();

                    $now = date('Y-m-d H:i:s');

                    $objeto_datos = [
                        "recepcion_id" => $recepcion_id,
                        "proyecto_id" => $recepcion->proyecto_id,
                        "cliente_id" => $recepcion->cliente_id,
                        "adetalle_id" => $ag->adetalle_id,
                        "captura_estado" => $captura_estado,
                        "captura_estado_glb" => "cap",
                        "created_at" => $now,
                        "updated_at" => $now
                    ];

                    $array_datos[] = $objeto_datos;
                    //  $captura->crear_lista_captura($array);

                    //joseController::convert_auto($pathfile_info);
                  //  $this->convertir_multiple($pathfile_info, $array_datos);
                    try
                    {
                        $this->convertir_multiple($pathfile_info, $array_datos);
                    }catch(Exception  $e){
                        return $e->getMessage();
                    }


                    return $ag->adetalle_id;
                } else {

                    $path = self::verify_path_capturas($recepcion_id);
                    $subir_archivo = $file->store($path); // guarda en el servidor con un nombre modificado (HASH)
                    $nombre_original = $file->getClientOriginalName(); // Captura el nombre del archivo enviado de front-end
                    $nombre_original_guardado = $file->getClientOriginalName(); // Captura el nombre del archivo enviado de front-end
                    $ag_p = new App\adetalle;
                    $ag_p->adetalle_url = $subir_archivo;
                    $ag_p->adetalle_nombre = str_replace(" ", "_", $nombre_original);
                    $ag_p->save();

                    $nueva_ruta = $root . $path;


                    $explode = explode("/", $nueva_ruta);
                    $nombre_original = $explode[count($explode) - 1];
                    $explode = explode("/app/", $nueva_ruta);
                    $subir_archivo = $explode[count($explode) - 1];

                    $retorno = array($nueva_ruta, $nombre_original, $subir_archivo);

                    //Guardado automático de captura
                    $captura = new App\captura();

                    $now = date('Y-m-d H:i:s');

                    $objeto = [
                        "recepcion_id" => $recepcion_id,
                        "proyecto_id" => $recepcion->proyecto_id,
                        "cliente_id" => $recepcion->cliente_id,
                        "adetalle_id" => $ag_p->adetalle_id,
                        "documento_nombre" => $nombre_original_guardado,
                        "captura_estado" => $captura_estado,
                        "captura_estado_glb" => "cap",
                        "imagen_nombre" => $nombre_original,
                        "imagen_pagina" => 1,
                        "imagen_url" => $subir_archivo,
                        "created_at" => $now,
                        "updated_at" => $now];

                    $array[] = $objeto;

                    $captura->crear_lista_captura($array, true);

                    return $ag_p->adetalle_id;


                }
                return $extension;

            } else {
                return "no se ha recibido ningun archivo";
            }
        }
    }
*/
    /*Función para obtener la extensión

    public function get_file_extension($file_name)
    {
      //  $a = substr(strrchr($file_name, '.'), 1);
        $a=  explode(".",$file_name);
        $contador = count($a);
        return $a[$contador -1 ];
    }

    /**
     * Recibe un archivo y verifica su extension antes de guardarlo
     * @param string $path_file Ruta de la ubicacion del archivo
     * @return array Informacion de la ruta y el archivo [onlyname, extension, path_origin, path, path_destiny]
     * @author Juan Ignacio Basilio Flores
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function informacion_ruta_archivo($path_file)
    {
        $retorno = array();
        $retorno["path"] = $path_file;
        $explode = explode(".", $path_file);
        $retorno["extension"] = $explode[count($explode) - 1];
        $explode = explode("/", $path_file);
        $filename = explode(".", $explode[count($explode) - 1]);
        array_pop($filename);
        $retorno["only_name"] = implode(".", $filename);
        array_pop($explode);
        $retorno["path_origin"] = implode("/", $explode);
        $retorno["path_destiny"] = $retorno["path_origin"] . "/imagenes";
        return $retorno;
    }

    /**
     * Verifica la existencia de los directorios a partir del id_recepcion, para asegurar las carpetas
     * @param int $id_recepcion Identificador de la recepcion
     * @return string Direccion de donde se debe guardar los archivos
     * @author Juan Ignacio Basilio Flores
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function verify_path_capturas($recepcion_id)
    {
        $root = storage_path() . "/app/";
        $path_base = "documentos";
        $info = App\recepcion::where('recepcion_id', $recepcion_id)
            ->join("proyecto as p", "p.proyecto_id", "recepcion.proyecto_id")
            ->first();
        if (isset($info["proyecto_nombre"]) && isset($info["recepcion_nombre"])) {
            if (self::ensure_path_directory($root . $path_base . "/" . $info["proyecto_nombre"])) {
                $path_base .= "/" . $info["proyecto_nombre"];
                if (self::ensure_path_directory($root . $path_base . "/" . $info["recepcion_nombre"])) {
                    $path_base .= "/" . $info["recepcion_nombre"];
                    if (self::ensure_path_directory($root . $path_base . "/imagenes")) {

                    } else {
                        return false;
                    }
                }
            }
        }
        return $path_base;
    }

    /**
     * Asegura la existencia de la ruta $path
     * @param string $path Ruta que sera verificada
     * @return boolean Devuelve TRUE, en caso de que haya ocurrido algun error retorna FALSE
     * @author Juan Ignacio Basilio Flores
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function ensure_path_directory($path)
    {
        if (!file_exists($path . "/")) {
            if (mkdir($path, 0777, true)) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Convierte las imagenes de termnacion jpg,png,bmp,gif a formato JPEG
     * @param string $image_path Direccion de de la imagen en el proyecto
     * @param string $dir_path Directorio en el que se guardara la nueva imagen JPEG
     * @param int $quality Calidad de la imagen que puede estar entre 0 y 100
     * @return string direccion del nuevo archivo o false en caso de que el formato no sea correcto
     * @author Juan Ignacio Basilio Flores
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function convert_to_jpeg($image_path, $dir_path = "", $quality = 100)
    {
        $exploded = explode('.', $image_path);
        $ext = $exploded[count($exploded) - 1];
        $exploded[count($exploded) - 1] = "jpeg";
        $exploded[count($exploded) - 2] .= "_1";
        $nuevo_path = implode(".", $exploded);

        if ($dir_path != "") {
            if (self::ensure_path_directory($dir_path)) {
                $exploded = explode('/', $nuevo_path);
                $nuevo_path = $dir_path . "/" . $exploded[count($exploded) - 1];
            } else {
                return false;
            }
        }

        if (preg_match('/jpg|jpeg/i', $ext)) {
            if (copy($image_path, $nuevo_path)) {
                return $nuevo_path;
            } else {
                return false;
            }
        } else if (preg_match('/png/i', $ext)) {
            $input = imagecreatefrompng($image_path);
            list($width, $height) = getimagesize($image_path);
            $image_tmp = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($image_tmp, 255, 255, 255);
            imagefilledrectangle($image_tmp, 0, 0, $width, $height, $white);
            imagecopy($image_tmp, $input, 0, 0, 0, 0, $width, $height);
        } else if (preg_match('/gif/i', $ext)) {
            $image_tmp = imagecreatefromgif($image_path);
        } else if (preg_match('/bmp/i', $ext)) {
            $image_tmp = self::imagecreatefrombmp($image_path);
        } else {
            return false;
        }

        // quality is a value from 0 (worst) to 100 (best)
        imagejpeg($image_tmp, $nuevo_path, $quality);
        imagedestroy($image_tmp);

        return $nuevo_path;
    }

    /**
     * Reemplaza a la funcion de php imagecreatefrombmp que no funciono en las pruebas, No tocar
     * @param string $dir Direccion del archivo en el proyecto
     * @return image Retorna el identificador del recurso de imagen
     * @author Juan Ignacio Basilio Flores
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function imagecreatefrombmp($dir)
    {
        $bmp = "";
        if (file_exists($dir)) {
            $file = fopen($dir, "r");
            while (!feof($file)) $bmp .= fgets($file, filesize($dir));
            if (substr($bmp, 0, 2) == "BM") {
                // Lecture du header
                $header = unpack("vtype/Vlength/v2reserved/Vbegin/Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant", $bmp);
                extract($header);
                // Lecture de l'image
                $im = imagecreatetruecolor($width, $height);
                $i = 0;
                $diff = floor(($imagesize - ($width * $height * ($bits / 8))) / $height);
                for ($y = $height - 1; $y >= 0; $y--) {
                    for ($x = 0; $x < $width; $x++) {
                        if ($bits == 32) {
                            $b = ord(substr($bmp, $begin + $i, 1));
                            $v = ord(substr($bmp, $begin + $i + 1, 1));
                            $r = ord(substr($bmp, $begin + $i + 2, 1));
                            $i += 4;
                        } else if ($bits == 24) {
                            $b = ord(substr($bmp, $begin + $i, 1));
                            $v = ord(substr($bmp, $begin + $i + 1, 1));
                            $r = ord(substr($bmp, $begin + $i + 2, 1));
                            $i += 3;
                        } else if ($bits == 16) {
                            $tot1 = decbin(ord(substr($bmp, $begin + $i, 1)));
                            while (strlen($tot1) < 8) $tot1 = "0" . $tot1;
                            $tot2 = decbin(ord(substr($bmp, $begin + $i + 1, 1)));
                            while (strlen($tot2) < 8) $tot2 = "0" . $tot2;
                            $tot = $tot2 . $tot1;
                            $r = bindec(substr($tot, 1, 5)) * 8;
                            $v = bindec(substr($tot, 6, 5)) * 8;
                            $b = bindec(substr($tot, 11, 5)) * 8;
                            $i += 2;
                        }
                        $col = imagecolorexact($im, $r, $v, $b);
                        if ($col == -1) $col = imagecolorallocate($im, $r, $v, $b);
                        imagesetpixel($im, $x, $y, $col);
                    }
                    $i += $diff;
                }
                // retourne l'image
                return $im;
                imagedestroy($im);
            } else return false;
        } else return false;
    }


    public function convertir_simple($array, $imagen)
    {

        // return $array;


        //return $array['only_name'];
        $only_name = (string)$array['only_name'];
        // return $only_name;

        $extension = (string)$array['extension'];
        //$origin_name = (string) $array['origin_name'];
        $full_path_name = (string)$array['path'];


        $path_destiny = $array['path_destiny'];


        $path_origin = $array['path_origin'];


        $recepcion_id = $imagen['recepcion_id'];
        $captura_id = $imagen['captura_id'];
        $documento_id = $imagen['documento_id'];


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
        $imagenes = array();


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


                $array_tmp = [];
                $array_tmp['recepcion_id'] = $recepcion_id;
                $array_tmp['captura_id'] = $captura_id;
                $array_tmp['documento_id'] = $documento_id;
                $array_tmp['imagen_nombre'] = $only_name;
                $array_tmp['imagen_pagina'] = $i;
                $array_tmp['imagen_url'] = $adetalle_url;
                $array_tmp['imagen_estado'] = 1;
                $array_tmp['created_at'] = $now;
                $array_tmp['updated_at'] = $now;

                $imagenes[] = $array_tmp;

            }


            DB::table('adetalle')->insert(
                $elementos
            );
            DB::table('imagen')->insert(
                $imagenes
            );

        } elseif ($extension == 'tiff' || $extension == 'tif' ) {

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

                $array_tmp = [];
                $array_tmp['recepcion_id'] = $recepcion_id;
                $array_tmp['captura_id'] = $captura_id;
                $array_tmp['documento_id'] = $documento_id;
                $array_tmp['imagen_nombre'] = $only_name;
                $array_tmp['imagen_pagina'] = $i;
                $array_tmp['imagen_url'] = $adetalle_url;
                $array_tmp['imagen_estado'] = 1;
                $array_tmp['created_at'] = $now;
                $array_tmp['updated_at'] = $now;

                $imagenes[] = $array_tmp;

            }

            DB::table('adetalle')->insert(
                $elementos
            );
            DB::table('imagen')->insert(
                $imagenes
            );

        } else {
            return "error de formato";

        }


        return 'ok';
    }

    public function convertir_multiple($array, $captura)
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
        $imagenes = array();

        $flag_file = true;

        foreach ($captura as $cap) {

            if(empty($cap["captura_file_id"])){
                // Creando la captura file
                $file_created = filesController::create_captura([
                    "recepcion_id" => (!empty($cap["recepcion_id"]))? $cap['recepcion_id'] : 0,
                    "nombre" => (!empty($origin_name))? $origin_name : "",
                    "captura_estado" => (!empty($cap["captura_estado"]))? $cap['captura_estado'] : 0,
                    "padre_id" => (!empty($cap["padre_id"]))? $cap['padre_id'] : 0
                ]);

                if(!$file_created["estado"]){
                    $flag_file = false;
                } else {
                    $file_created = $file_created["payload"];
                }

                $cap["captura_file_id"] = $file_created["file_id"];
            }

            $captura_instancia = new App\captura();
            $captura_instancia->proyecto_id = $cap['proyecto_id'];
            $captura_instancia->recepcion_id = $cap['recepcion_id'];
            $captura_instancia->cliente_id = $cap['cliente_id'];
            $captura_instancia->captura_file_id = (!empty($cap['captura_file_id']))? $cap['captura_file_id'] : null;
            $captura_instancia->captura_estado = $cap['captura_estado'];
            $captura_instancia->captura_estado_glb = $cap['captura_estado_glb'];
            $captura_instancia->usuario_creador = session("usuario_id");
            $captura_instancia->flujo_id_actual = 1;
            $captura_save = $captura_instancia->save();

            $captura_id = $captura_instancia['captura_id'];

            //grabamos log de captura
            $log = new App\log();
            $log->create_log_ez(
                        $captura_id,//$log_captura_id  ,
                        $captura_id,//$log_id_asociado  ,
                        1,//$log_modulo_step_id  ,
                        'captura',//$log_tabla_asociada  ,
                        'CAP',//$log_proceso  ,
                        'Ingreso de Captura',//$log_descripcion  ,
                        '',//$log_comentario  ,
                        null//$log_archivo_id
                    );

            //grabar proyecto_captura_flujo
            (new App\proyecto_captura_flujo())->crear_PCF_from_PF($captura_id);

            $documento_instancia = new App\documento();
            $documento_instancia->captura_id = $captura_id;
            $documento_instancia->recepcion_id = $cap['recepcion_id'];
            $documento_instancia->proyecto_id = $cap['proyecto_id'];
            $documento_instancia->cliente_id = $cap['cliente_id'];
            if (isset($cap['adetalle_id'])) {

                $documento_instancia->adetalle_id = $cap['adetalle_id'];
            }

            $documento_instancia->documento_nombre = $origin_name;
            $documento_instancia->documento_estado = 1;
            $documento_instancia->save();
            $documento_save = $documento_instancia->save();


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


                    $array_tmp_imagen = [];
                    $array_tmp_imagen['recepcion_id'] = $cap['recepcion_id'];
                    $array_tmp_imagen['captura_id'] = $captura_id;
                    $array_tmp_imagen['documento_id'] = $documento_instancia['documento_id'];
                    //$array_tmp_imagen['imagen_nombre'] = $only_name;
                    $array_tmp_imagen['imagen_nombre'] = $adetalle_nombre;
                    $array_tmp_imagen['imagen_pagina'] = $i;
                    $array_tmp_imagen['imagen_estado'] = 1;
                    $array_tmp_imagen['imagen_url'] = $adetalle_url;
                    $array_tmp_imagen['created_at'] = $now;
                    $array_tmp_imagen['updated_at'] = $now;

                    $imagenes[] = $array_tmp_imagen;
                }

                DB::table('adetalle')->insert(
                    $elementos
                );
                DB::table('imagen')->insert(
                    $imagenes
                );

            } elseif ($extension == 'tiff' || $extension == 'tif') {

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

                    $array_tmp_imagen = [];
                    $array_tmp_imagen['recepcion_id'] = $cap['recepcion_id'];
                    $array_tmp_imagen['captura_id'] = $captura_id;
                    $array_tmp_imagen['documento_id'] = $documento_instancia['documento_id'];
                    //$array_tmp_imagen['imagen_nombre'] = $only_name;
                    $array_tmp_imagen['imagen_nombre'] = $adetalle_nombre;
                    $array_tmp_imagen['imagen_pagina'] = $i;
                    $array_tmp_imagen['imagen_url'] = $adetalle_url;
                    $array_tmp_imagen['created_at'] = $now;
                    $array_tmp_imagen['updated_at'] = $now;

                    $imagenes[] = $array_tmp_imagen;
                }

                DB::table('adetalle')->insert(
                    $elementos
                );
                DB::table('imagen')->insert(
                    $imagenes
                );

            } else {
                return "error de formato";

            }

        }

        if(!$flag_file){
            return response("Los registros file no se han creado correctamente.", 500);
        }

        return 'ok';
    }


}
