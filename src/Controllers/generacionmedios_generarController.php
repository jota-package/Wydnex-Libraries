<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\respuesta;
use View;
use App;
use Illuminate\Support\Facades\DB;

use App\proyecto;
use App\Http\Controllers\tipo_calibradorController;
use App\medio_exportacion;
use App\generacion_medio;
use App\files;
use App\generacion_medio_detalle_captura;
use App\generacion_medio_detalle;
use App\recepcion;



class generacionmedios_generarController extends Controller
{
    public function __construct()
    {

        $this->middleware('auth');
        parent::__construct();

    }

    public function index()
    {

        $is_proyecto = new proyecto();
        $proyectos = $is_proyecto->proyecto_usuario();

        $ins_tipo_calibrador = new tipo_calibradorController();

        $is_me = new medio_exportacion();
        $medios_generados = $is_me
            ->select("me_id", "me_descripcion", "me_capacidad")
            ->get();
        $tipo_calibrador = $ins_tipo_calibrador->listar_tipo_calibrador();


        return view::make('generacion_medios_generar.index.content')
            ->with('proyectos', $proyectos)
            ->with('medios_generados', $medios_generados)
            ->with('tipo_calibrador', $tipo_calibrador);

    }

    public function index_personalizado()
    {

        $is_proyecto = new proyecto();
        $proyectos = $is_proyecto->select('proyecto_id', 'proyecto_nombre')->get();

        $ins_tipo_calibrador = new tipo_calibradorController();

        $is_me = new medio_exportacion();
        $medios_generados = $is_me
            ->select("me_id", "me_descripcion", "me_capacidad")
            ->get();
        $tipo_calibrador = $ins_tipo_calibrador->listar_tipo_calibrador();


        return view::make('generacion_medios_personalizado.index.content')
            ->with('proyectos', $proyectos)
            ->with('medios_generados', $medios_generados)
            ->with('tipo_calibrador', $tipo_calibrador);

    }

    public function proyecto_lista_gm(Request $request)
    {

        $proyecto_id = request("proyecto_id");

        $recepcion_x_gm = DB::select(
            "
            with datos as (
                select distinct
                    --r.recepcion_id,r.recepcion_nombre
                    gmr.recepcion_id
                    ,row_number() over(partition by gmd.gmd_id) as filtro
                    ,gm.gm_id, gm.gm_prefijo
                    ,gm.gm_correlativo, gm.gm_estado
                    ,gmd.gmd_id
                    ,to_char(gmd.gmd_peso_maximo,'FM999,999,999,999,990D00')||' Mb' as gmd_peso_maximo
                    ,gmd_estado
                    ,to_char(gmd_peso_ocupado,'FM999,999,999,999,990D00')||' Mb' as gmd_peso_ocupado
                    ,gmd_total_documento
                    ,gmd_partes_procesadas
                    ,gmd_partes_total
                    ,(CAST (gmd_partes_procesadas AS DOUBLE PRECISION))/(CAST (gmd_partes_total AS DOUBLE PRECISION))*100 as porcentaje
                    ,gmd_nombre
                    ,me.me_descripcion
                    ,gmd_cant_pagina_total
                    ,gmd.created_at
                from proyecto p
                  join recepcion r on p.proyecto_id = r.proyecto_id
                  join generacion_medio_recepcion gmr on gmr.recepcion_id = r.recepcion_id
                  join generacion_medio gm on gm.gm_id = gmr.gm_id
                  join generacion_medio_detalle gmd on gmd.gm_id = gm.gm_id
                  join medio_exportacion me on me.me_id = gm.me_id
                where gm_estado = 1 and p.proyecto_id= :proyecto_id
                order by gm.gm_id,gmd.gmd_id
            )
            select * from datos where filtro = 1;
            "
            , ['proyecto_id' => $proyecto_id]);

        /*  $is_query = new App\proyecto();
          $recepcion_x_gm = $is_query -> join ('recepcion as r','r.proyecto_id','proyecto.proyecto_id')
              -> join('generacion_medio_recepcion as gmr','gmr.recepcion_id','r.recepcion_id')
              -> join('generacion_medio as gm','gm.gm_id','gmr.gm_id')
              -> join('generacion_medio_detalle as gmd','gmd.gm_id','gm.gm_id')
              -> join('medio_exportacion as me','me.me_id','gm.me_id')
              -> select('r.recepcion_id','r.recepcion_nombre','gmr.recepcion_id','gm.gm_id', 'gm.gm_prefijo', 'gm.gm_correlativo', 'gm.gm_estado', 'gm.created_at'
                  ,'gmd.gmd_id','gmd.gmd_peso_maximo','gmd_estado','gmd_peso_ocupado','gmd_total_documento','gmd_nombre','me.me_descripcion','gmd_cant_pagina_total')
              -> where('gm_estado', 1)
              -> where('proyecto.proyecto_id', $proyecto_id)
              -> orderBy('r.recepcion_id')
              -> get();*/

        return $recepcion_x_gm;

    }

