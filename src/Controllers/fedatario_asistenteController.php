<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\proyecto;
use App\indizacion;
use App\captura;
use App\fedatario;
use App\log;
use App\Http\Controllers\incidenciaController;
use App;
use View;
use DB;

class fedatario_asistenteController extends Controller
{

    public function __construct()
    {

        $this->middleware('auth');
        parent::__construct();

    }

    public function index()
    {

        $is_proyecto = new proyecto();
        $lista_proyecto = $is_proyecto->proyecto_usuario_asistente();

        //Instancia Incidencia
        $ins_incidencia = new incidenciaController();
        $incidencia = $ins_incidencia->listar_incidencia();

        return view::make('fedatario_asistente.index.content')
            ->with("lista_proyecto",$lista_proyecto)
            ->with("incidencia",$incidencia)
            ;

    }

    public function listar_plantilla_fedatario_asistente(){

        $plantilla_id = request("plantilla_id");
        $captura_id = request("captura_id");

        $indizacion = (new indizacion())->where('captura_id',$captura_id)->first();
        $indizacion_id = $indizacion['indizacion_id'];

        //validacion de autoasignacion
        $usuario_id = session('usuario_id');


        $cant = captura::where('captura_id', $captura_id)
            ->whereNotNull('usuario_asignado_fed_revisar_asis')
            ->Where('usuario_asignado_fed_revisar_asis', '!=', $usuario_id)
            ->count();

        if ($cant > 0) {

            $tipo = 'error';
            $mensaje = 'La captura ya ha sido asignada a otro usuario respecto a su Control de Calidad.';
            return Controller::crear_objeto($tipo, $mensaje);
        }


        $captura = captura::where('captura_id', $captura_id)
            ->update(['usuario_asignado_fed_revisar_asis' => $usuario_id
            ]);


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
        if(count($elemento_array)>0){
            array_push($array_final["elementos"], $elemento_array);
        }

        return self::jsonPlantilla_fedatario_indizacion_control([$array_final]);

    }

    public function jsonPlantilla_fedatario_indizacion_control($plantillas, $demo = 0)
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

    public function arbol_fedatario()
    {

        $porcentaje = request('porcentaje');
        $proyecto_id = request('proyecto_id');
        $usuario_creador = session("usuario_id");

        $validacion = (new fedatario())->validacion_update_grupo_antiguo($proyecto_id,1);
        $data = (new fedatario())->arbol_fedatario($porcentaje,$proyecto_id,1);

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
                "id_captura" => $fila->captura_id,
                "indizacion_id" => $fila->indizacion_id,
                "fedatario_id" => $fila->fedatario_id,
                "id_documento" => $fila->documento_id,
                "cc_id" => $fila->cc_id,
                "adetalle_id" => $fila->adetalle_id,
                "recepcion_tipo" => $fila->recepcion_tipo,
                "cliente_id" => $fila->cliente_id,
                "usuario_creador" => $fila->usuario_creador,
                "proyecto_id" => $fila->proyecto_id,
                "recepcion_id" => $fila->recepcion_id
            ];
            $recepcion_old = $fila->recepcion_id;
            $recepcion_nombre_old = $fila->recepcion_nombre;

