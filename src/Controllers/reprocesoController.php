<?php

namespace Fedatario\Controllers;

use DemeterChain\A;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use function Matrix\trace;
use View;
use Response;
use App;
use Illuminate\Support\Facades\Storage;
use Imagick;
use Spatie;
use DateTime;
use Illuminate\Http\UploadedFile;
use App\Http\Controllers\filesController;
use App\Services\PayUService\Exception;
use finfo;

Trait reprocesoController
{

    private $tiempo_de_ejecucion = 1200;

    public function __construct()
    {

        $this->middleware('auth', ['except' => ['reemplazar_scanner_reproceso', 'reemplazar_tiff_reproceso']]);
        parent::__construct();

    }

    public function index()
    {
        //Instancia Documento
        $ins_documento = new App\documento();
        //Instancia Incidencia
        $ins_incidencia = new App\Http\Controllers\incidenciaController();

        $lista_documentos = $ins_documento->listar_documento();
        $incidencia = $ins_incidencia->listar_incidencia();

        return view::make('reproceso.index.content')
            ->with("lotes", $lista_documentos)
            ->with("incidencia", $incidencia);
    }

    public function lista_arbol_reproceso()
    {

        $data = (new App\incidencia_imagen())->arbol_reproceso();
        $array_proyecto = array();
        $array_recepcion = array();
        $array_captura = array();
        $recepcion_old = "0";
        $recepcion_nombre_old = "";
        $proyecto_old = "0";
        $proyecto_nombre_old = "";
        $prefijo_id_captura = "captura_";
        foreach ($data as $fila) {
            if ($fila->recepcion_id != $recepcion_old && $recepcion_old != "0") {
                $array_recepcion[] = [
                    "id_recepcion" => $recepcion_old,//$fila->recepcion_id,
                    "text" => $recepcion_nombre_old,
                    "children" => $array_captura
                ];
                $array_captura = array();

                if ($fila->proyecto_id != $proyecto_old && $proyecto_old != "0") {
                    $array_proyecto[] = [
                        "id_proyecto" => $proyecto_old,//$fila->proyecto_id,
                        "text" => $proyecto_nombre_old,
                        "children" => $array_recepcion
                    ];
                    $array_recepcion = array();
                }
            }
            $array_captura[] = [
                "id" => $prefijo_id_captura . $fila->captura_id,
                "text" => $fila->documento_nombre,
                "icon" => "fa fa-file",
                "id_captura" => $fila->captura_id,
                "flag_reproceso" => $fila->flag_reproceso,
                "indizacion_id" => $fila->indizacion_id,
                "id_documento" => $fila->documento_id,
                "adetalle_id" => $fila->adetalle_id,
                "recepcion_tipo" => $fila->recepcion_tipo,
                "proyecto_id" => $fila->proyecto_id,
                "recepcion_id" => $fila->recepcion_id,
                "indizacion_tipo" => $fila->indizacion_tipo
            ];
            $recepcion_old = $fila->recepcion_id;
            $recepcion_nombre_old = $fila->recepcion_nombre;

            $proyecto_old = $fila->proyecto_id;
            $proyecto_nombre_old = $fila->proyecto_nombre;
        }

        if ($recepcion_old != "0" && $proyecto_old != "0") {

            $array_recepcion[] = [
                "id_recepcion" => $recepcion_old,
                "text" => $recepcion_nombre_old,
                "children" => $array_captura
            ];
            $array_proyecto[] = [
                "id_proyecto" => $proyecto_old,
                "text" => $proyecto_nombre_old,
                "children" => $array_recepcion
            ];
        }
        return $array_proyecto;

    }

    public function listar_plantilla_reproceso()
    {

        $plantilla_id = request("plantilla_id");
        $captura_id = request("captura_id");
        $proyecto_id = request("proyecto_id");
        $recepcion_id = request("recepcion_id");
        $indizacion_id= request("indizacion_id");

        //validacion de autoasignacion
        $usuario_id = session('usuario_id');

        $cant = App\captura::where('captura_id', $captura_id)
            ->whereNotNull('usuario_asignado_reproceso')
            ->Where('usuario_asignado_reproceso', '!=', $usuario_id)
            ->count();

        if($cant >0){
            $tipo = 'error';
            $mensaje = 'La captura ya ha sido asignada a otro usuario respecto a su Reproceso';
            return Controller::crear_objeto($tipo,$mensaje);
        }

        $captura = App\captura::where('captura_id',$captura_id)
            ->update(['usuario_asignado_reproceso' => $usuario_id ]);

        $data = DB::select(
            'select
        a.plantilla_id,a.plantilla_nombre,a.plantilla_estado,a.usuario_creador,
        b.elemento_id,b.elemento_nombre,b.plantilla_id,b.elemento_tipo,
        c.simple_id,c.simple_tipo_dato,c.simple_tipo_formato,c.plantilla_id,
        d.combo_id,d.plantilla_id,
        e.opcion_id,e.opcion_nombre,e.combo_id,e.plantilla_id,
        f.elemento_opciones_id,f.plantilla_id,f.elemento_opciones_incremental,f.elemento_opciones_guia,f.elemento_opciones_multipagina,f.elemento_opciones_obligatorio,
        h.tipo_elemento_id,h.plantilla_id,h.tipo_elemento_nombre,h.tipo_elemento_abreviacion,
        g.respuesta_id,
        g.valor
        from plantilla a
        left join elemento b on a.plantilla_id = b.plantilla_id
        left join simple c on b.elemento_id = c.elemento_id
        left join combo d on d.elemento_id=b.elemento_id
        left join opcion e on e.combo_id = d.combo_id
        left join elemento_opciones f on f.elemento_id = b.elemento_id
        left join respuesta g on g.elemento_id= b.elemento_id and g.indizacion_id=:indizacion_id
        left join tipo_elemento h on h.tipo_elemento_id= b.elemento_tipo
        where a.plantilla_id= :plantilla_id
        order by a.plantilla_id,b.elemento_id,c.simple_id,d.combo_id,e.opcion_id;'
            , ['indizacion_id' => $indizacion_id, 'plantilla_id' => $plantilla_id]);

        // return (array)$data;


        $array_final = [
            "plantilla_id" => "",
            "plantilla_nombre" => "",
            "plantilla_estado" => "",
            "usuario_creador" => "",
            "elementos" => []
        ];

        $plantilla_id_old = null;
        $elemento_id_old = null;
        $opcion_id_old = null;
        $tipo_elemento_id_old = null;
        $elemento_array = array();
        $opcion_array = array();
        $i = 0;
        foreach ((array)$data as $key => $fila) {
            $fila = (array)$fila;
            // return $fila;
            if ($plantilla_id_old === null || $fila["plantilla_id"] !== $plantilla_id_old) {
                $plantilla_id_old = $fila["plantilla_id"];
                $array_final["plantilla_id"] = $plantilla_id_old;
                $array_final["plantilla_id"] = $fila["plantilla_id"];
                $array_final["plantilla_nombre"] = $fila["plantilla_nombre"];
                $array_final["plantilla_estado"] = $fila["plantilla_estado"];
                $array_final["usuario_creador"] = $fila["usuario_creador"];
            }

            if ($elemento_id_old === null || $elemento_id_old !== $fila["elemento_id"]) {

                // if($elemento_id_old!==null){
                if (!is_null($elemento_id_old)) {

                    if ($tipo_elemento_id_old === 2) {// && count($opcion_array)>0
                        $elemento_array["children_combo"][0]["children"] = $opcion_array;
                    }

                    array_push($array_final["elementos"], $elemento_array);
                }
                $elemento_array = [];
                $opcion_array = [];

                $elemento_array["elemento_id"] = $fila["elemento_id"];
                $elemento_array["elemento_nombre"] = $fila["elemento_nombre"];
                $elemento_array["plantilla_id"] = $fila["plantilla_id"];
                $elemento_array["elemento_tipo"] = $fila["elemento_tipo"];

                $elemento_array["elemento_opciones"] = [
                    [
                        "elemento_opciones_id" => $fila["elemento_opciones_id"],
                        "plantilla_id" => $fila["plantilla_id"],
                        "elemento_id" => $fila["elemento_id"],
                        "elemento_opciones_incremental" => $fila["elemento_opciones_incremental"],
                        "elemento_opciones_guia" => $fila["elemento_opciones_guia"],
                        "elemento_opciones_multipagina" => $fila["elemento_opciones_multipagina"],
                        "elemento_opciones_obligatorio" => $fila["elemento_opciones_obligatorio"]
                    ]
                ];
                $elemento_array["tipo_elemento"] = [
                    [
                        "tipo_elemento_id" => $fila["tipo_elemento_id"],
                        "plantilla_id" => $fila["plantilla_id"],
                        "elemento_id" => $fila["elemento_id"],
                        "tipo_elemento_nombre" => $fila["tipo_elemento_nombre"],
                        "tipo_elemento_abreviacion" => $fila["tipo_elemento_abreviacion"]
                    ]
                ];
                if ($fila["tipo_elemento_id"] === 1) {
                    $elemento_array["children_simple"] = [
                        [
                            "simple_id" => $fila["simple_id"],
                            "simple_tipo_dato" => $fila["simple_tipo_dato"],
                            "simple_tipo_formato" => $fila["simple_tipo_formato"],
                            "elemento_id" => $fila["elemento_id"],
                            "plantilla_id" => $fila["plantilla_id"]
                        ]
                    ];
                    $elemento_array["children_combo"] = [];
                } else if ($fila["tipo_elemento_id"] === 2) {
                    $elemento_array["children_simple"] = [];
                    $elemento_array["children_combo"] = [
                        [
                            "combo_id" => $fila["combo_id"],
                            "elemento_id" => $fila["elemento_id"],
                            "plantilla_id" => $fila["plantilla_id"],
                            "children" => []
                        ]
                    ];
                }
                //zona de respuestas
                $elemento_array["respuesta_id"] = $fila["respuesta_id"];
                $elemento_array["valor"] = $fila["valor"];

                $elemento_id_old = $fila["elemento_id"];
                // return $elemento_id_old;

            }
            if ($fila["tipo_elemento_id"] === 2) {
                array_push($opcion_array,
                    [
                        "opcion_id" => $fila["opcion_id"],
                        "opcion_nombre" => $fila["opcion_nombre"],
                        "combo_id" => $fila["combo_id"],
                        "elemento_id" => $fila["elemento_id"],
                        "plantilla_id" => $fila["plantilla_id"]

                    ]
                );
            }
            // return $fila["tipo_elemento_id"];
            $tipo_elemento_id_old = $fila["tipo_elemento_id"];
            // return $tipo_elemento_id_old;

        }
        //agrego un push mas para la ultima iteracion
        if ($tipo_elemento_id_old === 2) {// && count($opcion_array)>0
            $elemento_array["children_combo"][0]["children"] = $opcion_array;
        }
        if (count($elemento_array) > 0) {
            array_push($array_final["elementos"], $elemento_array);
        }
        return self::jsonPlantilla_indizacion_control([$array_final]);
        return [$array_final];

    }

    public function jsonPlantilla_indizacion_control($plantillas, $demo = 0)
    {

        $retorno = array();

        foreach ($plantillas as $i => $plantilla) {

            $plantilla_id = $plantilla["plantilla_id"];
            $plantilla_nombre = $plantilla["plantilla_nombre"];

            $elementos = array();

            foreach ($plantilla["elementos"] as $j => $elemento) {
                $elemento_opciones = array();
                $elemento_final = array();

                $elemento_id = $elemento["elemento_id"];
                $elemento_nombre = $elemento["elemento_nombre"];
                $plantilla_id = $elemento["plantilla_id"];
                $te_abreviacion = $elemento["tipo_elemento"][0]["tipo_elemento_abreviacion"];
                $te_id = (string)$elemento["tipo_elemento"][0]["tipo_elemento_id"];
                $te_nombre = $elemento["tipo_elemento"][0]["tipo_elemento_nombre"];

                // faltan los te (tipo elemento)


                $elemento_opciones = array(
                    "eo_incremental" => $elemento["elemento_opciones"][0]["elemento_opciones_incremental"],
                    "eo_guia" => $elemento["elemento_opciones"][0]["elemento_opciones_guia"],
                    "eo_multipagina" => $elemento["elemento_opciones"][0]["elemento_opciones_multipagina"],
                    "eo_obligatorio" => $elemento["elemento_opciones"][0]["elemento_opciones_multipagina"]
                );
                // para sacar los elementos simples
                $children_simple = array();
                if (count($elemento["children_simple"]) > 0) {

                    $children_simple = array(
                        "plantilla_id" => $plantilla_id,
                        "elemento_id" => $elemento_id,
                        "simple_id" => $elemento["children_simple"][0]["simple_id"],
                        "simple_tipo_dato" => $elemento["children_simple"][0]["simple_tipo_dato"],
                        "simple_tipo_formato" => $elemento["children_simple"][0]["simple_tipo_formato"]
                        //falta tipo elemento
                    );
                    array_push($elementos, array(
                        "elemento_id" => $elemento_id,
                        "elemento_nombre" => $elemento_nombre,
                        "plantilla_id" => $plantilla_id,
                        "elemento_opciones" => $elemento_opciones,
                        "te_abreviacion" => $te_abreviacion,
                        "te_id" => $te_id,
                        "te_nombre" => $te_nombre,
                        "subelemento" => $children_simple,
                        "respuesta_id" => $elemento["respuesta_id"],
                        "respuesta_valor" => $elemento["valor"]
                        //falta tipo elemento
                    ));
                }
                // para sacar los elementos combo
                $children_combo = array();
                if (count($elemento["children_combo"]) > 0) {
                    $opciones = array();
                    foreach ($elemento["children_combo"][0]["children"] as $k => $opcion) {

                        array_push($opciones,
                            array(
                                "plantilla_id" => $opcion["plantilla_id"],
                                "elemento_id" => $opcion["elemento_id"],
                                "combo_id" => $opcion["combo_id"],
                                "opcion_id" => $opcion["opcion_id"],
                                "opcion_nombre" => $opcion["opcion_nombre"],
                            )
                        );

                    }

                    $children_combo = array(
                        "plantilla_id" => $plantilla_id,
                        "elemento_id" => $elemento_id,
                        "combo_id" => $elemento["children_combo"][0]["combo_id"],
                        "opciones" => $opciones
                        //falta tipo elemento
                    );
                    array_push($elementos, array(
                        "elemento_id" => $elemento_id,
                        "elemento_nombre" => $elemento_nombre,
                        "plantilla_id" => $plantilla_id,
                        "elemento_opciones" => $elemento_opciones,
                        "te_abreviacion" => $te_abreviacion,
                        "te_id" => $te_id,
                        "te_nombre" => $te_nombre,
                        "subelemento" => $children_combo,
                        "respuesta_id" => $elemento["respuesta_id"],
                        "respuesta_valor" => $elemento["valor"]
                        //falta tipo elemento
                    ));
                }

            }
            // array_push($retorno,
            //     array(
            //         "plantilla_id" => $plantilla_id,
            //         "plantilla_nombre" => $plantilla_nombre,
            //         "elementos" => $elementos

            //     )
            // );
            $retorno = array(
                "plantilla_id" => $plantilla_id,
                "plantilla_nombre" => $plantilla_nombre,
                "elementos" => $elementos

            );

        }

        return $retorno;
    }

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

    public function convert_to_jpeg($image_path, $dir_path = "", $quality = 100)
    {
        $exploded = explode('.', $image_path);
        $ext = $exploded[count($exploded) - 1];
        $exploded[count($exploded) - 1] = "jpg";
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


    public function validar_update_estado_imagen($array_imagen_id, $documento_id)
    {
        $val = DB::table('imagen')
            ->where('documento_id', '=', $documento_id)
            ->whereNotIn('imagen_id', $array_imagen_id)
            ->update(
                ['imagen_estado' => 0]
            );
    }

    public function convertir_simple($array, $imagen, $opcion, $nro_pagina, $imagen_id)
    {

        $only_name = (string)$array['only_name'];

        $extension = (string)$array['extension'];
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

        $array_nuevo = array();
        $imagen = array();

        if ($extension == 'pdf') {

            for ($i = 1; $i <= $numero_paginas; $i++) {

                $adetalle_url = $path_url . "/" . $only_name . '_' . $i . '.jpg';
                $adetalle_nombre = $only_name . '_' . $i . '.jpg';

                $now = new DateTime();
                $imagen = new App\imagen();
                $imagen->recepcion_id = intval($recepcion_id);
                $imagen->captura_id = intval($captura_id);
                $imagen->documento_id = intval($documento_id);
                $imagen->imagen_nombre = $only_name;
                $imagen->imagen_url = $adetalle_url;
                $imagen->imagen_pagina = $nro_pagina;
                $imagen->imagen_estado = 1;

                $array_nuevo[] = $imagen;

            }


            $is_incidencia_imagen = new App\incidencia_imagen();

            if ($opcion == 1) {
                $is_incidencia_imagen->insertarImagenAntes($array_nuevo, $recepcion_id, $captura_id, $documento_id, $nro_pagina);
                return 'ok';
            } elseif ($opcion == 2) {
                $is_incidencia_imagen->insertarImagenDespues($array_nuevo, $recepcion_id, $captura_id, $documento_id, $nro_pagina);
                return 'ok';
            } elseif ($opcion == 3) {
                if ($numero_paginas > 1) {
                    return self::crear_objeto("error", "Cantidad de paginas no permitidas");
                } else {
                    $is_incidencia_imagen->reemplazarImagen($imagen, $imagen_id);
                    return 'ok';
                }
            }

        } elseif ($extension == 'tiff') {


            for ($i = 1; $i <= $numero_paginas; $i++) {

                $adetalle_url = $path_url . "/" . $only_name . '_' . $i . '.jpg';
                $adetalle_nombre = $only_name . '_' . $i . '.jpg';

                $now = new DateTime();
                $imagen = new App\imagen();
                $imagen->recepcion_id = intval($recepcion_id);
                $imagen->captura_id = intval($captura_id);
                $imagen->documento_id = intval($documento_id);
                $imagen->imagen_nombre = $only_name;
                $imagen->imagen_url = $adetalle_url;
                $imagen->imagen_pagina = $nro_pagina;
                $imagen->imagen_estado = 1;

                $array_nuevo[] = $imagen;

            }

            $is_incidencia_imagen = new App\incidencia_imagen();

            if ($opcion == 1) {
                $is_incidencia_imagen->insertarImagenAntes($array_nuevo, $recepcion_id, $captura_id, $documento_id, $nro_pagina);
                return 'ok';
            } elseif ($opcion == 2) {
                $is_incidencia_imagen->insertarImagenDespues($array_nuevo, $recepcion_id, $captura_id, $documento_id, $nro_pagina);
                return 'ok';
            } elseif ($opcion == 3) {
                if ($numero_paginas > 1) {
                    return self::crear_objeto("error", "Cantidad de paginas no permitidas");
                } else {
                    $is_incidencia_imagen->reemplazarImagen($imagen, $imagen_id);
                    return 'ok';
                }
            }

        } else {
            return "error de formato";

        }

    }

    /**
     * Permite subir los archivos para insertar antes, después o reemplazar
     * @param Request $request Informacion recibida a través del árbol de archivos y visualizador de archivos"
     * @author El juaquer Bueno (ง •̀_•́)ง
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.1
     */
    public function subir_archivo(Request $request, $input_file_name, $fileTiff = null)
    {
        //Registro de lo anterior
        $root = storage_path() . "/app/";
        $recepcion_tipo = request('recepcion_tipo');
        $recepcion_id = request('recepcion_id');
        $proyecto_id = request('proyecto_id');
        $captura_id = request('captura_id');
        $documento_id = request('documento_id');
        $adetalle_id = request('adetalle_id');
        $nro_pagina = request('nro_pagina');
        $imagen_id = request('imagen_id');
        //$input_file_name = 'file';

        //Opcion 1: Insertar Antes, Opción 2: Insertar Después, Opción 3: Reemplazar
        $opcion = request('opcion');

        //Registro del nuevo archivo
        $documento_nombre = request('documento_nombre');

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

        //1.-Obtenemos el documento_id
        $documento_id = request('documento_id');

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
        }

        //3.1 Validamos la extension
        if (!self::validar_extension($extension, $proyecto_id)) {
            return self::crear_objeto("error", "Extension de archivo no permitido");
        }

        //4.-Conseguimos el path donde irán los archivos
        $path = self::verify_path_capturas($recepcion_id);

        if ($extension == "image/jpg" || $extension == "image/jpeg" || $extension == "image/bmp" || $extension == "image/png" || $extension == "image/gif") {
            if ($flag_base64) {
                Storage::disk('public')->put($nombre_original, base64_decode($base64));
                Storage::move("public/" . $nombre_original, $path . '/' . $nombre_original);
                $subir_archivo = $path . "/" . $nombre_original;
            } else {
                $subir_archivo = $file->store($path); // guarda en el servidor con un nombre modificado (HASH)
                $nombre_original = $file->getClientOriginalName(); // Captura el nombre del archivo enviado de front-end
            }
            //5.-Guardo adetalle del archivo
            // $ag_p= new App\adetalle();
            // $ag_p->insertar_adetalle($subir_archivo,str_replace(" ", "_", $nombre_original));

            //6.-Convertir archivo a jpg y obtener nombre original y ruta directorio dentro de app
            $nueva_ruta = self::convert_to_jpeg($root . $subir_archivo, $root . $path . "/imagenes");
            $explode = explode("/", $nueva_ruta);

            $nombre_original = $explode[count($explode) - 1];
            $explode = explode("/app/", $nueva_ruta);
            $subir_archivo = $explode[count($explode) - 1];
            //7.-Guardo la imagen convertida a jpg en imagen con la paginación que corresponde
            $imagen = new App\imagen();
            $imagen->recepcion_id = intval($recepcion_id);
            $imagen->captura_id = intval($captura_id);
            $imagen->documento_id = intval($documento_id);
            $imagen->imagen_nombre = $nombre_original;
            $imagen->imagen_url = $subir_archivo;
            $imagen->imagen_pagina = $nro_pagina;
            $imagen->imagen_estado = 1;

            $array[] = $imagen;

            $is_incidencia_imagen = new App\incidencia_imagen();
            if ($opcion == 1) {
                $is_incidencia_imagen->insertarImagenAntes($array, $recepcion_id, $captura_id, $documento_id, $nro_pagina);
            } elseif ($opcion == 2) {
                $is_incidencia_imagen->insertarImagenDespues($array, $recepcion_id, $captura_id, $documento_id, $nro_pagina);
            } elseif ($opcion == 3) {
                $is_incidencia_imagen->reemplazarImagen($imagen, $imagen_id);
            }

            return self::crear_objeto("ok", "Paginas insertadas correctamente");

        } else if ($extension == "image/tiff" || $extension == "application/pdf" || $extension == "image/tif") {

            if ($flag_base64) {
                Storage::disk('public')->put($nombre_original, base64_decode($base64));
                Storage::move("public/" . $nombre_original, $path . '/' . $nombre_original);
                $subir_archivo = $path . "/" . $nombre_original;
                $pathfile_info["origin_name"] = $nombre_original;

                $pathfile_info = self::informacion_ruta_archivo($root . $subir_archivo);

            } else {
                $subir_archivo = $file->store($path);
                $pathfile_info = self::informacion_ruta_archivo($root . $subir_archivo);
                $pathfile_info["origin_name"] = $nombre_original; // Captura el nombre del archivo enviado de front-end

            }

            //Si es tiff-> convierto a pdf y lo guardo con el mismo nombre pero extension pdf
            $pos_ = strrpos($subir_archivo, ".");
            $exte = substr($subir_archivo, $pos_ + 1);
            if ($exte == "tif" || $exte == "tiff") {

                $prePath = storage_path() . "/app/";
                $docu = new Imagick($prePath . $subir_archivo);
                $docu->setimageformat("pdf");
                $docu->writeimages($prePath . substr($subir_archivo, 0, $pos_ + 1) . "pdf", true);

            }


            //9.- Convertir PDF/TIFF a imagenes
            $array_imagen = [
                "recepcion_id" => $recepcion_id,
                "captura_id" => $captura_id,
                "documento_id" => $documento_id,
            ];
            try {
                return $this->convertir_simple($pathfile_info, $array_imagen, $opcion, $nro_pagina, $imagen_id);
            } catch (Exception  $e) {
                return $e->getMessage();
            }

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

            return $ag_p->adetalle_id;

        }

    }

    public function eliminar_imagen_reproceso(Request $request)
    {

        $imagen_id = request('imagen_id');
        $recepcion_id = request('recepcion_id');
        $captura_id = request('captura_id');
        $documento_id = request('documento_id');

        $is_img = new App\imagen();
        $cantidad_captura= $is_img->where('captura_id',$captura_id)->get()->count();

        if($cantidad_captura>1){
            $is_incidencia_imagen = new App\incidencia_imagen();
            return $is_incidencia_imagen->eliminarImagen($imagen_id, $recepcion_id, $captura_id, $documento_id);
        }else{
            return self::crear_objeto("error", "No puedes eliminar una captura con una sola imagen");
        }

    }


    public function validar_extension($extension, $proyecto_id, $estado = "1")
    {
        //Query para los tipos de formato para el proyecto
        $extensiones_query = DB::select("
        SELECT ARRAY [jpg,png,tiff,pdf,gif,bmp,word,otro] as vista
            from (select
                   case when proyecto_jpg=1 then 'image/jpeg'
                         end as jpg,
                   case when proyecto_png=1  then 'image/png'
                          end as png,
                   case when proyecto_tiff=1  then 'image/tiff'
                          end as tiff,
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
        ", ["proyecto_id" => $proyecto_id]);

        //Limpiar dato obtenido en el query
        $extensiones_query_string = substr($extensiones_query[0]->vista, 1, -1);
        //Generación del array
        $array_extensiones_permitidas = explode(",", $extensiones_query_string);
        //Limpiar array
        $respuesta = str_replace(array("NULL"), '', $array_extensiones_permitidas);

        $bloque_array = array_values(array_filter($respuesta));

        $archivos_permitidos_captura = $bloque_array;

        if(!(in_array('otro',$bloque_array))){
            $resultado = (in_array($extension, $archivos_permitidos_captura)) ? true : false;
        }else{
            $resultado = true;
        }

        return $resultado;

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
            case "tif":
                $mime = "image/tiff";
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

    public function reemplazar_scanner_reproceso(Request $request)
    {

        ini_set('max_execution_time', $this->tiempo_de_ejecucion);
        return $this->subir_archivo($request, "archivo");
    }

    public function subir_archivo_reproceso(Request $request)
    {

        ini_set('max_execution_time', $this->tiempo_de_ejecucion);
        return $this->subir_archivo($request, "file");
    }

    public function reemplazar_tiff_reproceso(Request $request)
    {
        ini_set('max_execution_time', $this->tiempo_de_ejecucion);
        $finalFile = self::procesar_pdf_a_tiff($request);
        $adetalle_id_tif = self::subir_archivo($request, "", $finalFile);
        return $adetalle_id_tif;
    }

    public function procesar_pdf_a_tiff(Request $request)
    {
        $base64 = request("base64");
        $flag_base64 = (empty($base64)) ? false : true;
        if (!$flag_base64) {
            $root = storage_path() . "/app/";
            $file = request()->file("archivo"); //solo puede haber sido enviado por scanner
            $nombre_original = $file->getClientOriginalName();
            $prePath = storage_path() . "/app/public/";
            $subir_archivo = $file->store($prePath); // guarda en el servidor con un nombre modificado (HASH)
            return self::cambiar_formato_archivo($root . $subir_archivo, "tiff");
        } else {
            $nombre_original = request("nombre");
            //grabamos el archivo en base 64
            Storage::disk('public')->put($nombre_original, base64_decode($base64));
            $prePath = storage_path() . "/app/public/";
            //cambiamos el formato a tiff
            return self::cambiar_formato_archivo($prePath . $nombre_original, "tiff");
        }
    }

    public function cambiar_formato_archivo($pathFile, $formatoFinal)
    {

        $pos_ = strrpos($pathFile, ".");
        $pathFileFinal = substr($pathFile, 0, $pos_ + 1) . $formatoFinal;
        $docu = new Imagick($pathFile);
        $docu->setimageformat($formatoFinal);
        $docu->writeimages($pathFileFinal, true);

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return new UploadedFile(
            $pathFileFinal,
            substr($pathFileFinal, strrpos($pathFileFinal, "/") + 1),
            $finfo->file($pathFileFinal),
            filesize($pathFileFinal),
            0,
            false
        );
    }

    public function guardar_reproceso()
    {
        $documento_id = request('documento_id');
        $imagen_id = request('imagen_id');
        $usuario_creador = session("usuario_id");

        $nueva_captura_id = '';

        $imagen = new App\imagen();
        $imagen_respuesta = $imagen
            ->join('incidencia_imagen as in_im','in_im.imagen_id','imagen.imagen_id')
            -> where('in_im.imagen_id',$imagen_id)
            ->where('in_im.estado',1)
            ->first();

        $captura = new App\captura();
        $cap_and_ind_sql = $captura
            -> leftjoin ('indizacion as ind','ind.captura_id','captura.captura_id')
            ->join('documento as doc','doc.captura_id','captura.captura_id')
            ->join('adetalle as ad','ad.adetalle_id','doc.adetalle_id')
            ->where('captura.captura_id',$documento_id)
            ->select('ind.indizacion_id','ad.adetalle_url')
            ->first();

        $id_asociado =  $imagen_respuesta->id_asociado;
        $tipo_asociado =  $imagen_respuesta->tipo_asociado;

        $array_respuesta = request('array_respuesta');

        if(isset($array_respuesta)){

            if($tipo_asociado != 'ind'){

                if($tipo_asociado != 'cap'){
                    $inst_respuesta = new App\respuesta();
                    $inst_respuesta -> eliminar_respuesta($cap_and_ind_sql ->indizacion_id);

                    self::guardar_respuesta($cap_and_ind_sql ->indizacion_id, $array_respuesta);
                }
            }
        }

        $is_incidencia_imagen = new App\incidencia_imagen();
        $is_incidencia_imagen -> update_reproceso($documento_id);

        $is_incidencia_imagen -> update_tabla_asociada($id_asociado,$tipo_asociado,$documento_id);

        $ruta = self::crear_pdf($documento_id,$cap_and_ind_sql->adetalle_url);

        App\adetalle::where('adetalle_url', $cap_and_ind_sql->adetalle_url)
             ->update(['adetalle_peso' =>  filesize(storage_path("/app/".$cap_and_ind_sql->adetalle_url))]);

        //grabamos log de captura
        $log = new App\log();
        $log->create_log_ez(
            $documento_id,//$log_captura_id  ,
            $id_asociado,//$log_id_asociado  ,
            6,//$log_modulo_step_id  ,
            'reproceso',//$log_tabla_asociada  ,
            'REP-FIN',//$log_proceso  ,
            'Finalizar Registro de Reproceso',//$log_descripcion  ,
            '',//$log_comentario  ,
            null//$log_archivo_id
        );

        $obj_query_ruta= DB::select("
                    select adetalle_url,documento_id,proyecto_id from documento d
                    join adetalle ad on d.adetalle_id = ad.adetalle_id
                        where documento_id = :documento_id;
                    ", ["documento_id"=>$documento_id]);

        $proyecto_id = $obj_query_ruta[0]->proyecto_id;

        $obj_query_validador= DB::select("
                    select proyecto_ocr from proyecto
                        where proyecto_id = :proyecto_id;
                    ", ["proyecto_id"=>$proyecto_id]);

        $proyecto_ocr = $obj_query_validador[0]->proyecto_ocr;

        if($proyecto_ocr == '1'){


            //Enviamos la ruta al WS de OCR
            $is_OCR = new App\Http\Controllers\OCRController();
            $ws_OCR = $is_OCR->path_file_ws_ocr($ruta);

            $log->create_log_ez(
                $id_asociado,//$log_captura_id  ,
                $id_asociado,//$log_id_asociado  ,
                7,//$log_modulo_step_id  ,
                'ocr',//$log_tabla_asociada  ,
                'OCR-FIN',//$log_proceso  ,
                'Archivo con proceso de OCR - reproceso',//$log_descripcion  ,
                '',//$log_comentario  ,
                null//$log_archivo_id
            );
            //Guardar los registros por página en la base de datos
            //$is_guardar_paginas = new App\Http\Controllers\capturaController();
            //$guardar_paginas = $is_guardar_paginas->guardar_paginas($ws_OCR,$id_asociado,$usuario_creador);
            return 'ok';

        }

        return $this->crear_objeto('ok', (string)$nueva_captura_id);

    }

    public function crear_pdf($captura_id,$path)
    {
        $array = [];
        $captura = new App\captura();
        $cap_and_ind_sql = $captura
            ->join('imagen as ima','ima.documento_id','captura.captura_id')
            ->where('captura.captura_id',$captura_id)
            ->where('imagen_estado',1)
            ->select('ima.imagen_url','imagen_pagina')
            ->get();

        foreach ($cap_and_ind_sql as $key => $value){
            $array [$value->imagen_pagina] = storage_path("app/".$value->imagen_url);
        }

        ksort($array);

        $ruta = storage_path("app/".$path);
        $is_incidencia_imagen = new App\incidencia_imagen();
        return   $is_incidencia_imagen -> imagesToPdf($array,$ruta);

    }

    public function guardar_respuesta($indizacion_id, $array_respuesta)
    {

        foreach ($array_respuesta as $key => $elemento) {
            $inst_respuesta = new App\respuesta();

            $opcion_id = $elemento['opcion_id'];
            $combo_id = $elemento['combo_id'];
            $elemento_id = $elemento['elemento_id'];
            $elemento_tipo = $elemento['elemento_tipo'];
            $plantilla_id = $elemento['plantilla_id'];

            $valor = $elemento['valor'];
            $wa = $inst_respuesta->crear_respuesta($opcion_id, $combo_id, $elemento_id, $elemento_tipo, $plantilla_id, $indizacion_id, $valor);

        }
    }

    function autoasignar_captura_inicial_reproceso(){
        $usuario_creador = session("usuario_id");
        return $this->retorna_autoasignacion_nueva_captura(0,0,0,$usuario_creador);
    }

    function retorna_autoasignacion_nueva_captura($proyecto_id,$recepcion_id){
        $data = (array)DB::select(
            "select
                a.captura_id,
                case when a.recepcion_id=:recepcion_id then 0
                else a.recepcion_id end as recepcion_id,
                case when a.proyecto_id=:proyecto_id then 0
                else a.proyecto_id end as proyecto_id
                from  captura a
                where (a.usuario_asignado_reproceso is null or a.usuario_asignado_reproceso=1)
                and a.captura_estado_glb = 'rep'
                order by proyecto_id,recepcion_id,captura_id;"
            , ['recepcion_id' => $recepcion_id,'proyecto_id' => $proyecto_id]);

        //return $data;

        $nueva_captura_id = '0';
        if (count($data) > 0) {
            $nueva_captura_id = ((array)$data[0])['captura_id'];
        }
        return $nueva_captura_id;
    }



}
