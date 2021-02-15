<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use View;
use Response;
use App;
use DB;


Trait dangoController
{
    public function listar_plantilla()
    {

        $plantilla_id = request("plantilla_id");
        $captura_id = request("captura_id");
        $indizacion_id = request("indizacion_id");
        // autoasignacion
        //validacion de autoasignacion
        $usuario_id = session('usuario_id');


        $cant = App\captura::where('captura_id', $captura_id)
            ->whereNotNull('usuario_asignado_indizacion')
            ->Where('usuario_asignado_indizacion', '!=', $usuario_id)
            ->count();

        if ($cant > 0) {

            $tipo = 'error';
            $mensaje = 'La captura ya ha sido asignada a otro usuario para su Indización.';
            return Controller::crear_objeto($tipo, $mensaje);
        }


        $captura = App\captura::where('captura_id', $captura_id)
            ->update(['usuario_asignado_indizacion' => $usuario_id
            ]);


        $data = DB::select(
            "select
        a.plantilla_id,a.plantilla_nombre,a.plantilla_estado,a.usuario_creador,
        b.elemento_id,b.elemento_nombre,b.plantilla_id,b.elemento_tipo,
        c.simple_id,c.simple_tipo_dato,c.simple_tipo_formato,c.plantilla_id,
        d.combo_id,d.plantilla_id,
        e.opcion_id,e.opcion_nombre,e.combo_id,e.plantilla_id,
        f.elemento_opciones_id,f.plantilla_id,f.elemento_opciones_incremental,f.elemento_opciones_guia,f.elemento_opciones_multipagina,f.elemento_opciones_obligatorio,
        h.tipo_elemento_id,h.plantilla_id,h.tipo_elemento_nombre,h.tipo_elemento_abreviacion,
        g.respuesta_id,
        --g.valor
        case when g.valor is null then '' else g.valor end as valor
        from plantilla a
        left join elemento b on a.plantilla_id = b.plantilla_id
        left join simple c on b.elemento_id = c.elemento_id
        left join combo d on d.elemento_id=b.elemento_id
        left join opcion e on e.combo_id = d.combo_id
        left join elemento_opciones f on f.elemento_id = b.elemento_id
        left join respuesta g on g.elemento_id= b.elemento_id and g.indizacion_id=:indizacion_id
        left join tipo_elemento h on h.tipo_elemento_id= b.elemento_tipo
        where a.plantilla_id= :plantilla_id
        order by a.plantilla_id,b.elemento_id,c.simple_id,d.combo_id,e.opcion_id;"
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

        if(count($elemento_array)>0){
            array_push($array_final["elementos"], $elemento_array);
        }

        return self::jsonPlantilla_indizacion([$array_final]);
        // return [$array_final];

    }

    public function listar_plantilla_antiguo()
    {
        $plantilla_id = request("plantilla_id");
        $captura_id = request("captura_id");
        $indizacion_id = "1";//request("indizacion_id");

        $plantilla = App\plantilla::
        select(
            "plantilla_id",
            "plantilla_nombre",
            "plantilla_estado",
            "usuario_creador"
        );
        $data = $plantilla
            ->with("elementos")
            ->where("plantilla_id", $plantilla_id)
            ->get();


        //  return $data;
        return self::jsonPlantilla_indizacion($data);


    }

    public function lista_arbol_indizacion2()
    {

        $data = App\proyecto::
        select(
            "proyecto_nombre as text",
            "proyecto_id"
        )
            ->with("children")
            // ->where("cliente_id",1)
            ->get();


        // return $data;
        return self::verifyDirectoryTreeCliente($data);


    }

    public function lista_arbol_indizacion()
    {


        $data = App\proyecto::
        select(
            "proyecto_nombre as text",
            "proyecto_id"
        )
            ->with("children")
            ->orderBy("proyecto_id")
            // ->where("cliente_id",1)
            ->get();


        //  return $data;
        return self::verifyDirectoryTreeCliente($data);


    }

    public function lista_arbol_captura()
    {


        $data = App\proyecto::
        select(
            "proyecto_nombre as text",
            "proyecto_id"
        )
            ->with("children_captura")
            // ->where("cliente_id",1)
            ->get();

//        return $data;

        return self::verifyDirectoryTreeClienteCaptura($data);


    }

    public function jsonPlantilla_indizacion($plantillas, $demo = 0)
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
                    "eo_obligatorio" => $elemento["elemento_opciones"][0]["elemento_opciones_obligatorio"]
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

    public function verifyDirectoryTreeCliente($client, $demo = 0)
    {

        $retorno = array();
        $prefijo_id = "captura_";

        $is_indizacion = new App\indizacion();
        foreach ($client as $i => $proyecto) {

            $proyecto_id = $proyecto["proyecto_id"];
            $children = array();

            foreach ($proyecto["children"] as $j => $recepcion) {
                $children_recepcion = array();
                $recepcion_tipo = $recepcion["recepcion_tipo"];

                foreach ($recepcion["children"] as $k => $captura) {

                    // if (!($is_indizacion->where("captura_id", $captura["captura_id"])
                    //         ->where("indizacion_estado", '1')->count() > 0)) {
                        $filtro = (array)DB::select(
                            'select a.indizacion_id from indizacion a
                            left join incidencia_indizacion b on a.indizacion_id=b.indizacion_id
                            left join incidencia c on b.incidencia_id = c.incidencia_id
                            where a.captura_id=:captura_id
                            and ( a.indizacion_estado=1
                                 or c.incidencia_control=0
                            )'
                            , ['captura_id' => $captura["captura_id"]]);

                    if (!(count($filtro) > 0)) {
                        array_push($children_recepcion, array(
                            // "text" => $recepcion["text"],
                            "id" => ($prefijo_id . (string)($captura["captura_id"])),
                            "text" => $captura["children"][0]["documento_nombre"],
                            "id_captura" => $captura["captura_id"],
                            "id_documento" => $captura["children"][0]["documento_id"],
                            "adetalle_id" => $captura["children"][0]["adetalle_id"],
                            "recepcion_tipo" => $recepcion_tipo,
                            "proyecto_id" => $proyecto_id,
                            "recepcion_id" => $captura["recepcion_id"],
                            "indizacion_id" => (count($captura["indizacion"]) > 0) ? $captura["indizacion"][0]["indizacion_id"] : null
                        ));
                    }

                }

                array_push($children, array(
                    // "text" => $recepcion["text"],
                    "text" => $recepcion["recepcion_nombre"],
                    "id_recepcion" => $recepcion["recepcion_id"],
                    "children" => $children_recepcion
                ));

            }
            array_push($retorno, array(
                "text" => $proyecto["text"],
                "children" => $children,
                "proyecto_id" => $proyecto_id
            ));

        }
        //var_dump($retorno);
        //echo json_encode($retorno);
        return $retorno;
    }


    public static function verifyDirectoryTreeClienteCaptura($client, $children_status=false)
    {
        $retorno = array();
        $prefijo_id = "captura_";

        foreach ($client as $i => $proyecto) {

            $proyecto_id = $proyecto["proyecto_id"];
            $children = array();

            foreach ($proyecto["children_captura"] as $j => $recepcion) {
                $children_recepcion = array();
                $recepcion_tipo = $recepcion["recepcion_tipo"];

                array_push($children_recepcion, array(
                    "text" => "Calibradoras",
                    "recepcion_id" => $recepcion["recepcion_id"],
                    "recepcion_tipo" => $recepcion["recepcion_tipo"],
                    "captura_estado" => 3,
                    "children" => $children_status
                ));

                array_push($children_recepcion, array(
                    "text" => "Aperturas",
                    "recepcion_id" => $recepcion["recepcion_id"],
                    "recepcion_tipo" => $recepcion["recepcion_tipo"],
                    "captura_estado" => 2,
                    "children" => $children_status
                ));

                array_push($children_recepcion, array(
                    "text" => "Documentos",
                    "recepcion_id" => $recepcion["recepcion_id"],
                    "recepcion_tipo" => $recepcion["recepcion_tipo"],
                    "captura_estado" => 1,
                    "children" => $children_status
                ));

                array_push($children, array(
                    "text" => $recepcion["recepcion_nombre"],
                    "recepcion_id" => $recepcion["recepcion_id"],
                    "recepcion_tipo" => $recepcion["recepcion_tipo"],
                    "children" => $children_recepcion
                ));

            }
            array_push($retorno, array(
                "text" => $proyecto["text"],
                "children" => $children,
                "proyecto_id" => $proyecto_id
            ));

        }
        //var_dump($retorno);
        //echo json_encode($retorno);
        return $retorno;
    }

    public static function verifyDirectoryTreeClienteCapturaAdmin($client, $children_status=false)
    {
        $retorno = array();
        $prefijo_id = "captura_";

        foreach ($client as $i => $proyecto) {

            $proyecto_id = $proyecto["proyecto_id"];
            $children = array();

            foreach ($proyecto["children_captura"] as $j => $recepcion) {
                $children_recepcion = array();
                $recepcion_tipo = $recepcion["recepcion_tipo"];

                array_push($children_recepcion, array(
                    "text" => "Documentos",
                    "recepcion_id" => $recepcion["recepcion_id"],
                    "recepcion_tipo" => $recepcion["recepcion_tipo"],
                    "captura_estado" => 1,
                    "children" => $children_status
                ));

                array_push($children, array(
                    "text" => $recepcion["recepcion_nombre"],
                    "recepcion_id" => $recepcion["recepcion_id"],
                    "recepcion_tipo" => $recepcion["recepcion_tipo"],
                    "children" => $children_recepcion
                ));

            }
            array_push($retorno, array(
                "text" => $proyecto["text"],
                "children" => $children,
                "proyecto_id" => $proyecto_id
            ));

        }
        //var_dump($retorno);
        //echo json_encode($retorno);
        return $retorno;
    }

    public static function verifyDirectoryTreeClienteCapturaDocumento($client, $children_status=false)
    {
        $retorno = array();
        $prefijo_id = "captura_";

        foreach ($client as $i => $proyecto) {

            $proyecto_id = $proyecto["proyecto_id"];
            $children = array();

            foreach ($proyecto["children_captura"] as $j => $recepcion) {
                $children_recepcion = array();
                $recepcion_tipo = $recepcion["recepcion_tipo"];

                array_push($children_recepcion, array(
                    "text" => "Calibradoras",
                    "recepcion_id" => $recepcion["recepcion_id"],
                    "recepcion_tipo" => $recepcion["recepcion_tipo"],
                    "captura_estado" => 3,
                    "children" => $children_status
                ));

                array_push($children_recepcion, array(
                    "text" => "Aperturas",
                    "recepcion_id" => $recepcion["recepcion_id"],
                    "recepcion_tipo" => $recepcion["recepcion_tipo"],
                    "captura_estado" => 2,
                    "children" => $children_status
                ));

                array_push($children_recepcion, array(
                    "text" => "Documentos",
                    "recepcion_id" => $recepcion["recepcion_id"],
                    "recepcion_tipo" => $recepcion["recepcion_tipo"],
                    "captura_estado" => 1,
                    "children" => $children_status
                ));

                array_push($children, array(
                    "text" => $recepcion["recepcion_nombre"],
                    "recepcion_id" => $recepcion["recepcion_id"],
                    "recepcion_tipo" => $recepcion["recepcion_tipo"],
                    "children" => $children_recepcion
                ));

            }
            array_push($retorno, array(
                "text" => $proyecto["text"],
                "children" => $children,
                "proyecto_id" => $proyecto_id
            ));

        }
        //var_dump($retorno);
        //echo json_encode($retorno);
        return $retorno;
    }


    public function devolver_plantilla2()
    {

        $data = App\plantilla::
        select(
            "plantilla_nombre",
            "plantilla_id"
        )
            ->with("children")
            // ->where("cliente_id",1)
            ->get();


        return $data;
        // return self::verifyDirectoryTreeCliente($data);


    }

    public function devolver_plantilla()
    {

        $plantilla_id = "1";//request("plantilla_id");

        $plantilla = App\plantilla::where("plantilla_id", $plantilla_id)
            ->select(
                "plantilla_id",
                "plantilla_nombre"


            )
            ->first();


        $elemento = App\elemento::
        where("plantilla_id", $plantilla_id)
            ->select(
                "elemento_id",
                "elemento_nombre",
                "plantilla_id"
            )
            ->get();

        foreach ($elemento as $key => $value) {

            $elemento_opciones = App\elemento_opciones::
            where("elemento_id", $value->elemento_id)
                ->select(
                    "elemento_opciones_incremental as eo_incremental",
                    "elemento_opciones_guia as eo_guia",
                    "elemento_opciones_multipagina as eo_multipagina",
                    "elemento_opciones_obligatorio as eo_obligatorio"


                )
                ->first();

            $value['elemento_opciones'] = $elemento_opciones;

            $combo = App\combo::where("elemento_id", $value->elemento_id)
                ->first();

            $simple = App\simple::where("elemento_id", $value->elemento_id)
                ->first();

            if ($simple != "" && $simple != null) {

                $value['te_abreviacion'] = "s";
                $value['te_id'] = "1";
                $value['te_nombre'] = "simple";

                $value['subelemento'] = array(

                    "plantilla_id" => $plantilla_id,
                    "elemento_id" => $value->elemento_id,
                    "simple_id" => $simple->simple_id,
                    "simple_tipo_dato" => $simple->simple_tipo_dato,
                    "simple_tipo_formato" => $simple->simple_tipo_formato

                );

            } else if ($combo != "" && $combo != null) {

                $opciones = App\opcion::where("combo_id", $combo->combo_id)
                    ->select(
                        "plantilla_id",
                        "elemento_id",
                        "combo_id",
                        "opcion_id",
                        "opcion_nombre"


                    )
                    ->get();

                $value['te_abreviacion'] = "c";
                $value['te_id'] = "2";
                $value['te_nombre'] = "combo";

                $value['subelemento'] = array(

                    "plantilla_id" => $plantilla_id,
                    "elemento_id" => $value->elemento_id,
                    "combo_id" => $combo->combo_id,
                    "opciones" => $opciones


                );

            }


        }
        $plantilla['elementos'] = $elemento;


        return $plantilla;


    }


    public function guardar_respuestas(Request $request)
    {


        $proyecto_id = request('proyecto_id');

        $cliente_id = request('cliente_id');
        $indizacion_estado = request('indizacion_estado');
        $indizacion_id = request('indizacion_id');
        $usuario_creador = request('usuario_creador');
        $incidente_id = request('incidente_id');
        $flag_confirmacion =request('flag_confirmacion');
       // $flag_cont = request('flag_cont');//flag que viene de frontend

        $inst_indizacion = new App\indizacion();
        $inst_proyecto = new App\proyecto();

        $flag_cont = 0;

        // $nueva_captura_id =  self::guardar_indizacion($captura_id, $proyecto_id, $recepcion_id, $cliente_id, $indizacion_estado, $usuario_creador,$incidente_id);
        // return $nueva_captura_id;



            $doble_validacion = $inst_proyecto->where("proyecto_id", $proyecto_id)
                ->where("proyecto_validacion", 1)->count();

            if ($doble_validacion > 0) {

                $indi = (array)DB::select(
                    "select indizacion_id from indizacion
                    where
                    captura_id = :captura_id ;"
                    , ['captura_id'=>$captura_id]);

                if(count($indi)>0){
                    $array_respuesta = request('array_respuesta');
                    $valor_sql = "";
                    //$array_valor_sql =[];
                    foreach ($array_respuesta as $key => $elemento) {
                        $valor_sql .= ("'".(string)$elemento['elemento_id']."%%%".$elemento['valor']."',");
                        //array_push($array_valor_sql,(string)$elemento['elemento_id']."%%%".$elemento['valor']);
                    }

                    $valor_sql = substr($valor_sql,0,strlen($valor_sql)-1);
                   //return $valor_sql;
                    $r = (array)DB::select(
                        "SELECT elemento_id FROM respuesta
                        left join indizacion a on a.indizacion_id = respuesta.indizacion_id
                        where
                        captura_id = :captura_id and
                        cast(elemento_id as varchar)||'%%%'||valor not in
                            (".$valor_sql.");"
                            //(:valor_sql);"
                        //, ['valor_sql' => $valor_sql,'captura_id'=>$captura_id]);
                        , ['captura_id'=>$captura_id]);

                    if(count($r)>0){//cuando hay resultados es por que no hay diferencias en la comparacion
                        if($flag_confirmacion=='1'){
                            $indizacion_estado='1';
                            //con estado 1
                            $nueva_captura_id = self::guardar_indizacion($captura_id, $proyecto_id, $recepcion_id, $cliente_id, $indizacion_estado, $usuario_creador,$incidente_id);
                            return $nueva_captura_id;

                        }else{
                            return 'conf';
                        }


                    }else{
                        //con estado 1
                        $indizacion_estado='1';
                        $nueva_captura_id = self::guardar_indizacion($captura_id, $proyecto_id, $recepcion_id, $cliente_id, $indizacion_estado, $usuario_creador,$incidente_id);
                        return $nueva_captura_id;
                    }
                }else{
                    $indizacion_estado='0';
                    $nueva_captura_id =  self::guardar_indizacion($captura_id, $proyecto_id, $recepcion_id, $cliente_id, $indizacion_estado, $usuario_creador,$incidente_id);
                    return $nueva_captura_id;
                }



            } else {

                /********Guarda una nueva indización estado 1***********/
                $indizacion_estado='1';
                $nueva_captura_id =  self::guardar_indizacion($captura_id, $proyecto_id, $recepcion_id, $cliente_id, $indizacion_estado, $usuario_creador,$incidente_id);


                /********Guardado del bloque de incidentes*****/
                $is_imagen = new App\imagen();
                $obj_captura = $is_imagen->join("incidencia_imagen as i_i","i_i.imagen_id","imagen.imagen_id")
                    ->join("captura as cap","cap.captura_id","imagen.captura_id")
                    ->join("incidencia as in","in.incidencia_id","i_i.incidencia_id")
                    ->where("imagen.imagen_id",$imagen_id)
                    ->get();

                $captura_id=$obj_captura[0]['captura_id'];

                $validador= 0;

                foreach ($obj_captura as $key => $value){
                    if($value['incidencia_control'] == 0){
                        $validador++;
                    }
                }

                if($validador> 0){

                    Controller::estado_captura_glb($captura_id,"rep");

                    foreach ($obj_captura as $key => $value){

                        Controller::incidencia_imagen_glb($value['imagen_id'], $value['incidencia_id'], "cap",$captura_id,1);
                    }

                }else{

                    Controller::estado_captura_glb($captura_id,$captura_estado_glb);

                    foreach ($obj_captura as $key => $value){

                        Controller::incidencia_imagen_glb($value['imagen_id'], $value['incidencia_id'], $tipo_asociado,$captura_id,2);
                    }

                }

                /********Fin Guardado del bloque de incidentes*****/


                return $nueva_captura_id;
            }


    }

   public function guardar_indizacion($captura_id, $proyecto_id, $recepcion_id, $cliente_id, $indizacion_estado, $usuario_creador,$incidente_id)
    {

        $inst_indizacion = new App\indizacion();

        $we = $inst_indizacion->crear_indizacion($captura_id, $proyecto_id, $recepcion_id, $cliente_id, $indizacion_estado, $usuario_creador);

        $array_respuesta = request('array_respuesta');
        foreach ($array_respuesta as $key => $elemento) {
            $inst_respuesta = new App\respuesta();

            $opcion_id = $elemento['opcion_id'];
            $combo_id = $elemento['combo_id'];
            $elemento_id = $elemento['elemento_id'];
            $elemento_tipo = $elemento['elemento_tipo'];
            $plantilla_id = $elemento['plantilla_id'];
            // $indizacion_id=  $elemento['indizacion_id'];
            $indizacion_id = $we->indizacion_id;
            $valor = $elemento['valor'];
            $wa = $inst_respuesta->crear_respuesta($opcion_id, $combo_id, $elemento_id, $elemento_tipo, $plantilla_id, $indizacion_id, $valor);

        }

        $is_incidencia_indizacion = new App\incidencia_indizacion();

        $wo = $is_incidencia_indizacion->crear_incidencia_indizacion($indizacion_id, $incidente_id);

        $data = (array)DB::select(
            'select a.captura_id,
            case when a.recepcion_id=:recepcion_id then 0
            else a.recepcion_id end as recepcion_id,
            case when a.proyecto_id=:proyecto_id then 0
            else a.proyecto_id end as proyecto_id,
            case when a.captura_id=:captura_id then 0
            else a.captura_id end as captura_id_fake
            from captura a
            left join indizacion b on a.captura_id = b.captura_id and b.indizacion_estado=0
			left join incidencia_indizacion c on c.indizacion_id = b.indizacion_id
			left join incidencia d on d.incidencia_id = c.incidencia_id and d.incidencia_control = 0
            left join indizacion bb on
            (b.captura_id = bb.captura_id or a.captura_id = bb.captura_id) and bb.indizacion_estado=1
            where (a.usuario_asignado_indizacion is null or a.usuario_asignado_indizacion=:user)
            and (bb.indizacion_estado is null)
			and (d.incidencia_id is null)
            and a.captura_estado = 1
            order by proyecto_id,recepcion_id,captura_id_fake;'
            , ['recepcion_id' => $recepcion_id, 'captura_id'=> $captura_id,'proyecto_id' => $proyecto_id, 'user' => session("usuario_id")]);

        //return $data;

        $nueva_captura_id = '0';
        if (count($data) > 0) {
            $nueva_captura_id = ((array)$data[0])['captura_id'];
        }
        return $nueva_captura_id;
        // return "ok";


    }

}