            $proyecto_old = $fila->proyecto_id;
            $proyecto_nombre_old = $fila->proyecto_nombre;
        }
        if($recepcion_old!="0" && $proyecto_old!="0"){
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

        $captura_autoasignada = $this->retorna_autoasignacion_nueva_captura($proyecto_id,0,0,$usuario_creador);

        return [$array_proyecto,$captura_autoasignada];

    }

    /**
     * Finaliza
     * @param Request $request Informacion recibida a través del botón "guardar" del "vista_previa_plantilla"
     * @author El juaquer Bueno (ง -̀_-́)ง
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function finalizar_registro_incidencia_fedatario()
    {
        $usuario_creador = session("usuario_id");
        $tipo_asociado = "fed";
        $fedatario_id = request("fedatario_id");
        $id_asociado = $fedatario_id;


        //Enviado a la funcion global de incidencia
        $obj = (new incidenciaController())->finalizar_registro_incidencia_glb($id_asociado, $usuario_creador, $tipo_asociado,
            function ($id_asociado, $usuario_creador, $count, $request) {

                $cap_est_glb_0 = 'rep';
                $cap_est_glb_1 = 'fed';
                $recepcion_id = request('recepcion_id');
                $captura_id = request('captura_id');
                $proyecto_id = request('proyecto_id');
                $cliente_id = request('cliente_id');
                $indizacion_id = request('indizacion_id');
                $cc_id = request('cc_id');
                $fedatario_id = request('fedatario_id');

                if ($count > 0) {
                    //mandar captura a estado reproceso id_asociado = cc_id
                    $fedatario_update = (new fedatario())->update_estado_fedatario($id_asociado, 1);
                    $this->estado_captura_glb($captura_id, $cap_est_glb_0);

                    //grabamos log de captura
                    $log = new App\log();
                    $log->create_log_ez(
                                $captura_id,//$log_captura_id  ,
                                $id_asociado,//$log_id_asociado  ,
                                4,//$log_modulo_step_id  ,
                                'fedatario',//$log_tabla_asociada  ,
                                'FED-ASIS-FIN',//$log_proceso  ,
                                'Finalizar Registro de Fedatario Revisar Asistente - Reproceso',//$log_descripcion  ,
                                '',//$log_comentario  ,
                                null//$log_archivo_id
                            );


                } else {


                    $fedatario_update = (new fedatario())->update_estado_fedatario($id_asociado, 2);

                    //grabamos log de captura
                    $log = new log();
                    $log->create_log_ez(
                                $captura_id,//$log_captura_id  ,
                                $id_asociado,//$log_id_asociado  ,
                                4,//$log_modulo_step_id  ,
                                'fedatario',//$log_tabla_asociada  ,
                                'FED-ASIS-FIN',//$log_proceso  ,
                                'Finalizar Registro de Fedatario Revisar Asistente - Revisión Documento',//$log_descripcion  ,
                                '',//$log_comentario  ,
                                null//$log_archivo_id
                            );

                    (new fedatario())->guardar_proyecto_grupo_fedatario_asistente($id_asociado,$usuario_creador);
                    //actualizar proyecto grupo a finalizado si terminó todo el grupo
                    //cambiar de estado los fedatarios NORMAL a estado 0 para que se activen

                    //$this->estado_captura_glb($id_asociado, $cap_est_glb_1); // comentado por que debe pasar a fedatario NORMAL
                    // Actualizar control de calidad

                }
                return $this->retorna_autoasignacion_nueva_captura($proyecto_id,$recepcion_id,$captura_id,$usuario_creador);
                //return "1";

            });

        return $obj;

    }



    public function arbol_precargado_proyecto()
    {

        $porcentaje = "0";
        $proyecto_id = request('proyecto_id');
        $usuario_creador = session("usuario_id");

        //validar que haya terminado y
        //cambiar de estado al grupo old
        //update grupo old a grupo actual

        $data = (new fedatario())->arbol_fedatario_previo($proyecto_id,1);

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
                "id_captura" => $fila->captura_id,
                "indizacion_id" => $fila->indizacion_id,
                "fedatario_id" => $fila->fedatario_id,
                "id_documento" => $fila->documento_id,
                "cc_id" => $fila->cc_id,
                "adetalle_id" => $fila->adetalle_id,
                "recepcion_tipo" => $fila->recepcion_tipo,
                "cliente_id" => $fila->cliente_id,
                "usuario_creador" => $fila->usuario_creador,
                "proyecto_id" => $fila->proyecto_id,
                "recepcion_id" => $fila->recepcion_id
            ];
            $recepcion_old = $fila->recepcion_id;
            $recepcion_nombre_old = $fila->recepcion_nombre;

            $proyecto_old = $fila->proyecto_id;
            $proyecto_nombre_old = $fila->proyecto_nombre;
            $porcentaje=$fila->proyecto_grupo_muestreo;
        }
        if($recepcion_old!="0" && $proyecto_old!="0"){
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

        $captura_autoasignada = $this->retorna_autoasignacion_nueva_captura($proyecto_id,0,0,$usuario_creador);

        return [$porcentaje,$array_proyecto,$captura_autoasignada];

    }

    function retorna_autoasignacion_nueva_captura($proyecto_id,$recepcion_id,$captura_id,$usuario_id){
        $data = (array)DB::select(
            "select a.captura_id,
            case when a.recepcion_id=:recepcion_id then 0
            else a.recepcion_id end as recepcion_id,
            case when a.proyecto_id=:proyecto_id then 0
            else a.proyecto_id end as proyecto_id
            from fedatario a
            left join captura b on a.captura_id = b.captura_id
            where (b.usuario_asignado_fed_revisar_asis is null or b.usuario_asignado_fed_revisar_asis=:user)
            and a.fedatario_estado=0
            and a.fedatario_tipo='ASISTENTE'
            and a.fedatario_elegido_aleatorio=1
            order by proyecto_id,recepcion_id,captura_id;"
            , ['recepcion_id' => $recepcion_id,'proyecto_id' => $proyecto_id, 'user' => $usuario_id]);//session("usuario_id")

        //return $data;

        $nueva_captura_id = '0';
        if (count($data) > 0) {
            $nueva_captura_id = ((array)$data[0])['captura_id'];
        }
        return $nueva_captura_id;
    }

    function autoasignar_captura_inicial(){
        $usuario_creador = session("usuario_id");
        return $this->retorna_autoasignacion_nueva_captura(0,0,0,$usuario_creador);
    }

}