    public function proyecto_lista_gm_total(Request $request)
    {

        $recepcion_x_gm = DB::select(
            "
            with datos as (
                select distinct
                    --r.recepcion_id,r.recepcion_nombre
                    gmr.recepcion_id
                    ,row_number() over(partition by gmd.gmd_id) as filtro
                    ,gm.gm_id, gm.gm_prefijo
                    ,gm.gm_correlativo, gm.gm_estado
                    ,gmd.gmd_id
                    ,to_char(gmd.gmd_peso_maximo,'FM999,999,999,999,990D00')||' Mb' as gmd_peso_maximo
                    ,gmd_estado
                    ,to_char(gmd_peso_ocupado,'FM999,999,999,999,990D00')||' Mb' as gmd_peso_ocupado
                    ,gmd_total_documento
                    ,gmd_partes_procesadas
                    ,gmd_partes_total
                    ,(CAST (gmd_partes_procesadas AS DOUBLE PRECISION))/(CAST (gmd_partes_total AS DOUBLE PRECISION))*100 as porcentaje
                    ,gmd_nombre
                    ,me.me_descripcion
                    ,gmd_cant_pagina_total
                    ,gmd.created_at
                from proyecto p
                  join recepcion r on p.proyecto_id = r.proyecto_id
                  join generacion_medio_recepcion gmr on gmr.recepcion_id = r.recepcion_id
                  join generacion_medio gm on gm.gm_id = gmr.gm_id
                  join generacion_medio_detalle gmd on gmd.gm_id = gm.gm_id
                  join medio_exportacion me on me.me_id = gm.me_id
                where gm_estado = 1
                order by gm.gm_id,gmd.gmd_id
            )
            select * from datos where filtro = 1;
            "
            , []);

        return $recepcion_x_gm;

    }

    public function listar_captura_organizar()
    {

        $array_check = request("array_check");
        $nombre = request("nombre");
        $correlativo = request("correlativo");
        $medio_id = request("medio_id");

        $is_me = new medio_exportacion();
        $peso_maximo = $is_me->select('me_capacidad')->where('me_id', $medio_id)->first();

        $is_gm = new generacion_medio();
            $gm_data = $is_gm->organizar($nombre, $correlativo, $peso_maximo['me_capacidad'], $array_check);

        return $gm_data;

    }

/////////////////////////////////////////////////////
    public function generar_json($gmd_id, $ruta_salida, $ruta_destino)
    {
        //conseguir los gmd
        //con eso conseguir las recepciones
        // definir
        //$gmd_id = 103;

        $modelo_gm = new generacion_medio();

        $array_recepciones = $modelo_gm->recepciones_x_gmd($gmd_id);
        $ids = [];
        for ($i = 0; $i < count($array_recepciones); $i++) {
            $ids[] = ($array_recepciones[$i]->recepcion_id);
        }

        //$ids = [4, 5];
        //$ruta_salida = "storage/app/documentos";
        $nombre_file = "main_".$gmd_id.".json";
        $nombre_file_hijo = "recepcion_";

        //$nombre_file = ($gmd_id."_main.json");
        //$nombre_file_hijo = ($gmd_id."_recepcion_");

        $modelo_file = new files();

        //$array = $modelo_file->prueba_arbol($ids, $ruta_salida, $nombre_file_hijo);

        $array = $modelo_file->prueba_arbol_final($gmd_id, $ruta_salida, $nombre_file_hijo, $ruta_destino);

        //guarda el objeto json en un archivo .json
        $ruta_main = $modelo_file->guardar_file_json($ruta_salida, $nombre_file, [$array]);

        $rutas = $modelo_file->obtener_files_recepcion_final($ids, $ruta_salida, $nombre_file_hijo, $gmd_id);

        $rutas[] = $ruta_main;

        $json_plantilla = $modelo_file->json_plantilla($gmd_id);

        $ruta_json_plantilla = $modelo_file->guardar_file_json($ruta_salida, 'plantilla_'.$gmd_id.'.json', $json_plantilla);

        $rutas[] = $ruta_json_plantilla;

        return $rutas;
    }


