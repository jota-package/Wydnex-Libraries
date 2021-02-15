<?php

namespace Wydnex;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\respuesta;
use App;

trait Wydnex
{

    static public function arbol_fedatario_previo($proyecto_id, $flag_asistente = 0)
    {
        $fedatario_tipo = $flag_asistente == 0 ? 'NORMAL' : 'ASISTENTE';
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
        ", ["proyecto_id" => $proyecto_id, "fedatario_tipo" => $fedatario_tipo]);
    }

    static public function create_node(Request $request)
    {
        $f = new App\files();
        $parent = $request->input('padre_id', 0);
        if ($parent == 0) {
            $data = [
                "recepcion_id" => intval($request->input('recepcion_id', 0)),
                "file_nombre" => $request->input('nombre', ''),
                "file_captura_estado" => intval($request->input('captura_estado', 0)),
                "file_tipo" => 'd',
                "file_usuario_id" => session("usuario_id")
            ];
            return $f->crear($data);
        } else {
            $parent = App\files::where('file_id', $parent)
                ->where('recepcion_id', intval($request->input('recepcion_id', 0)))
                ->where('file_captura_estado', intval($request->input('captura_estado', 0)))
                ->first();
            if ($parent) {
                $data = [
                    "recepcion_id" => $parent["recepcion_id"],
                    "file_nombre" => $request->input('nombre', ''),
                    "file_padre_id" => $request->input('padre_id', 0),
                    "file_captura_estado" => $parent["file_captura_estado"],
                    "file_tipo" => 'd',
                    "file_usuario_id" => session("usuario_id")
                ];
                return $f->crear($data);
            } else {
                return respuesta::error("Uno de los parámetros no es correcto para poder crear el archivo o directorio.", 500);
            }
        }
    }

    static public function borrar_directorio($file_id)
    {
        $file_estado = 0;
        $num_child = DB::select(
            DB::raw("
            with datos_final as (
                select count(a.file_id) total
                from files a
                join files b
                    on b.file_padre_id = a.file_id
                    and b.file_estado != 0
                where
                a.file_id= :file_id
                and a.file_tipo ='d'
            ),
            update_files as (
                update files set file_estado = 0
                from datos_final b
                where files.file_id = :file_id and b.total= :file_estado
            )
            select total from datos_final;"),
            ["file_id" => $file_id, "file_estado" => $file_estado]
        )[0]->total;
        if ($num_child > 0) {
            return respuesta::error("No se puede eliminar un directorio que contenga elementos.", 500);
        } else {
            return respuesta::ok();
        }
    }

    static public function verifyDirectoryTreeClienteCaptura($client, $children_status = false)
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

        return $retorno;
    }

