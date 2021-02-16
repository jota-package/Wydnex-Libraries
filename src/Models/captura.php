<?php

namespace Fedatario\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\filesController;
use App\Http\Controllers\respuesta;
use DB;
use App\documento;
use App\incidencia_captura;
use App\log;
use App\proyecto_captura_flujo;
use App\imagen;
use App\recepcion;


Trait captura
{
    protected $primaryKey = 'captura_id';
    protected $table = 'captura';


    protected $fillable = ['proyecto_id','recepcion_id','cliente_id','captura_estado_glb','captura_estado','usuario_creador','usuario_asignado_indizacion','usuario_asignado_control_calidad','usuario_asignado_fed_revisar_asis','usuario_asignado_fed_revisar_nor','usuario_asignado_reproceso','tc_id','tc_descripcion','captura_orden','flujo_id_actual','captura_file_id'];

    public function recepcion()
    {
        return $this->belongsTo('App\recepcion', "recepcion_id", "recepcion_id");
    }

    public function children()//Todas las recepciones
    {
        return $this
            ->hasMany('App\documento', "captura_id")
            ->select(
                "documento_id",
                "documento_nombre",
                "captura_id",
                "adetalle_id"
            )// ->with("children")//Esto nos permite concatenar un nivel mas de profundidad
            ;
    }

    public function children_captura()//Todas las recepciones
    {
        return $this
            ->hasMany('App\documento', "captura_id")
            ->select(
                "documento_id",
                "documento_nombre",
                "captura_id",
                "adetalle_id"
            )// ->with("children")//Esto nos permite concatenar un nivel mas de profundidad
            ;
    }

    public function indizacion()//Todas las recepciones
    {
        return $this
            ->hasMany('App\indizacion', "captura_id")
            ->select(
                "indizacion_id",
                "captura_id"
            )
            ->where("indizacion_estado", "1")
            ->orderBy('indizacion_id', 'DESC')
            // ->take(1)
            // ->with("children")//Esto nos permite concatenar un nivel mas de profundidad
            ;
    }

    public function guardar_captura($documento_id, $cliente_id, $adetalle_id)
    {


        $adet = new adetalle();
        $validacion = $adet::where("adetalle_id", $adetalle_id)->count();


        // return $validacion;
        if ($validacion === 1) {
            // if ($validacion === 0) {

            $validacion2 = $this::where('cliente_id', $cliente_id)
                ->where('adetalle_id', $adetalle_id)
                ->count();


            if ($validacion2 === 0) {
                $captura = $this::create([
                    "documento_id" => $documento_id,
                    "cliente_id" => $cliente_id,
                    "adetalle_id" => $adetalle_id
                ]);

                return $captura;
            }
            
            return "No valido.";
        } else {
            return "ya registrado";
        }

    }

    public function validador($recepcion_id)
    {

        return $this::join("documento as doc", "doc.documento_id", "captura.documento_id")
            ->join("recepcion as re", "re.recepcion_id", "doc.recepcion_id")
            ->where("re.recepcion_id", $recepcion_id)
            ->count();

    }

    public function crear_lista_captura($capturas, $imagen)
    {

        $id_usuario_actual = session('usuario_id');

        foreach ($capturas as $cap) {
            
            //viene por captura
            if (isset($cap['documento_id']) && isset($cap['adetalle_id'])) {

                $documento_instancia = new documento();
                $documento_instancia->where("documento_id", $cap['documento_id'])->update(['documento_nombre' => $cap['documento_nombre'],
                    'adetalle_id' => $cap['adetalle_id']]);

            } //viene por recepcion
            elseif (isset($cap['documento_id'])) {

                $documento_instancia = new documento();
                $documento_instancia->where("documento_id", $cap['documento_id'])
                    ->update(['documento_nombre' => $cap['documento_nombre']]);

                $incidencia_captura_instancia = new incidencia_captura();
                $incidencia_captura_instancia->where("captura_id", $cap['documento_id'])
                    ->update(['incidencia_id' => $cap['captura_incidencia_informativa']]);

                $log = new log();
                $log->create_log_ez(
                    $cap['documento_id'],//$log_captura_id  ,
                    $cap['documento_id'],//$log_id_asociado  ,
                    1,//$log_modulo_step_id  ,
                    'captura',//$log_tabla_asociada  ,
                    'CAP',//$log_proceso  ,
                    'Ingreso de Captura',//$log_descripcion  ,
                    '',//$log_comentario  ,
                    null//$log_archivo_id
                );

            } else {
                
                if (empty($cap["captura_file_id"])) {
                    // Creando la captura file
                    $file_created = filesController::create_captura([
                        "recepcion_id" => (!empty($cap["recepcion_id"])) ? $cap['recepcion_id'] : 0,
                        "nombre" => (!empty($cap["documento_nombre"])) ? $cap['documento_nombre'] : "",
                        "captura_estado" => (!empty($cap["captura_estado"])) ? $cap['captura_estado'] : 0,
                        "padre_id" => (!empty($cap["padre_id"])) ? $cap['padre_id'] : 0
                    ]);
                    if (!$file_created["estado"]) {
                        return response($file_created["mensaje"], 500);
                    } else {
                        $file_created = $file_created["payload"];
                    }

                    $cap["captura_file_id"] = $file_created["file_id"];
                }


                // $captura_instancia = new App\captura();
                $data = [
                    "proyecto_id" => $cap['proyecto_id'],
                    "recepcion_id" => $cap['recepcion_id'],
                    "cliente_id" => $cap['cliente_id'],
                    "captura_estado" => $cap['captura_estado'],
                    "captura_file_id" => (!empty($cap['captura_file_id'])) ? $cap['captura_file_id'] : null,
                    "captura_estado_glb" => 'cap',
                    "usuario_creador" => $id_usuario_actual,
                    "flujo_id_actual" => 1
                ];

                $captura_instancia = $this::create($data);
                
                $captura_id = $captura_instancia['captura_id'];

                $log = new log();
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
                (new proyecto_captura_flujo())->crear_PCF_from_PF($captura_id);


                $documento_instancia = new documento();
                $documento_instancia->captura_id = $captura_id;
                $documento_instancia->recepcion_id = $cap['recepcion_id'];
                $documento_instancia->proyecto_id = $cap['proyecto_id'];
                $documento_instancia->cliente_id = $cap['cliente_id'];
                if (isset($cap['adetalle_id'])) {

                    $documento_instancia->adetalle_id = $cap['adetalle_id'];
                }

                $documento_instancia->documento_nombre = $cap['documento_nombre'];
                $documento_instancia->documento_estado = 1;
                $documento_instancia->save();
                $documento_save = $documento_instancia->save();


                if ($imagen === true) { //IMAGEN
                    $imagen = new imagen();
                    $imagen->recepcion_id = $cap['recepcion_id'];
                    $imagen->captura_id = $captura_instancia['captura_id'];
                    $imagen->documento_id = $documento_instancia['documento_id'];
                    $imagen->imagen_nombre = $cap['imagen_nombre'];
                    $imagen->imagen_estado = 1;
                    $imagen->imagen_pagina = $cap['imagen_pagina'];
                    $imagen->imagen_url = $cap['imagen_url'];
                    $imagen->save();
                }


                $obj_captura = $this->where('captura_id', $captura_id)->first();
                $recepcion_id = $obj_captura->recepcion_id;

                $recepcion_instancia = new recepcion();
                $obj_recepcion = $recepcion_instancia->where('recepcion_id', $recepcion_id)->first();

                $recepcion_tipo = $obj_recepcion->recepcion_tipo;
                if ($cap['captura_estado'] == 1 && $recepcion_tipo == "s") {
                    $incidencia_captura = new incidencia_captura();
                    $incidencia_captura->incidencia_id = $cap['captura_incidencia_informativa'];
                    $incidencia_captura->captura_id = $captura_instancia['captura_id'];
                    $incidencia_captura->save();

                }

            }

        }


    }

    public function editar_captura($captura_id, $captura_nombre)
    {


        return $this->where("captura_id", $captura_id)->update(['captura_nombre' => $captura_nombre]);

    }

    public function listar_captura($recepcion_id)
    {

        return $this::join("documento as doc", "doc.documento_id", "captura.documento_id")
            ->join("recepcion as r", "r.recepcion_id", "doc.recepcion_id")
            ->leftjoin("adetalle as ad", "ad.adetalle_id", "captura.adetalle_id")
            ->where("r.recepcion_id", $recepcion_id)
            ->get();

    }

    public function mantenimiento($captura_id)
    {

        return DB::select("
        with tablero_control as (
            /*
             * Contexto:                Tablero que recibe los parámetros del backend
             * Funcionalidad:           Filtra los campos que utiliza en los querys que harán match a este tablero
             * Parámetros:              captura_id = 1
             */
            select proyecto_id,
                   captura_id,
                   captura_estado_glb,
                   flujo_id_actual,
                   usuario_creador,
                   captura_estado,
                   usuario_asignado_fed_revisar_nor,
                   usuario_asignado_fed_revisar_asis,
                   usuario_asignado_control_calidad,
                   usuario_asignado_indizacion,
                   usuario_asignado_reproceso
            from captura
            where captura_id = :captura_id
              and captura_estado = 1
        ),
             captura_modulo as (
                 /*
                  * Contexto:                Obtiene el módulo y el proyecto al que corresponde la captura
                  * Funcionalidad:           Selecciona la captura, modulo con su proyecto correspondiente
                  * Parámetros:              modulo_step_id = 1 - fedatario firmar
                  */
                 select distinct p_f.proyecto_id,
                                 modulo_step_id as modulo_captura,
                                 captura_id
                 from proyecto_flujo p_f
                          left join tablero_control tc
                                    on p_f.proyecto_id = tc.proyecto_id
                 where modulo_step_id = 1
             ),
             indizacion_modulo as (
                 /*
                  * Contexto:                Obtiene el módulo y el proyecto al que corresponde la indización
                  * Funcionalidad:           Selecciona la indización, modulo con su proyecto correspondiente
                  * Parámetros:              modulo_step_id = 2 - fedatario firmar
                  */
                 select distinct p_f.proyecto_id,
                                 case
                                     when modulo_step_id = 2 then 1
                                     else 0
                                     end as modulo_indizacion,
                                 captura_id
                 from proyecto_flujo p_f
                          left join tablero_control tc
                                    on p_f.proyecto_id = tc.proyecto_id
                 where modulo_step_id = 2),
             cc_modulo as (
                 /*
                  * Contexto:                Obtiene el módulo y el proyecto al que corresponde el control de calidad
                  * Funcionalidad:           Selecciona el control de calidad, modulo con su proyecto correspondiente
                  * Parámetros:              modulo_step_id = 3 - fedatario firmar
                  */
                 select distinct p_f.proyecto_id,
                                 case
                                     when modulo_step_id = 3 then 1
                                     else 0
                                     end as modulo_cc,
                                 captura_id
                 from proyecto_flujo p_f
                          left join tablero_control tc
                                    on p_f.proyecto_id = tc.proyecto_id
                 where modulo_step_id = 3),
             fedatario_modulo as (
                 /*
                  * Contexto:                Obtiene el módulo y el proyecto al que corresponde al fedatario
                  * Funcionalidad:           Selecciona el fedatario, modulo con su proyecto correspondiente
                  * Parámetros:              modulo_step_id = 4 - fedatario
                  */
                 select distinct p_f.proyecto_id,
                                 case
                                     when modulo_step_id = 4 then 1
                                     else 0
                                     end as modulo_fedatario,
                                 captura_id
                 from proyecto_flujo p_f
                          left join tablero_control tc
                                    on p_f.proyecto_id = tc.proyecto_id
                 where modulo_step_id = 4),
             fedatario_firmar_modulo as (
                 /*
                  * Contexto:                Obtiene el módulo y el proyecto al que corresponde al fedatario firmar
                  * Funcionalidad:           Selecciona el fedatario firmar, modulo con su proyecto correspondiente
                  * Parámetros:              modulo_step_id = 5 - fedatario firmar
                  */
                 select distinct p_f.proyecto_id,
                                 case
                                     when modulo_step_id = 5 then 1
                                     else 0
                                     end as modulo_fedatario_firmar,
                                 captura_id
                 from proyecto_flujo p_f
                          left join tablero_control tc
                                    on p_f.proyecto_id = tc.proyecto_id
                 where modulo_step_id = 5),
             modulos_flujo as (
                 /*
                  * Contexto:                Integramos todos los modulos
                  * Funcionalidad:           Selecciona la captura id, el proyecto id y los modulo correspondientes
                  */
                 select c_m.captura_id,
                        c_m.proyecto_id,
                        modulo_captura,
                        modulo_indizacion,
                        modulo_cc,
                        modulo_fedatario,
                        modulo_fedatario_firmar
                 from captura_modulo c_m
                          left join indizacion_modulo i_m
                                    on i_m.captura_id = c_m.captura_id
                          left join cc_modulo cc_m
                                    on cc_m.captura_id = c_m.captura_id
                          left join fedatario_modulo f_m
                                    on f_m.captura_id = c_m.captura_id
                          left join fedatario_firmar_modulo ff_m
                                    on ff_m.captura_id = c_m.captura_id
             ),
             cap_fed_asi as (
                 /*
                  * Contexto:                Obtiene el registro de fedatario - captura
                  * Funcionalidad:           Selecciona la captura id, el fedatario tipo y fedatario estado
                  */
                 select tc.captura_id
                      , fedatario_tipo   as estado_fed_asistente
                      , fedatario_estado as fedatario_estado_asistente
                 from tablero_control tc
                          left join fedatario fed
                                    on tc.captura_id = fed.captura_id
                 where fedatario_tipo = 'ASISTENTE'
             ),
             cap_fed_nor as (
                 /*
                  * Contexto:                Obtiene el registro de fedatario - captura
                  * Funcionalidad:           Selecciona la captura id, el fedatario tipo y fedatario estado
                  */
                 select tc.captura_id
                      , fedatario_tipo   as estado_fed_normal
                      , fedatario_estado as fedatario_estado_normal
                 from tablero_control tc
                          left join fedatario fed
                                    on tc.captura_id = fed.captura_id
                 where fedatario_tipo = 'NORMAL'
             ),
             cap_fed_fir as (
                 /*
                  * Contexto:                Obtiene el registro de fedatario firmar - captura
                  * Funcionalidad:           Selecciona la captura id, el fedatario firmar estado y usuario_creador
                  */
                 select tc.captura_id
                      , fedatario_firmar_estado
                      , fed_fir.usuario_creador as usuario_fed_fir
                 from tablero_control tc
                          left join fedatario_firmar fed_fir
                                    on tc.captura_id = fed_fir.captura_id
             ),
             union_fedatario_tipo as (
                 /*
                  * Contexto:                Obtiene el registro de fedatario asistente - fedatario normal - captura
                  * Funcionalidad:           Selecciona la captura id, el fedatario estado asistente, fedatario estado normal,
                                             fedatario estado asistente y fedatario estado normal
                  */
                 select cfa.captura_id,
                        estado_fed_asistente,
                        estado_fed_normal,
                        fedatario_estado_asistente,
                        fedatario_estado_normal
                 from cap_fed_asi cfa
                          right join cap_fed_nor cfn
                                     on cfa.captura_id = cfn.captura_id
                 union all
                 select cfa.captura_id,
                        estado_fed_asistente,
                        null,
                        fedatario_estado_asistente,
                        null
                 from cap_fed_asi cfa
                 union all
                 select cfn.captura_id,
                        null,
                        estado_fed_normal,
                        null,
                        fedatario_estado_normal
                 from cap_fed_nor cfn
             ),
             usuario_reproceso as (
                 select distinct usuario_asignado_reproceso as usuario_id,
                                 persona_nombre,
                                 captura_id
                 from tablero_control tc
                          left join persona per
                                    on tc.usuario_asignado_reproceso = per.usuario_id
                 where usuario_id is not null
             ),
             captura_estado_global as (
                 /*
                  * Contexto:                Obtiene el estado global de captura
                  * Funcionalidad:           Selecciona el estado global de captura
                  */
                 select distinct tc.captura_id
                               , captura_estado_glb
                               , CASE --Todos pasan captura si no está en el flujo actual captura y el estado glb cap está finalizado la captura
                                     WHEN captura_estado_glb = 'cap' and flujo_id_actual = 1 and modulo_captura is not null
                                         THEN 1 --en proceso
                                     ELSE
                                         2 --ya finalizado(ok)
                     END                                                               as estado_captura
                               , CASE --Corroborar el estado de indizacion
                                     WHEN captura_estado_glb = 'ind' and flujo_id_actual = 2 and modulo_indizacion is not null
                                         THEN 1 --en proceso
                                     WHEN flujo_id_actual > 2 and modulo_indizacion is not null THEN 2 --finalizado
                                     ELSE
                                         0 --ya finalizado(ok)
                     END                                                               as estado_indizacion
                               , CASE --Corroborar el estado de control de calidad
                                     WHEN captura_estado_glb = 'cc' and flujo_id_actual = 3 and modulo_cc is not null
                                         THEN 1 --en proceso
                                     WHEN flujo_id_actual > 3 and modulo_cc is not null THEN 2 --finalizado
                                     ELSE
                                         0 --no iniciado
                     END                                                               as estado_control_calidad
                               , CASE --Corroborar el estado de fedatario asistente
                                     WHEN fedatario_estado_asistente = 0 and estado_fed_asistente = 'ASISTENTE' and
                                          captura_estado_glb = 'fed' and modulo_fedatario is not null THEN 1 --en proceso
                                     WHEN fedatario_estado_asistente = 2 and modulo_fedatario is not null THEN 2 --finalizado
                                     WHEN captura_estado_glb != 'fed' and flujo_id_actual > 3 and modulo_fedatario is not null
                                         THEN 2 --finalizado
                                     ELSE
                                         0 --no iniciado
                     END                                                               as estado_fed_asistente
                               , CASE --Corroborar el estado de fedatario normal
                                     WHEN estado_fed_normal = 'NORMAL' and captura_estado_glb = 'fed' and
                                          fedatario_estado_normal in (0, 3) and modulo_fedatario is not null THEN 1 --en proceso
                                     WHEN fedatario_estado_normal = 2 and modulo_fedatario is not null THEN 2 --finalizado
                                     WHEN captura_estado_glb != 'fed' and flujo_id_actual > 3 and modulo_fedatario is not null
                                         THEN 2
                                     ELSE
                                         0 --no iniciado
                     END                                                               as estado_fed_normal
                               , CASE --Corroborar el estado de fedatario firmar
                                     WHEN captura_estado_glb = 'fed_fir' and modulo_fedatario_firmar is not null and fedatario_firmar_estado != 4
                                         THEN 1 --en proceso
                                     WHEN fedatario_firmar_estado = 4 and modulo_fedatario_firmar is not null
                                         THEN 2 --finalizado
                                     ELSE
                                         0 --no iniciado
                     END                                                               as estado_fed_firmar
                               , CASE
                                     WHEN gmd.gmd_estado = 3 THEN 2 -- en Finalizado
                                     WHEN gmd.gmd_estado = 1 THEN 1 -- en proceso
                                     WHEN gmd.gmd_estado is null THEN 0 -- en Finalizado
                                     ELSE 0 -- no inicia
                     end                                                               as estado_gen_medio
                               , count(i.imagen_id) over (partition by i.documento_id) as cant_paginas
                               , tc.usuario_creador                                    as usuario_captura
                               , usuario_asignado_indizacion                           as usuario_ind
                               , usuario_asignado_control_calidad                      as usuario_cc
                               , usuario_asignado_fed_revisar_asis                     as usuario_fed_asi
                               , usuario_asignado_fed_revisar_nor                      as usuario_fed_nor
                               , usuario_asignado_reproceso                            as usuario_rep
                               , usuario_fed_fir
                               , modulo_captura
                               , modulo_indizacion
                               , modulo_cc
                               , modulo_fedatario
                               , modulo_fedatario_firmar
                 from tablero_control tc
                          left join documento d
                                    on d.captura_id = tc.captura_id
                          left join imagen i
                                    on i.documento_id = d.documento_id and i.imagen_estado = 1
                          left join union_fedatario_tipo uft
                                    on tc.captura_id = uft.captura_id
                          left join cap_fed_fir cff
                                    on cff.captura_id = uft.captura_id
                          left join modulos_flujo m_f
                                    on m_f.captura_id = tc.captura_id
                          left join generacion_medio_detalle_captura gmdc
                                    on tc.captura_id = gmdc.captura_id
                          left join generacion_medio_detalle gmd
                                    on gmd.gm_id = gmdc.gm_id
                          left join usuario_reproceso u_r
                                    on u_r.captura_id = tc.captura_id
             ),
             captura_cantidad as (
                 select *
                      , CASE --cantidad de páginas si es que finalizaron todas las capturas
                            WHEN estado_captura = 2 THEN cant_paginas --
                            ELSE
                                0
                     END as cant_paginas_cap
                      , CASE --cantidad de páginas si es que finalizó su indización
                            WHEN estado_indizacion = 2 THEN cant_paginas --ya finalizado(ok)
                            ELSE
                                0 --en proceso o cuando se señale
                     END as cant_paginas_ind
                      , CASE --cantidad de páginas si es que finalizó su control de calidad
                            WHEN estado_control_calidad = 2 THEN cant_paginas --ya finalizado(ok)
                            ELSE
                                0 --en proceso o cuando se señale
                     END as cant_paginas_cc
                      , CASE --cantidad de páginas si es que finalizó su fedatario asistente
                            WHEN estado_fed_asistente = 2 THEN cant_paginas --ya finalizado(ok)
                            ELSE
                                0 --en proceso o cuando se señale
                     END as cant_paginas_fa
                      , CASE --cantidad de páginas si es que finalizó su fedatario normal
                            WHEN estado_fed_normal = 2 THEN cant_paginas --ya finalizado(ok)
                            ELSE
                                0 --en proceso o cuando se señale
                     END as cant_paginas_fn
                      , CASE --cantidad de páginas si es que finalizó su fedatario firmar
                            WHEN estado_fed_firmar = 2 THEN cant_paginas --ya finalizado(ok)
                            ELSE
                                0 --en proceso o cuando se señale
                     END as cant_paginas_ff
                      , CASE --cantidad de páginas si es que finalizó su generacion de medios
                            WHEN estado_gen_medio = 2 THEN cant_paginas --ya finalizado(ok)
                            ELSE
                                0 --en proceso o cuando se señale
                     END as cant_paginas_gm
                 from captura_estado_global
             ),
             captura_total as (
                 select *
                      , CASE --cantidad de páginas la captura
                            WHEN estado_captura = 2 THEN cant_paginas --
                            ELSE
                                0
                     END as total_cap
                      , CASE --cantidad de páginas de indización
                            WHEN estado_indizacion = 2 THEN cant_paginas --
                            ELSE
                                0
                     END as total_ind
                      , CASE --cantidad de páginas de control de calidad
                            WHEN estado_control_calidad = 2 THEN cant_paginas --
                            ELSE
                                0
                     END as total_cc
                      , CASE --cantidad de páginas de fedatario asistente
                            WHEN estado_fed_asistente = 2 THEN cant_paginas --
                            ELSE
                                0
                     END as total_fed_asistente
                      , CASE --cantidad de páginas de fedatario normal
                            WHEN estado_fed_normal = 2 THEN cant_paginas --
                            ELSE
                                0
                     END as total_fed_normal
                      , CASE --cantidad de páginas de fedatario firmar
                            WHEN estado_fed_firmar = 2 THEN cant_paginas --
                            ELSE
                                0
                     END as total_fed_firmar
                      , CASE --cantidad de páginas de generacion medios
                            WHEN estado_gen_medio = 2 THEN cant_paginas --
                            ELSE
                                0
                     END as total_gen_medios
                 from captura_cantidad
             )
        select *
        from captura_total;
             ", ["captura_id" => $captura_id]);
    }

    public function modulo_step_glb_validador($recepcion_id, $modulo_step_id)
    {

        return DB::select(
            "select
                count(modulo_step_id) as modulo_step
                --,recepcion_id
                --,r.proyecto_id
            from proyecto_flujo p_f
                left join recepcion r
                    on p_f.proyecto_id = r.proyecto_id
            where recepcion_id = :recepcion_id and  modulo_step_id = :modulo_step_id
            ", ["recepcion_id" => $recepcion_id, "modulo_step_id" => $modulo_step_id]);
    }

    public function modulo_cambio_flujo($estado_captura,
                                        $estado_indizacion,
                                        $estado_control_calidad,
                                        $estado_fed_normal,
                                        $estado_fed_firmar,
                                        $estado_gen_generar)
    {

        return DB::select(
            "
            with tablero_control as (
                --Contruimos la vista de la matriz de control que recibiremos del backend con todos los valores que se pueden editar 😎
                select *
                from (values (:cap::INTEGER, :ind::INTEGER, :cc::INTEGER, :fn::INTEGER, :ff::INTEGER, :gm::INTEGER))
                         AS t (tab_est_cap, tab_est_ind, tab_est_cc, tab_est_fed_nor, tab_est_fed_fir, tab_est_gen_med)
            ),
                 tablero_control_array as (
                     --Contruimos la vista de la matriz de control que recibiremos del backend con todos los valores que se pueden editar 😎
                     select *
                     from (values (1, :cap::INTEGER, 'captura'),
                                  (2, :ind::INTEGER, 'indizacion'),
                                  (3, :cc::INTEGER, 'control de calidad'),
                                  (4, :fn::INTEGER, 'fedatario normal'),
                                  (5, :ff::INTEGER, 'fedatario firmar'),
                                  (5, :gm::INTEGER, 'generacion medio'))
                              AS t (modulo_step, valores, modulo)
                 ),
                 step_one_check as (
                     select COUNT(valores) filter (where valores = 1) as enproceso,
                            case
                                when (COUNT(valores) filter (where valores = 1)) > 1
                                    then 'No es puede poner en proceso en varios módulos, se corregirá automáticamente'
                                else 'ok'
                                end                                      mensaje,
                            case
                                when (COUNT(valores) filter (where valores = 1)) > 1 then 1
                                else 0
                                end                                      estado
                     from tablero_control_array
                 ),
                 step_one_tmp as (
                     select case
                                when tab_est_ind = 0 then 1
                                else tab_est_cap
                                end estado_captura,
                            case
                                when (tab_est_cap = 1 or tab_est_cap = 0) or tab_est_cap = 0 then 0
                                when tab_est_cc = 0 and tab_est_ind != 0 and tab_est_cap != 0 then 1
                                else tab_est_ind
                                end estado_indizacion,
                            case
                                when (tab_est_ind = 1 and tab_est_cap != 1) or (tab_est_ind = 0) or (tab_est_cap = 0) or
                                     (tab_est_cap = 1) then 0
                                when tab_est_fed_nor = 0 and tab_est_cc != 0 and tab_est_ind != 0 and tab_est_cap != 0 then 1
                                else tab_est_cc
                                end estado_control_calidad,
                            case
                                when tab_est_cc = 1 or tab_est_ind = 1 or
                                     tab_est_cap = 1 and tab_est_cc != 1 and tab_est_ind != 1 and tab_est_cap != 1 or
                                     tab_est_cc = 0 or tab_est_ind = 0 or tab_est_cap = 0 then 0
                                when tab_est_fed_fir = 0 and tab_est_fed_nor != 0 and tab_est_cc != 0 and
                                     tab_est_ind != 0 and tab_est_cap != 0 then 1
                                else tab_est_fed_nor
                                end estado_fed_normal,
                            case
                                when tab_est_fed_nor = 1 or tab_est_cc = 1 or tab_est_ind = 1 or
                                     tab_est_cap = 1 and tab_est_cc != 1 and tab_est_ind != 1 and
                                     tab_est_cap != 1 or tab_est_fed_nor = 0 or tab_est_cc = 0 or
                                     tab_est_ind = 0 or tab_est_cap = 0 then 0
                                when tab_est_gen_med = 0 and tab_est_fed_fir != 0 and tab_est_fed_nor != 0  and tab_est_cc != 0 and
                                     tab_est_ind != 0 and tab_est_cap != 0 then 1
                                else tab_est_fed_fir
                                end estado_fed_firmar,
                            case
                                when tab_est_fed_fir = 1 or tab_est_fed_nor = 1 or tab_est_cc = 1 or tab_est_ind = 1 or tab_est_cap = 1 and
                                     tab_est_fed_fir != 1 and tab_est_fed_nor != 1 and tab_est_cc != 1 and tab_est_ind != 1 and tab_est_cap != 1 or
                                     tab_est_fed_fir = 0 or tab_est_fed_nor = 0 or tab_est_cc = 0 or tab_est_ind = 0 or tab_est_cap = 0 then 0
                                else tab_est_gen_med
                                end estado_gen_medio
                     from tablero_control tc
                     --left join step_one_check soc
                     --on tc.captura_id = soc.captura_id
                     --where estado = 1
                 )
            select *
            from step_one_tmp;
            ", ["cap" => $estado_captura,
            "ind" => $estado_indizacion,
            "cc" => $estado_control_calidad,
            "fn" => $estado_fed_normal,
            "ff" => $estado_fed_firmar,
            "gm" => $estado_gen_generar]);

    }

    public function super_squery_upd_mantenimiento(
        $captura_id
        , $estado_captura
        , $estado_indizacion
        , $estado_control_calidad
        , $estado_fed_normal
        , $estado_fed_firmar
        , $estado_gen_medio

        , $usuario_captura
        , $usuario_indizacion
        , $usuario_cc
        , $usuario_fa
        , $usuario_feda
        , $usuario_firmado
        , $usuario_reproceso
    )
    {

        return DB::select(
            "
            with tablero_control as (
                /*
                 * Contexto:                Contruimos una tabla que se forma con los valores elegidos en backend
                 * Funcionalidad:           Utilizaremos esta vista para hacer el match que controla toda la lógica
                 */
                select *
                from (values (:cap::INTEGER, :est_cap::INTEGER, :est_ind::INTEGER, :est_cc::INTEGER,
                              :est_fn::INTEGER, :est_ff::INTEGER, :est_gm::INTEGER, :usu_cap::INTEGER, :usu_ind::INTEGER,
                              :usu_cc::INTEGER, :usu_fa::INTEGER, :usu_feda::INTEGER, :usu_fir::INTEGER, :usu_rep::INTEGER))
                         AS t (captura_id, tab_est_cap, tab_est_ind, tab_est_cc,
                               tab_est_fed_nor, tab_est_fed_fir, tab_est_gen_med, tab_usu_cap, tab_usu_ind,
                               tab_usu_cc, tab_usu_fed_asi, tab_usu_fed_nor, tab_usu_fed_fir,tab_usu_rep)
            ),
                 gen_med_eliminar as (
                     /*
                      * Contexto:                Este bloque se aplica cuando el flujo retrocede antes de generación de medios
                      * Funcionalidad:           Eliminamos el registro de generación medios detalle captura
                      * Tablero de control:      En el tablero de control el campo tab_est_gen_med eligió ninguno <> 0
                      */
                     delete from generacion_medio_detalle_captura gmdc_ini
                         using generacion_medio_detalle_captura gmdc
                             left join tablero_control AS tc ON
                                 gmdc.captura_id = tc.captura_id
                         where
                                 gmdc_ini.gmdc_id = gmdc.gmdc_id and
                                 tc.tab_est_gen_med = 0 -- Valor del tablero de control
                         returning gmdc.captura_id
                 ),
                 fed_fir_eliminar as (
                     /*
                      * Contexto:                Este bloque se aplica cuando el flujo retrocede antes de fedatario firmar
                      * Funcionalidad:           Eliminamos el registro de fedatario firmar
                      * Información adicional:   Los estados en fedatario firmar son: 0 - Proceso, 1 - Reproceso, 2 - Finalizado, 3 - Por firmar, 4 - Firmado
                      * Tablero de control:      En el tablero de control el campo tab_est_fed_fir eligió ninguno <> 0
                      */
                     delete from fedatario_firmar ff
                         using fedatario_firmar ff_1
                             left join tablero_control AS tc ON
                                 ff_1.captura_id = tc.captura_id
                         where
                                 ff_1.captura_id = ff.captura_id and
                                 tc.tab_est_fed_fir = 0 and -- Valor del tablero de control
                                 ff_1.captura_id = tc.captura_id
                         returning ff.captura_id
                 ),
                 fed_fir_actualizar as (
                     /*
                      * Contexto:                Este bloque se aplica cuando el flujo pasa al estado en proceso de fedatario firmar
                      * Funcionalidad:           Actualizamos el estado del registro de fedatario firmar
                      * Información adicional:   Los estados en fedatario firmar son: 0 - Proceso, 1 - Reproceso, 2 - Finalizado, 3 - Por firmar, 4 - Firmado
                      * Tablero de control:      En el tablero de control el campo tab_est_fed_fir eligió 'En proceso' <> 1
                      */
                     update fedatario_firmar
                         set fedatario_firmar_estado = 1,
                             usuario_creador = tab_usu_fed_fir
                         --Generamos una tabla de la union del tablero de control con la tabla fedatario firmar
                         from tablero_control tc
                             left join fedatario_firmar ff
                             on tc.captura_id = ff.captura_id
                         where
                             --Se referencia la tabla original con la tabla utilizada en la union anterior y se le pone las condiciones necesarias
                                 fedatario_firmar.fedatario_firmar_id = ff.fedatario_firmar_id
                                 and tc.tab_est_fed_fir = 1 -- Valor del tablero de control
                         returning fedatario_firmar.fedatario_firmar_id
                 ),
                 tmp_captura_glb as (
                     /*
                      * Contexto:                Este bloque se forma a través del tablero de control formando valores que corresponden a la captura
                      * Funcionalidad:           Construímos a través del tablero de control los valores que corresponden a la captura 'captura_estado_glb' y el  'flujo_id_actual'
                      */
                     select
                          --tc.captura_id,
                         case
                             when tab_est_fed_fir = 1 then 'fed_fir'
                             when tab_est_fed_nor = 1 then 'fed'
                             when tab_est_cc = 1 then 'cc'
                             when tab_est_ind = 1 then 'ind'
                             when tab_est_cap = 1 then 'cap'
                             end as cap_new_std_glb
                          , case
                                when tab_est_fed_fir = 1 then 5
                                when tab_est_fed_nor = 1 then 4
                                when tab_est_cc = 1 then 3
                                when tab_est_ind = 1 then 2
                                when tab_est_cap = 1 then 1
                         end     as cap_new_step_glb
                          , tc.*
                     from tablero_control tc
                              left join captura cap
                                        on cap.captura_id = tc.captura_id
                 ),
                 upd_captura_glb as (
                     /*
                      * Contexto:                Este bloque actualiza el usuario asignado y el estado flujo global de captura con ayuda de la tabla anterior 'tmp_captura_glb'
                      * Funcionalidad:           Actualizamos el estado de captura glb 'captura_estado_glb' y los usuarios asignados 'usuario_asignado_'
                      */
                     update captura
                         set captura_estado_glb = tmp_cg.cap_new_std_glb,
                             flujo_id_actual = tmp_cg.cap_new_step_glb,
                             usuario_creador = tab_usu_cap,
                             --Asignando en el campo usuario_asignado_indizacion = tab_usu_ind,
                             usuario_asignado_indizacion =
                                     case
                                         when tab_usu_ind = 0 then null
                                         else tab_usu_ind
                                         end,
                             --Asignando en el campo usuario_asignado_control_calidad = tab_usu_cc,
                             usuario_asignado_control_calidad =
                                     case
                                         when tab_usu_cc = 0 then null
                                         else tab_usu_cc
                                         end,
                             --Asignando en el campo usuario_asignado_fed_revisar_asis = tab_usu_fed_asi
                             usuario_asignado_fed_revisar_asis =
                                     case
                                         when tab_usu_fed_asi = 0 then null
                                         else tab_usu_fed_asi
                                         end,
                             --Asignando en el campo usuario_asignado_fed_revisar_nor = tab_usu_fed_nor
                             usuario_asignado_fed_revisar_nor =
                                     case
                                         when tab_usu_fed_nor = 0 then null
                                         else tab_usu_fed_nor
                                         end,
                             --Asignando en el campo usuario_asignado_reproceso = tab_usu_rep
                             usuario_asignado_reproceso =
                                     case
                                         when tab_usu_rep = 0 then null
                                         else tab_usu_rep
                                         end,
                             captura_estado =
                                     case
                                         when tmp_cg.tab_est_cap = 1 then 1
                                         else cap.captura_estado
                                         end
                         --coloco el query anterior
                         from tmp_captura_glb tmp_cg
                             left join captura cap
                             on tmp_cg.captura_id = cap.captura_id
                         where
                             --colocar el where del query anterior
                             captura.captura_id = cap.captura_id
                         returning captura.captura_estado_glb,captura.captura_id
                 ),
                 fed_rev_eliminar_en_proceso as (
                     /*
                      * Contexto:                Este bloque se aplica cuando el flujo pasa al estado en 'proceso' de fedatario revisar, debido al caso particular
                                                 del proyecto se eliminará el registro y se generar uno nuevo en el siguiente bloque.
                      * Funcionalidad:           Eliminamos el registro de fedatario revisar
                      * Información adicional:   Los estados en fedatario revisar son: 0 - Proceso ... pendiente
                      * Tablero de control:      En el tablero de control el campo tab_est_fed_nor eligió 'En proceso' <> 1
                      */
                     delete from fedatario fed_nor
                         using fedatario fed_1
                             left join tablero_control AS tc ON
                                 fed_1.captura_id = tc.captura_id
                         where
                                 fed_1.fedatario_id = fed_nor.fedatario_id and
                                 tc.tab_est_fed_nor = 1 and
                                 --fed_nor.fedatario_tipo = 'NORMAL' and--Adicional no necesario si las validaciones previas lo filtran
                                 fed_1.captura_id = tc.captura_id
                         returning fed_nor.fedatario_id,fed_nor.captura_id
                 ),
                 fed_rev_crear_registro_limpio as (
                     /*
                      * Contexto:                Este bloque se aplica para generar un registro de fedatario revisar limpio, complemento del bloque anterior.
                      * Funcionalidad:           Insertaremos el registro de fedatario revisar con estado en 'proceso'
                      * Información adicional:   Los estados en fedatario revisar son: 0 - Proceso ... pendiente
                      * Tablero de control:      En el tablero de control el campo tab_est_fed_nor eligió 'En proceso' <> 1
                      */
                     insert into fedatario (captura_id, proyecto_id, recepcion_id, cliente_id, fedatario_estado, usuario_creador,
                                            fedatario_tipo, fedatario_grupo, created_at, updated_at)
                         select a.captura_id,
                                a.proyecto_id,
                                a.recepcion_id,
                                a.cliente_id,
                                --0,
                                case
                                    when b.proyecto_fedatario_asistente = 1
                                        then 3
                                    else 0
                                    end,
                                tab_usu_fed_nor,--usuario creador
                                'NORMAL',
                                case
                                    when b.proyecto_fedatario_asistente = 1
                                        then b.proyecto_grupo_fedatario_asis_actual
                                    else b.proyecto_grupo_fedatario_actual
                                    end,
                                now(),
                                now()
                         from captura a
                                  left join proyecto b on a.proyecto_id = b.proyecto_id
                                  join tablero_control tc on tc.captura_id = a.captura_id
                         where a.captura_id = :cap::INTEGER
                           and tc.tab_est_fed_nor = 1
                         union
                         select a.captura_id,
                                a.proyecto_id,
                                a.recepcion_id,
                                a.cliente_id,
                                0,
                                tab_usu_fed_nor,--usuario creador
                                'ASISTENTE',
                                b.proyecto_grupo_fedatario_asis_actual,
                                now(),
                                now()
                         from captura a
                                  join proyecto b on a.proyecto_id = b.proyecto_id and b.proyecto_fedatario_asistente = 1
                                  join tablero_control tc on tc.captura_id = a.captura_id
                         where a.captura_id = :cap::INTEGER
                           and tc.tab_est_fed_nor = 1
                         RETURNING fedatario_id
                 ),
                 cc_eliminar as (
                     /*
                      * Contexto:                Este bloque se aplica cuando el flujo retrocede antes de control de calidad
                      * Funcionalidad:           Eliminamos el registro de control de calidad
                      * Información adicional:   Los estados en control de calidad son: ...
                      * Tablero de control:      En el tablero de control el campo tab_est_cc eligió ninguno <> 0
                      */
                     delete from control_calidad cc
                         using control_calidad cc_2
                             left join tablero_control AS tc ON
                                 cc_2.captura_id = tc.captura_id
                         where
                                 cc_2.cc_id = cc.cc_id and
                                 tc.tab_est_cc = 0 and -- Cuando elige 'ninguno' en el modulo control de calidad
                                 cc_2.captura_id = tc.captura_id
                         returning cc.cc_id,cc.captura_id
                 ),
                 cc_actualizar as (
                     /*
                      * Contexto:                Este bloque se aplica cuando el flujo pasa al estado en proceso de control de calidad
                      * Funcionalidad:           Actualizamos el estado del registro de control de calidad
                      * Información adicional:   Los estados en control de calidad son: 0 - 'En proceso', 2 - 'Finalizado'
                      * Tablero de control:      En el tablero de control el campo tab_est_cc eligió 'En proceso' <> 1
                      select * from generacion_medio_detalle;
                      */
                     update control_calidad cc
                         set cc_estado = 0
                         from control_calidad cc_1
                             left join tablero_control tc
                             on cc_1.captura_id = tc.captura_id
                         where cc_1.cc_id = cc.cc_id
                             --Se compara con la tabla original y se le pone las condiciones necesarias
                             and cc_1.captura_id = tc.captura_id
                             and tc.tab_est_cc = 1
                 ),
                 ind_eliminar as (
                     /*
                      * Contexto:                Este bloque se aplica cuando el flujo retrocede antes de indización
                      * Funcionalidad:           Eliminamos el registro de indización
                      * Información adicional:   Los estados en indización son: ...
                      * Tablero de control:      En el tablero de control el campo tab_est_ind eligió ninguno <> 0
                      */
                     delete from indizacion
                         using indizacion ind
                             left join tablero_control tc
                             on ind.captura_id = tc.captura_id
                         where ind.indizacion_id = indizacion.indizacion_id
                             and tc.tab_est_ind = 0 -- Cuando elige 'Ninguno' en el módulo indización
                         --returning ind.captura_id, ind.indizacion_id
                         returning indizacion.captura_id, indizacion.indizacion_id
                 ),
                 ind_actualizar as (
                     /*
                      * Contexto:                Este bloque se aplica cuando el flujo pasa al estado en proceso de indización
                      * Funcionalidad:           Actualizamos el estado del registro de indización
                      * Información adicional:   Los estados en indización son: ...
                      * Tablero de control:      En el tablero de control el campo tab_est_ind eligió 'En proceso' <> 1
                      */
                     update indizacion ind
                         set indizacion_estado = 0
                         from tablero_control tc
                             left join indizacion ind_1
                             on tc.captura_id = ind_1.captura_id
                         where ind.indizacion_id = ind_1.indizacion_id
                             --Se compara con la tabla original y se le pone las condiciones necesarias
                             and tc.tab_est_ind = 1
                         returning ind.indizacion_id, ind.captura_id,ind.indizacion_estado
                 ),
                 update_proyecto_captura_flujo as (
                     /*
                      * Contexto:                Este bloque modifica los estados del proyecto captura flujo
                      * Funcionalidad:           Actualizamos la tabla proyecto_captura_flujo para reiniciar los id's guardados
                      */
                     update proyecto_captura_flujo
                         set modulo_id = null, created_at = null, updated_at = null
                         from tablero_control t
                             left join captura c on c.captura_id = t.captura_id
                             left join proyecto_flujo pf on c.proyecto_id = pf.proyecto_id
                                 and pf.modulo_step_id =
                                     case
                                         when t.tab_est_cap = 1 then 1
                                         when t.tab_est_ind = 1 then 2
                                         when t.tab_est_cc = 1 then 3
                                         when t.tab_est_fed_nor = 1 then 4
                                         when t.tab_est_fed_fir = 1 then 5
                                         else 0 end
                             left join proyecto_captura_flujo pcf on pcf.captura_id = c.captura_id and
                                                                     pcf.modulo_step_orden > pf.modulo_step_orden
                         where proyecto_captura_flujo.proyecto_captura_flujo_id = pcf.proyecto_captura_flujo_id
                         returning *
                 )
            select *
            from upd_captura_glb;
            ", ["cap" => $captura_id,
            "est_cap" => $estado_captura,
            "est_ind" => $estado_indizacion,
            "est_cc" => $estado_control_calidad,
            "est_fn" => $estado_fed_normal,
            "est_ff" => $estado_fed_firmar,
            "est_gm" => $estado_gen_medio,

            "usu_cap" => $usuario_captura,
            "usu_ind" => $usuario_indizacion,
            "usu_cc" => $usuario_cc,
            "usu_fa" => $usuario_fa,
            "usu_feda" => $usuario_feda,
            "usu_fir" => $usuario_firmado,
            "usu_rep" => $usuario_reproceso
        ]);

    }

    static public function obtener_indices_capturas($lista_capturas = "")
    {
        $lista_indices = DB::select(
            "with
            datos_input as (
                select :lista_capturas::INT[] as capturas
            ),
            datos as (
                select
                c.captura_id,
                --e.elemento_nombre,
                case
                    when r.elemento_tipo = 1 and r.valor is not null then r.valor
                    when r.elemento_tipo = 2 and o.opcion_nombre is not null then o.opcion_nombre
                    else ''
                end
                 as valor_ingresado
                ,r.respuesta_id
                from captura c
                cross join datos_input di
                join indizacion i on i.captura_id = c.captura_id and i.indizacion_tipo='VF'
                join respuesta r on r.indizacion_id = i.indizacion_id
                left join elemento e on e.elemento_id = r.elemento_id
                left join opcion o on r.elemento_tipo = 2 and r.opcion_id = o.opcion_id
                where c.captura_id  = ANY(di.capturas)
                order by c.captura_id,r.respuesta_id
            )
            select captura_id,
                string_agg(valor_ingresado,'|' order by respuesta_id) as datos
                from datos
                group by captura_id
                order by captura_id;", ["lista_capturas" => $lista_capturas]);

        if (isset($lista_indices)) {
            return respuesta::ok($lista_indices);
        } else {
            return respuesta::error("Hubo un problema en la consulta de la base de datos");
        }
    }


}
