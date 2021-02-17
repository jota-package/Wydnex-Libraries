<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use App;
use View;
use DB;
use Response;
use App\proyecto;
use App\recepcion;
use App\cliente;
use App\equipo;
use App\documento;
use App\log;
use App\Http\Controllers\incidenciaController;
use App\Http\Controllers\tipo_calibradorController;

Trait reporteController
{

    public function index_user_proceso(){

        $is_proyecto = new proyecto();
        $proyectos = $is_proyecto->select('proyecto_id', 'proyecto_nombre')->get();

        $equipo = equipo::join('persona as p','p.usuario_id','equipo.usuario_id')
            ->select('p.persona_nombre','p.persona_apellido','equipo.usuario_id')
            ->distinct()
            ->get();

        $is_recepcion = new recepcion();
        $recepcion = $is_recepcion->select('recepcion_id', 'recepcion_nombre')
            ->distinct()
            ->get();

        $is_cliente = new cliente();
        $cliente = $is_cliente->select('cliente_id', 'cliente_nombre')
            ->distinct()
            ->get();

        return view::make('reporte_usuario_proceso.index.content')
            ->with('proyectos', $proyectos)
            ->with('usuarios', $equipo)
            ->with('cliente', $cliente)
            ->with('recepcion', $recepcion);

    }

    public function reporte_general_x_usuario()
    {
        $usuarios = request("nombre_usuario");
        $proyectos = request("nombre_proyecto");
        $recepciones = request("nombre_recepcion");
        $clientes = request("nombre_cliente");
        $fecha_inicio = request("fecha_inicio");
        $fecha_fin = request("fecha_fin");
        $nombre_proceso = request("nombre_proceso");

        if( is_null($nombre_proceso) ){
            $nombre_proceso = ["1","2","3","4","5","6"];
        }


        $usuarios = self::to_array_sql( is_null($usuarios)?[]:$usuarios );
        $proyectos = self::to_array_sql( is_null($proyectos)?[]:$proyectos );
        $recepciones = self::to_array_sql( is_null($recepciones)?[]:$recepciones );
        $clientes = self::to_array_sql( is_null($clientes)?[]:$clientes );


        $rows = DB::select(
            DB::raw("

            with
            captura_base as (
                select distinct
                c.*
                ,count(i.imagen_id) over(partition by i.documento_id) as cant_paginas
                from captura c
                left join documento d
                    on d.captura_id = c.captura_id
                left join imagen i
                    on i.documento_id = d.documento_id and i.imagen_estado = 1
                where
                    (c.proyecto_id =  ANY(:proyectos::INT[]) or :proyectos='{}')
                and (c.recepcion_id =  ANY(:recepciones::INT[]) or :recepciones='{}')
                and (c.cliente_id =  ANY(:clientes::INT[]) or :clientes='{}')
            )
            ,persona_base as (
                select *
                from persona p
                where (p.usuario_id = ANY(:usuarios::INT[]) or :usuarios='{}')
            )
            , capturas as (
                select distinct
                p.persona_nombre||' '||p.persona_apellido as usuario
                ,count(c.captura_id) over(partition by c.usuario_creador) as capturas
                ,sum(c.cant_paginas) over(partition by c.usuario_creador) as cant_paginas
                ,c.usuario_creador as usuario_id
                from captura_base c
                join persona_base p
                    on c.usuario_creador = p.usuario_id
                where c.updated_at between to_date(:fecha_inicio,'DD/MM/YYYY') and to_date(:fecha_fin,'DD/MM/YYYY')
                --c.updated_at between to_date('05/02/2020','DD/MM/YYYY') and to_date('15/02/2020','DD/MM/YYYY')
            )
            , indizaciones as (
                select distinct
                p.persona_nombre||' '||p.persona_apellido as usuario
                ,count(i.indizacion_id) over(partition by c.usuario_asignado_indizacion) as indizaciones
                ,sum(c.cant_paginas) over(partition by c.usuario_asignado_indizacion) as cant_paginas
                ,c.usuario_asignado_indizacion as usuario_id
                from captura_base c
                join persona_base p
                    on c.usuario_asignado_indizacion = p.usuario_id
                join indizacion i
                    on i.captura_id = c.captura_id
                where c.usuario_asignado_indizacion is not null
                --and i.updated_at between to_date('05/02/2020','DD/MM/YYYY') and to_date('15/02/2020','DD/MM/YYYY')
                and i.updated_at between to_date(:fecha_inicio,'DD/MM/YYYY') and to_date(:fecha_fin,'DD/MM/YYYY')
            )
            , control_calidades as (
                select distinct
                p.persona_nombre||' '||p.persona_apellido as usuario
                ,count(cc.cc_id) over(partition by c.usuario_asignado_control_calidad) as control_calidades
                ,sum(c.cant_paginas) over(partition by c.usuario_asignado_control_calidad) as cant_paginas
                ,c.usuario_asignado_control_calidad as usuario_id
                from captura_base c
                join persona_base p
                    on c.usuario_asignado_control_calidad = p.usuario_id
                join control_calidad cc
                    on cc.captura_id = c.captura_id
                where c.usuario_asignado_control_calidad is not null
                and cc.updated_at between to_date(:fecha_inicio,'DD/MM/YYYY') and to_date(:fecha_fin,'DD/MM/YYYY')
            )
            , fedatario_revisar_asistentes as (
                select distinct
                p.persona_nombre||' '||p.persona_apellido as usuario
                ,count(f.fedatario_id) over(partition by c.usuario_asignado_fed_revisar_asis) as fedatario_revisar_asistentes
                ,sum(c.cant_paginas) over(partition by c.usuario_asignado_fed_revisar_asis) as cant_paginas
                ,c.usuario_asignado_fed_revisar_asis as usuario_id
                from captura_base c
                join persona_base p
                    on c.usuario_asignado_fed_revisar_asis = p.usuario_id
                join fedatario f
                    on f.captura_id = c.captura_id
                where c.usuario_asignado_fed_revisar_asis is not null
                and f.updated_at between to_date(:fecha_inicio,'DD/MM/YYYY') and to_date(:fecha_fin,'DD/MM/YYYY')
            )
            , fedatario_revisar_normal as (
                select distinct
                p.persona_nombre||' '||p.persona_apellido as usuario
                ,count(f.fedatario_id) over(partition by c.usuario_asignado_fed_revisar_nor) as fedatario_revisar_normal
                ,sum(c.cant_paginas) over(partition by c.usuario_asignado_fed_revisar_nor) as cant_paginas
                ,c.usuario_asignado_fed_revisar_nor as usuario_id
                from captura_base c
                join persona_base p
                    on c.usuario_asignado_fed_revisar_nor = p.usuario_id
                join fedatario f
                    on f.captura_id = c.captura_id
                where c.usuario_asignado_fed_revisar_nor is not null
                and f.updated_at between to_date(:fecha_inicio,'DD/MM/YYYY') and to_date(:fecha_fin,'DD/MM/YYYY')
            )
            , reprocesos as (
                select distinct
                p.persona_nombre||' '||p.persona_apellido as usuario
                ,count(a.incidencia_imagen_id) over(partition by c.usuario_asignado_reproceso) as cant_paginas
                ,c.usuario_asignado_reproceso as usuario_id
                from captura_base c
                join persona_base p
                        on c.usuario_asignado_reproceso = p.usuario_id
                join documento d
                    on d.captura_id = c.captura_id
                join imagen i
                    on i.documento_id = d.documento_id
                join incidencia_imagen a
                    on i.imagen_id = a.imagen_id and a.estado = 2
                join incidencia b
                    on a.incidencia_id = b.incidencia_id and b.incidencia_control = 0
                where c.usuario_asignado_reproceso is not null
                and a.updated_at between to_date(:fecha_inicio,'DD/MM/YYYY') and to_date(:fecha_fin,'DD/MM/YYYY')
            )
            select
            case
                when c.usuario_id is not null then c.usuario
                when i.usuario_id is not null then i.usuario
                when cc.usuario_id is not null then cc.usuario
                when fra.usuario_id is not null then fra.usuario
                when frn.usuario_id is not null then frn.usuario
                when rr.usuario_id is not null then rr.usuario
                else ''
            end as usuario
            --c.usuario
            --,case when c.capturas is not null then c.capturas else 0 end as capturas
            --,case when i.indizaciones is not null then i.indizaciones else 0 end as indizaciones
            --,case when cc.control_calidades is not null then cc.control_calidades else 0 end as control_calidades
            --,case when fra.fedatario_revisar_asistentes is not null then fra.fedatario_revisar_asistentes else 0 end as fedatario_revisar_asistentes
            --,case when frn.fedatario_revisar_normal is not null then frn.fedatario_revisar_normal else 0 end as fedatario_revisar_normales

            ".(in_array("1", $nombre_proceso)?"":"--").
            ",(case when c.cant_paginas is not null then c.capturas else 0 end)::varchar(20)||' imágenes' as capturas
            ".(in_array("2", $nombre_proceso)?"":"--").
            ",(case when i.cant_paginas is not null then i.indizaciones else 0 end)::varchar(20)||' imágenes' as indizaciones
            ".(in_array("3", $nombre_proceso)?"":"--").
            ",(case when cc.cant_paginas is not null then cc.control_calidades else 0 end)::varchar(20)||' imágenes' as control_calidades
            ".(in_array("4", $nombre_proceso)?"":"--").
            ",(case when fra.cant_paginas is not null then fra.fedatario_revisar_asistentes else 0 end)::varchar(20)||' imágenes' as fedatario_revisar_asistentes
            ".(in_array("5", $nombre_proceso)?"":"--").
            ",(case when frn.cant_paginas is not null then frn.fedatario_revisar_normal else 0 end)::varchar(20)||' imágenes' as fedatario_revisar_normales
            ".(in_array("6", $nombre_proceso)?"":"--").
            ",(case when rr.cant_paginas is not null then rr.cant_paginas else 0 end)::varchar(20)||' imágenes' as reprocesos
            ,0
            ".(in_array("1", $nombre_proceso)?"":"--")."+(case when c.cant_paginas is not null then c.capturas else 0 end)
            ".(in_array("2", $nombre_proceso)?"":"--")."+(case when i.cant_paginas is not null then i.indizaciones else 0 end)
            ".(in_array("3", $nombre_proceso)?"":"--")."+(case when cc.cant_paginas is not null then cc.control_calidades else 0 end)
            ".(in_array("4", $nombre_proceso)?"":"--")."+(case when fra.cant_paginas is not null then fra.fedatario_revisar_asistentes else 0 end)
            ".(in_array("5", $nombre_proceso)?"":"--")."+(case when frn.cant_paginas is not null then frn.fedatario_revisar_normal else 0 end)
            ".(in_array("6", $nombre_proceso)?"":"--")."+(case when rr.cant_paginas is not null then rr.cant_paginas else 0 end)
             as total_imagenes
            from capturas c
            full outer join indizaciones i
                on c.usuario_id=i.usuario_id
            full outer join control_calidades cc
                on c.usuario_id=cc.usuario_id
            full outer join fedatario_revisar_asistentes fra
                on c.usuario_id=fra.usuario_id
            full outer join fedatario_revisar_normal frn
                on c.usuario_id=frn.usuario_id
            full outer join reprocesos rr
                on c.usuario_id=rr.usuario_id;
             ")
             ,[
                 "usuarios" => $usuarios
                ,"clientes" => $clientes
                ,"recepciones" => $recepciones
                ,"proyectos" => $proyectos
                ,"fecha_inicio" => $fecha_inicio
                ,"fecha_fin" => $fecha_fin
             ]
        );
        $pie=[];
        foreach ($rows as $row) {
            $obj=array(
                "name"=>$row->usuario,
                "value"=>(int)$row->total_imagenes
            );
            $pie[]=$obj;
        }


        return [$rows,$pie];
    }

    public function reporte_x_usuario()
    {
        $usuarios = request("nombre_usuario");
        $fecha_inicio = request("fecha_inicio");
        $fecha_fin = request("fecha_fin");

        return $usuarios;

        $usuarios = self::to_array_sql( is_null($usuarios)?[]:$usuarios );

        $rows = DB::select(
            DB::raw(


            )
            ,[
                "usuarios" => $usuarios
                ,"fecha_inicio" => $fecha_inicio
                ,"fecha_fin" => $fecha_fin
            ]
        );
        $pie=[];
        foreach ($rows as $row) {
            $obj=array(
                "name"=>$row->usuario,
                "value"=>(int)$row->total_imagenes
            );
            $pie[]=$obj;
        }


        return [$rows,$pie];
    }


    public function to_array_sql($datos){
        $array_variable = "{";
        $count = count($datos);
        $contador = 0;
        foreach ($datos as $key) {
            $contador++;
            if ($count === $contador) {
                $array_variable .= $key;
            } else {
                $array_variable .= $key . ",";
            }
        }
        $array_variable .= "}";
        return $array_variable;
    }

    //Primer Grafico
    public function doughnut_chart()
    {
        /*
        $cantidad_asistentes =  DB::select("
        select
        count(distinct usuario_id)
        from asistencia
        WHERE
        asistencia_hora_inicio::DATE =  DATE 'today';
        ");

        $cantidad_total =DB::select('
        select
        count(usuario_id)
        from usuario_perfil
        where perfil_id= 5
       ');
        */
        $obj_asistentes = array('value' =>2,
            'name' => "Asistentes");

        $obj_faltaron = array('value' =>3,
        'name' => "Faltaron");

        $array_total[0] = $obj_asistentes;
        $array_total[1] = $obj_faltaron;

        return $array_total;

    }

    public function index_documento(){

        //Instancia Documento
        $ins_documento = new documento();
        //Instancia Incidencia
        $ins_incidencia = new incidenciaController();
        $ins_tipo_calibrador = new tipo_calibradorController();

        $lista_documentos = $ins_documento->listar_documento();
        $incidencia = $ins_incidencia->listar_incidencia();
        $tipo_calibrador = $ins_tipo_calibrador->listar_tipo_calibrador();

        return view::make('reporte_documento.index.content')
            ->with("lotes", $lista_documentos)
            ->with("incidencia", $incidencia)
            ->with("tipo_calibrador", $tipo_calibrador);

    }

    public function index_user(){

        $is_proyecto = new proyecto();
        $proyectos = $is_proyecto->select('proyecto_id', 'proyecto_nombre')->get();

        $equipo = equipo::join('persona as p','p.usuario_id','equipo.usuario_id')
            ->select('p.persona_nombre','p.persona_apellido','equipo.usuario_id')
            ->distinct()
            ->get();

        $is_recepcion = new recepcion();
        $recepcion = $is_recepcion->select('recepcion_id', 'recepcion_nombre')
            ->distinct()
            ->get();

        $is_cliente = new cliente();
        $cliente = $is_cliente->select('cliente_id', 'cliente_nombre')
            ->distinct()
            ->get();

        return view::make('reporte_usuario.index.content')
            ->with('proyectos', $proyectos)
            ->with('usuarios', $equipo)
            ->with('cliente', $cliente)
            ->with('recepcion', $recepcion);

    }

    public function resultado_modulo_reporte(){

        $captura_id = request('captura_id');
        $is_log = new log();
        $log = $is_log -> reporte_x_captura($captura_id);

        return $log;
    }

    public function index_formato_proceso(){

        $is_cliente = new cliente();
        $cliente = $is_cliente->select('cliente_id', 'cliente_nombre')
            ->distinct()
            ->get();

        return view::make('reporte_formato_proceso.index.content')
            ->with('cliente', $cliente)
            ;
    }

    public function resultado_reporte_formato_x_proceso(){

        $cliente_id = request('cliente_id');
        $fecha_inicio = request('fecha_inicio');
        $fecha_fin = request('fecha_fin');
        $modulo_id = request('modulo_id');

        $array_total = self::query_reporte_formato_proceso($cliente_id,$modulo_id,$fecha_inicio,$fecha_fin);

       /* $obj_1 = array('recepcion' =>'Recepcion 01',
            'captura' =>'1',
            'cant_doc' =>'8',
            'cant_ima' =>'76',
            'hora_ini' =>'21:15',
            'hora_ter' =>'21:15',
            'obs' =>'Observación 01');

        $obj_2 = array('recepcion' =>'Recepcion 02',
            'captura' =>'2',
            'cant_doc' =>'13',
            'cant_ima' =>'67',
            'hora_ini' =>'21:15',
            'hora_ter' =>'21:15',
            'obs' =>'Observación 02');

        $array_total[0] = $obj_1;
        $array_total[1] = $obj_2;*/

        return $array_total;

    }

    public function query_reporte_formato_proceso($cliente_id,$modulo_step_id,$fecha_inicio,$fecha_fin){

        return DB::select(
            DB::raw("
               with tablero_control as (
                    /*
                     * Contexto:                Tablero de control con los datos de backend
                     * Funcionalidad:           Recibe los parámetros del backend para generar una vista
                     */
                    select *
                    from (values (:cliente_id::INTEGER, :modulo_step_id::INTEGER))
                             AS t (tab_cliente_id, tab_modulo_step_id)
                ),
                     recepcion_cliente as (
                         /*
                          * Contexto:                Obtiene la lista de recepciones del cliente
                          * Funcionalidad:           Utiliza el tablero de control filtraremos las recepciones del cliente
                          */
                         select distinct recepcion_id, recepcion_nombre
                         from tablero_control tc
                                  left join recepcion rec
                                            on rec.cliente_id = tc.tab_cliente_id
                     ),
                     documento_recepcion_x_indizacion as (
                         /*
                          * Contexto:                Obtiene la lista de documentos por la recepciones del cliente
                          * Funcionalidad:           Filtra los documentos que corresponden a las recepciones del cliente
                          */
                         select i.recepcion_id, i.cliente_id, count(d.documento_id) as cant_documento
                         from tablero_control tc
                                  left join indizacion i
                                            on tc.tab_cliente_id = i.cliente_id
                                  left join documento d on i.captura_id = d.captura_id
                         where i.cliente_id is not null
                           and tc.tab_modulo_step_id = 2
                         group by i.recepcion_id, i.cliente_id
                     ),
                     imagen_documento_x_indizacion as (
                         /*
                          * Contexto:                Obtenemos la lista de imagenes por cada documento
                          * Funcionalidad:           Filtraremos las imágenes, de los documentos que corresponden a las recepciones del cliente
                          */
                         select i.recepcion_id, i.cliente_id, count(img.imagen_id) as cant_imagen
                         from tablero_control tc
                                  left join indizacion i on tc.tab_cliente_id = i.cliente_id
                                  left join documento d on i.captura_id = d.captura_id
                                  left join imagen img on d.documento_id = img.documento_id
                         where i.cliente_id = tc.tab_cliente_id
                           and tc.tab_modulo_step_id = 2
                         group by i.recepcion_id, i.cliente_id, d.recepcion_id
                     ),
                     hora_inicio_indizacion as (
                         /*
                          * Contexto:                A través de la tabla log, agrupamos por recepciones para determinar la hora de inicio de la indizacion
                          * Funcionalidad:           Filtraremos a través del campo log_proceso 'IND-INI' -> que determina el inicio de una indización
                          */
                         select c.recepcion_id,
                                recepcion_nombre,
                                min(log.created_at) as hora_inicio
                         from recepcion_cliente rec_cli
                                  left join captura c
                                            on c.recepcion_id = rec_cli.recepcion_id
                                  left join log
                                            on c.captura_id = log.log_captura_id

                         where log_proceso = 'IND-INI'
                         group by c.recepcion_id, recepcion_nombre
                         order by min(log.created_at)
                     ),
                     hora_fin_indizacion as (
                         /*
                          * Contexto:                A través de la tabla log, agrupamos por recepciones para determinar la hora de fin de la indizacion
                          * Funcionalidad:           Filtraremos a través del campo log_proceso 'IND-FIN' -> que determina el fin de una indización
                          */
                         select c.recepcion_id,
                                recepcion_nombre,
                                max(log.created_at) as hora_fin
                         from recepcion_cliente rec_cli
                                  left join captura c
                                            on c.recepcion_id = rec_cli.recepcion_id
                                  left join log
                                            on c.captura_id = log.log_captura_id

                         where log_proceso = 'IND-FIN'
                         group by c.recepcion_id, recepcion_nombre
                         order by max(log.created_at)
                     ),
                     hora_ini_fin_indizacion as (
                         /*
                          * Contexto:                Integra la hora de inicio con la hora de fin
                          * Funcionalidad:           Integra la hora de inicio, fin a través de la recepcion
                          */
                         select hfi.recepcion_id, hfi.recepcion_nombre, hora_inicio, hora_fin
                         from hora_inicio_indizacion hii
                                  left join hora_fin_indizacion hfi
                                            on hii.recepcion_id = hfi.recepcion_id
                     ),
                     indizacion_reporte as (
                         /*
                          * Contexto:                Integra los reportes de las vistas anteriores para formar el reporte final de indización
                          * Funcionalidad:           Filtra la unión de las vistas anteriores a través de tab_modulo_step_id = 2 <> indización
                          */
                         select recepcion_nombre, drxi.cliente_id, cant_documento, cant_imagen, hora_inicio, hora_fin
                         from documento_recepcion_x_indizacion drxi
                                  left join imagen_documento_x_indizacion idxi
                                            on drxi.recepcion_id = idxi.recepcion_id
                                  left join hora_ini_fin_indizacion hifi
                                            on hifi.recepcion_id = drxi.recepcion_id
                                  left join tablero_control tc
                                            on tc.tab_cliente_id = drxi.cliente_id
                     ),
                     documento_recepcion_x_control_calidad as (
                         /*
                          * Contexto:                Obtiene la lista de documentos por la recepciones del cliente
                          * Funcionalidad:           Filtra los documentos que corresponden a las recepciones del cliente
                          */
                         select cc.recepcion_id, cc.cliente_id, count(d.documento_id) as cant_documento
                         from tablero_control tc
                                  left join control_calidad cc
                                            on tc.tab_cliente_id = cc.cliente_id
                                  left join documento d on cc.captura_id = d.captura_id
                         where cc.cliente_id is not null
                           and tc.tab_modulo_step_id = 3
                         group by cc.recepcion_id, cc.cliente_id
                     ),
                     imagen_documento_x_control_calidad as (
                         /*
                          * Contexto:                Obtenemos la lista de imagenes por cada documento
                          * Funcionalidad:           Filtraremos las imágenes, de los documentos que corresponden a las recepciones del cliente
                          */
                         select cc.recepcion_id, cc.cliente_id, count(img.imagen_id) as cant_imagen
                         from tablero_control tc
                                  left join control_calidad cc on tc.tab_cliente_id = cc.cliente_id
                                  left join documento d on cc.captura_id = d.captura_id
                                  left join imagen img on d.documento_id = img.documento_id
                         where cc.cliente_id = tc.tab_cliente_id
                           and tc.tab_modulo_step_id = 3
                         group by cc.recepcion_id, cc.cliente_id, d.recepcion_id
                     ),
                     hora_inicio_control_calidad as (
                         /*
                          * Contexto:                A través de la tabla log, agrupamos por recepciones para determinar la hora de inicio de la control de calidad
                          * Funcionalidad:           Filtraremos a través del campo log_proceso 'CAL-INI' -> que determina el inicio de una control de calidad
                          */
                         select c.recepcion_id,
                                recepcion_nombre,
                                min(log.created_at) as hora_inicio
                         from recepcion_cliente rec_cli
                                  left join captura c
                                            on c.recepcion_id = rec_cli.recepcion_id
                                  left join log
                                            on c.captura_id = log.log_captura_id
                         where log_proceso = 'CAL-INI'
                         group by c.recepcion_id, recepcion_nombre
                         order by min(log.created_at)
                     ),
                     hora_fin_control_calidad as (
                         /*
                          * Contexto:                A través de la tabla log, agrupamos por recepciones para determinar la hora de find del control de calidad
                          * Funcionalidad:           Filtraremos a través del campo log_proceso 'CAL-FIN' -> que determina el fin de un control de calidad
                          */
                         select c.recepcion_id,
                                recepcion_nombre,
                                max(log.created_at) as hora_fin
                         from recepcion_cliente rec_cli
                                  left join captura c
                                            on c.recepcion_id = rec_cli.recepcion_id
                                  left join log
                                            on c.captura_id = log.log_captura_id
                         where log_proceso = 'CAL-FIN'
                         group by c.recepcion_id, recepcion_nombre
                         order by max(log.created_at)
                     ),
                     hora_ini_fin_control_calidad as (
                         /*
                          * Contexto:                Integra la hora de inicio con la hora de fin
                          * Funcionalidad:           Integra la hora de inicio, fin a través de la recepcion
                          */
                         select hicc.recepcion_id, hicc.recepcion_nombre, hora_inicio, hora_fin
                         from hora_inicio_control_calidad hicc
                                  left join hora_fin_control_calidad hfcc
                                            on hicc.recepcion_id = hfcc.recepcion_id
                     ),
                     control_calidad_reporte as (
                         /*
                          * Contexto:                Integra los reportes de las vistas anteriores para formar el reporte final de control de calidad
                          * Funcionalidad:           Filtra la unión de las vistas anteriores a través de tab_modulo_step_id = 3 <> control de calidad
                          */
                         select recepcion_nombre, drxcc.cliente_id, cant_documento, cant_imagen, hora_inicio, hora_fin
                         from documento_recepcion_x_control_calidad drxcc
                                  left join imagen_documento_x_control_calidad idxcc
                                            on drxcc.recepcion_id = idxcc.recepcion_id
                                  left join hora_ini_fin_control_calidad hifcc
                                            on hifcc.recepcion_id = drxcc.recepcion_id
                                  left join tablero_control tc
                                            on tc.tab_cliente_id = drxcc.cliente_id
                     ),
                     documento_recepcion_x_fedatario as (
                         /*
                          * Contexto:                Obtiene la lista de documentos por la recepciones del cliente
                          * Funcionalidad:           Filtra los documentos que corresponden a las recepciones del cliente
                          */
                         select f.recepcion_id, f.cliente_id, count(d.documento_id) as cant_documento
                         from tablero_control tc
                                  left join fedatario f
                                            on tc.tab_cliente_id = f.cliente_id
                                  left join documento d on f.captura_id = d.captura_id
                         where f.cliente_id is not null
                           and tc.tab_modulo_step_id = 4
                         group by f.recepcion_id, f.cliente_id
                     ),
                     imagen_documento_x_fedatario as (
                         /*
                          * Contexto:                Obtenemos la lista de imagenes por cada documento
                          * Funcionalidad:           Filtraremos las imágenes, de los documentos que corresponden a las recepciones del cliente
                          */
                         select f.recepcion_id, f.cliente_id, count(img.imagen_id) as cant_imagen
                         from tablero_control tc
                                  left join fedatario f on tc.tab_cliente_id = f.cliente_id
                                  left join documento d on f.captura_id = d.captura_id
                                  left join imagen img on d.documento_id = img.documento_id
                         where f.cliente_id = tc.tab_cliente_id
                           and tc.tab_modulo_step_id = 4
                         group by f.recepcion_id, f.cliente_id, d.recepcion_id
                     ),
                     hora_inicio_fedatario as (
                         /*
                          * Contexto:                A través de la tabla log, agrupamos por recepciones para determinar la hora de inicio del fedatario normal
                          * Funcionalidad:           Filtraremos a través del campo log_proceso 'FED_NOR-INI' -> que determina el inicio de un fedatario normal
                          */
                         select c.recepcion_id,
                                recepcion_nombre,
                                min(log.created_at) as hora_inicio
                         from recepcion_cliente rec_cli
                                  left join captura c
                                            on c.recepcion_id = rec_cli.recepcion_id
                                  left join log
                                            on c.captura_id = log.log_captura_id
                         where log_proceso = 'FED_NOR-INI'
                         group by c.recepcion_id, recepcion_nombre
                         order by min(log.created_at)
                     ),
                     hora_fin_fedatario as (
                         /*
                          * Contexto:                A través de la tabla log, agrupamos por recepciones para determinar la hora de find del control de calidad
                          * Funcionalidad:           Filtraremos a través del campo log_proceso 'FED-NOR-FIN' -> que determina el fin de un control de calidad
                          */
                         select c.recepcion_id,
                                recepcion_nombre,
                                max(log.created_at) as hora_fin
                         from recepcion_cliente rec_cli
                                  left join captura c
                                            on c.recepcion_id = rec_cli.recepcion_id
                                  left join log
                                            on c.captura_id = log.log_captura_id
                         where log_proceso = 'FED-NOR-FIN'
                         group by c.recepcion_id, recepcion_nombre
                         order by max(log.created_at)
                     ),
                     hora_ini_fin_fedatario as (
                         /*
                          * Contexto:                Integra la hora de inicio con la hora de fin
                          * Funcionalidad:           Integra la hora de inicio, fin a través de la recepcion
                          */
                         select hif.recepcion_id, hif.recepcion_nombre, hora_inicio, hora_fin
                         from hora_inicio_fedatario hif
                                  left join hora_fin_fedatario hff
                                            on hif.recepcion_id = hff.recepcion_id
                     ),
                     fedatario_reporte as (
                         /*
                          * Contexto:                Integra los reportes de las vistas anteriores para formar el reporte final de fedatario
                          * Funcionalidad:           Filtra la unión de las vistas anteriores a través de tab_modulo_step_id = 4 <> fedatario
                          */
                         select recepcion_nombre, drxf.cliente_id, cant_documento, cant_imagen, hora_inicio, hora_fin
                         from documento_recepcion_x_fedatario drxf
                                  left join imagen_documento_x_fedatario idxf
                                            on drxf.recepcion_id = idxf.recepcion_id
                                  left join hora_ini_fin_fedatario hiff
                                            on hiff.recepcion_id = drxf.recepcion_id
                                  left join tablero_control tc
                                            on tc.tab_cliente_id = drxf.cliente_id
                     ),
                     documento_recepcion_x_reproceso as (
                         /*
                          * Contexto:                Obtiene la lista de documentos por la recepciones del cliente
                          * Funcionalidad:           Filtra los documentos que corresponden a las recepciones del cliente
                          */
                         select c.recepcion_id, c.cliente_id, count(d.documento_id) as cant_documento
                         from tablero_control tc
                                  left join captura c
                                            on tc.tab_cliente_id = c.cliente_id
                                  left join documento d on c.captura_id = d.captura_id
                                  left join log l on l.log_captura_id = c.captura_id
                         where c.cliente_id is not null
                           and l.log_proceso = 'REP-INI'
                           and tc.tab_modulo_step_id = 5
                         group by c.recepcion_id, c.cliente_id
                     ),
                     imagen_documento_x_reproceso as (
                         /*
                         * Contexto:                Obtenemos la lista de imagenes por cada documento
                         * Funcionalidad:           Filtraremos las imágenes, de los documentos que corresponden a las recepciones del cliente
                         */
                         select c.recepcion_id, c.cliente_id, count(img.imagen_id) as cant_imagen
                         from tablero_control tc
                                  left join captura c on tc.tab_cliente_id = c.cliente_id
                                  left join documento d on c.captura_id = d.captura_id
                                  left join log l on l.log_captura_id = c.captura_id
                                  left join imagen img on d.documento_id = img.documento_id
                         where c.cliente_id = tc.tab_cliente_id
                           and l.log_proceso = 'REP-INI'
                           and tc.tab_modulo_step_id = 5
                         group by c.recepcion_id, c.cliente_id, d.recepcion_id
                     ),
                     hora_inicio_reproceso as (
                         /*
                          * Contexto:                A través de la tabla log, agrupamos por recepciones para determinar la hora de inicio del reproceso
                          * Funcionalidad:           Filtraremos a través del campo log_proceso 'REP-INI' -> que determina el inicio de reproceso
                          */
                         select c.recepcion_id,
                                recepcion_nombre,
                                min(log.created_at) as hora_inicio
                         from recepcion_cliente rec_cli
                                  left join captura c
                                            on c.recepcion_id = rec_cli.recepcion_id
                                  left join log
                                            on c.captura_id = log.log_captura_id
                         where log_proceso = 'REP-INI'
                         group by c.recepcion_id, recepcion_nombre
                         order by min(log.created_at)
                     ),
                     hora_fin_reproceso as (
                         /*
                          * Contexto:                A través de la tabla log, agrupamos por recepciones para determinar la hora de find del control de calidad
                          * Funcionalidad:           Filtraremos a través del campo log_proceso 'REP-FIN' -> que determina el fin de un control de calidad
                          */
                         select c.recepcion_id,
                                recepcion_nombre,
                                max(log.created_at) as hora_fin
                         from recepcion_cliente rec_cli
                                  left join captura c
                                            on c.recepcion_id = rec_cli.recepcion_id
                                  left join log
                                            on c.captura_id = log.log_captura_id
                         where log_proceso = 'REP-FIN'
                         group by c.recepcion_id, recepcion_nombre
                         order by max(log.created_at)
                     ),
                     hora_ini_fin_reproceso as (
                         /*
                          * Contexto:                Integra la hora de inicio con la hora de fin
                          * Funcionalidad:           Integra la hora de inicio, fin a través de la recepcion
                          */
                         select hir.recepcion_id, hir.recepcion_nombre, hora_inicio, hora_fin
                         from hora_inicio_reproceso hir
                                  left join hora_fin_reproceso hfr
                                            on hir.recepcion_id = hfr.recepcion_id
                     ),
                     reproceso_reporte as (
                         /*
                          * Contexto:                Integra los reportes de las vistas anteriores para formar el reporte final de reproceso
                          * Funcionalidad:           Filtra la unión de las vistas anteriores a través de tab_modulo_step_id = 5 <> reproceso
                          */
                         select recepcion_nombre, drxr.cliente_id, cant_documento, cant_imagen, hora_inicio, hora_fin
                         from documento_recepcion_x_reproceso drxr
                                  left join imagen_documento_x_reproceso idxr
                                            on drxr.recepcion_id = idxr.recepcion_id
                                  left join hora_ini_fin_reproceso hifr
                                            on hifr.recepcion_id = drxr.recepcion_id
                                  left join tablero_control tc
                                            on tc.tab_cliente_id = drxr.cliente_id
                     ),
                     documento_recepcion_x_generacion as (
                         /*
                          * Contexto:                Obtiene la lista de documentos por la recepciones del cliente
                          * Funcionalidad:           Filtra los documentos que corresponden a las recepciones del cliente
                          */
                         select c.recepcion_id, c.cliente_id, count(d.documento_id) as cant_documento
                         from tablero_control tc
                                  left join captura c
                                            on tc.tab_cliente_id = c.cliente_id
                                  left join documento d on c.captura_id = d.captura_id
                                  left join log l on l.log_captura_id = c.captura_id
                         where c.cliente_id is not null
                           and l.log_proceso = 'GM-ORG'
                           and tc.tab_modulo_step_id = 6
                         group by c.recepcion_id, c.cliente_id
                     ),
                     imagen_documento_x_generacion as (
                         /*
                         * Contexto:                Obtenemos la lista de imagenes por cada documento
                         * Funcionalidad:           Filtraremos las imágenes, de los documentos que corresponden a las recepciones del cliente
                         */
                         select c.recepcion_id, c.cliente_id, count(img.imagen_id) as cant_imagen
                         from tablero_control tc
                                  left join captura c on tc.tab_cliente_id = c.cliente_id
                                  left join documento d on c.captura_id = d.captura_id
                                  left join log l on l.log_captura_id = c.captura_id
                                  left join imagen img on d.documento_id = img.documento_id
                         where c.cliente_id = tc.tab_cliente_id
                           and l.log_proceso = 'GM-ORG'
                           and tc.tab_modulo_step_id = 6
                         group by c.recepcion_id, c.cliente_id, d.recepcion_id
                     ),
                     hora_inicio_generacion as (
                         /*
                          * Contexto:                A través de la tabla log, agrupamos por recepciones para determinar la hora de inicio del generacion
                          * Funcionalidad:           Filtraremos a través del campo log_proceso 'GM-ORG' -> que determina el inicio de generacion
                          */
                         select c.recepcion_id,
                                recepcion_nombre,
                                min(log.created_at) as hora_inicio
                         from recepcion_cliente rec_cli
                                  left join captura c
                                            on c.recepcion_id = rec_cli.recepcion_id
                                  left join log
                                            on c.captura_id = log.log_captura_id
                         where log_proceso = 'GM-ORG'
                         group by c.recepcion_id, recepcion_nombre
                         order by min(log.created_at)
                     ),
                     hora_fin_generacion as (
                         /*
                          * Contexto:                A través de la tabla log, agrupamos por recepciones para determinar la hora de fin del generacion
                          * Funcionalidad:           Filtraremos a través del campo log_proceso 'GM-FIN' -> que determina el fin de un generacion
                          */
                         select c.recepcion_id,
                                recepcion_nombre,
                                max(log.created_at) as hora_fin
                         from recepcion_cliente rec_cli
                                  left join captura c
                                            on c.recepcion_id = rec_cli.recepcion_id
                                  left join log
                                            on c.captura_id = log.log_captura_id
                         where log_proceso = 'GM-FIN'
                         group by c.recepcion_id, recepcion_nombre
                         order by max(log.created_at)
                     ),
                     hora_ini_fin_generacion as (
                         /*
                          * Contexto:                Integra la hora de inicio con la hora de fin
                          * Funcionalidad:           Integra la hora de inicio, fin a través de la recepcion
                          */
                         select hig.recepcion_id, hig.recepcion_nombre, hora_inicio, hora_fin
                         from hora_inicio_generacion hig
                                  left join hora_fin_generacion hfg
                                            on hig.recepcion_id = hfg.recepcion_id
                     ),
                     generacion_reporte as (
                         /*
                          * Contexto:                Integra los reportes de las vistas anteriores para formar el reporte final de generacion
                          * Funcionalidad:           Filtra la unión de las vistas anteriores
                          */
                         select recepcion_nombre, drxg.cliente_id, cant_documento, cant_imagen, hora_inicio, hora_fin
                         from documento_recepcion_x_generacion drxg
                                  left join imagen_documento_x_generacion idxg
                                            on drxg.recepcion_id = idxg.recepcion_id
                                  left join hora_ini_fin_generacion hifg
                                            on hifg.recepcion_id = drxg.recepcion_id
                                  left join tablero_control tc
                                            on tc.tab_cliente_id = drxg.cliente_id
                     ),
                     reporte_global as (
                         select *
                         from indizacion_reporte
                         union all
                         select *
                         from control_calidad_reporte
                         union all
                         select *
                         from fedatario_reporte
                         union all
                         select *
                         from reproceso_reporte
                         union all
                         select *
                         from generacion_reporte
                     )
                select *
                from reporte_global
                where hora_inicio >= to_date(:fecha_inicio,'DD/MM/YYYY') and hora_fin <= to_date(:fecha_fin,'DD/MM/YYYY')
                ;  "),[ "cliente_id" => $cliente_id,"modulo_step_id" => $modulo_step_id,"fecha_inicio"=>$fecha_inicio, "fecha_fin"=>$fecha_fin]
        );
    }

    public function select2_cliente_proyecto(){

        $cliente_id = request('cliente_id');
        $arr_cliente_id = "{".implode(",",$cliente_id)."}";

        $is_recepcion = new cliente();
        $proyecto_x_cliente = $is_recepcion -> cliente_x_recepcion($arr_cliente_id);

        if (count($proyecto_x_cliente) > 0) {

            return $this->crear_objeto("ok", $proyecto_x_cliente);

        } else {

            return $this->crear_objeto("error", "No se encontraron recepciones para este proyecto");

        }
    }

    public function select2_proyecto_recepcion(){

        $proyecto_id = request('proyecto_id');
        $arr_proyecto_id = "{".implode(",",$proyecto_id)."}";

        $is_recepcion = new recepcion();
        $recepcion_x_proyecto = $is_recepcion -> recepcion_x_proyecto($arr_proyecto_id);

        if (count($recepcion_x_proyecto) > 0) {

            return $this->crear_objeto("ok", $recepcion_x_proyecto);

        } else {

            return $this->crear_objeto("error", "No se encontraron recepciones para este proyecto");

        }
    }

    public function select2_proyecto_usuario(){

        $proyecto_id = request('proyecto_id');
        $arr_proyecto_id = "{".implode(",",$proyecto_id)."}";

        $is_proyecto = new proyecto();
        $usuario_x_proyecto = $is_proyecto -> usuario_x_proyecto($arr_proyecto_id);

        if (count($usuario_x_proyecto) > 0) {

            return $this->crear_objeto("ok", $usuario_x_proyecto);

        } else {

            return $this->crear_objeto("error", "No se encontraron usuarios para este proyecto");

        }
    }

}