    static public function verifyDirectoryTreeCliente($client, $demo = 0)
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
                            )',
                        ['captura_id' => $captura["captura_id"]]
                    );

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

    static public function arbol_indizacion()
    {

        $is_admin = user::is_admin();
        if ($is_admin) {
            return DB::select("
            select
            a.proyecto_id, a.recepcion_id, a.captura_id, a.indizacion_id,
            d.documento_id,
            b.proyecto_nombre,
            c.recepcion_nombre,
            d.documento_nombre,
            d.adetalle_id,
            c.recepcion_tipo,
            a.indizacion_tipo,
            a.indizacion_anterior_id
            from indizacion a
            left join proyecto b on a.proyecto_id = b.proyecto_id
            left join recepcion c on c.recepcion_id = a.recepcion_id
            left join documento d on d.captura_id = a.captura_id
            where a.indizacion_estado = 0
            order by a.proyecto_id, a.recepcion_id, a.captura_id, a.indizacion_id;
            ");
        } else {
            $usuario_id = session('usuario_id');

            return DB::select("
            select
            a.proyecto_id, a.recepcion_id, a.captura_id, a.indizacion_id,
            d.documento_id,
            b.proyecto_nombre,
            c.recepcion_nombre,
            d.documento_nombre,
            d.adetalle_id,
            c.recepcion_tipo,
            a.indizacion_tipo,
            a.indizacion_anterior_id,
            e.usuario_id
            from indizacion a
            left join proyecto b on a.proyecto_id = b.proyecto_id
            left join recepcion c on c.recepcion_id = a.recepcion_id
            left join documento d on d.captura_id = a.captura_id
            left join equipo e on b.proyecto_id = e.proyecto_id
            where a.indizacion_estado = 0
            and e.usuario_id = :usuario_id
            order by a.proyecto_id, a.recepcion_id, a.captura_id, a.indizacion_id;
            ", ["usuario_id" => $usuario_id]);
        }
    }

    static public function arbol_reproceso()
    {
        return DB::select("
            with indizacion_rpt as (
                select
                    distinct
                       i.indizacion_id,
                       i.captura_id,
                       i.indizacion_tipo,
                       case
                           when respuesta_id is not null then 1
                           else 0
                       end flag_reproceso
                from  indizacion i
                    left join respuesta r
                         on i.indizacion_id = r.indizacion_id
                --where indizacion_tipo = 'VF'
                group by i.indizacion_id,respuesta_id),
            reproceso as (select distinct
                             a.proyecto_id, a.recepcion_id, a.captura_id,a.captura_estado_glb
                                          ,e.indizacion_id,
                                           d.documento_id,
                                           b.proyecto_nombre,
                                           c.recepcion_nombre,
                                           d.documento_nombre,
                                           d.adetalle_id,
                                           c.recepcion_tipo,
                                           e.indizacion_tipo,
                                           e.flag_reproceso
                           from incidencia_imagen x
                                  join imagen y on x.imagen_id = y.imagen_id
                                  left join captura a on a.captura_id = y.captura_id
                                  left join proyecto b on a.proyecto_id = b.proyecto_id
                                  left join recepcion c on c.recepcion_id = a.recepcion_id
                                  left join documento d on d.captura_id = a.captura_id
                                  left join indizacion_rpt e on e.captura_id = y.captura_id and e.indizacion_tipo='VF'
                           where x.estado = 1)
            SELECT
               proyecto_id, recepcion_id,captura_id,
               captura_estado_glb, indizacion_id, documento_id,
               proyecto_nombre, recepcion_nombre, documento_nombre,
               adetalle_id, recepcion_tipo,indizacion_tipo,
               case
                 when flag_reproceso is null then 0
                 when flag_reproceso = 1 then 1
                 when flag_reproceso = 0 then 0
               end flag_reproceso
            FROM reproceso;");
    }

    static public function arbol_controlcalidad()
    {
        $is_admin = user::is_admin();
        if ($is_admin) {
            return DB::select("
            select
            a.proyecto_id, a.recepcion_id, a.captura_id, a.cc_id, a.indizacion_id,a.cliente_id,a.usuario_creador,
            d.documento_id,
            b.proyecto_nombre,
            c.recepcion_nombre,
            d.documento_nombre,
            d.adetalle_id,
            c.recepcion_tipo,
            f.indizacion_id
            from control_calidad a
            left join proyecto b on a.proyecto_id = b.proyecto_id
            left join recepcion c on c.recepcion_id = a.recepcion_id
            left join documento d on d.captura_id = a.captura_id
            left join proyecto_captura_flujo e on a.captura_id = e.captura_id and e.modulo_step_id =2
            left join indizacion f  on e.modulo_id = f.indizacion_id
            where a.cc_estado = 0
            order by a.proyecto_id, a.recepcion_id, a.captura_id, a.indizacion_id;
            ");
        } else {
            $usuario_id = session('usuario_id');
            return DB::select("
            select
            a.proyecto_id, a.recepcion_id, a.captura_id, a.cc_id, a.indizacion_id,a.cliente_id,a.usuario_creador,
            d.documento_id,
            b.proyecto_nombre,
            c.recepcion_nombre,
            d.documento_nombre,
            d.adetalle_id,
            c.recepcion_tipo,
            f.indizacion_id
            from control_calidad a
            left join proyecto b on a.proyecto_id = b.proyecto_id
            left join recepcion c on c.recepcion_id = a.recepcion_id
            left join documento d on d.captura_id = a.captura_id
            left join proyecto_captura_flujo e on a.captura_id = e.captura_id and e.modulo_step_id =2
            left join indizacion f  on e.modulo_id = f.indizacion_id
            left join equipo eq on b.proyecto_id = eq.proyecto_id
            where a.cc_estado = 0
            and eq.usuario_id = :usuario_id
            order by a.proyecto_id, a.recepcion_id, a.captura_id, a.indizacion_id;
            ", ["usuario_id" => $usuario_id]);
        }
    }

    static public function validacion_update_grupo_antiguo($proyecto_id, $flag_asistente = 0)
    {
        $fedatario_tipo = $flag_asistente == 0 ? 'NORMAL' : 'ASISTENTE';
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
        ", ["proyecto_id" => $proyecto_id, "fedatario_tipo" => $fedatario_tipo]);
    }

    static public function arbol_fedatario($porcentaje, $proyecto_id, $flag_asistente = 0)
    {
        $fedatario_tipo = $flag_asistente == 0 ? 'NORMAL' : 'ASISTENTE';
        $usuario_id = session("usuario_id");
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
            ", [
            "porcentaje" => $porcentaje, "proyecto_id" => $proyecto_id, "fedatario_tipo" => $fedatario_tipo, "usuario_id" => $usuario_id, "ip" => $ip
        ]);
    }

    static public function arbol_fedatario_firmar()
    {
        $is_admin = user::is_admin();
        if ($is_admin) {
            return DB::select("
                select
                a.proyecto_id, a.recepcion_id, a.captura_id, a.cc_id,a.fedatario_id,a.fedatario_firmar_id, a.indizacion_id,a.cliente_id,a.usuario_creador,
                b.proyecto_nombre,
                c.recepcion_nombre,
                c.recepcion_tipo
                from fedatario_firmar a
                left join proyecto b on a.proyecto_id = b.proyecto_id
                left join recepcion c on c.recepcion_id = a.recepcion_id

            order by a.proyecto_id, a.recepcion_id, a.captura_id, a.indizacion_id
            ");
        } else {
            $usuario_id = session('usuario_id');
            return DB::select("
                select
                a.proyecto_id, a.recepcion_id, a.captura_id, a.cc_id,a.fedatario_id,a.fedatario_firmar_id, a.indizacion_id,a.cliente_id,a.usuario_creador,
                b.proyecto_nombre,
                c.recepcion_nombre,
                c.recepcion_tipo
                from fedatario_firmar a
                left join proyecto b on a.proyecto_id = b.proyecto_id
                left join recepcion c on c.recepcion_id = a.recepcion_id
                left join equipo e on b.proyecto_id = e.proyecto_id
                where e.usuario_id = :usuario_id
            order by a.proyecto_id, a.recepcion_id, a.captura_id, a.indizacion_id
            ", ["usuario_id" => $usuario_id]);
        }
    }

    static public function load_main_tree()
    {

        $is_admin = App\user::is_admin();

        if ($is_admin) {
            $data = App\proyecto::select(
                    "proyecto_id",
                    "proyecto_nombre as text"
                )
                ->with("children_captura")
                // ->where("cliente_id",1)
                ->orderBy('proyecto_id')
                ->get();
        } else {
            $usuario_id = session('usuario_id');
            $data = App\proyecto::select(
                    "proyecto.proyecto_id",
                    "proyecto.proyecto_nombre as text",
                    "equipo.usuario_id"
                )
                ->leftJoin('equipo', 'equipo.proyecto_id', 'proyecto.proyecto_id')
                ->with("children_captura")
                ->where('usuario_id', $usuario_id)
                // ->where("cliente_id",1)
                ->orderBy('proyecto_id')
                ->get();
        }

        return self::verifyDirectoryTreeClienteCaptura($data, true);
    }

    static public function load_node($recepcion_id, $file_id, $captura_estado){
		
        $f = new App\files();
		if($file_id == 0){
			$lista = $f->listar_desde_recepcion($recepcion_id, $captura_estado);
		} else {
			$lista = $f->listar_desde_padre($recepcion_id, $file_id);
		}

		$nodo = [];
		if($lista["estado"]){
			$lista = $lista["payload"];
			foreach ($lista as $i => $elem) {
				if($elem->file_tipo == "d"){
					array_push($nodo, [
						"text" => $elem->file_nombre,
						"file_id" => $elem->file_id,
						"file_tipo" => $elem->file_tipo,
						"recepcion_id" => $elem->recepcion_id,
						"recepcion_tipo" => $elem->recepcion_tipo,
						"captura_estado" => $elem->file_captura_estado,
						"captura_id" => $elem->captura_id,
						"proyecto_id" => $elem->proyecto_id,
						"cliente_id" => $elem->cliente_id,
						"children" => true,
					]);
				} else if ($elem->file_tipo == "f") {
					if($elem->recepcion_tipo == "s" && !empty($elem->adetalle_id)){
						array_push($nodo, [
							"text" => $elem->documento_nombre,
							"icon" => $this->obtener_icon_file($elem->adetalle_nombre),
							"file_id" => $elem->file_id,
							"file_tipo" => $elem->file_tipo,
							"recepcion_id" => $elem->recepcion_id,
							"recepcion_tipo" => $elem->recepcion_tipo,
							"captura_estado" => $elem->file_captura_estado,
							"adetalle_id" => $elem->adetalle_id,
							"documento_id" => $elem->documento_id,
							"cliente_id" => $elem->cliente_id,
							"captura_id" => $elem->captura_id,
							"captura_estado_glb" => $elem->captura_estado_glb,
							"proyecto_id" => $elem->proyecto_id,
							"documento_nombre" => $elem->documento_nombre,
							"padre_id" => ((!empty($elem->file_padre_id))? $elem->file_padre_id : 0)

						]);
					} else if ($elem->recepcion_tipo == "m"){
						array_push($nodo, [
							"text" => $elem->documento_nombre,
							"icon" => $this->obtener_icon_file($elem->adetalle_nombre),
							"file_id" => $elem->file_id,
							"file_tipo" => $elem->file_tipo,
							"recepcion_id" => $elem->recepcion_id,
							"recepcion_tipo" => $elem->recepcion_tipo,
							"captura_estado" => $elem->file_captura_estado,
							"adetalle_id" => $elem->adetalle_id,
							"documento_id" => $elem->documento_id,
							"cliente_id" => $elem->cliente_id,
							"captura_id" => $elem->captura_id,
							"captura_estado_glb" => $elem->captura_estado_glb,
							"proyecto_id" => $elem->proyecto_id,
							"documento_nombre" => $elem->documento_nombre,
							"padre_id" => ((!empty($elem->file_padre_id))? $elem->file_padre_id : 0)
						]);
					}
				} else {
				}
			}
			return $nodo;
		} else {
			return [];
		}
	}

    static public function listar_desde_recepcion($recepcion_id, $captura_estado)
    {

        //$valido = capturaController::validar_listar_captura($recepcion_id, $captura_estado);
        //if ($valido["estado"]) {
        $files = DB::select(
            DB::raw("
                select
                f.file_id,
                f.file_nombre,
                f.file_tipo,
                f.recepcion_id,
                f.file_captura_estado,
                f.file_padre_id,
                r.recepcion_tipo,
                c.captura_id,
                c.captura_estado,
                c.captura_estado_glb,
                c.proyecto_id,
                c.cliente_id,
                c.tc_descripcion,
                c.tc_id,
                d.documento_id,
                d.documento_nombre,
                f.created_at,
                a.*
                from files f
                left join recepcion r on f.recepcion_id = r.recepcion_id
                left join captura c on c.captura_file_id = f.file_id
                left join documento d on d.captura_id = c.captura_id
                left join adetalle a on a.adetalle_id = d.adetalle_id
                where f.recepcion_id = :recepcion_id
                and f.file_captura_estado= :captura_estado
                and f.file_estado = 1
                and f.file_padre_id is null
                order by f.file_tipo,f.created_at,
                case when c.captura_orden is null then c.captura_id else c.captura_orden end;"),
            ["recepcion_id" => $recepcion_id, "captura_estado" => $captura_estado]
        );

        if (isset($files)) {
            return respuesta::ok($files);
        } else {
            return respuesta::error("Ha ocurrido un error mientras se procesaba la consulta", 500);
        }

    }

    static public function listar_desde_padre($recepcion_id, $padre_id)
    {
        $padre = $this::where('file_id', $padre_id)
            ->where('file_tipo', 'd')
            ->where('file_estado', '!=', 0)
            ->first();

        if (isset($padre)) {
            //$valido = capturaController::validar_listar_captura($recepcion_id, $padre["file_captura_estado"]);
            //if ($valido["estado"]) {
            $files = DB::select(
                DB::raw("
                    select
                    f.file_id,
                    f.file_nombre,
                    f.file_tipo,
                    f.recepcion_id,
                    f.file_captura_estado,
                    f.file_padre_id,
                    r.recepcion_tipo,
                    c.captura_id,
                    c.captura_estado,
                    c.captura_estado_glb,
                    c.proyecto_id,
                    c.cliente_id,
                    c.tc_descripcion,
                    c.tc_id,
                    d.documento_id,
                    d.documento_nombre,
                    a.*
                    from files f
                    left join recepcion r on f.recepcion_id = r.recepcion_id
                    left join captura c on c.captura_file_id = f.file_id
                    left join documento d on d.captura_id = c.captura_id
                    left join adetalle a on a.adetalle_id = d.adetalle_id
                    where f.recepcion_id = :recepcion_id
                    and f.file_estado = 1
                    and f.file_padre_id = :padre_id
                    order by f.file_tipo,
                    case when c.captura_orden is null then c.captura_id else c.captura_orden end;"),
                ["recepcion_id" => $recepcion_id, "padre_id" => $padre_id]
            );

            if (isset($files)) {
                return respuesta::ok($files);
            } else {
                return respuesta::error("Ha ocurrido un error mientras se procesaba la consulta", 500);
            }
            // } else {
            //     return $valido;
            // }
        } else {
            return respuesta::error("El directorio padre indicado no ha sido encontrado.", 500);
        }
    }




}