    public function confirmar_medio(Request $request)
    {

        $array_check = request("array_check");

        $contador_error = 0;

        $is_gm = new generacion_medio();
        $path = storage_path() . "/app/documentos/";
        $path_documentos = "documentos/";

        $ruta_inicio = $path;
        $ruta_destino = env('FOLDER_GENERADO');

        foreach ($array_check as $gmd_id) {

            $is_capturas = new generacion_medio_detalle_captura();

            $contador_gmd = $is_capturas->validador_captura_listar_ac($gmd_id, 5);

            if (count($contador_gmd) == 0) {

                //return $this->crear_objeto("Error", "Compruebe si cuenta con Actas."); //descomentar para validar las actas previas

            }

        }

        $usuario_creador =session("usuario_id");
        $ip = $request->ip();

        foreach ($array_check as $gmd_id) {

            //lleno las rutas de los archivos
            $res_gm = $is_gm->listar_rutas($path, $path_documentos, $gmd_id,$usuario_creador,$ip);
            //quitado para solo  pdf's
            //$res_gm_imagenes = $is_gm->listar_rutas_imagenes($path, $path_documentos, $gmd_id);
            $res_gm_imagenes=[];
            $res_gm = array_merge($res_gm, $res_gm_imagenes);
            //$ruta_salida = "storage/app/documentos/";
            $ruta_salida = $ruta_inicio;
            $ruta_auxiliar = $res_gm[0]->ruta;
            //Para obtener la ruta de carpetas donde irá el visor (proyecto/CD1/)
            $ruta_sin_asterisco = substr($ruta_auxiliar, stripos($ruta_auxiliar, '****') + 4);
            $pos_slash = stripos($ruta_sin_asterisco, "/", stripos($ruta_sin_asterisco, "/") + 1);
            $ruta_final = (substr($ruta_sin_asterisco, 0, $pos_slash) . "/");
            //Para obtener las rutas de los json generados
            //$rutas_extra = $this->generar_json($gmd_id , $ruta_salida,$ruta_final."/visor/");
            $rutas_extra = $this->generar_json($gmd_id, $ruta_salida, "/visor/database/");
            foreach ($rutas_extra as $ruta_extra) {

                $res_gm[] = (object)[
                    "ruta" => (str_replace($ruta_salida, "", "/" . $ruta_extra) //ruta origen
                        . '****'
                        . $ruta_final
                        . "/visor/database/"
                        . str_replace($ruta_salida, "", "/" . $this->retirar_gmd_id_filename($ruta_extra)) //ruta destino
                    )
                ];
            }
            //Añado la ruta del visor para que se copie en el CD
            $ruta_visor = env('RUTA_VISOR');
            $res_gm[] = (object)[
                "ruta" => ($ruta_visor
                    . '****'
                    . $ruta_final
                    . "/visor/visualizador/"
                )
            ];

            // Copiando el ejecutable del blattscan
            $source = storage_path() . '/app/documentos/blattscan.exe';
            $destination = env('FOLDER_GENERADO') . $ruta_final . 'blattscan.exe';
            //Añado la ruta del ejecutable para que se copie en el CD
            $ruta_exe = env('RUTA_EJECUTABLE');
            $res_gm[] = (object)[
                "ruta" => ($ruta_exe
                    . '****'
                    . $ruta_final
                )
            ];


            //$ruta_destino = storage_path()."/app/archivos_copia";
            $ruta_destino = env('FOLDER_GENERADO');

            $fp = fopen($ruta_inicio . "prueba_".$gmd_id.".txt", 'w');

            foreach ($res_gm as $res_gm_value) {

                fwrite($fp, $res_gm_value->ruta . "\n");

            }

            fclose($fp);

            //Aquí se consume el WS de juanchel 😎
            $url_put = "http://localhost:3000/mover";

            //Objeto que se enviará para el Update
            $data_put = [
                "id" => $gmd_id,
                "ruta_inicio" => $ruta_inicio,
                "ruta_destino" => $ruta_destino,
                "ruta_lista" => $ruta_inicio . "prueba_".$gmd_id.".txt",
            ];


            //Cambiando formato de objeto
            $var = json_encode($data_put, true);

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_PORT => "3000",
                CURLOPT_URL => $url_put,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 3000,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $var,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "Postman-Token:  f6383e44-afc1-47c7-a62d-cbe4820c6fb7",
                    "cache-control: no-cache"
                ),
            ));

            $response = curl_exec($curl);
            $error = json_decode($response, true);

            if ($error['estado'] == false) {
                $contador_error++;
            }


            $err = curl_error($curl);

            curl_close($curl);

            //return $err; //descomentar

        }

        //copy ($source, $destination);

        if ($contador_error == 0) {
            return $this->crear_objeto("ok", "Se está copiando los archivos.");
        } else {
            return $this->crear_objeto("error", "No completado, algo salió mal");
        }


    }

    static public function retirar_gmd_id_filename($filename){
        $parte1 = explode(".", $filename);
        $parte2 = explode("_", $parte1[count($parte1)-2]);
        array_pop($parte2);
        $parte1[count($parte1)-2] = implode("_", $parte2);
        return implode(".", $parte1);
    }

    public function ver_generacion_medio()
    {

        $gm_id = request("gm_id");

        $is_gm = new generacion_medio();
        $gm_componentes = $is_gm->select('gm_prefijo', 'gm_correlativo', 'me_id', 'gm_peso_otros')
            ->where('gm_id', $gm_id)
            ->first();

        $is_gmd = new generacion_medio_detalle();
        $gm_detalle = $is_gmd->select('gmd_id', 'gm_id', 'gmd_nombre', 'gmd_peso_maximo', 'gmd_peso_ocupado', 'gmd_total_documento', 'gmd_grupo', 'gmd_estado')
            ->where('gm_id', $gm_id)
            ->get();

        $array_cabecera = [];
        $array_cabecera['gm_prefijo'] = $gm_componentes['gm_prefijo'];
        $array_cabecera['gm_correlativo'] = $gm_componentes['gm_correlativo'];
        $array_cabecera['me_id'] = $gm_componentes['me_id'];
        $array_cabecera['gm_peso_otros'] = $gm_componentes['gm_peso_otros'];

        $cuerpo = [];

        foreach ($gm_detalle as $value) {

            $array_cuerpo = [];
            $array_cuerpo['gmd_id'] = $value->gmd_id;
            $array_cuerpo['gm_id'] = $value->gm_id;
            $array_cuerpo['gmd_nombre'] = $value->gmd_nombre;
            $array_cuerpo['gmd_peso_maximo'] = $value->gmd_peso_maximo;
            $array_cuerpo['gmd_peso_ocupado'] = $value->gmd_peso_ocupado;
            $array_cuerpo['gmd_total_documento'] = $value->gmd_total_documento;
            $array_cuerpo['gmd_estado'] = $value->gmd_estado;

            $cuerpo[] = $array_cuerpo;

        }

        $bloque_completo[] = $array_cabecera;
        $bloque_completo[] = $cuerpo;

        return $bloque_completo;

    }

    public function modal_acta_cierre_gmd()
    {

        $gmd_id = request("gmd_id");

        $is_capturas = new generacion_medio_detalle_captura();
        $modal_gmd = $is_capturas->captura_listar_acta_cierre($gmd_id);

        return $modal_gmd;

    }

    public function modal_calibradora_gmd()
    {

        $gmd_id = request("gmd_id");

        $is_capturas = new generacion_medio_detalle_captura();
        $contador_gmd = $is_capturas->validador_captura_listar_ac($gmd_id, 4);

        if (count($contador_gmd) == 0) {

            return $this->crear_objeto("Error", "Compruebe si cuenta con Acta .");

        }

        $modal_gmd = $is_capturas->captura_listar_calibradora_cierre($gmd_id);

        return $modal_gmd;

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
        $info = recepcion::where('recepcion_id', $recepcion_id)
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

    public function podergreen_cont(){

        $modelo_file = new files();
        $path = storage_path() . "/app/documentos/";

        return $modelo_file->podergreen('21',$path);

    }

    public function podergreenv2_cont(){

        $modelo_file = new files();
        $path = storage_path() . "/app/documentos/";

        return $modelo_file->podergreenv2('21','41','36',$path);

    }


    public function generar_json_ocr(){
        return respuesta::ok();
    }

    public function generar_json_database(){
        return respuesta::ok();
    }

    public function generar_discos_independientes(){
        return respuesta::ok();
    }

}
