<?php

namespace Fedatario\Controllers;

use Illuminate\Database\Eloquent\Model;
use DB;
use Request;

Trait fedatario
{
    //

    public function crear_fedatario_inicial_from_cc($captura_id,$usuario_creador,$recepcion_id,$cc_id,$proyecto_id,$indizacion_id,$cliente_id){

        return DB::insert("
            insert into fedatario(
                captura_id
                ,indizacion_id
                ,proyecto_id
                ,recepcion_id
                ,cc_id
                ,cliente_id
                ,fedatario_estado
                ,usuario_creador
                ,created_at
                ,updated_at
                )
                values (:captura_id,
                :indizacion_id,
                :proyecto_id,
                :recepcion_id,
                :cc_id,
                :cliente_id,
                0,
                :usuario_creador,
                now(),
                now()
                )", ["captura_id"=>$captura_id
            , "recepcion_id"=>$recepcion_id
            , "cc_id"=>$cc_id
            , "proyecto_id"=>$proyecto_id
            , "indizacion_id"=>$indizacion_id
            , "cliente_id"=>$cliente_id
            , "usuario_creador"=>$usuario_creador]);
    }

    public function auto_asignacion($usuario_asignado){



    }

    public function arbol_fedatario($porcentaje,$proyecto_id,$flag_asistente = 0)
    {
        $fedatario_tipo = $flag_asistente==0?'NORMAL':'ASISTENTE';
        $usuario_id =session("usuario_id");
        $ip = Request::ip();
        
        return DB::select("
             with 
             
            --conseguimos el numero de filas por el porcentaje indicado
            datos as (
                select 
                case 
                    when count(a.captura_id)>0 and floor(count(a.captura_id)*(0.01)*(:porcentaje))>0
                        then floor(count(a.captura_id)*(0.01)*(:porcentaje))
                    when count(a.captura_id)>0 and floor(count(a.captura_id)*(0.01)*(:porcentaje))=0
                        then 1
                    else 0 end as cantidad_necesaria
                from fedatario a
                left join proyecto b on a.proyecto_id = b.proyecto_id
                left join recepcion c on c.recepcion_id = a.recepcion_id
                left join documento d on d.captura_id = a.captura_id
                --where a.fedatario_estado = 0
                where a.fedatario_estado in (0,2)
                and a.fedatario_tipo = :fedatario_tipo
                and a.proyecto_id = :proyecto_id
                and a.fedatario_grupo = 
                        case when a.fedatario_tipo='NORMAL' then b.proyecto_grupo_fedatario_actual 
                        else b.proyecto_grupo_fedatario_asis_actual end )
            ,
            --conseguimos la data random con el nro filas previamente calculado
            data_previa_sin_orden as (
                select 
                a.proyecto_id, a.recepcion_id, a.captura_id, a.cc_id, a.indizacion_id,a.cliente_id,a.usuario_creador,a.fedatario_id,
                d.documento_id,
                b.proyecto_nombre,
                c.recepcion_nombre,
                d.documento_nombre,
                d.adetalle_id,
                c.recepcion_tipo
                from fedatario a
                left join proyecto b on a.proyecto_id = b.proyecto_id
                left join recepcion c on c.recepcion_id = a.recepcion_id
                left join documento d on d.captura_id = a.captura_id
                --where a.fedatario_estado = 0
                where a.fedatario_estado in (0,2)
                and fedatario_tipo = :fedatario_tipo
                and a.proyecto_id = :proyecto_id
                and a.fedatario_grupo =
                            case when a.fedatario_tipo='NORMAL' then b.proyecto_grupo_fedatario_actual 
                            else b.proyecto_grupo_fedatario_asis_actual end 
                order by random()
                limit (select cantidad_necesaria from datos) )
            ,
            --hacemos update a los registros seleccionados aleatoriamente para el arbol
            update_fedatario as (
                update fedatario
                    set fedatario_elegido_aleatorio = 
                        case when fedatario.fedatario_id = a.fedatario_id
                            then 1
                        else 0 
                            end,
                    fedatario_estado=0
                from fedatario b
                left join data_previa_sin_orden a on b.fedatario_id = a.fedatario_id
                left join proyecto c on c.proyecto_id = b.proyecto_id
                where fedatario.fedatario_id = b.fedatario_id 
                and fedatario.proyecto_id = :proyecto_id
                --and fedatario.fedatario_estado = 0
                and fedatario.fedatario_estado in (0,2)
                and fedatario.fedatario_tipo = :fedatario_tipo
                and fedatario.fedatario_grupo =
                            case when fedatario.fedatario_tipo='NORMAL' then c.proyecto_grupo_fedatario_actual 
                            else c.proyecto_grupo_fedatario_asis_actual end 

            ),
            --borramos(update) el registro de proyecto_grupo previo si es que hubiese
            delete_proyecto_grupo as (
                update proyecto_grupo set proyecto_grupo_estado=0
                from proyecto a
                where 
                a.proyecto_id = proyecto_grupo.proyecto_id
                and proyecto_grupo.proyecto_id = :proyecto_id
                and proyecto_grupo.proyecto_grupo_tipo = :fedatario_tipo	
                and proyecto_grupo.proyecto_grupo_nro = case when :fedatario_tipo = 'NORMAL' 
                                        then a.proyecto_grupo_fedatario_actual
                                        else a.proyecto_grupo_fedatario_asis_actual
                                    end
            ),
            --insertamos el nuevo registro de proyecto_grupo
            insert_proyecto_grupo as (
                insert into proyecto_grupo(
                    proyecto_id
                    ,proyecto_grupo_nro
                    ,proyecto_grupo_tipo
                    ,proyecto_grupo_muestreo
                    ,proyecto_grupo_flag_finalizado
                )
                select 
                    a.proyecto_id,
                    case when :fedatario_tipo = 'NORMAL' 
                        then proyecto_grupo_fedatario_actual
                        else proyecto_grupo_fedatario_asis_actual
                    end,
                    :fedatario_tipo,
                    :porcentaje,
                    0
                from proyecto a
                where a.proyecto_id = :proyecto_id
                ),
			--hacemos update al grupo del proyecto
			update_proyecto as (
				update proyecto 
				set proyecto_grupo_fedatario_actual = 
									case when :fedatario_tipo ='NORMAL' then proyecto_grupo_fedatario_actual+1 
										 else proyecto_grupo_fedatario_actual end ,
					proyecto_grupo_fedatario_asis_actual = 
									case when :fedatario_tipo ='ASISTENTE' then proyecto_grupo_fedatario_asis_actual+1 
										 else proyecto_grupo_fedatario_asis_actual end
				where proyecto_id = :proyecto_id
            ),
            --insertamos el log tomando como from where lo mismo del primer query
            --no hay problema con que se hayan hecho update a las tablas por que 
            --no se toma en cuenta el update hasta que todo el with query se ejecute
            insert_log as(
                insert into log(log_fecha_hora,log_usuario,log_estado,log_ip,
                                log_captura_id,log_id_asociado,log_modulo_step_id,
                                log_tabla_asociada,log_proceso,log_archivo_id,
                                log_descripcion,log_comentario,created_at,updated_at)
                
                select 
                now()
                ,:usuario_id
                ,1
                ,:ip
                ,a.captura_id
                ,a.fedatario_id
                ,4
                ,'fedatario'
                ,case 
                    when a.fedatario_tipo='ASISTENTE'
                        then 'FED-REV-ASIS-MUESTREO'
                    else 'FED-REV-NOR-MUESTREO'
                end 
                
                ,null
                ,case 
                    when a.fedatario_tipo='ASISTENTE'
                        then 'Muestreo de Grupo de Revisión de Fedatario Asistente'
                    else 'Muestreo de Grupo de Revisión de Fedatario Normal'
                end
                ,''
                ,now()
                ,now()
                from fedatario a
                left join proyecto b on a.proyecto_id = b.proyecto_id
                --where a.fedatario_estado = 0
                where a.fedatario_estado in (0,2)
                and a.fedatario_tipo = :fedatario_tipo
                and a.proyecto_id = :proyecto_id
                and a.fedatario_grupo = 
                        case when a.fedatario_tipo='NORMAL' then b.proyecto_grupo_fedatario_actual 
                        else b.proyecto_grupo_fedatario_asis_actual end
            )
            --select final con el orden que requiere el arbol
            select * from data_previa_sin_orden a
            order by a.proyecto_id, a.recepcion_id, a.captura_id, a.indizacion_id	
            ", ["porcentaje"=>$porcentaje,"proyecto_id"=>$proyecto_id,"fedatario_tipo"=>$fedatario_tipo
            ,"usuario_id"=>$usuario_id,"ip"=>$ip]);
    }

    public function update_estado_fedatario($fedatario_id,$fedatario_estado)
    {

        return DB::update("
            UPDATE fedatario 
            SET fedatario_estado = :fedatario_estado
            WHERE fedatario_id = :fedatario_id", ["fedatario_estado"=>$fedatario_estado
            , "fedatario_id"=>$fedatario_id]);



    }

    public function crear_fedatario_inicial_from_captura_masivo($usuario_creador,$recepcion_id){
    

        return DB::select(
                    DB::raw("
                    insert into fedatario(
                        captura_id
                        ,proyecto_id
                        ,recepcion_id
                        ,cliente_id
                        ,fedatario_estado
                        ,usuario_creador
                        ,fedatario_tipo
                        ,fedatario_grupo
                        ,created_at
                        ,updated_at
                        )
                        select 
                            a.captura_id,
                            a.proyecto_id,
                            a.recepcion_id,
                            a.cliente_id,
                            --0,
                            case when b.proyecto_fedatario_asistente=1
                                    then 3
                                    else 0 
                                end,
                            :usuario_creador::int,--usuario creador
                            'NORMAL',
                            case when b.proyecto_fedatario_asistente=1 
                                then b.proyecto_grupo_fedatario_asis_actual
                                else b.proyecto_grupo_fedatario_actual
                            end,
                            now(),
                            now()
                        from captura a
                        left join proyecto b on a.proyecto_id = b.proyecto_id
                        where a.recepcion_id=:recepcion_id
                        and a.captura_estado_glb = 'cc'
                        union
                        select 
                            a.captura_id,
                            a.proyecto_id,
                            a.recepcion_id,
                            a.cliente_id,
                            0,
                            :usuario_creador::int,--usuario creador
                            'ASISTENTE',
                            b.proyecto_grupo_fedatario_asis_actual,
                            now(),
                            now()
                        from captura a
                        join proyecto b on a.proyecto_id = b.proyecto_id and b.proyecto_fedatario_asistente=1
                        where a.recepcion_id=:recepcion_id
                        and a.captura_estado_glb = 'cc'
                        RETURNING fedatario_id;
                        "),["usuario_creador" => $usuario_creador, "recepcion_id" => $recepcion_id]
        )[0]->fedatario_id;
    }

    public function crear_fedatario_inicial_from_captura($captura_id,$usuario_creador){
    

        return DB::select(
                    DB::raw("
                    insert into fedatario(
                        captura_id
                        ,proyecto_id
                        ,recepcion_id
                        ,cliente_id
                        ,fedatario_estado
                        ,usuario_creador
                        ,fedatario_tipo
                        ,fedatario_grupo
                        ,created_at
                        ,updated_at
                        )
                        select 
                            a.captura_id,
                            a.proyecto_id,
                            a.recepcion_id,
                            a.cliente_id,
                            --0,
                                case when b.proyecto_fedatario_asistente=1
                                    then 3
                                    else 0 
                                end,
                            :usuario_creador::int,--usuario creador
                            'NORMAL',
                            case when b.proyecto_fedatario_asistente=1 
                                then b.proyecto_grupo_fedatario_asis_actual
                                else b.proyecto_grupo_fedatario_actual
                            end,
                            now(),
                            now()
                        from captura a
                        left join proyecto b on a.proyecto_id = b.proyecto_id
                        where a.captura_id=:captura_id
                        union
                        select 
                            a.captura_id,
                            a.proyecto_id,
                            a.recepcion_id,
                            a.cliente_id,
                            0,
                            :usuario_creador::int,--usuario creador
                            'ASISTENTE',
                            b.proyecto_grupo_fedatario_asis_actual,
                            now(),
                            now()
                        from captura a
                        join proyecto b on a.proyecto_id = b.proyecto_id and b.proyecto_fedatario_asistente=1
                        where a.captura_id=:captura_id
                        RETURNING fedatario_id;
                        "),["usuario_creador" => $usuario_creador, "captura_id" => $captura_id]
        )[0]->fedatario_id;
    }

    public function arbol_fedatario_previo($proyecto_id,$flag_asistente = 0)
    {
        $fedatario_tipo = $flag_asistente==0?'NORMAL':'ASISTENTE';
        return DB::select("
        with grupo_actual as (
            select 
            proyecto_id,
            proyecto_grupo_nro,
            proyecto_grupo_tipo,
            proyecto_grupo_muestreo
            from proyecto_grupo
            where proyecto_grupo_estado=1
            and proyecto_grupo_tipo=:fedatario_tipo
            and proyecto_grupo_flag_finalizado=0
            and proyecto_id = :proyecto_id
            limit 1
        )
        select 
            a.proyecto_id, a.recepcion_id, a.captura_id, a.cc_id, a.indizacion_id,a.cliente_id,a.usuario_creador,a.fedatario_id,
            d.documento_id,
            b.proyecto_nombre,
            c.recepcion_nombre,
            d.documento_nombre,
            d.adetalle_id,
            c.recepcion_tipo,
            e.proyecto_grupo_muestreo
        from fedatario a
        left join proyecto b on a.proyecto_id = b.proyecto_id
        left join recepcion c on c.recepcion_id = a.recepcion_id
        left join documento d on d.captura_id = a.captura_id
        join grupo_actual e on e.proyecto_id = b.proyecto_id
             where a.fedatario_estado = 0
            and fedatario_tipo = e.proyecto_grupo_tipo
            --and a.proyecto_id = :proyecto_id
            and a.fedatario_grupo = e.proyecto_grupo_nro
            and a.fedatario_elegido_aleatorio is not null
            and a.fedatario_elegido_aleatorio = 1
        order by a.proyecto_id, a.recepcion_id, a.captura_id, a.indizacion_id;
            ", ["proyecto_id"=>$proyecto_id,"fedatario_tipo"=>$fedatario_tipo]);
    }

    public function validacion_update_grupo_antiguo($proyecto_id,$flag_asistente = 0)
    {
        $fedatario_tipo = $flag_asistente==0?'NORMAL':'ASISTENTE';
        return DB::select("
        with
        --actualizamos el grupo antiguo a estado 0 si tuviese registro previo
        --y retornamos registro con el cual se hara lo siguiente
        update_estado_grupo_old as (
           update proyecto_grupo
               set proyecto_grupo_estado = 0
           where proyecto_grupo_estado=1
           and proyecto_grupo_tipo=:fedatario_tipo
           and proyecto_grupo_flag_finalizado=0
           and proyecto_id = :proyecto_id 
           returning proyecto_id,proyecto_grupo_nro
        ), 
        --si el anterior query devolvió registros, actualizamos de grupo a los fed NORMALES
        update_fedatario_fed as (
            update fedatario 
                set fedatario_grupo= fedatario_grupo+1
            from update_estado_grupo_old a
            where fedatario.fedatario_tipo ='NORMAL'
            --and fedatario.fedatario_estado = 0
            and fedatario.fedatario_estado in (0,2,3)
            and fedatario.fedatario_grupo= a.proyecto_grupo_nro
            and fedatario.proyecto_id= a.proyecto_id
            returning fedatario_id,captura_id,proyecto_grupo_nro,fedatario.proyecto_id
        ),
        --tambien actualizamos los fed ASISTENTE si el tipo ingresado es ASISTENTE
        update_fedatario_asis as (
            update fedatario 
                set fedatario_grupo= fedatario_grupo+1
            from update_fedatario_fed a
            where fedatario.fedatario_tipo ='ASISTENTE'
            --and fedatario.fedatario_estado = 0
            and fedatario.fedatario_estado in (0,2,3)
            and fedatario.fedatario_grupo= a.proyecto_grupo_nro
            and fedatario.proyecto_id= a.proyecto_id
            and fedatario.captura_id= a.captura_id
            and :fedatario_tipo = 'ASISTENTE'
            --returning fedatario_id,captura_id
        ),
        --comprobamos si el grupo actual de fedatario normal es el correcto y sino capturamos el siguiente correcto
        nuevo_proyecto_grupo_fedatario_actual as (
            select 
            a.proyecto_id
            ,case 
                when a.proyecto_fedatario_asistente = 1
                then b.proyecto_grupo_nro 
                else a.proyecto_grupo_fedatario_actual end as proyecto_grupo_fedatario_actual
            from proyecto a
                join proyecto_grupo b
                on a.proyecto_id = b.proyecto_id 
                and b.proyecto_grupo_tipo = 'ASISTENTE'
                and b.proyecto_grupo_nro >= a.proyecto_grupo_fedatario_actual
                and b.proyecto_grupo_estado != 0
                --and b.proyecto_grupo_flag_finalizado = 1
            where a.proyecto_id = :proyecto_id
            and :fedatario_tipo = 'NORMAL'
            order by b.proyecto_grupo_nro
            limit 1
        ),
        --hacemos update con el grupo actual correcto encontrado para fedatario_firmar
        updated_proyecto_grupo_nro as (
            update proyecto 
            set proyecto_grupo_fedatario_actual = a.proyecto_grupo_fedatario_actual
            from nuevo_proyecto_grupo_fedatario_actual a
            where proyecto.proyecto_id = a.proyecto_id
            and proyecto.proyecto_fedatario_asistente = 1
            returning proyecto.proyecto_grupo_fedatario_actual
        )
        select * from update_estado_grupo_old;
        ", ["proyecto_id"=>$proyecto_id,"fedatario_tipo"=>$fedatario_tipo]);
    }

    public function guardar_proyecto_grupo_fedatario_asistente($fedatario_id,$usuario_creador){
        $ip = Request::ip();

        return DB::select(
                    DB::raw("
                    with 
                    --primer update igual que normal(cualquier cambio aplicar a los dos)
                    update_proyecto_grupo as (
                        update proyecto_grupo
                            set proyecto_grupo_flag_finalizado=1
                        from fedatario a
                        left join fedatario b 
                            on b.fedatario_tipo = a.fedatario_tipo 
                            and b.fedatario_grupo = a.fedatario_grupo
                            and b.proyecto_id = a.proyecto_id
                            and b.fedatario_estado !=2
                            and b.fedatario_elegido_aleatorio=1
                        where a.fedatario_id = :fedatario_id
                        and proyecto_grupo.proyecto_id =a.proyecto_id
                        and proyecto_grupo.proyecto_grupo_tipo=a.fedatario_tipo
                        and proyecto_grupo.proyecto_grupo_nro= a.fedatario_grupo
                        and proyecto_grupo.proyecto_grupo_estado=1
                        and b.fedatario_id is null
                        returning proyecto_grupo.proyecto_grupo_nro,proyecto_grupo.proyecto_id,proyecto_grupo.proyecto_grupo_tipo
                    ),
                    update_fedatario as(
                        update fedatario
                            set fedatario_estado =0 
                        from update_proyecto_grupo a
                        where fedatario.proyecto_id = a.proyecto_id
                        and fedatario.fedatario_grupo = a.proyecto_grupo_nro
                        and fedatario.fedatario_tipo = 'NORMAL'
                        and a.proyecto_grupo_tipo = 'ASISTENTE'
                    ),
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
                        ,b.captura_id
                        ,b.fedatario_id
                        ,4
                        ,'fedatario'
                        ,case 
                            when b.fedatario_tipo='ASISTENTE'
                                then 'FED-REV-ASIS-GRPO-FIN'
                            else 'FED-REV-NOR-GRPO-FIN'
                        end 
                        ,null
                        ,case 
                            when b.fedatario_tipo='ASISTENTE'
                                then 'Finalización de Grupo de Revisión de Fedatario Asistente'
                            else 'Finalización de Grupo de Revisión de Fedatario Normal'
                        end
                        ,''
                        ,now()
                        ,now()
                        from update_proyecto_grupo a
                        left join fedatario b 
                            on a.proyecto_grupo_nro= b.fedatario_grupo 
                            and a.proyecto_id= b.proyecto_id
                            and a.proyecto_grupo_tipo= b.fedatario_tipo
                    )
                    select * from update_proyecto_grupo;
                        "),[ "fedatario_id" => $fedatario_id,"usuario_creador" => $usuario_creador,"ip" => $ip]
        );
    }


}
