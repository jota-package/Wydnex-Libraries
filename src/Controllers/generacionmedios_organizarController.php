<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use View;
use App;
use DB;

use App\proyecto;
use App\medio_exportacion;
use App\generacion_medio_recepcion;
use App\generacion_medio_detalle;
use App\generacion_medio;
use App\generacion_medio_detalle_captura;

Trait generacionmedios_organizarController
{
    
    public function index()
    {
        $is_proyecto = new proyecto();
        $proyectos = $is_proyecto->proyecto_usuario();

        $is_me = new medio_exportacion();
        $medios_generados = $is_me
            ->select("me_id", "me_descripcion", "me_capacidad")
            ->get();

        return view::make('generacion_medios_organizar.index.content')
            ->with('proyectos', $proyectos)
            ->with('medios_generados', $medios_generados);

    }

    public function proyecto_ver_recepcion(Request $request)
    {

        $proyecto_id = request("proyecto_id");

        $recepcion_x_proyecto = DB::select("select
                                                distinct on (b.recepcion_id) b.recepcion_id,
                                                b.recepcion_nombre,
                                                a.proyecto_id,
                                                c.gm_id
                                            from proyecto a
                                                       join recepcion b on a.proyecto_id = b.proyecto_id
                                                       left join generacion_medio_recepcion c on c.recepcion_id = b.recepcion_id
                                                       left join generacion_medio d on d.gm_id = c.gm_id
                                            where a.proyecto_id = :proyecto_id;", ["proyecto_id" => $proyecto_id]);


        return $recepcion_x_proyecto;

    }

    public function proyecto_ver_gm(Request $request)
    {

        $recepcion_id = request("recepcion_id");

        $is_gmr = new generacion_medio_recepcion();
        $gm_componentes = $is_gmr->join('generacion_medio as gm', 'gm.gm_id', 'generacion_medio_recepcion.gm_id')
            ->select('gm_prefijo', 'gm_correlativo', 'me_id', 'gm_peso_otros')
            ->where('generacion_medio_recepcion.recepcion_id', $recepcion_id)
            ->first();


        $is_gmd = new generacion_medio_detalle();
        $gm_detalle = $is_gmd->join('generacion_medio as gm', 'gm.gm_id', 'generacion_medio_detalle.gm_id')
            ->join('generacion_medio_recepcion as gmr', 'gmr.gm_id', 'gm.gm_id')
            ->select('generacion_medio_detalle.gmd_id', 'gmr.gm_id', 'generacion_medio_detalle.gmd_nombre', 'generacion_medio_detalle.gmd_peso_maximo', 'generacion_medio_detalle.gmd_peso_ocupado', 'generacion_medio_detalle.gmd_total_documento', 'generacion_medio_detalle.gmd_grupo', 'generacion_medio_detalle.gmd_estado')
            ->where('gmr.recepcion_id', $recepcion_id)
            ->orderBy('generacion_medio_detalle.gmd_id')
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
            $array_cuerpo['generacion_medio_id'] = $value->gm_id;
            $array_cuerpo['nombre'] = $value->gmd_nombre;
            $array_cuerpo['peso_maximo'] = $value->gmd_peso_maximo;
            $array_cuerpo['peso_ocupado'] = $value->gmd_peso_ocupado;
            $array_cuerpo['total_documentos'] = $value->gmd_total_documento;
            $array_cuerpo['estado'] = $value->gmd_estado;

            $cuerpo[] = $array_cuerpo;

        }

        $bloque_completo[] = $array_cabecera;
        $bloque_completo[] = $cuerpo;

        return $bloque_completo;

    }

    public function listar_captura_organizar()
    {

        $recepciones = request("array_check");
        $nombre = request("nombre");
        $correlativo = request("correlativo");
        //Espacio de holgura del disco
        $espacio_libre = request("espacio_libre");
        $medio_id = request("medio_id");
        $gm_id = request("gm_id");

        if(self::validar_recepcion_firmada($gm_id)> 0){
            $tipo = 'error';
            $mensaje = 'No están firmadas las capturas correspondientes';
            return Controller::crear_objeto($tipo, $mensaje);
        }

        if($gm_id == "")
        {
            $gm_id = "0";
        }

        $size = request("size");

        $is_me = new medio_exportacion();

        if ($medio_id == 6) {

            if ($size == '') {
                $tipo = 'error';
                $mensaje = 'Ingrese el tamaño del grupo';
                return Controller::crear_objeto($tipo, $mensaje);
            } else {
                $is_me->where('me_id', 6)->update(['me_capacidad' => $size * 1000000]);
            }

        }

        $me_exportacion_datos = $is_me->select('me_id', 'me_capacidad')->where('me_id', $medio_id)->first();
        $me_capacidad_double = ($me_exportacion_datos['me_capacidad']) / 1000000;
        $is_gm = new generacion_medio();

        $validacion = $is_gm->validacion($nombre, $correlativo, $me_capacidad_double, $recepciones, $medio_id,$espacio_libre,$gm_id);
        if(count($validacion)>0){
            $tipo = 'error';
            $mensaje = $validacion[0]->mensaje;
            return Controller::crear_objeto($tipo, $mensaje);
        }

        $gm_data = $is_gm->organizar($nombre, $correlativo, $me_capacidad_double, $recepciones, $medio_id,$espacio_libre,$gm_id);

        if(count($gm_data)==0){
            $tipo = 'error';
            $mensaje = 'Ya se organizaron todos los documentos correspondientes.';
            return Controller::crear_objeto($tipo, $mensaje);
        }
        return $gm_data;
        /*
        $tipo = 'error';
        $mensaje = 'La captura ya ha sido asignada a otro usuario para su Indización.';
        return Controller::crear_objeto($tipo, $mensaje);
            */

    }

   /*public function validar_organizar($recepciones){

        $validador = 0 ;
        foreach ($recepciones as $value){
           $validador += self::validar_recepcion_firmada($value);
        }

        return $validador;
    }*/

    public function validar_recepcion_firmada($gm_id){

        $data = DB::select(
            "
             with documento_imagen AS (
                select
                     c.documento_id,
                     max(cast(imagen_pagina as integer)) as total_pag,
                     c.documento_estado,
                     c.documento_nombre,
                     c.captura_id,
                     c.adetalle_id
                from documento c
                join imagen i on c.documento_id = i.documento_id
                group by c.documento_id),
            esquery AS (
              select d.adetalle_id,
                  e.fedatario_firmar_estado,
                  gmr.gm_id
              from recepcion a
              left join generacion_medio_recepcion gmr on gmr.recepcion_id = a.recepcion_id
              left join captura b on a.recepcion_id = b.recepcion_id
              left join documento_imagen c on b.captura_id = c.captura_id
              left join adetalle d on d.adetalle_id = c.adetalle_id
              join fedatario_firmar e on e.captura_id = b.captura_id
              where gmr.gm_id = :gm_id
                and b.captura_estado = 1
                and b.captura_estado_glb = 'fed_fir'
                order by
                case when b.captura_orden is null then b.captura_id else b.captura_orden end
              )
                select count(fedatario_firmar_estado)
                from esquery
                where fedatario_firmar_estado !=4;"
            , ['gm_id' => $gm_id]);

        return $data[0]->count;
    }



    public function ver_gm_grupo()
    {

        $gm_id = request("gm_id");

        $is_gmd = new generacion_medio();
        $array_cabecera = $is_gmd->consulta_cabecera($gm_id);
        $gm_detalle = $is_gmd->consulta($gm_id);
        //return $array_cabecera;

        $bloque_completo[] = $array_cabecera[0];
        $bloque_completo[] = $gm_detalle;

        return $bloque_completo;

    }


    public function eliminar_medio()
    {

        $array_gmd = request("array_check");

        $gmd = new generacion_medio_detalle();
        $gmdc = new generacion_medio_detalle_captura();

        foreach ($array_gmd as $gmd_id){

            $gmd->where('gmd_id', $gmd_id)->delete();
            $gmdc->where('gmd_id', $gmd_id)->delete();
        }

        return 'ok';

    }

}
