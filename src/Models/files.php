<?php

namespace Fedatario\Models;

use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\respuesta;
use App\Http\Controllers\capturaController;
use DB;

Trait files
{
    protected $primaryKey = "file_id";
    protected $table = "files";

    protected $fillable = ['recepcion_id', 'file_nombre', 'file_tipo', 'file_padre_id', 'file_captura_estado', 'file_captura_id', 'file_estado', 'file_usuario_id'];
    protected $post_require = ['recepcion_id', 'file_nombre', 'file_tipo', 'file_estado'];

    /**
     * Crea un file verificando los datos obligatorios
     * @param array $file_data Array asociativo de la informacion del nuevo file con elementos obligatorios[recepcion_id, file_nombre, padre_id, file_tipo]
     * @return respuesta Se retorna una respuesta {estado, mensaje, status, payload*}
     * @author Juan Ignacio Basilio Flores
     * @version v1.00.2
     */
    public function crear($file_data)
    {
        if (empty($file_data["file_estado"])) {
            $file_data["file_estado"] = 1;
        }
        if ($this->verificar_elementos($this->post_require, $file_data)) {
            $file = $this->create($file_data);
            if (isset($file)) {
                return respuesta::ok($file);
            } else {
                return respuesta::error("No se ha logrado crear el archivo.", 500);
            }
        } else {
            return respuesta::error("Parametros insuficientes para crear la captura o el directorio.", 428);
        }
    }

    /**
     * Realiza el movimiento de un elemento a otra jerarquia modificando el padre_id del elemento
     * @param int $file_id Identificador del file
     * @param int $padre_id Identificador del contenedor que tendra el elemento o null en caso tenga la mayor jerarquia
     * @return respuesta Se retorna una respuesta {estado, mensaje, status, payload*}
     * @author Juan Ignacio Basilio Flores
     * @version v1.00.2
     */
    public function mover_a_directorio($file_id, $padre_id)
    {
        $file = $this->find($file_id);
        $parent = $this::where('file_id', $padre_id)
            ->where('recepcion_id', $file["recepcion_id"])
            ->where('file_captura_estado', $file["file_captura_estado"])
            ->where('file_tipo', 'd')
            ->where('file_estado', '!=', 0)
            ->first();
        // Hacer consulta sql para verificar si el nuevo padre no pertenece al elemento
        if (isset($file) && isset($parent)) {
            if ($file["file_estado"] == 1) {
                $file->file_padre_id = $padre_id;
                $file->save();
                return respuesta::ok();
            } else {
                return respuesta::error("El elemento no esta activo, por tanto no puede moverse de directorio.", 500);
            }
        } else {
            return respuesta::error("Uno de los elementos no ha sido valido.", 500);
        }
    }

    /**
     * Realiza el movimiento de un elemento a la maxima jerarquia, la cual es la recepcion
     * @param int $file_id Identificador del file
     * @param int $recepcion_id Identificador de la recepción en la que esta el elemento
     * @param int $captura_estado Es el valor para identificar que tipo de captura es
     * @return respuesta Se retorna una respuesta {estado, mensaje, status, payload*}
     * @author Juan Ignacio Basilio Flores
     * @version v1.00.2
     */
    public function mover_a_recepcion($file_id, $recepcion_id, $captura_estado)
    {
        $file = $this::where('file_id', $file_id)
            ->where('recepcion_id', $recepcion_id)
            ->where('file_captura_estado', $captura_estado)
            ->where('file_estado', '!=', 0)
            ->first();

        // Hacer consulta sql para verificar si el nuevo padre no pertenece al elemento
        if (isset($file)) {
            $file->file_padre_id = null;
            $file->save();
            return respuesta::ok();
        } else {
            return respuesta::error("El elemento no es valido para moverse.", 500);
        }
    }

    /**
     * Modifica el nombre de un archivo o directorio
     * @param int $file_id Identificador del file
     * @param string $new_name Nuevo nombre para el archivo o directorio
     * @param string $file_tipo Tipo de directorio 'f' o 'd'
     * @return respuesta Se retorna una respuesta {estado, mensaje, status}
     * @author Juan Ignacio Basilio Flores
     * @version v1.00.2
     */
    public function renombrar($file_id, $new_name, $file_tipo = 'd')
    {
        $file = $this->find($file_id);
        // Hacer consulta sql para verificar si el nuevo padre no pertenece al elemento
        if (isset($file) && !empty($new_name)) {
            if ($file["file_estado"] == 1 && $file["file_tipo"] == $file_tipo) {
                $file->file_nombre = $new_name;
                $file->save();
                return respuesta::ok();
            } else {
                return respuesta::error("El elemento no esta activo o no coincide el tipo de archivo, por lo que no se puede renombrar el archivo o directorio.", 500);
            }
        } else {
            return respuesta::error("El elemento no existe.", 500);
        }
    }

    /**
     * Cambia el estado de un elemento a inactivo (borrado)
     * @param int $file_id Identificador del archivo
     * @return respuesta Se retorna una respuesta {estado, mensaje, status, payload*}
     * @author Juan Ignacio Basilio Flores
     * @version v1.00.1
     */
    public function borrar_archivo($file_id = 0)
    {
        $file = $this::where('file_id', $file_id)
            ->where('file_tipo', 'f')
            ->where('file_estado', '!=', 0)
            ->first();

        if (isset($file)) {
            $file["file_estado"] = 0;
            $file->save();
            return respuesta::ok();
        } else {
            return respuesta::ok(NULL, "El archivo no existe, no es necesario eliminarlo");
        }
    }

    /**
     * Cambia el estado de un elemento a inactivo (borrado)
     * @param int $file_id Identificador del directorio
     * @return respuesta Se retorna una respuesta {estado, mensaje, status, payload*}
     * @author Dango
     * @version v1.00.1
     */
    public function borrar_directorio($file_id)
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

    /**
     * Lista todos los elementos que contienen una recepcion en un tipo de captura
     * @param int $recepcion_id Identificador de la recepcion
     * @param int $captura_estado Identificador de la captura_estado de la captura
     * @return respuesta Se retorna una respuesta {estado, mensaje, status, payload*}
     * @author Juan Ignacio Basilio Flores
     * @version v1.00.1
     */
    public function listar_todo_desde_recepcion($recepcion_id, $captura_estado)
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
                a.*
                from files f
                left join recepcion r on f.recepcion_id = r.recepcion_id
                left join captura c on c.captura_file_id = f.file_id
                left join documento d on d.captura_id = c.captura_id
                left join adetalle a on a.adetalle_id = d.adetalle_id
                where f.recepcion_id = :recepcion_id
                and f.file_captura_estado= :captura_estado
                and f.file_estado = 1
                order by f.file_tipo,
                case when c.captura_orden is null then c.captura_id else c.captura_orden end;"),
            ["recepcion_id" => $recepcion_id, "captura_estado" => $captura_estado]
        );

        if (isset($files)) {
            return respuesta::ok($files);
        } else {
            return respuesta::error("Ha ocurrido un error mientras se procesaba la consulta", 500);
        }
        /*} else {
            return $valido;
        }*/
    }

    public function listar_todo_desde_recepcion_admin($recepcion_id, $captura_estado)
    {

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
                and f.file_captura_estado= :captura_estado
                and f.file_estado = 1
                order by f.file_tipo,
                case when c.captura_orden is null then c.captura_id else c.captura_orden end;"),
            ["recepcion_id" => $recepcion_id, "captura_estado" => $captura_estado]
        );

        if (isset($files)) {
            return respuesta::ok($files);
        } else {
            return respuesta::error("Ha ocurrido un error mientras se procesaba la consulta", 500);
        }
    }


    public function listar_todo_desde_recepcion_documento($recepcion_id, $captura_estado)
    {

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

    /**
     * Lista todos los elementos de mayor jerarquia que contienen una recepcion
     * @param int $recepcion_id Identificador de la recepcion
     * @param int $captura_estado Identificador de la captura_estado de la captura
     * @return respuesta Se retorna una respuesta {estado, mensaje, status, payload*}
     * @author Juan Ignacio Basilio Flores
     * @version v1.00.1
     */
    public function listar_desde_recepcion($recepcion_id, $captura_estado)
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
        //} else {
        //    return $valido;
        //}
    }

    /**
     * Lista todos los elementos que tengan un $padre_id
     * @param int $padre_id Identificador del padre de los elementos
     * @return respuesta Se retorna una respuesta {estado, mensaje, status, payload*}
     * @author Juan Ignacio Basilio Flores
     * @version v1.00.1
     */
    public function listar_desde_padre($recepcion_id, $padre_id)
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

    public function listar_desde_padre_admin($recepcion_id, $padre_id)
    {
        $padre = $this::where('file_id', $padre_id)
            ->where('file_tipo', 'd')
            ->where('file_estado', '!=', 0)
            ->first();

        if (isset($padre)) {

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
        } else {
            return respuesta::error("El directorio padre indicado no ha sido encontrado.", 500);
        }
    }

    public function listar_desde_padre_documento($recepcion_id, $padre_id)
    {
        $padre = $this::where('file_id', $padre_id)
            ->where('file_tipo', 'd')
            ->where('file_estado', '!=', 0)
            ->first();

        if (isset($padre)) {

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
        } else {
            return respuesta::error("El directorio padre indicado no ha sido encontrado.", 500);
        }
    }

    public function verificar_elementos($elementos, $array)
    {
        if (is_array($array)) {
            if (is_array($elementos)) {
                foreach ($elementos as $i => $elemento) {
                    if (empty($array[$elemento])) {
                        return false;
                    }
                }
            }
        } else {
            return false;
        }
        return true;
    }


    public function prueba_arbol($recepcion_ids, $ruta_salida, $nombre_file)
    {
        $array_variable = "{";
        $count = count($recepcion_ids);
        $contador = 0;
        foreach ($recepcion_ids as $key) {
            $contador++;
            if ($count === $contador) {
                $array_variable .= $key;
            } else {
                $array_variable .= $key . ",";
            }
        }
        $array_variable .= "}";

        $files = DB::select(
            DB::raw("
            with recursive archivos as(
                select
                    r.recepcion_id*-1 as file_id,
                    r.recepcion_nombre as file_nombre,
                    'r'::varchar as file_tipo,
                    0 as file_padre_id,
                    1 as nivel,
                    ARRAY[row_number()over(order by recepcion_id desc)-1] as posicion,
                    r.recepcion_id,
                    r.proyecto_id,
                    p.proyecto_nombre
                from recepcion r
                join proyecto p
                on r.proyecto_id = p.proyecto_id
                where recepcion_id =   ANY(:recepcion_id::INT[])
                --where recepcion_id =   ANY('{4,5}'::INT[])
                union all

                    select  a.file_id,
                           a.file_nombre,
                           a.file_tipo,
                           --a.file_padre_id,
                           case
                                when a.file_padre_id is null
                                    then a.recepcion_id*-1
                                    else a.file_padre_id
                                end
                                as file_padre_id
                           ,
                           b.nivel+1 as nivel,
                            --row_number() over(order by a.file_tipo)   ,
                            --row_number() over(partition by a.file_padre_id order by a.file_padre_id )-1   as posicion,
                            posicion||row_number() over(partition by
                                                        case
                                                            when a.file_padre_id is null
                                                                then a.recepcion_id*-1
                                                                else a.file_padre_id
                                                            end
                                                        --order by a.file_padre_id)-1 as posicion,
                                                        order by a.file_id)-1 as posicion,
                            a.recepcion_id,
                            b.proyecto_id,
                            ''::varchar(191)
                        from files a
                        join archivos b on
                            case
                                when a.file_padre_id is null
                                    then a.recepcion_id*-1
                                    else a.file_padre_id
                                end
                            = b.file_id
                        where a.file_captura_estado=1
            )
            select * from archivos
            --order by nivel,file_padre_id,posicion;
            order by nivel,posicion;
            "),
            ["recepcion_id" => $array_variable]
        );


        $array_final = [];

        //return $files;
        // return self::convert_to_posicion_string($files[10]->posicion);
        //$key=$query[$posicion_query]->nivel;

        self::ordenar($files, 0, $array_final, $ruta_salida, $nombre_file);

        $objectoFinal = (object)[];
        $objectoFinal->proyecto_id = $files[0]->proyecto_id;
        $objectoFinal->text = $files[0]->proyecto_nombre;
        $objectoFinal->children = $array_final;


        return $objectoFinal;
    }

    public function prueba_arbol_final($gmd_id, $ruta_salida, $nombre_file, $ruta_destino)
    {

        $files = DB::select(
            DB::raw("
            with recursive
            files_filtrado as (
                select
                f.file_id
                ,f.recepcion_id
                ,f.file_nombre
                ,f.file_tipo
                ,f.file_padre_id
                ,f.file_captura_estado
                ,f.file_estado
                ,f.file_usuario_id
                ,f.created_at
                ,f.updated_at
                from generacion_medio_detalle_captura a
                left join captura b on b.captura_id = a.captura_id
                left join files f on f.file_id = b.captura_file_id
                where a.gmd_id = :gmd_id

                union all

                select distinct
                f_padre.file_id
                ,f_padre.recepcion_id
                ,f_padre.file_nombre
                ,f_padre.file_tipo
                ,f_padre.file_padre_id
                ,f_padre.file_captura_estado
                ,f_padre.file_estado
                ,f_padre.file_usuario_id
                ,f_padre.created_at
                ,f_padre.updated_at
                from files_filtrado a
                --left join files_filtrado b on b.file_id = a.file_padre_id
                join files f_padre on f_padre.file_id = a.file_padre_id
                --where b.file_id is null
            ),
            files_final as (
                select distinct * from files_filtrado
            ),
            archivos_inicial as(
                select
                    r.recepcion_id*-1 as file_id,
                    r.recepcion_nombre as file_nombre,
                    'r'::varchar as file_tipo,
                    0 as file_padre_id,
                    1 as nivel,
                    --ARRAY[row_number()over(order by recepcion_id desc)-1] as posicion,
                    ARRAY[row_number()over(order by recepcion_id )-1] as posicion,
                    r.recepcion_id,
                    r.proyecto_id,
                    p.proyecto_nombre
                from recepcion r
                join proyecto p
                on r.proyecto_id = p.proyecto_id
                where recepcion_id in
                    (
                        select distinct b.recepcion_id
                        from generacion_medio_detalle_captura a
                        join captura b on a.captura_id = b.captura_id
                        where a.gmd_id = :gmd_id
                    )

                union all

                select
                    a.recepcion_id*-1000,
                    'CALIBRADORAS Y ACTAS',
                    'd',
                    a.recepcion_id*-1,
                    2,
                    ARRAY[row_number()over(order by a.recepcion_id )-1,0] as posicion,
                    --array[0]::bigint[],
                    a.recepcion_id,
                    b.proyecto_id,
                    p.proyecto_nombre
                from generacion_medio_recepcion a
                left join recepcion b on a.recepcion_id = b.recepcion_id
                left join proyecto p on p.proyecto_id = b.proyecto_id
                where a.gmr_id in(
                    select distinct c.gmr_id
                    from generacion_medio_detalle_captura a
                    join captura b on a.captura_id = b.captura_id
                    join generacion_medio_recepcion c on b.recepcion_id = c.recepcion_id and a.gm_id = c.gm_id
                    where a.gmd_id = :gmd_id
                )

            ),
            archivos as (
                select * from archivos_inicial

                union all

                select  a.file_id,
                a.file_nombre,
                a.file_tipo,
                --a.file_padre_id,
                case
                        when a.file_padre_id is null and a.file_captura_estado=1
                            then a.recepcion_id*-1
                        when a.file_captura_estado!=1
                            then a.recepcion_id*-1000
                        else a.file_padre_id
                    end
                        as file_padre_id
                ,
                b.nivel+1 as nivel,
                    --row_number() over(order by a.file_tipo)   ,
                    --row_number() over(partition by a.file_padre_id order by a.file_padre_id )-1   as posicion,
                    posicion||row_number() over(partition by
                                                case
                                                    when a.file_padre_id is null and a.file_captura_estado=1
                                                        then a.recepcion_id*-1
                                                    when a.file_captura_estado!=1
                                                        then a.recepcion_id*-1000
                                                    else a.file_padre_id
                                                end
                                                --order by a.file_padre_id)-1 as posicion,
                                                order by a.file_id)
                                                    - case when a.file_padre_id is null and a.file_captura_estado=1 then 0 else 1 end
                                                --solo resto 0 para el primer nivel de recepcion por que tendrá carpeta de calibradora y acta
                                                as posicion,
                    a.recepcion_id,
                    b.proyecto_id,
                    ''::varchar(191)
                --from files a
                from files_final a
                join archivos b on
                    case
                        when a.file_padre_id is null and a.file_captura_estado=1
                            then a.recepcion_id*-1
                        when a.file_captura_estado!=1
                            then a.recepcion_id*-1000
                        else a.file_padre_id
                        end
                    = b.file_id
                --where a.file_captura_estado=1
            )
            select * from archivos
            --order by nivel,file_padre_id,posicion;
            order by nivel,posicion;
            "),
            ["gmd_id" => $gmd_id]
        );


        $array_final = [];

        //return $files;
        // return self::convert_to_posicion_string($files[10]->posicion);
        //$key=$query[$posicion_query]->nivel;

        self::ordenar($files, 0, $array_final, $ruta_salida, $nombre_file, $ruta_destino);

        $objectoFinal = (object)[];
        $objectoFinal->proyecto_id = $files[0]->proyecto_id;
        $objectoFinal->text = $files[0]->proyecto_nombre;
        $objectoFinal->children = $array_final;


        return $objectoFinal;
    }

    public function ordenar($query, $posicion_query, &$array_final, $ruta_salida, $nombre_file, $ruta_destino)
    {

        $posicion = $query[$posicion_query]->posicion;

        $objecto = (object)[];

        $file_tipo_doc = $query[$posicion_query]->file_tipo;
        $objecto->text = $query[$posicion_query]->file_nombre;
        $objecto->recepcion_id = $query[$posicion_query]->recepcion_id;
        if(!empty($query[$posicion_query]->plantilla_id)){
            $objecto->plantilla_file = $ruta_destino . 'plantilla_' . $query[$posicion_query]->plantilla_id . '.json';
        }

        if ($file_tipo_doc == 'd') {
            $objecto->file_id = $query[$posicion_query]->file_id;
            $objecto->file_tipo = $query[$posicion_query]->file_tipo;
            $objecto->children = [];
            //$objecto->file_data =  $ruta_salida . $nombre_file . $query[$posicion_query]->recepcion_id . '.json';
            $objecto->file_data = $ruta_destino . $nombre_file . $query[$posicion_query]->recepcion_id . '.json';
        } else if ($file_tipo_doc == 'f') {
            $objecto->file_id = $query[$posicion_query]->file_id;
            $objecto->file_tipo = $query[$posicion_query]->file_tipo;
            $objecto->icon = 'fa fa-file';
            //$objecto->file_data = $ruta_salida . $nombre_file . $query[$posicion_query]->recepcion_id . '.json';
            $objecto->file_data = $ruta_destino . $nombre_file . $query[$posicion_query]->recepcion_id . '.json';
        }


        $codigo_incial = ('$array_final' . (self::convert_to_posicion_string($posicion)) . '[]=$objecto;');

        eval($codigo_incial);

        if (count($query) > $posicion_query + 1) {

            self::ordenar($query, $posicion_query + 1, $array_final, $ruta_salida, $nombre_file, $ruta_destino);
        }
    }

    public function convert_to_posicion_string($str_posicion)
    {
        $posiciones = explode(",", str_replace(["{", "}"], "", $str_posicion));
        //return $posiciones;
        $resultado = '';
        for ($i = 0; $i < count($posiciones) - 1; $i++) {
            $resultado = $resultado . ('[' . $posiciones[$i] . ']->children');
        }
        return $resultado;
    }

    public function file_json($recepcion_ids)
    {
        $files = DB::select(
            DB::raw("
            WITH cabecera AS(
                select
                 distinct
                case
                   when fi.file_tipo = 'd'
                   then  jsonb_agg(fi_hijos.file_id) over (partition by fi.file_id)
                   else
                   null
                   end  AS children,
                   fi.file_id,
                       fi.file_nombre,
                       pro.proyecto_id,
                       re.recepcion_id,
                       fi.file_tipo,
                       fi.file_padre_id,
                       ca.captura_id,
                       ca.captura_estado,
                       doc.documento_id,
                       pla.plantilla_nombre,
                       ad.adetalle_nombre,
                       ad.adetalle_url ,
                       ad.adetalle_peso

                       from proyecto pro
                       join plantilla pla
                       on pla.plantilla_id= pro.plantilla_id
                       left join recepcion re
                       on pro.proyecto_id= re.proyecto_id
                       left join files fi
                       on fi.recepcion_id = re.recepcion_id
                       left join captura  ca
                       on ca.captura_file_id = fi.file_id
                       left join documento doc
                       on doc.captura_id= ca.captura_id
                       left join adetalle ad
                       on ad.adetalle_id = doc.adetalle_id
                       --join para sacar hijos
                       left join files fi_hijos
                           on fi_hijos.file_padre_id = fi.file_id
                       where re.recepcion_id = :recepcion_id
                       and fi.file_captura_estado = 1
                ),
                datos as (
                   select distinct
                       ca.file_nombre,
                       ca.proyecto_id,
                       ca.recepcion_id,
                       ca.file_id,
                       ca.file_tipo,
                       ca.file_padre_id,
                       ca.captura_id,
                       ca.captura_estado,
                       ca.documento_id,
                       ca.plantilla_nombre,
                       ca.adetalle_nombre,
                       ca.adetalle_url,
                       ca.adetalle_peso,

                       jsonb_agg(
                           case when res.respuesta_id is null
                            then null
                           else
                               json_build_object(
                               'respuesta_id', res.respuesta_id,
                               'opcion_id', res.opcion_id,
                               'combo_id', res.combo_id,
                               'elemento_id', res.elemento_id,
                               'elemento_tipo', res.elemento_tipo,
                               'plantilla_id', res.plantilla_id,
                               'valor', res.valor,
                               'indizacion_id', res.indizacion_id,
                               'conca_id', res.conca_id,
                               'simple_tipo_dato', s.simple_tipo_dato,
                               'simple_tipo_formato', s.simple_tipo_formato,
                               'elemento_nombre', e.elemento_nombre
                       )
                       end)
                       over (partition by ca.file_id) AS items ,
                       ca.children
                       from cabecera ca  left join indizacion ind
                       on ca.captura_id = ind.captura_id
                       left join  respuesta res
                       on res.indizacion_id = ind.indizacion_id
                       left join elemento e
                       on e.elemento_id = res.elemento_id
                       left join simple s
                       on s.elemento_id = res.elemento_id
                ),
                datos2 as (
                   select distinct
                       ca.file_nombre,
                       ca.proyecto_id,
                       ca.recepcion_id,
                       ca.file_id,
                       ca.file_tipo,
                       ca.file_padre_id,
                       ca.captura_id,
                       ca.captura_estado,
                       ca.documento_id,
                       ca.plantilla_nombre,
                       case
                            when ca.items = '[null]'::jsonb
                                then '[]'::jsonb
                            else
                                ca.items
                       end as items,
                       ca.adetalle_nombre,
                       ca.adetalle_url as file_name,
                       ca.adetalle_peso,
                       jsonb_agg(im.imagen_url)
                       over (partition by ca.file_id) AS ruta,
                       ca.children
                       from datos ca  left join imagen im
                       on  ca.captura_id = im.captura_id
                )
                select
                       file_nombre,
                       proyecto_id,
                       recepcion_id,
                       'visor/database/recepcion_'||(recepcion_id::varchar(10))||'.json' as file_data,
                       file_id,
                       file_tipo,
                       file_padre_id,
                       captura_id,
                       captura_estado,
                       documento_id,
                       plantilla_nombre,
                       items,
                       adetalle_nombre,
                       file_name,
                       adetalle_peso,
                       children,
                case
                    when ruta = '[null]'::jsonb
                        then '[]'::jsonb
                    else
                        ruta
                end as ruta
                from datos2;
             "),
            ["recepcion_id" => $recepcion_ids]
        );
        return $files;
    }

    public function file_json_final($recepcion_ids, $gmd_id)
    {
        $files = DB::select(
            DB::raw("
            with recursive
            files_filtrado as (
                select
                f.file_id
                ,f.recepcion_id
                ,f.file_nombre
                ,f.file_tipo
                --,f.file_padre_id
                ,case when f.file_captura_estado != 1 then f.recepcion_id*-1000
                else f.file_padre_id end as file_padre_id
                ,f.file_captura_estado
                ,f.file_estado
                ,f.file_usuario_id
                ,f.created_at
                ,f.updated_at
                from generacion_medio_detalle_captura a
                left join captura b on b.captura_id = a.captura_id
                left join files f on f.file_id = b.captura_file_id
                where a.gmd_id = :gmd_id

                union all

                select distinct
                f_padre.file_id
                ,f_padre.recepcion_id
                ,f_padre.file_nombre
                ,f_padre.file_tipo
                ,f_padre.file_padre_id
                ,f_padre.file_captura_estado
                ,f_padre.file_estado
                ,f_padre.file_usuario_id
                ,f_padre.created_at
                ,f_padre.updated_at
                from files_filtrado a
                --left join files_filtrado b on b.file_id = a.file_padre_id
                join files f_padre on f_padre.file_id = a.file_padre_id
                --where b.file_id is null
            ),
            files_final as (
                select distinct * from files_filtrado
                            union all
                select distinct
                    a.recepcion_id*-1000,
                    a.recepcion_id,
                    'CALIBRADORAS Y ACTAS',
                    'd',
                    null::int,--a.recepcion_id*-1,
                    1,
                    1,
                    1,
                    now(),
                    now()
                from generacion_medio_recepcion a
                --left join recepcion b on a.recepcion_id = b.recepcion_id
                where a.gmr_id in(
                    select distinct c.gmr_id
                    from generacion_medio_detalle_captura a
                    join captura b on a.captura_id = b.captura_id
                    join generacion_medio_recepcion c on b.recepcion_id = c.recepcion_id and a.gm_id = c.gm_id
                    where a.gmd_id = :gmd_id
                )
            ),
            cabecera AS(
                select
                distinct
                case
                when fi.file_tipo = 'd'
                --then  jsonb_agg(fi_hijos.file_id) over (partition by fi.file_id)--v1.0
                --then  jsonb_agg(fi_hijos.file_id) over (partition by fi_hijos.file_padre_id) --v1.1
                then  array_agg(fi_hijos.file_id) over (partition by fi_hijos.file_padre_id) --v1.2
                else
                null
                end  AS children,
                fi.file_id,
                    fi.file_nombre,
                    pro.proyecto_id,
                    re.recepcion_id,
                    fi.file_tipo,
                    fi.file_padre_id,
                    ca.captura_id,
                    ca.captura_estado,
                    doc.documento_id,
                    pla.plantilla_nombre,
                    ad.adetalle_nombre,
                    ad.adetalle_url ,
                    ad.adetalle_peso
                    ,p.persona_nombre||' '||p.persona_apellido as usuario_nombre
                    ,to_char(ca.created_at,'DD/MM/YYYY - HH24:MI:SS') as created_at
                    from proyecto pro
                    join plantilla pla
                    on pla.plantilla_id= pro.plantilla_id
                    join recepcion re
                    on pro.proyecto_id= re.proyecto_id
                    join files_final fi
                    on fi.recepcion_id = re.recepcion_id
                    left join captura  ca
                    on ca.captura_file_id = fi.file_id
                    left join documento doc
                    on doc.captura_id= ca.captura_id
                    left join adetalle ad
                    on ad.adetalle_id = doc.adetalle_id
                    --join para sacar hijos
                    --left join files_final fi_hijos
                    left join (select * from files_final x order by (case when x.file_padre_id is null then 0 else x.file_padre_id end),x.file_id)  fi_hijos --cambiooo
                        on fi_hijos.file_padre_id = fi.file_id
                    --join para obtener el nombre del usuario
                    left join persona p
                        on ca.usuario_creador = p.usuario_id
                    where
                    --fi.file_captura_estado = 1 and
                    re.recepcion_id = :recepcion_id
            ),
            datos_pre as (
                select distinct
                    ca.file_nombre,
                    ca.proyecto_id,
                    ca.recepcion_id,
                    ca.file_id,
                    ca.file_tipo,
                    ca.file_padre_id,
                    ca.captura_id,
                    ca.captura_estado,
                    ca.documento_id,
                    ca.plantilla_nombre,
                    ca.adetalle_nombre,
                    ca.adetalle_url,
                    ca.adetalle_peso,
                    ca.usuario_nombre,
                    ca.created_at,
                    --jsonb_agg(
                        case when res.respuesta_id is null
                            then null
                        else
                            json_build_object(
                            'respuesta_id', res.respuesta_id,
                            'opcion_id', res.opcion_id,
                            'combo_id', res.combo_id,
                            'elemento_id', res.elemento_id,
                            'elemento_tipo', res.elemento_tipo,
                            'plantilla_id', res.plantilla_id,
                            'valor', res.valor,
                            'indizacion_id', res.indizacion_id,
                            'conca_id', res.conca_id,
                            'simple_tipo_dato', s.simple_tipo_dato,
                            'simple_tipo_formato', s.simple_tipo_formato,
                            'elemento_nombre', e.elemento_nombre,
                            'opcion_nombre', o.opcion_nombre
                        )::jsonb
                    end as item
                    --)
                    --over (partition by ca.file_id) AS items
                    --,ca.children
                    ,array_to_json(array(select unnest(ca.children) order by 1 ))::jsonb as children
                    ,res.elemento_id
                    from cabecera ca  left join indizacion ind
                    on ca.captura_id = ind.captura_id
                    left join  respuesta res
                    on res.indizacion_id = ind.indizacion_id
                    left join elemento e
                    on e.elemento_id = res.elemento_id
                    left join simple s
                    on s.elemento_id = res.elemento_id
                    left join opcion o
                    on o.opcion_id = res.opcion_id
            ),
            datos as(
                select distinct
                a.file_nombre,
                a.proyecto_id,
                a.recepcion_id,
                a.file_id,
                a.file_tipo,
                a.file_padre_id,
                a.captura_id,
                a.captura_estado,
                a.documento_id,
                a.plantilla_nombre,
                a.adetalle_nombre,
                a.adetalle_url,
                a.adetalle_peso,
                a.usuario_nombre,
                a.created_at,
                jsonb_agg(
                    a.item
                )
                over(partition by a.file_id)
                as items
                ,a.children
                from
                    (select * from datos_pre order by file_id,elemento_id) a
            ),
            datos2 as (
            select distinct
                ca.file_nombre,
                ca.proyecto_id,
                ca.recepcion_id,
                ca.file_id,
                ca.file_tipo,
                ca.file_padre_id,
                ca.captura_id,
                ca.captura_estado,
                ca.documento_id,
                ca.plantilla_nombre,
                ca.usuario_nombre,
                ca.created_at,
                case
                        when ca.items = '[null]'::jsonb
                            then '[]'::jsonb
                        else
                            ca.items
                end as items,
                ca.adetalle_nombre,
                --ca.adetalle_url as file_name,
                --p.proyecto_nombre||'/'||gmd.gmd_nombre||
                '/'||gmrd.gmrd_ruta||ca.adetalle_nombre as file_name,
                -----

                ca.adetalle_peso,
                jsonb_agg(
                    --im.imagen_url
                    --p.proyecto_nombre||'/'||gmd.gmd_nombre||
                    '/visor/componentes/'||im.imagen_nombre
                    )
                over (partition by ca.file_id) AS ruta,
                ca.children
                from datos ca  left join imagen im
                on  ca.captura_id = im.captura_id
                left join proyecto p
                    on p.proyecto_id = ca.proyecto_id
                left join generacion_medio_ruta_destino gmrd
                    on gmrd.captura_estado = ca.captura_estado
                left join generacion_medio_detalle gmd
                    on gmd.gmd_id = :gmd_id
            )
            select
                file_nombre,
                proyecto_id,
                recepcion_id,
                'visor/database/recepcion_'||(recepcion_id::varchar(10))||'.json' as file_data,
                file_id,
                file_tipo,
                file_padre_id,
                captura_id,
                captura_estado,
                documento_id,
                plantilla_nombre,
                items,
                adetalle_nombre,
                file_name,
                usuario_nombre,
                created_at,
                adetalle_peso,
                children,
            case
                when ruta = '[null]'::jsonb
                    then '[]'::jsonb
                else
                    ruta
            end as ruta
            from datos2;
            "),
            ["gmd_id" => $gmd_id, "recepcion_id" => $recepcion_ids]
        );
        return $files;
    }

    public function obtener_files_recepcion($ids, $ruta, $nombre)
    {

        $arrays = [];
        foreach ($ids as $key => $id) {
            $array = self::file_json($id);

            $obj = (object)[];
            foreach ($array as $value) {
                $key = $value->recepcion_id . "_" . $value->file_id;
                $json_items = json_decode($value->items);
                $json_rutas = json_decode($value->ruta);
                $json_children = json_decode($value->children);
                $value->items = $json_items;
                $value->ruta = $json_rutas;
                $value->children = $json_children;
                $obj->$key = $value;
            }
            array_push($arrays, $array);
            $this->guardar_file_json($ruta, $nombre . $id . '.json', $obj);
        }
        $this->guardar_file_json($ruta, 'all_files.json', $arrays);
    }

    public function obtener_files_recepcion_final($ids, $ruta, $nombre, $gmd_id)
    {
        $rutas = [];
        $arrays = [];
        foreach ($ids as $key => $id) {
            $array = self::file_json_final($id, $gmd_id);

            $obj = (object)[];
            foreach ($array as $value) {
                $key = $value->recepcion_id . "_" . $value->file_id;
                $json_items = json_decode($value->items);
                $json_rutas = json_decode($value->ruta);
                $json_children = json_decode($value->children);
                $value->items = $json_items;
                $value->ruta = $json_rutas;
                $value->children = $json_children;
                $obj->$key = $value;
            }
            array_push($arrays, $array);
            $ruta_guardada = $this->guardar_file_json($ruta, $nombre . $id . '_'.$gmd_id.'.json', $obj);
            $rutas[] = $ruta_guardada;
        }
        $ruta_all_files = $this->guardar_file_json($ruta, 'all_files_'.$gmd_id.'.json', $arrays);
        $rutas[] = $ruta_all_files;
        return $rutas;
    }


    public function guardar_file_json($directorio, $nombre, $lista)
    {

        try {
            //Convert updated array to JSON
            $jsondata = json_encode($lista, JSON_UNESCAPED_SLASHES);

            //write json data into data.json file
            if (file_put_contents($directorio . $nombre, $jsondata)) {
                //echo 'Data successfully saved';
                return $directorio . $nombre;
            } else {
            }
            //echo "error";
        } catch (Exception $e) {
            //echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        return '';
    }

    public function exec()
    {
        $ids = [6, 7, 8];
        $ruta_salida = "storage/";
        $nombre_file = "main.json";
        $nombre_file_hijo = "recepcion_";
        $array = $this->prueba_arbol($ids, $ruta_salida, $nombre_file_hijo);
        $this->guardar_file_json($ruta_salida, $nombre_file, [$array]);
        return $this->obtener_files_recepcion($ids, $ruta_salida, $nombre_file_hijo);
    }


    function copiar_archivos($array_rutas, $path_salida)
    {
        $directorio = storage_path() . "/app/";

        foreach ($array_rutas as $key) {

            $ruta = $this->get_ruta(array($key));
            if ($ruta) {
                copy($directorio . $key, $path_salida . $key);
            }
        }
    }

    function existe_carpeta($path)
    {

        //mkdir($root.$proyecto["text"], 0777, true))
        return is_dir($path) || mkdir($path, 0777, true);
    }

    public function get_ruta($path)
    {
        // $path =  "documentos/proyecto 123/Simple 02/imagenes/cLoQzY2zz1cO42GIg2jqHOi05l4rLanE1wmdHFMj_1.jpg";
        $pos = strrpos($path, "/");
        $nueva_ruta = substr($path, 0, $pos);
        return $this->existe_carpeta($nueva_ruta);
    }

    public function json_plantilla($gmd_id)
    {
        $files = DB::select(
            DB::raw("
            with combo_opciones as (
                select
                    opcion_id,
                    opcion_nombre,
                    co.combo_id,
                    co.elemento_id
                from combo co
                left join opcion op
                on co.combo_id = op.combo_id
            )select distinct
            jsonb_agg(
                    json_build_object(
                    --'respuesta_id', res.respuesta_id,
                    --'opcion_id', res.opcion_id,
                    'combo_id', c.combo_id,
                    'elemento_id', e.elemento_id,
                    'elemento_tipo', e.elemento_tipo,
                    'plantilla_id', e.plantilla_id,
                    --'valor', res.valor,
                    --'indizacion_id', res.indizacion_id,
                    'simple_tipo_dato', s.simple_tipo_dato,
                    'simple_tipo_formato', s.simple_tipo_formato,
                    'elemento_nombre', e.elemento_nombre,
                    'opcion',
                    CASE
                          WHEN c.combo_id is null THEN null
                          ELSE  (select jsonb_agg(json_build_object(
                                'opcion_nombre',comop.opcion_nombre,
                                'opcion_id',comop.opcion_id))
                                from combo_opciones as comop)
                          END
                )
            )
            over (partition by a.plantilla_id) AS items
            from generacion_medio_detalle gmd
            join generacion_medio_recepcion gmr
                on gmr.gm_id = gmd.gm_id
            join recepcion r
                on r.recepcion_id = gmr.recepcion_id
            join proyecto a
                on a.proyecto_id = r.proyecto_id
            left join elemento e
            on e.plantilla_id = a.plantilla_id
            --subconsultas para ordenar la data y que los nulls
            left join
                (select * from combo order by elemento_id) c
            on e.elemento_id = c.elemento_id
            left join
                (select * from simple order by elemento_id) s
            on e.elemento_id = s.elemento_id
            where gmd.gmd_id = :gmd_id;
             "),
            ["gmd_id" => $gmd_id]
        )[0]->items;
        return $files;
    }

    public function json_ocr_captura($gmd_id)
    {

         $files = DB::select(
             DB::raw("
             select distinct
            cap.captura_id,
            json_build_object(
                'ocr_contenido', ocr.ocr_contenido,
                'ocr_pagina', ocr.ocr_pagina,
                'ocr_total_paginas', ocr.ocr_total_paginas
            )::jsonb as ocr

            from generacion_medio_detalle gmd
            join generacion_medio_recepcion gmr
                on gmr.gm_id = gmd.gm_id
            join recepcion r
                on r.recepcion_id = gmr.recepcion_id
            join proyecto a
                on a.proyecto_id = r.proyecto_id
            left join captura cap
                on cap.recepcion_id = r.recepcion_id
            left join ocr
                on ocr. captura_id = cap.captura_id
            where gmd.gmd_id = :gmd_id;
             "),
             ["gmd_id" => $gmd_id]
         );
        return $files;
    }



    public function podergreen($gm_id,$path){

        //$path = storage_path() . "/app/documentos/";
        $ruta_inicio = $path;
        $ruta_salida = $ruta_inicio;
        $ruta_destino  =  "/database/";
        $nombre_file = "main.json";
        $nombre_file_hijo = "recepcion_";



        //$rutas_extra = $this->generar_json($gmd_id, $ruta_inicio, "/visor/database/");

        //------------------arbol
        $array = $this->prueba_arbol_final_gmcompleto($gm_id, $ruta_salida, $nombre_file_hijo, $ruta_destino);

        $ruta_main = $this->guardar_file_json($ruta_salida, $nombre_file, [$array]);

        //------------------recepcion

        $ids = ['1'];

        $rutas = $this->obtener_files_recepcion_final_gmcompleto($ids, $ruta_salida, $nombre_file_hijo, $gm_id);

        $rutas[] = $ruta_main;

        //$json_plantilla = $modelo_file->json_plantilla($gmd_id);

        //$ruta_json_plantilla = $modelo_file->guardar_file_json($ruta_salida, 'plantilla.json', $json_plantilla);

        //$rutas[] = $ruta_json_plantilla;

        return $rutas;
    }
    //inicio--------------------------------------------------------------------------------------------------------------------------------------------
    // ruta destino  =  "/visor/database/"
    public function prueba_arbol_final_gmcompleto($gm_id, $ruta_salida, $nombre_file, $ruta_destino)
    {

        $files = DB::select(
            DB::raw("
            with recursive
            --falta validacion cuando la plantilla es null
            files_filtrado as (
                select
                f.file_id
                ,f.recepcion_id
                ,f.file_nombre
                ,f.file_tipo
                ,f.file_padre_id
                ,f.file_captura_estado
                ,f.file_estado
                ,f.file_usuario_id
                ,f.created_at
                ,f.updated_at
                ,p.plantilla_id
                from generacion_medio_detalle_captura a
                left join captura b on b.captura_id = a.captura_id
                left join files f on f.file_id = b.captura_file_id
                left join proyecto p on p.proyecto_id = b.proyecto_id
                --where a.gmd_id = 13--:gmd_id ----------------
                where a.gm_id = :gm_id

                union all

                select distinct
                f_padre.file_id
                ,f_padre.recepcion_id
                ,f_padre.file_nombre
                ,f_padre.file_tipo
                ,f_padre.file_padre_id
                ,f_padre.file_captura_estado
                ,f_padre.file_estado
                ,f_padre.file_usuario_id
                ,f_padre.created_at
                ,f_padre.updated_at
                ,p.plantilla_id
                from files_filtrado a
                --left join files_filtrado b on b.file_id = a.file_padre_id
                join files f_padre on f_padre.file_id = a.file_padre_id
                --podria usar la misma plantilla que el hijo ya que son el mismo proyecto...
                --pero lo dejo asi por si fuera a atender a varios proyectos a futuro
                left join recepcion r on f_padre.recepcion_id = r.recepcion_id
                left join proyecto p on p.proyecto_id = r.proyecto_id
                --where b.file_id is null
            ),
            files_final as (
                select distinct * from files_filtrado
            ),
            archivos_inicial as(
                select
                    r.recepcion_id*-1 as file_id,
                    r.recepcion_nombre as file_nombre,
                    'r'::varchar as file_tipo,
                    0 as file_padre_id,
                    1 as nivel,
                    --ARRAY[row_number()over(order by recepcion_id desc)-1] as posicion,
                    ARRAY[row_number()over(order by recepcion_id )-1] as posicion,
                    r.recepcion_id,
                    r.proyecto_id,
                    p.proyecto_nombre,
                    p.plantilla_id
                from recepcion r
                join proyecto p
                on r.proyecto_id = p.proyecto_id
                where recepcion_id in
                    (
                        select distinct b.recepcion_id
                        from generacion_medio_detalle_captura a
                        join captura b on a.captura_id = b.captura_id
                        --where a.gmd_id = 13--:gmd_id-------------------------
                        where a.gm_id = :gm_id
                    )

                union all

                select
                    a.recepcion_id*-1000,
                    'CALIBRADORAS Y ACTAS',
                    'd',
                    a.recepcion_id*-1,
                    2,
                    ARRAY[row_number()over(order by a.recepcion_id )-1,0] as posicion,
                    --array[0]::bigint[],
                    a.recepcion_id,
                    b.proyecto_id,
                    p.proyecto_nombre,
                    p.plantilla_id
                from generacion_medio_recepcion a
                left join recepcion b on a.recepcion_id = b.recepcion_id
                left join proyecto p on p.proyecto_id = b.proyecto_id
                where a.gmr_id in(
                    select distinct c.gmr_id
                    from generacion_medio_detalle_captura a
                    join captura b on a.captura_id = b.captura_id
                    join generacion_medio_recepcion c on b.recepcion_id = c.recepcion_id and a.gm_id = c.gm_id
                    --where a.gmd_id = 13--:gmd_id--------------------
                    where a.gm_id = :gm_id
                )

            ),
            archivos as (
                select * from archivos_inicial

                union all

                select  a.file_id,
                a.file_nombre,
                a.file_tipo,
                --a.file_padre_id,
                case
                        when a.file_padre_id is null and a.file_captura_estado=1
                            then a.recepcion_id*-1
                        when a.file_captura_estado!=1
                            then a.recepcion_id*-1000
                        else a.file_padre_id
                    end
                        as file_padre_id
                ,
                b.nivel+1 as nivel,
                    --row_number() over(order by a.file_tipo)   ,
                    --row_number() over(partition by a.file_padre_id order by a.file_padre_id )-1   as posicion,
                    posicion||row_number() over(partition by
                                                case
                                                    when a.file_padre_id is null and a.file_captura_estado=1
                                                        then a.recepcion_id*-1
                                                    when a.file_captura_estado!=1
                                                        then a.recepcion_id*-1000
                                                    else a.file_padre_id
                                                end
                                                --order by a.file_padre_id)-1 as posicion,
                                                order by a.file_id)
                                                    - case when a.file_padre_id is null and a.file_captura_estado=1 then 0 else 1 end
                                                --solo resto 0 para el primer nivel de recepcion por que tendrá carpeta de calibradora y acta
                                                as posicion,
                    a.recepcion_id,
                    b.proyecto_id,
                    ''::varchar(191),
                    a.plantilla_id
                --from files a
                from files_final a
                join archivos b on
                    case
                        when a.file_padre_id is null and a.file_captura_estado=1
                            then a.recepcion_id*-1
                        when a.file_captura_estado!=1
                            then a.recepcion_id*-1000
                        else a.file_padre_id
                        end
                    = b.file_id
                --where a.file_captura_estado=1
            )
            select * from archivos
            --order by nivel,file_padre_id,posicion;
            order by nivel,posicion;
            "),
            ["gm_id" => $gm_id]
        );


        $array_final = [];

        //return $files;
        // return self::convert_to_posicion_string($files[10]->posicion);
        //$key=$query[$posicion_query]->nivel;

        self::ordenar($files, 0, $array_final, $ruta_salida, $nombre_file, $ruta_destino);

        $objectoFinal = (object)[];
        $objectoFinal->proyecto_id = $files[0]->proyecto_id;
        $objectoFinal->text = $files[0]->proyecto_nombre;
        $objectoFinal->children = $array_final;


        return $objectoFinal;
    }

    //inicio 2--------------------------------------------------------------------------------------------------------------------------------------------

    public function file_json_final_gmcompleto($recepcion_ids, $gm_id)
    {
        $files = DB::select(
            DB::raw("
            with recursive
            paginas as (
                select
                a.captura_id
                ,c.captura_file_id
                ,count(b.imagen_id) as cant_paginas
                from generacion_medio_detalle_captura a
                left join captura c on c.captura_id = a.captura_id
                left join imagen b on b.captura_id = a.captura_id and b.imagen_estado = 1
                ------------------------------------------------------------------------
                where a.gm_id = :gm_id
                group by a.captura_id,c.captura_file_id
            ),
            files_filtrado as (
                select
                f.file_id
                ,f.recepcion_id
                ,f.file_nombre
                ,f.file_tipo
                --,f.file_padre_id
                ,case when f.file_captura_estado != 1 then f.recepcion_id*-1000
                else f.file_padre_id end as file_padre_id
                ,f.file_captura_estado
                ,f.file_estado
                ,f.file_usuario_id
                ,f.created_at
                ,f.updated_at
                --,p.cant_paginas
                from generacion_medio_detalle_captura a
                left join captura b on b.captura_id = a.captura_id
                left join files f on f.file_id = b.captura_file_id
                --para cantidad de imagenes
                --left join paginas p on p.captura_id = a.captura_id
                --where a.gmd_id = 13----------------------
                where a.gm_id = :gm_id

                union all

                select distinct
                f_padre.file_id
                ,f_padre.recepcion_id
                ,f_padre.file_nombre
                ,f_padre.file_tipo
                ,f_padre.file_padre_id
                ,f_padre.file_captura_estado
                ,f_padre.file_estado
                ,f_padre.file_usuario_id
                ,f_padre.created_at
                ,f_padre.updated_at
                --,p.cant_paginas
                from files_filtrado a
                --left join files_filtrado b on b.file_id = a.file_padre_id
                join files f_padre on f_padre.file_id = a.file_padre_id
                --left join paginas p on p.captura_file_id = f_padre.file_id
                --where b.file_id is null
            ),
            files_final as (
                select distinct * from files_filtrado
                            union all
                select distinct
                    a.recepcion_id*-1000,
                    a.recepcion_id,
                    'CALIBRADORAS Y ACTAS',
                    'd',
                    null::int,--a.recepcion_id*-1,
                    1,
                    1,
                    1,
                    now(),
                    now()
                    --,0
                from generacion_medio_recepcion a
                --left join recepcion b on a.recepcion_id = b.recepcion_id
                where a.gmr_id in(
                    select distinct c.gmr_id
                    from generacion_medio_detalle_captura a
                    join captura b on a.captura_id = b.captura_id
                    join generacion_medio_recepcion c on b.recepcion_id = c.recepcion_id and a.gm_id = c.gm_id
                    --where a.gmd_id = 13--:gmd_id-------------------------
                    where a.gm_id = :gm_id
                )
            ),
            cabecera AS(
                select
                distinct
                case
                when fi.file_tipo = 'd'
                --then  jsonb_agg(fi_hijos.file_id) over (partition by fi.file_id)
                then  jsonb_agg(fi_hijos.file_id) over (partition by fi_hijos.file_padre_id)
                else
                null
                end  AS children,
                fi.file_id,
                    fi.file_nombre,
                    pro.proyecto_id,
                    re.recepcion_id,
                    fi.file_tipo,
                    fi.file_padre_id,
                    ca.captura_id,
                    ca.captura_estado,
                    doc.documento_id,
                    pla.plantilla_nombre,
                    ad.adetalle_nombre,
                    ad.adetalle_url ,
                    ad.adetalle_peso
                    ,p.persona_nombre||' '||p.persona_apellido as usuario_nombre
                    ,to_char(ca.created_at,'DD/MM/YYYY - HH24:MI:SS') as created_at
                    from proyecto pro
                    join plantilla pla
                    on pla.plantilla_id= pro.plantilla_id
                    join recepcion re
                    on pro.proyecto_id= re.proyecto_id
                    join files_final fi
                    on fi.recepcion_id = re.recepcion_id
                    left join captura  ca
                    on ca.captura_file_id = fi.file_id
                    left join documento doc
                    on doc.captura_id= ca.captura_id
                    left join adetalle ad
                    on ad.adetalle_id = doc.adetalle_id
                    --join para sacar hijos
                    --left join files_final fi_hijos
                    left join (select * from files_final x order by (case when x.file_padre_id is null then 0 else x.file_padre_id end),x.file_id)  fi_hijos --cambiooo
                        on fi_hijos.file_padre_id = fi.file_id
                    --join para obtener el nombre del usuario
                    left join persona p
                        on ca.usuario_creador = p.usuario_id
                    where
                    --fi.file_captura_estado = 1 and
                    re.recepcion_id = :recepcion_id---------------------------------------------------
            ),
            datos_pre as (
                select distinct
                    ca.file_nombre,
                    ca.proyecto_id,
                    ca.recepcion_id,
                    ca.file_id,
                    ca.file_tipo,
                    ca.file_padre_id,
                    ca.captura_id,
                    ca.captura_estado,
                    ca.documento_id,
                    ca.plantilla_nombre,
                    ca.adetalle_nombre,
                    ca.adetalle_url,
                    ca.adetalle_peso,
                    ca.usuario_nombre,
                    ca.created_at,
                    --jsonb_agg(
                        case when res.respuesta_id is null
                            then null
                        else
                            json_build_object(
                            'respuesta_id', res.respuesta_id,
                            'opcion_id', res.opcion_id,
                            'combo_id', res.combo_id,
                            'elemento_id', res.elemento_id,
                            'elemento_tipo', res.elemento_tipo,
                            'plantilla_id', res.plantilla_id,
                            'valor', res.valor,
                            'indizacion_id', res.indizacion_id,
                            'conca_id', res.conca_id,
                            'simple_tipo_dato', s.simple_tipo_dato,
                            'simple_tipo_formato', s.simple_tipo_formato,
                            'elemento_nombre', e.elemento_nombre,
                            'opcion_nombre', o.opcion_nombre
                        )::jsonb
                    end as item
                    --)
                    --over (partition by ca.file_id) AS items
                    ,ca.children
                    ,res.elemento_id
                    from cabecera ca  left join indizacion ind
                    on ca.captura_id = ind.captura_id
                    left join  respuesta res
                    on res.indizacion_id = ind.indizacion_id
                    left join elemento e
                    on e.elemento_id = res.elemento_id
                    left join simple s
                    on s.elemento_id = res.elemento_id
                    left join opcion o
                    on o.opcion_id = res.opcion_id
            ),
            datos as(
                select distinct
                a.file_nombre,
                a.proyecto_id,
                a.recepcion_id,
                a.file_id,
                a.file_tipo,
                a.file_padre_id,
                a.captura_id,
                a.captura_estado,
                a.documento_id,
                a.plantilla_nombre,
                a.adetalle_nombre,
                a.adetalle_url,
                a.adetalle_peso,
                a.usuario_nombre,
                a.created_at,
                jsonb_agg(
                    a.item
                )
                over(partition by a.file_id)
                as items
                ,a.children
                from
                    (select * from datos_pre order by file_id,elemento_id) a
            ),
            datos2 as (
            select distinct
                ca.file_nombre,
                ca.proyecto_id,
                ca.recepcion_id,
                ca.file_id,
                ca.file_tipo,
                ca.file_padre_id,
                ca.captura_id,
                ca.captura_estado,
                ca.documento_id,
                ca.plantilla_nombre,
                ca.usuario_nombre,
                ca.created_at,
                case
                        when ca.items = '[null]'::jsonb
                            then '[]'::jsonb
                        else
                            ca.items
                end as items,
                ca.adetalle_nombre,
                --ca.adetalle_url as file_name,
                --p.proyecto_nombre||'/'||gmd.gmd_nombre||
                --'/'||gmrd.gmrd_ruta||ca.adetalle_nombre as file_name,
                '/'||gmd.gmd_nombre||'/'||gmrd.gmrd_ruta||ca.adetalle_nombre as file_name,
                -----
                'database/json/'||gmd.gmd_nombre||'/'||replace(ca.adetalle_nombre,'.pdf','.json') as file_content,
                ca.adetalle_peso,
                /*
                jsonb_agg(
                    --im.imagen_url
                    --p.proyecto_nombre||'/'||gmd.gmd_nombre||
                    '/visor/componentes/'||im.imagen_nombre
                    )
                over (partition by ca.file_id) AS ruta,
                */
                '[]'::jsonb AS ruta,

                ca.children
                , case when pa.captura_id is null
                    then 0
                    else pa.cant_paginas end
                , p.plantilla_id
                from datos ca
                --left join imagen im
                --on  ca.captura_id = im.captura_id
                left join proyecto p
                    on p.proyecto_id = ca.proyecto_id
                left join generacion_medio_ruta_destino gmrd
                    on gmrd.captura_estado = ca.captura_estado
                left join generacion_medio_detalle_captura gmdc
                    on gmdc.captura_id = ca.captura_id
                left join generacion_medio_detalle gmd
                    --on gmd.gmd_id = 13--:gmd_id------------------------------------------------------------------------
                    --on gmd.gm_id = :gm_id
                    on gmd.gmd_id = gmdc.gmd_id
                left join paginas pa
                    on pa.captura_id=gmdc.captura_id
            )
            select
                file_nombre,
                proyecto_id,
                recepcion_id,
                --'visor/database/recepcion_'||(recepcion_id::varchar(10))||'.json' as file_data,
                'database/recepcion_'||(recepcion_id::varchar(10))||'.json' as file_data,
                file_content,
                file_id,
                file_tipo,
                file_padre_id,
                captura_id,
                captura_estado,
                documento_id,
                plantilla_nombre,
                items,
                adetalle_nombre,
                file_name,
                usuario_nombre,
                created_at,
                adetalle_peso,
                children,
            case
                when ruta = '[null]'::jsonb
                    then '[]'::jsonb
                else
                    ruta
            end as ruta
                ,cant_paginas
                --,plantilla_id
                ,'database/plantilla_'||(plantilla_id::varchar(10))||'.json' as plantilla_file
            from datos2;
            "),
            ["gm_id" => $gm_id, "recepcion_id" => $recepcion_ids]
        );
        return $files;
    }

    //inicio 3--------------------------------------------------------------------------------------------------------------------------------------------



    public static function arbol_json_alone($gm_id)
    {

        $files = DB::select(
            DB::raw("
            with recursive
            --falta validacion cuando la plantilla es null
            files_filtrado as (
                select
                f.file_id
                ,f.recepcion_id
                ,f.file_nombre
                ,f.file_tipo
                ,f.file_padre_id
                ,f.file_captura_estado
                ,f.file_estado
                ,f.file_usuario_id
                ,f.created_at
                ,f.updated_at
                ,p.plantilla_id
                from generacion_medio_detalle_captura a
                left join captura b on b.captura_id = a.captura_id
                left join files f on f.file_id = b.captura_file_id
                left join proyecto p on p.proyecto_id = b.proyecto_id
                --where a.gmd_id = 13--:gmd_id ----------------
                where a.gm_id = :gm_id

                union all

                select distinct
                f_padre.file_id
                ,f_padre.recepcion_id
                ,f_padre.file_nombre
                ,f_padre.file_tipo
                ,f_padre.file_padre_id
                ,f_padre.file_captura_estado
                ,f_padre.file_estado
                ,f_padre.file_usuario_id
                ,f_padre.created_at
                ,f_padre.updated_at
                ,p.plantilla_id
                from files_filtrado a
                --left join files_filtrado b on b.file_id = a.file_padre_id
                join files f_padre on f_padre.file_id = a.file_padre_id
                --podria usar la misma plantilla que el hijo ya que son el mismo proyecto...
                --pero lo dejo asi por si fuera a atender a varios proyectos a futuro
                left join recepcion r on f_padre.recepcion_id = r.recepcion_id
                left join proyecto p on p.proyecto_id = r.proyecto_id
                --where b.file_id is null
            ),
            files_final as (
                select distinct * from files_filtrado
            ),
            archivos_inicial as(
                select
                    r.recepcion_id*-1 as file_id,
                    r.recepcion_nombre as file_nombre,
                    'r'::varchar as file_tipo,
                    0 as file_padre_id,
                    1 as nivel,
                    --ARRAY[row_number()over(order by recepcion_id desc)-1] as posicion,
                    ARRAY[row_number()over(order by recepcion_id )-1] as posicion,
                    r.recepcion_id,
                    r.proyecto_id,
                    p.proyecto_nombre,
                    p.plantilla_id
                from recepcion r
                join proyecto p
                on r.proyecto_id = p.proyecto_id
                where recepcion_id in
                    (
                        select distinct b.recepcion_id
                        from generacion_medio_detalle_captura a
                        join captura b on a.captura_id = b.captura_id
                        --where a.gmd_id = 13--:gmd_id-------------------------
                        where a.gm_id = :gm_id
                    )

                union all

                select
                    a.recepcion_id*-1000,
                    'CALIBRADORAS Y ACTAS',
                    'd',
                    a.recepcion_id*-1,
                    2,
                    ARRAY[row_number()over(order by a.recepcion_id )-1,0] as posicion,
                    --array[0]::bigint[],
                    a.recepcion_id,
                    b.proyecto_id,
                    p.proyecto_nombre,
                    p.plantilla_id
                from generacion_medio_recepcion a
                left join recepcion b on a.recepcion_id = b.recepcion_id
                left join proyecto p on p.proyecto_id = b.proyecto_id
                where a.gmr_id in(
                    select distinct c.gmr_id
                    from generacion_medio_detalle_captura a
                    join captura b on a.captura_id = b.captura_id
                    join generacion_medio_recepcion c on b.recepcion_id = c.recepcion_id and a.gm_id = c.gm_id
                    --where a.gmd_id = 13--:gmd_id--------------------
                    where a.gm_id = :gm_id
                )

            ),
            archivos as (
                select * from archivos_inicial

                union all

                select  a.file_id,
                a.file_nombre,
                a.file_tipo,
                --a.file_padre_id,
                case
                        when a.file_padre_id is null and a.file_captura_estado=1
                            then a.recepcion_id*-1
                        when a.file_captura_estado!=1
                            then a.recepcion_id*-1000
                        else a.file_padre_id
                    end
                        as file_padre_id
                ,
                b.nivel+1 as nivel,
                    --row_number() over(order by a.file_tipo)   ,
                    --row_number() over(partition by a.file_padre_id order by a.file_padre_id )-1   as posicion,
                    posicion||row_number() over(partition by
                                                case
                                                    when a.file_padre_id is null and a.file_captura_estado=1
                                                        then a.recepcion_id*-1
                                                    when a.file_captura_estado!=1
                                                        then a.recepcion_id*-1000
                                                    else a.file_padre_id
                                                end
                                                --order by a.file_padre_id)-1 as posicion,
                                                order by a.file_id)
                                                    - case when a.file_padre_id is null and a.file_captura_estado=1 then 0 else 1 end
                                                --solo resto 0 para el primer nivel de recepcion por que tendrá carpeta de calibradora y acta
                                                as posicion,
                    a.recepcion_id,
                    b.proyecto_id,
                    ''::varchar(191),
                    a.plantilla_id
                --from files a
                from files_final a
                join archivos b on
                    case
                        when a.file_padre_id is null and a.file_captura_estado=1
                            then a.recepcion_id*-1
                        when a.file_captura_estado!=1
                            then a.recepcion_id*-1000
                        else a.file_padre_id
                        end
                    = b.file_id
                --where a.file_captura_estado=1
            )
            select * from archivos
            --order by nivel,file_padre_id,posicion;
            order by nivel,posicion;
            "),
            ["gm_id" => $gm_id]
        );

        return $files;
    }

    public function prueba_arbol_final_gmcompleto_total($gm_id1,$gm_id2,$gm_id3, $ruta_salida, $nombre_file, $ruta_destino)
    {
        $files1 = self::arbol_json_alone($gm_id1);
        $files2 = self::arbol_json_alone($gm_id2);
        $files3 = self::arbol_json_alone($gm_id3);

        $array_final1 = [];
        $array_final2 = [];
        $array_final3 = [];

        self::ordenar($files1, 0, $array_final1, $ruta_salida, $nombre_file, $ruta_destino);
        self::ordenar($files2, 0, $array_final2, $ruta_salida, $nombre_file, $ruta_destino);
        self::ordenar($files3, 0, $array_final3, $ruta_salida, $nombre_file, $ruta_destino);

        $objectoFinal1 = (object)[];
        $objectoFinal1->proyecto_id = $files1[0]->proyecto_id;
        $objectoFinal1->text = $files1[0]->proyecto_nombre;
        $objectoFinal1->plantilla_file = $ruta_destino . 'plantilla_' . $files1[0]->plantilla_id . '.json';
        $objectoFinal1->children = $array_final1;
        

        $objectoFinal2 = (object)[];
        $objectoFinal2->proyecto_id = $files2[0]->proyecto_id;
        $objectoFinal2->text = $files2[0]->proyecto_nombre;
        $objectoFinal2->plantilla_file = $ruta_destino . 'plantilla_' . $files2[0]->plantilla_id . '.json';
        $objectoFinal2->children = $array_final2;

        $objectoFinal3 = (object)[];
        $objectoFinal3->proyecto_id = $files3[0]->proyecto_id;
        $objectoFinal3->text = $files3[0]->proyecto_nombre;
        $objectoFinal3->plantilla_file = $ruta_destino . 'plantilla_' . $files3[0]->plantilla_id . '.json';
        $objectoFinal3->children = $array_final3;


        return [$objectoFinal1,$objectoFinal2,$objectoFinal3];
    }

    public function podergreenv2($gm_id1 , $gm_id2  ,$gm_id3 ,   $path){

        //$path = storage_path() . "/app/documentos/";
        $ruta_inicio = $path;
        $ruta_salida = $ruta_inicio;
        $ruta_destino  =  "/database/";
        $nombre_file = "main.json";
        $nombre_file_hijo = "recepcion_";



        //$rutas_extra = $this->generar_json($gmd_id, $ruta_inicio, "/visor/database/");

        //------------------arbol
        $array_arbol = $this->prueba_arbol_final_gmcompleto_total($gm_id1,$gm_id2,$gm_id3, $ruta_salida, $nombre_file_hijo, $ruta_destino);

        $ruta_main = $this->guardar_file_json($ruta_salida, $nombre_file, $array_arbol);

        //------------------recepcion

        $ids = ['1','24','29'];
        $gm_ids = [$gm_id1,$gm_id2,$gm_id3];
        $rutas = $this->obtener_files_recepcion_final_gmcompleto_total($ids, $ruta_salida, $nombre_file_hijo, $gm_ids);

        $rutas[] = $ruta_main;

        //$json_plantilla = $modelo_file->json_plantilla($gmd_id);

        //$ruta_json_plantilla = $modelo_file->guardar_file_json($ruta_salida, 'plantilla.json', $json_plantilla);

        //$rutas[] = $ruta_json_plantilla;

        return $rutas;
    }

    public function obtener_files_recepcion_final_gmcompleto_total($ids, $ruta, $nombre, $gm_ids)
    {
        $rutas = [];
        $arrays = [];
        $cont = 0;
        foreach ($ids as $key => $id) {
            $gm_id = $gm_ids[$cont++];
            $array = self::file_json_final_gmcompleto($id, $gm_id);

            $obj = (object)[];
            foreach ($array as $value) {
                $key = $value->recepcion_id . "_" . $value->file_id;
                $json_items = json_decode($value->items);
                $json_rutas = json_decode($value->ruta);
                $json_children = json_decode($value->children);
                $value->items = $json_items;
                $value->ruta = $json_rutas;
                $value->children = $json_children;
                $obj->$key = $value;
            }
            array_push($arrays, $array);
            $ruta_guardada = $this->guardar_file_json($ruta, $nombre . $id . '.json', $obj);
            $rutas[] = $ruta_guardada;
        }
        $ruta_all_files = $this->guardar_file_json($ruta, 'all_files.json', $arrays);
        $rutas[] = $ruta_all_files;
        return $rutas;
    }

    static public function borrar_file_contenido($file_id)
    {
        if(empty($file_id)){
            return respuesta::error("Parametros incorrectos");
        }

        $files = DB::select(
            DB::raw("
            with recursive
            files_modulo as (
                select 
                1 as grupo
                ,f.file_nombre
                ,f.file_id
                ,f.file_padre_id
                ,f.file_tipo
                from files f
                where f.file_id = :file_id
                
                union all
                
                select 
                f_padre.grupo+1
                ,f.file_nombre
                ,f.file_id
                ,f.file_padre_id
                ,f.file_tipo
                from files_modulo f_padre
                join files f on f.file_padre_id = f_padre.file_id
                --where f_padre.file_tipo ='d'
            ),
            captura_modulo as (
                select
                c.captura_id,
                c.captura_file_id
                from captura c
                join files_modulo fm
                on fm.file_id = c.captura_file_id
            ),
            documento_modulo as (
                select 
                d.documento_id,
                d.captura_id,
                d.adetalle_id
                from documento d
                join captura_modulo cm on d.captura_id = cm.captura_id
            ),
            documento_ocr_modulo as (
                select
                docr.captura_id,
                docr.documento_ocr_id,
                docr.file_id
                from documento_ocr docr
                join captura_modulo cm on docr.captura_id = cm.captura_id
            ),
            documentos_filtrados_modulo as (
                select
                df.captura_id,
                df.id,
                df.documento_id
                from documentos_filtrados df
                join captura_modulo cm on df.captura_id = cm.captura_id
            ),
            imagen_modulo as (
                select
                i.captura_id,
                i.documento_id,
                i.imagen_id
                from imagen i
                join captura_modulo cm on i.captura_id = cm.captura_id
            ),
            adetalle_modulo as (
                select
                ad.adetalle_id
                from adetalle ad
                join documento_modulo dm
                on ad.adetalle_id = dm.adetalle_id
            ),
            indizacion_modulo as (
                select 
                i.indizacion_id,
                i.captura_id
                from indizacion i
                join captura_modulo cm
                on cm.captura_id = i.captura_id
            ),
            respuesta_modulo as (
                select
                r.indizacion_id,
                r.respuesta_id
                from respuesta r
                join indizacion_modulo im
                on r.indizacion_id = im.indizacion_id
            ),
            control_calidad_modulo as (
                select
                cc.captura_id,
                cc.cc_id,
                cc.indizacion_id
                from control_calidad cc
                join captura_modulo cm
                on cc.captura_id = cm.captura_id
            ),
            incidencia_indizacion_modulo as (
                select
                ixi.incidencia_indizacion_id,
                ixi.indizacion_id
                from incidencia_indizacion ixi
                join indizacion_modulo im
                on ixi.indizacion_id = im.indizacion_id
            ),
            incidencia_captura_modulo as (
                select
                ic.captura_id,
                ic.incidencia_captura_id
                from incidencia_captura ic
                join captura_modulo cm
                on ic.captura_id = cm.captura_id
            ),
            incidencia_imagen_modulo as (
                select
                ii.imagen_id,
                ii.incidencia_id,
                ii.incidencia_imagen_id
                from incidencia_imagen ii
                join imagen_modulo im
                on im.imagen_id = ii.imagen_id
            ),
            fedatario_modulo as (
                select
                f.captura_id,
                f.fedatario_id,
                f.indizacion_id
                from fedatario f
                join captura_modulo cm
                on f.captura_id = cm.captura_id
            ),
            fedatario_firmar_modulo as (
                select
                ff.captura_id,
                ff.fedatario_id,
                ff.fedatario_firmar_id,
                ff.indizacion_id
                from fedatario_firmar ff
                join captura_modulo cm
                on ff.captura_id = cm.captura_id
            ),
            generacion_medio_detalle_captura_modulo as (
                select
                gmdc.captura_id,
                gmdc.gm_id,
                gmdc.gmd_id,
                gmdc.gmd_grupo
                from generacion_medio_detalle_captura gmdc
                join captura_modulo cm
                on gmdc.captura_id = cm.captura_id
            ),
            proyecto_captura_flujo_modulo as (
                select
                pcf.captura_id,
                pcf.proyecto_captura_flujo_id
                from proyecto_captura_flujo pcf
                join captura_modulo cm
                on cm.captura_id = pcf.captura_id
            ),
            ocr_modulo as (
                select
                ocr.captura_id,
                ocr.imagen_id,
                ocr.ocr_id
                from ocr
                join captura_modulo cm
                on ocr.captura_id = cm.captura_id
            ),
            -- **********************************************
            -- EMPEZAMOS A ELIMINAR
            -- Eliminamos los files que coinciden
            delete_files_modulo as (
                delete from files f
                using files_modulo fm
                where f.file_id = fm.file_id
                returning fm.file_id
            ),
            -- Eliminamos las capturas que coinciden
            delete_captura_modulo as (
                delete from captura c
                using captura_modulo cm
                where c.captura_id = cm.captura_id
                returning cm.captura_id
            ),
            -- Eliminamos los documentos que coinciden
            delete_documento_modulo as (
                delete from documento d
                using documento_modulo dm
                where d.documento_id = dm.documento_id
                returning dm.documento_id
            ),
            -- Eliminamos los documentos ocr que coinciden
            delete_documento_ocr_modulo as (
                delete from documento_ocr docr
                using documento_ocr_modulo docrm
                where docr.captura_id = docrm.captura_id
                returning docrm.captura_id
            ),
            -- Eliminamos los documentos filtrados que coinciden
            delete_documentos_filtrados_modulo as (
                delete from documentos_filtrados df
                using documentos_filtrados_modulo dfm
                where df.captura_id = dfm.captura_id
                returning dfm.captura_id
            ),
            -- Eliminamos los las imagenes que coinciden
            delete_imagen_modulo as (
                delete from imagen i
                using imagen_modulo im
                where i.captura_id = im.captura_id
                returning im.captura_id
            ),
            -- Eliminamos los adetalle que coinciden
            delete_adetalle_modulo as (
                delete from adetalle a
                using adetalle_modulo am
                where a.adetalle_id = am.adetalle_id
                returning am.adetalle_id
            ),
            -- Eliminamos las indizaciones que coinciden
            delete_indizacion_modulo as (
                delete from indizacion i
                using indizacion_modulo im
                where i.captura_id = im.captura_id
                returning im.captura_id
            ),
            -- Eliminamos las respuestas que coinciden
            delete_respuesta_modulo as (
                delete from respuesta r
                using respuesta_modulo rm
                where r.indizacion_id = rm.indizacion_id
                returning rm.indizacion_id
            ),
            -- Eliminando los registros de control de calidad que coinciden
            delete_control_calidad_modulo as (
                delete from control_calidad cc
                using control_calidad_modulo ccm
                where cc.captura_id = ccm.captura_id
                returning ccm.captura_id
            ),
            -- Eliminando los registros de incidencia indizacion que coinciden
            delete_incidencia_indizacion_modulo as (
                delete from incidencia_indizacion ii
                using incidencia_indizacion_modulo iim
                where ii.indizacion_id = iim.indizacion_id
                returning iim.indizacion_id
            ),
            -- Eliminando los registros de incidencia captura que coinciden 
            delete_incidencia_captura_modulo as (
                delete from incidencia_captura ic
                using incidencia_captura_modulo icm
                where ic.captura_id = icm.captura_id
                returning ic.captura_id
            ),
            -- Eliminando los registros de incidencia imagen que coinciden
            delete_incidencia_imagen_modulo as (
                delete from incidencia_imagen ii
                using incidencia_imagen_modulo iim
                where ii.imagen_id = iim.imagen_id
                returning iim.imagen_id
            ),
            -- Eliminando los registros de fedatario modulo que coinciden
            delete_fedatario_modulo as (
                delete from fedatario f
                using fedatario_modulo fm
                where f.captura_id = fm.captura_id
                returning fm.captura_id
            ),
            -- Eliminando los registros de firma fedatario que coinciden
            delete_fedatario_firmar_modulo as (
                delete from fedatario_firmar ff
                using fedatario_firmar_modulo ffm
                where ff.captura_id = ffm.captura_id
                returning ffm.captura_id
            ),
            -- Eliminando los registros de generacion medio detalle captura que coinciden
            delete_generacion_medio_detalle_captura_modulo as (
                delete from generacion_medio_detalle_captura gmdc
                using generacion_medio_detalle_captura_modulo gmdcm
                where gmdc.captura_id = gmdcm.captura_id
                returning gmdcm.captura_id
            ),
            -- Eliminando los registros de proyecto captura flujo que coinciden
            delete_proyecto_captura_flujo_modulo as (
                delete from proyecto_captura_flujo pcf
                using proyecto_captura_flujo_modulo pcfm
                where pcf.captura_id = pcfm.captura_id
                returning pcfm.captura_id
            ),
            -- Eliminando los registros de ocr que coinciden
            delete_ocr_modulo as (
                delete from ocr
                using ocr_modulo om
                where ocr.captura_id = om.captura_id
                returning om.captura_id
            )
            select * from files_modulo;
            "),
            ["file_id" => $file_id]
        );

        if (isset($files)) {
            return respuesta::ok();
        } else {
            return respuesta::error("Ocurrio un problema al intenta borrar los elementos relacionados al archivo.", 500);
        }
    }

}
