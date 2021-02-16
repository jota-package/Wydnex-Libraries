<?php

namespace Fedatario\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App;
use Request;

Trait generacion_medio
{
    protected $primaryKey = "generacion_medio_id";
    protected $table = "generacion_medio";

    public function organizar($prefijo,$correlativo,$peso_maximo,$recepciones,$me_id,$espacio_libre,$gm_id)
    {
        $usuario_creador= session("usuario_id");
        $ip = Request::ip();

        $array_variable = "{";
        if(is_null($recepciones)) {

            $array_variable= "{}";

        }else{
            $count = sizeof($recepciones);
            $contador = 0;
            foreach ($recepciones as $key) {
                $contador++;
                if ($count === $contador) {
                    $array_variable .= $key;
                } else {
                    $array_variable .= $key . ",";
                }
            }
            $array_variable .= "}";
        }
        return DB::select(
            DB::raw("
            with recursive
                datos_input as (
                    select
                    :prefijo::varchar(20) as prefijo
                    ,:correlativo::INT -1 as correlativo
                    --cambiado a numeric para hacer calculos con megabytes
                    ,:peso_maximo::numeric(20,6) as peso_maximo_input
                    --agregado 350 por peso del visor
                    ,:peso_maximo::numeric(20,6)-:espacio_libre::numeric(20,6)-350 as peso_maximo
                    ,:espacio_libre::numeric(20,6) as espacio_libre
                    ,:recepciones::INT[] as recepciones
                    ,:me_id::INT as me_id
                    ,:gm_id::INT as gm_id
                ),
                recepciones_input as (
                    select distinct
                    case when a.gm_id != 0
                            then array_agg(b.recepcion_id) over(partition by b.gm_id order by b.gmr_id)
                            else a.recepciones end
                    as recepciones
                    ,a.gm_id
                    from datos_input a
                    left join generacion_medio_recepcion b on a.gm_id = b.gm_id
                ),
                datos_iniciales_CA as (
                    select
                    distinct
                    a.recepcion_id
                    --,a.documento_nombre
                    --,b.adetalle_nombre
                    --,c.captura_id
                    --cambiado a numeric para hacer calculos con megabytes
                    --,case when b.adetalle_peso is null then 0 else round(b.adetalle_peso/1000000::numeric(20,6), 6) end as peso
                    ,sum(
                        case when b.adetalle_peso is null then 0 else round(b.adetalle_peso/1000000::numeric(20,6), 6) end
                    ) over(partition by c.created_at::date) --cambiado a created_at
                    as suma
                    ,c.created_at::date as fecha --cambiado a created_at
                    --,row_number() over(order by c.recepcion_id,c.captura_orden) as numero_orden
                    --,*
                    from captura c
                    cross join datos_input
                    cross join recepciones_input ri
                    left join documento a on a.captura_id = c.captura_id
                    left join adetalle b on a.adetalle_id = b.adetalle_id
                    where a.recepcion_id = ANY(ri.recepciones)--4
                    and c.captura_estado in (2,3)
                    --order by c.recepcion_id,c.captura_orden
                ),
                datos_iniciales_pre as (
                    select
                    distinct
                    c.recepcion_id
                    ,c.captura_orden
                    ,a.documento_nombre
                    ,b.adetalle_nombre
                    ,c.captura_id
                    --cambiado a numeric para hacer calculos con megabytes
                    ,case
                    when ad_ff.adetalle_peso is null and pf.proyecto_flujo_id is not null
                        then 0
                    when pf.proyecto_flujo_id is null
                        --b.adetalle_peso
                        then round(b.adetalle_peso/1000000::numeric(20,6), 6)
                    else
                        round(sum(ad_ff.adetalle_peso) over(partition by c.captura_id)/1000000::numeric(20,6), 6)
                    end as peso
                    ,row_number() over(order by c.recepcion_id,c.captura_orden) as numero_orden
                    --,*
                    ,d.suma as peso_ca
                    ,d.fecha
                    ,e.cant_paginas
                    from captura c
                    cross join datos_input di
                    cross join recepciones_input ri
                    left join proyecto_flujo pf on pf.proyecto_id = c.proyecto_id and pf.modulo_step_id = 5
                    --quitamos las capturas que ya esten procesadas si hubiesen
                    left join generacion_medio_detalle_captura gmdc on gmdc.gm_id = di.gm_id and c.captura_id = gmdc.captura_id
                    left join documento a on a.captura_id = c.captura_id
                    left join adetalle b on a.adetalle_id = b.adetalle_id
                    left join (select distinct max(imagen_pagina::int)over(partition by documento_id) as cant_paginas,documento_id from imagen) e
                        on e.documento_id = a.documento_id
                    --para sacar los pesos finales firmados
                    left join fedatario_firmar f on f.captura_id = c.captura_id  and pf.proyecto_flujo_id is not null
                    left join adetalle ad_ff on f.fedatario_documento_id=ad_ff.archivo_id  and pf.proyecto_flujo_id is not null
                    --inicio para CA
                    left join datos_iniciales_CA d on d.recepcion_id = c.recepcion_id and d.fecha=c.created_at::date --cambiado a created_at
                    where a.recepcion_id = ANY(ri.recepciones)--4
                    --añadido para quitar las capturas si existiecen
                    and gmdc.gmdc_id is null
                    and c.captura_estado = 1
                    --order by c.recepcion_id,c.captura_orden
                ),
                datos_iniciales as(
                    select *
                    from datos_iniciales_pre
                    order by recepcion_id,captura_orden
                ),
                datos_recursivos as (
                    select
                        recepcion_id
                        ,documento_nombre
                        ,adetalle_nombre
                        ,captura_id
                        ,peso
                            + peso_ca
                            as peso
                        ,peso
                            + peso_ca
                            as suma
                        ,numero_orden
                        ,1 as grupo
                        ,1 as contador_documentos
                        --añadiendo fecha
                        ,fecha as fecha
                        ,cant_paginas as cant_paginas_total
                    from datos_iniciales
                    cross join datos_input i
                    where numero_orden = 1

                    union all

                    select
                        a.recepcion_id
                        ,a.documento_nombre
                        ,a.adetalle_nombre
                        ,a.captura_id
                        ,a.peso
                        --,a.peso as suma
                        /*,case
                            when (b.suma+a.peso)>i.peso_maximo then
                                a.peso
                            else
                                (b.suma+a.peso)
                        end as suma */
                        ,case
                            when a.fecha= b.fecha and not (b.suma+a.peso)>i.peso_maximo then
                                (b.suma+a.peso)
                            when a.fecha<> b.fecha and not (b.suma+a.peso+a.peso_ca)>i.peso_maximo then
                                (b.suma+a.peso+a.peso_ca)
                            else
                                (a.peso+a.peso_ca)
                        end as suma
                        ,a.numero_orden
                        /*,case
                            when (b.suma+a.peso)>i.peso_maximo then
                                b.grupo+1
                            else
                                b.grupo
                        end as grupo
                        */
                        ,case
                            when a.fecha= b.fecha and not (b.suma+a.peso)>i.peso_maximo then
                                b.grupo
                            when a.fecha<> b.fecha and not (b.suma+a.peso+a.peso_ca)>i.peso_maximo then
                                b.grupo
                            else
                                b.grupo+1
                        end as grupo
                        /*,case
                            when (b.suma+a.peso)>i.peso_maximo then
                                1
                            else
                                b.contador_documentos +1
                        end as contador_documentos
                        */
                        ,case
                            when a.fecha= b.fecha and not (b.suma+a.peso)>i.peso_maximo then
                                b.contador_documentos +1
                            when a.fecha<> b.fecha and not (b.suma+a.peso+a.peso_ca)>i.peso_maximo then
                                b.contador_documentos +1
                            else
                                1
                        end as contador_documentos
                        ,a.fecha
                        --cant_paginas
                        ,case
                            when a.fecha= b.fecha and not (b.suma+a.peso)>i.peso_maximo then
                                b.cant_paginas_total +a.cant_paginas
                            when a.fecha<> b.fecha and not (b.suma+a.peso+a.peso_ca)>i.peso_maximo then
                                b.cant_paginas_total +a.cant_paginas
                            else
                                a.cant_paginas
                        end as cant_paginas_total
                    from datos_recursivos b
                    cross join datos_input i
                    join datos_iniciales a on a.numero_orden = b.numero_orden+1
                )
                --select * from datos_recursivos;
                , datos_recursivos_con_CA as (
                    select * from datos_recursivos
                    union all
                    select distinct
                    a.recepcion_id
                    ,d.documento_nombre
                    ,e.adetalle_nombre
                    ,c.captura_id
                    ,e.adetalle_peso
                    ,0 as suma
                    ,0 as numero_orden
                    ,a.grupo
                    ,0 as contador_documentos
                    ,a.fecha
                    ,0 as cant_paginas_total
                    from datos_recursivos a
                    join captura c
                        on c.created_at::date = a.fecha and --cambiado a created_at
                            c.recepcion_id = a.recepcion_id and
                            c.captura_estado in (2,3)
                    left join documento d on d.captura_id = c.captura_id
                    left join adetalle e on d.adetalle_id = e.adetalle_id
                )
                --select * from datos_recursivos;
                , datos_finales as (
                    select
                    --distinct
                    row_number() over(partition by a.grupo order by a.numero_orden desc) as maximo
                    ,*
                    --,a.peso
                    from datos_recursivos_con_CA a
                ),

                insert_generacion_medio as (
                    insert into generacion_medio(
                        usuario_id
                        ,gm_estado
                        ,gm_prefijo
                        ,gm_correlativo
                        ,me_id
                        ,gm_peso_otros
                        ,created_at
                        ,updated_at
                    )
                    select
                        1--usuario_id
                        ,1 --estado
                        ,prefijo
                        ,correlativo+1
                        ,me_id --me_id medio de exportacion
                        ,peso_maximo_input
                        ,now()
                        ,now()
                    from datos_input
                    where gm_id = 0
                    returning gm_id
                ),
                insert_generacion_medio_detalle as (
                    insert into generacion_medio_detalle(
                        gm_id
                        ,gmd_nombre
                        ,gmd_estado
                        ,gmd_peso_maximo
                        ,gmd_peso_ocupado
                        ,gmd_total_documento
                        ,gmd_grupo
                        ,gmd_espacio_libre
                        ,gmd_cant_pagina_total
                        ,gmd_partes_total
                        ,gmd_prefijo
		                ,gmd_correlativo
                        ,created_at
                        ,updated_at
                    )
                    select
                        --a.gm_id --gm_id
                        case when a.gm_id is null then c.gm_id else a.gm_id end
                        ,c.prefijo||(b.grupo+c.correlativo)::varchar --nombre
                        ,1
                        ,c.peso_maximo_input
                        ,b.suma
                        ,b.contador_documentos
                        ,b.grupo
                        ,c.espacio_libre
                        ,b.cant_paginas_total
                        ,4-- en duracell por mientras
                        ,c.prefijo
		                ,(b.grupo+c.correlativo)
                        ,now()
                        ,now()
                    from datos_finales b
                    left join insert_generacion_medio a on a.gm_id is not null
                    cross join datos_input c
                    where b.maximo=1
                    returning gmd_id,gm_id,gmd_grupo
                ),
                insert_generacion_medio_detalle_captura as (
                    insert into generacion_medio_detalle_captura(
                        gmd_id
                        ,gm_id
                        ,gmd_grupo
                        ,captura_id
                        ,created_at
                        ,updated_at
                    )
                    select
                        b.gmd_id
                        ,b.gm_id
                        ,b.gmd_grupo
                        ,a.captura_id
                        ,now()
                        ,now()
                    from datos_recursivos_con_CA a
                    left join insert_generacion_medio_detalle b
                        on a.grupo=b.gmd_grupo
                    returning gmdc_id,captura_id
                ),
                insert_generacion_medio_recepcion as (
                    insert into generacion_medio_recepcion(
                        gm_id
                        ,recepcion_id
                        ,created_at
                        ,updated_at
                    )
                    select
                    b.gm_id
                    ,unnest(ri.recepciones)
                    ,now()
                    ,now()
                    from datos_input a
                    cross join insert_generacion_medio b
                    cross join recepciones_input ri
                    returning gmr_id
                ),
                data_completa as (
                    select
                        a.gmd_nombre as  gmd_nombre
                        ,to_char(a.gmd_peso_maximo,'FM999,999,999,999,990D00')||' Mb' as gmd_peso_maximo
                        ,to_char(a.gmd_peso_ocupado,'FM999,999,999,999,990D00')||' Mb' as gmd_peso_ocupado
                        ,to_char(a.gmd_peso_ocupado*100/a.gmd_peso_maximo,'FM990D00')||'%'  as gmd_porcentaje_ocupado
                        --,(a.suma) as gmd_peso_ocupado
                        ,a.gmd_total_documento as gmd_total_documento
                        ,a.gmd_id as gmd_id--por mientras
                        ,d.me_descripcion as gmd_descripcion
                        ,a.gmd_cant_pagina_total
                        ,a.gm_id
                        ,a.gmd_prefijo
                        ,a.gmd_correlativo::INT
                        from datos_input di
                        join generacion_medio_detalle a on a.gm_id = di.gm_id
                        join generacion_medio b on a.gm_id = b.gm_id
                        join medio_exportacion d on b.me_id = d.me_id

                    union all

                    select
                        b.prefijo||(a.grupo+b.correlativo)::varchar as  gmd_nombre
                        ,to_char(b.peso_maximo_input,'FM999,999,999,999,990D00')||' Mb' as gmd_peso_maximo
                        ,to_char(a.suma,'FM999,999,999,999,990D00')||' Mb' as gmd_peso_ocupado
                        ,to_char(a.suma*100/b.peso_maximo_input,'FM990D00')||'%'  as gmd_porcentaje_ocupado
                        --,(a.suma) as gmd_peso_ocupado
                        ,a.contador_documentos as gmd_total_documento
                        ,c.gmd_id as gmd_id--por mientras
                        ,d.me_descripcion as gmd_descripcion
                        ,a.cant_paginas_total as gmd_cant_pagina_total
                        ,c.gm_id
                        ,b.prefijo as gmd_prefijo
                        ,(a.grupo+b.correlativo)::INT as gmd_correlativo
                    from datos_finales a
                    cross join datos_input b
                    left join insert_generacion_medio_detalle c
                        on c.gmd_grupo = a.grupo
                    left join medio_exportacion d on b.me_id = d.me_id
                    where maximo=1
                    --order by a.grupo
                ),
                --actualizamos el estado global de las capturas
                update_captura_estado_glb as(

                    update captura
                    set captura_estado_glb ='gen_org'
                    from datos_recursivos_con_CA a
                    where a.captura_id = captura.captura_id
                    returning *

                ),
                --grabamos el log usando insert_generacion_medio_detalle_captura
                --para poder tener los gmdc_id generados
                insert_log as(
                    insert into log(log_fecha_hora,log_usuario,log_estado,log_ip,
                                    log_captura_id,log_id_asociado,log_modulo_step_id,
                                    log_tabla_asociada,log_proceso,log_archivo_id,
                                    log_descripcion,log_comentario,created_at,updated_at)
                    select
                        now()
                        ,:usuario_creador
                        ,1
                        ,:ip
                        ,a.captura_id
                        ,a.gmdc_id
                        ,6
                        ,'generacion_medio_detalle_captura'
                        ,'GM-ORG'
                        ,null
                        ,'Organización de Generación de Medios'
                        ,''
                        ,now()
                        ,now()
                    from insert_generacion_medio_detalle_captura a
                )
                select * from data_completa
                order by gmd_correlativo
                ;
                "),
            ["prefijo" => $prefijo, "correlativo" => $correlativo, "peso_maximo" => $peso_maximo,"recepciones" => $array_variable
                ,"me_id" => $me_id,"espacio_libre" => $espacio_libre,"gm_id" => $gm_id,"usuario_creador"=>$usuario_creador,"ip"=>$ip]);

    }
    /**
     * validacion de tamaños
     */
    public function validacion($prefijo,$correlativo,$peso_maximo,$recepciones,$me_id,$espacio_libre,$gm_id)
    {

        $array_variable = "{";
        if(is_null($recepciones)) {

            $array_variable= "{}";

        }else{
            $count = sizeof($recepciones);
            $contador = 0;
            foreach ($recepciones as $key) {
                $contador++;
                if ($count === $contador) {
                    $array_variable .= $key;
                } else {
                    $array_variable .= $key . ",";
                }
            }
            $array_variable .= "}";
        }
        return DB::select(
            DB::raw("
            with recursive
            datos_input as (
                select
                :prefijo::varchar(20) as prefijo
                ,:correlativo::INT -1 as correlativo
                --cambiado a numeric para hacer calculos con megabytes
                ,:peso_maximo::numeric(20,6) as peso_maximo_input
                --agregado 350 por peso del visor
                ,:peso_maximo::numeric(20,6)-:espacio_libre::numeric(20,6)-350 as peso_maximo
                ,:espacio_libre::numeric(20,6) as espacio_libre
                ,:recepciones::INT[] as recepciones
                ,:me_id::INT as me_id
                ,:gm_id::INT as gm_id
            ),
            recepciones_input as (
                select distinct
                case when a.gm_id != 0
                        then array_agg(b.recepcion_id) over(partition by b.gm_id order by b.gmr_id)
                        else a.recepciones end
                as recepciones
                ,a.gm_id
                from datos_input a
                left join generacion_medio_recepcion b on a.gm_id = b.gm_id
            ),
            datos_iniciales_CA as (
                select
                distinct
                a.recepcion_id
                --,a.documento_nombre
                --,b.adetalle_nombre
                --,c.captura_id
                --cambiado a numeric para hacer calculos con megabytes
                --,case when b.adetalle_peso is null then 0 else round(b.adetalle_peso/1000000::numeric(20,6), 6) end as peso
                ,sum(
                    case when b.adetalle_peso is null then 0 else round(b.adetalle_peso/1000000::numeric(20,6), 6) end
                ) over(partition by c.created_at::date)
                as suma
                ,c.created_at::date as fecha
                --,row_number() over(order by c.recepcion_id,c.captura_orden) as numero_orden
                --,*
                from captura c
                cross join datos_input
                cross join recepciones_input ri
                left join documento a on a.captura_id = c.captura_id
                left join adetalle b on a.adetalle_id = b.adetalle_id
                where a.recepcion_id = ANY(ri.recepciones)--4
                and c.captura_estado in (2,3)
                --order by c.recepcion_id,c.captura_orden
            ),
            datos_iniciales_pre as (
                select
                distinct
                c.recepcion_id
                ,c.captura_orden
                ,a.documento_nombre
                ,b.adetalle_nombre
                ,c.captura_id
                --cambiado a numeric para hacer calculos con megabytes
                ,case
                when ad_ff.adetalle_peso is null and pf.proyecto_flujo_id is not null
                    then 0
                when pf.proyecto_flujo_id is null
                    --b.adetalle_peso
                    then round(b.adetalle_peso/1000000::numeric(20,6), 6)
                else
                    round(sum(ad_ff.adetalle_peso) over(partition by c.captura_id)/1000000::numeric(20,6), 6)
                end as peso
                ,row_number() over(order by c.recepcion_id,c.captura_orden) as numero_orden
                --,*
                ,d.suma as peso_ca
                ,d.fecha
                ,e.cant_paginas
                from captura c
                cross join datos_input di
                cross join recepciones_input ri
                left join proyecto_flujo pf on pf.proyecto_id = c.proyecto_id and pf.modulo_step_id = 5
                --quitamos las capturas que ya esten procesadas si hubiesen
                left join generacion_medio_detalle_captura gmdc on gmdc.gm_id = di.gm_id and c.captura_id = gmdc.captura_id
                left join documento a on a.captura_id = c.captura_id
                left join adetalle b on a.adetalle_id = b.adetalle_id
                left join (select distinct max(imagen_pagina::int)over(partition by documento_id) as cant_paginas,documento_id from imagen) e
                    on e.documento_id = a.documento_id
                --para sacar los pesos finales firmados
                left join fedatario_firmar f on f.captura_id = c.captura_id  and pf.proyecto_flujo_id is not null
                left join adetalle ad_ff on f.fedatario_documento_id=ad_ff.archivo_id  and pf.proyecto_flujo_id is not null
                --inicio para CA
                left join datos_iniciales_CA d on d.recepcion_id = c.recepcion_id and d.fecha=c.created_at::date
                where a.recepcion_id = ANY(ri.recepciones)--4
                --añadido para quitar las capturas si existiecen
                and gmdc.gmdc_id is null
                and c.captura_estado = 1
                --order by c.recepcion_id,c.captura_orden
            ),
            datos_iniciales as(
                select *
                from datos_iniciales_pre
                order by recepcion_id,captura_orden
            )
            select
                'Espacio mínimo requerido por medio: '||
                to_char(ceil((peso+peso_ca)*100)/100,'FM999,999,999,999,990D00')||'Mb'
                as mensaje
            from datos_iniciales
            cross join datos_input i
            where peso+peso_ca>peso_maximo
            order by (peso+peso_ca)desc
            limit 1
            ;
                "),
            ["prefijo" => $prefijo, "correlativo" => $correlativo, "peso_maximo" => $peso_maximo,"recepciones" => $array_variable
                ,"me_id" => $me_id,"espacio_libre" => $espacio_libre,"gm_id" => $gm_id]);

    }

    public function consulta($gm_id){
        return DB::select(
            DB::raw("
            select
                a.gmd_nombre as  gmd_nombre
                ,to_char(a.gmd_peso_maximo,'FM999,999,999,999,990D00')||' Mb' as gmd_peso_maximo
                ,to_char(a.gmd_peso_ocupado,'FM999,999,999,999,990D00')||' Mb' as gmd_peso_ocupado
                ,to_char(a.gmd_peso_ocupado*100/a.gmd_peso_maximo,'FM990D00')||'%'  as gmd_porcentaje_ocupado
                --,(a.suma) as gmd_peso_ocupado
                ,a.gmd_total_documento as gmd_total_documento
                ,a.gmd_id as gmd_id--por mientras
                ,d.me_descripcion as gmd_descripcion
                ,a.gmd_cant_pagina_total
                ,a.gmd_prefijo
	            ,a.gmd_correlativo
                from generacion_medio_detalle a
                left join generacion_medio b on a.gm_id = b.gm_id
                left join medio_exportacion d on b.me_id = d.me_id
            where a.gm_id=:gm_id;
            "),
            ["gm_id" => $gm_id]);


    }

    public function consulta_cabecera($gm_id){
        return DB::select(
            DB::raw("
            select
                a.gm_id
                ,a.gm_prefijo
                ,a.gm_correlativo
                ,a.me_id
                ,a.gm_peso_otros
                ,case
                    when  b.gmd_espacio_libre is null then 0
                    else b.gmd_espacio_libre end as gmd_espacio_libre
                from generacion_medio a
                left join generacion_medio_detalle b on a.gm_id=b.gm_id
            where a.gm_id = :gm_id
            order by b.gmd_id
            ;
            "),
            ["gm_id" => $gm_id]);

    }

    public function listar_rutas($path,$path_documento,$gmd_id,$usuario_creador,$ip){

        return DB::select(
            DB::raw("
            with recursive
            datos_input as (
                select
                :path::varchar(5000) as path
                ,:path_documento::varchar(5000) as path_documento
                ,:gmd_id::integer as gmd_id
            )
            ,datos as (
                select
                    case
                        when g.captura_estado = 1 and pf.proyecto_flujo_id is not null
                            then replace(ad_normal.adetalle_url,a.path_documento,'')
                        else
                                replace(ad_ca.adetalle_url,a.path_documento,'')
                    end
                            as ruta
                    ,
                    case
                        when g.captura_estado = 1 and pf.proyecto_flujo_id is not null
                            then ad_normal.adetalle_peso
                        else
                                ad_ca.adetalle_peso
                    end
                            as peso
                    ,b.gmd_peso_ocupado
                    --ruta destino
                    ,case
                        when g.captura_estado = 1 and pf.proyecto_flujo_id is not null
                            then p.proyecto_nombre||'/'||b.gmd_nombre||'/'||i.gmrd_ruta||ad_normal.adetalle_nombre
                        else
                                p.proyecto_nombre||'/'||b.gmd_nombre||'/'||i.gmrd_ruta||ad_ca.adetalle_nombre
                    end
                            as ruta_destino
                    ,c.*
                    ,row_number() over(order by c.gmdc_id) as correlativo
                    ,count(*) over() as total
                    from datos_input a
                    left join generacion_medio_detalle b on a.gmd_id = b.gmd_id
                    left join generacion_medio_detalle_captura c on b.gmd_id = c.gmd_id
                    left join captura g on g.captura_id = c.captura_id
                    left join proyecto p on p.proyecto_id = g.proyecto_id
                    left join proyecto_flujo pf on pf.proyecto_id = g.proyecto_id and pf.modulo_step_id = 5
                    left join generacion_medio_ruta_destino i on i.captura_estado = g.captura_estado
                    --para archivos firmados
                    left join fedatario_firmar f on f.captura_id = g.captura_id and g.captura_estado = 1  and pf.proyecto_flujo_id is not null
                    left join adetalle ad_normal on ad_normal.archivo_id = f.fedatario_documento_id  and pf.proyecto_flujo_id is not null
                    --para archivos calibradoras y aperturas
                    left join documento h on h.captura_id = g.captura_id and (g.captura_estado in (2,3,4,5) or pf.proyecto_flujo_id is null)
                    left join adetalle ad_ca on ad_ca.adetalle_id = h.adetalle_id
                order by c.gmdc_id
            )
            ,variables as (
                select
                    a.gmd_peso_ocupado*1000000::bigint as t
                    ,4::bigint as n
                    ,floor(a.gmd_peso_ocupado*1000000/4)::bigint as tn
                from datos a
                limit 1
            )
            ,datos_recursivos as (
                select
                    a.ruta||'****'||a.ruta_destino||
                    case
                        when a.peso > mod(a.peso,tn) or a.total=a.correlativo
                            then '%%%%'||(floor(a.peso/(tn)))::varchar(20)
                        else
                            ''
                        end
                    as ruta
                    ,
                    case
                        when a.peso > mod(a.peso,tn) or a.total=a.correlativo
                            then mod(a.peso,tn)
                        else
                            a.peso
                        end
                    as peso_acumulado
                    ,a.correlativo
                    ,a.peso
                from datos a
                cross join variables v
                where a.correlativo = 1

                union all

                select
                    a.ruta||'****'||a.ruta_destino||
                    case
                        when a.peso+b.peso_acumulado > mod(a.peso+b.peso_acumulado,tn) or a.total=a.correlativo
                            then '%%%%'||(floor((a.peso+b.peso_acumulado)/(tn))::varchar(20))
                        else
                            ''
                        end
                    as ruta
                    ,
                    case
                        when a.peso+b.peso_acumulado > mod(a.peso+b.peso_acumulado,tn) or a.total=a.correlativo
                            then mod(a.peso+b.peso_acumulado,tn)
                        else
                            a.peso+b.peso_acumulado
                        end
                    as peso_acumulado
                    ,a.correlativo
                    ,a.peso
                from datos_recursivos b
                cross join variables v
                    join datos a on b.correlativo+1 = a.correlativo
            )
            , insert_log as(
                    insert into log(log_fecha_hora,log_usuario,log_estado,log_ip,
                                    log_captura_id,log_id_asociado,log_modulo_step_id,
                                    log_tabla_asociada,log_proceso,log_archivo_id,
                                    log_descripcion,log_comentario,created_at,updated_at)
                    select
                        now()
                        ,:usuario_creador
                        ,1
                        ,:ip
                        ,a.captura_id
                        ,a.gmdc_id
                        ,6
                        ,'generacion_medio_detalle_captura'
                        ,'GM-FIN'
                        ,null
                        ,'Organización de Generación de Medios - Finalizado'
                        ,''
                        ,now()
                        ,now()
                    from datos a
            )
            select ruta
            from datos_recursivos
            order by correlativo;
            "),
        ["path" => $path,"path_documento" => $path_documento,"gmd_id" => $gmd_id,"usuario_creador"=>$usuario_creador,"ip"=>$ip]);

    }

    public function listar_rutas_imagenes($path,$path_documento,$gmd_id){

        return DB::select(
            DB::raw("
            with recursive
            datos_input as (
                select
                :path::varchar(5000) as path
                ,:path_documento::varchar(5000) as path_documento
                ,:gmd_id::integer as gmd_id
            )
            ,datos as (
                select
                    replace(im.imagen_url,a.path_documento,'') as ruta
                    --ruta destino
                    ,p.proyecto_nombre||'/'||b.gmd_nombre||'/visor/componentes/'||im.imagen_nombre as ruta_destino

                    from datos_input a
                    left join generacion_medio_detalle b on a.gmd_id = b.gmd_id
                    left join generacion_medio_detalle_captura c on b.gmd_id = c.gmd_id
                    left join captura g on g.captura_id = c.captura_id
                    left join imagen im on im.captura_id = g.captura_id
                    left join proyecto p on p.proyecto_id = g.proyecto_id
                    left join generacion_medio_ruta_destino i on i.captura_estado = g.captura_estado
                order by c.gmdc_id
            )
            select ruta||'****'||ruta_destino as ruta
            from datos;
            "),
        ["path" => $path,"path_documento" => $path_documento,"gmd_id" => $gmd_id]);

    }

    public function recepciones_x_gmd($gmd_id){

        return DB::select(
            DB::raw("
            select distinct b.recepcion_id
            from generacion_medio_detalle_captura a
            join captura b on a.captura_id = b.captura_id
            where a.gmd_id = :gmd_id
            "),
        ["gmd_id" => $gmd_id]);

    }

}
