<?php

namespace Fedatario\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use DateTime;
use App;
use Imagick;

Trait incidencia_imagen
{
    protected $primaryKey = "incidencia_imagen_id";
    protected $table = "incidencia_imagen";

    public function arbol_reproceso()
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

    /*
    Parametros requeridos para las dos funciones
        arrayObjImagen : arreglo de las imagenes
        recepcion_id
        captura_id
        documento_id
        imagen_pagina
 */
    public function insertarImagenAntes(
        $arrayObjImagen,
        $recepcion_id,
        $captura_id,
        $documento_id,
        $numero_pagina

    )
    {
        //obtener los registros que seran actualizados
        try {

            DB::beginTransaction();

            $datos = DB::select(
                "select * from imagen
                          where recepcion_id= :recepcion_id
                          and captura_id= :captura_id
                          and documento_id= :documento_id
                          and imagen_pagina::int >= :imagen_pagina
                          and imagen_estado = 1
                          order by imagen_pagina::int asc
                          ",
                [
                    "recepcion_id" => $recepcion_id,
                    "captura_id" => $captura_id,
                    "documento_id" => $documento_id,
                    "imagen_pagina" => $numero_pagina
                ]

            );


            $countLista = count($arrayObjImagen);
            $last_pagina = $numero_pagina + $countLista;
            //actualizar cada  registro obtenido con su nuevo numero de pagina
            $query = "";
            $query2 = "";
            $query3 = "";
            $contador_imagen = 0;

            $imagen_nombre = "";
            foreach ($datos as $key) {
                $now = new DateTime();
                if ($contador_imagen == 0) {
                    $imagen_generica = $key->imagen_id;
                }
                $contador_imagen++;
                $imagen_id = $key->imagen_id;
                $imagen_nombre = $key->imagen_nombre;
                $query .= "update imagen
            set imagen_pagina = $last_pagina
            where  recepcion_id= $recepcion_id
            and captura_id= $captura_id
            and documento_id= $documento_id
            and imagen_id=$imagen_id;";
                $last_pagina++;
            }

            $incidencia_imagen = new App\incidencia_imagen();

            $datos_incidencia = $incidencia_imagen
                ->join('imagen', 'imagen.imagen_id', 'incidencia_imagen.imagen_id')
                ->where('captura_id', '=', $captura_id)
                ->first();

            //registrar las nuevas imagenes con su pagina
            $imagenes = [];
            $imagen_estado = 1;
            $contador_inicio = 0;
            $cadena_texto_1 = $datos_incidencia->incidencia_id . ",";
            $cadena_texto_2 = ",'" . $datos_incidencia->tipo_asociado . "'," . $datos_incidencia->id_asociado . ",1,'" . $now->format('Y-m-d H:i:s') . "','" . $now->format('Y-m-d H:i:s') . "'";


            foreach ($arrayObjImagen as $cap) {

                $now = new DateTime();
                if ($contador_inicio != 0) {
                    $query3 .= ",";
                }

                $query3 .= "(" . $cap['recepcion_id'] . "," . $cap['captura_id'] . "," . $cap['documento_id'] . ",'" . $datos_incidencia->imagen_nombre . "'," . $numero_pagina . ",'" . $cap['imagen_url'] . "'," . $imagen_estado . ",'" . $now->format('Y-m-d H:i:s') . "','" . $now->format('Y-m-d H:i:s') . "')";
                $numero_pagina++;
                $contador_inicio++;

            }


            $query2 = "with insertados_prueba as (
                    insert into imagen(recepcion_id,captura_id,documento_id,imagen_nombre,imagen_pagina,imagen_url,imagen_estado,created_at
                    ,updated_at)
                    values
                    " . $query3 . "
                    returning imagen_id
                ),
                prueba2insert as (
                    insert into incidencia_imagen (incidencia_id,imagen_id,tipo_asociado,id_asociado,estado,created_at
                    ,updated_at)
                    select " . $cadena_texto_1 . "imagen_id" . $cadena_texto_2 . " from insertados_prueba
                    returning imagen_id
                )
                select * from prueba2insert;";

            DB::unprepared($query . $query2);


            DB::commit();
        } catch (\Exception $th) {

            DB::rollBack();
            throw $th;
        }
    }


    public function insertarImagenDespues(
        $arrayObjImagen,
        $recepcion_id,
        $captura_id,
        $documento_id,
        $numero_pagina

    )
    {
        //obtener los registros que seran actualizados

        try {
            DB::beginTransaction();

            $datos = DB::select(
                "select * from imagen
                          where recepcion_id= :recepcion_id
                          and captura_id= :captura_id
                          and documento_id= :documento_id
                          and imagen_pagina::int > :imagen_pagina
                          and imagen_estado = 1
                          order by imagen_pagina::int asc
                          ",
                [
                    "recepcion_id" => $recepcion_id,
                    "captura_id" => $captura_id,
                    "documento_id" => $documento_id,
                    "documento_id" => $documento_id,
                    "imagen_pagina" => $numero_pagina
                ]
            );

            $countLista = count($arrayObjImagen);
            $last_pagina = $numero_pagina + $countLista;
            //actualizar cada  registro obtenido con su nuevo numero de pagina
            $query = "";
            $query2 = "";
            $query3 = "";
            $contador_imagen = 0;
            $imagen_nombre = "";

            foreach ($datos as $key) {
                $last_pagina++;
                if ($contador_imagen == 0) {
                    $imagen_generica = $key->imagen_id;
                }
                $contador_imagen++;
                $imagen_id = $key->imagen_id;
                $imagen_nombre = $key->imagen_nombre;
                $query .= "update imagen
            set imagen_pagina = " . $last_pagina . "
            where  recepcion_id= " . $recepcion_id . "
            and captura_id= " . $captura_id . "
            and documento_id= " . $documento_id . "
            and imagen_id=" . $imagen_id . ";";

            }

            $incidencia_imagen = new App\incidencia_imagen();

            $datos_incidencia = $incidencia_imagen
                ->join('imagen', 'imagen.imagen_id', 'incidencia_imagen.imagen_id')
                ->where('captura_id', '=', $captura_id)
                ->first();
            $now = new DateTime();
            //registrar las nuevas imagenes con su pagina
            $imagenes = [];
            $imagen_estado = 1;
            $contador_inicio = 0;
            $cadena_texto_1 = $datos_incidencia->incidencia_id . ",";
            $cadena_texto_2 = ",'" . $datos_incidencia->tipo_asociado . "'," . $datos_incidencia->id_asociado . ",1,'" . $now->format('Y-m-d H:i:s') . "','" . $now->format('Y-m-d H:i:s') . "'";


            foreach ($arrayObjImagen as $cap) {

                $now = new DateTime();
                if ($contador_inicio != 0) {
                    $query3 .= ",";
                }

                $numero_pagina++;
                $query3 .= "(" . $cap['recepcion_id'] . "," . $cap['captura_id'] . "," . $cap['documento_id'] . ",'" . $datos_incidencia->imagen_nombre . "'," . $numero_pagina . ",'" . $cap['imagen_url'] . "'," . $imagen_estado . ",'" . $now->format('Y-m-d H:i:s') . "','" . $now->format('Y-m-d H:i:s') . "')";
                $contador_inicio++;

            }


            $query2 = "with insertados_prueba as (
                    insert into imagen(recepcion_id,captura_id,documento_id,imagen_nombre,imagen_pagina,imagen_url,imagen_estado,created_at
                    ,updated_at)
                    values
                    " . $query3 . "
                    returning imagen_id
                ),
                prueba2insert as (
                    insert into incidencia_imagen (incidencia_id,imagen_id,tipo_asociado,id_asociado,estado,created_at
                    ,updated_at)
                    select " . $cadena_texto_1 . "imagen_id" . $cadena_texto_2 . " from insertados_prueba
                    returning imagen_id
                )
                select * from prueba2insert;";


            DB::unprepared($query . $query2);


            DB::commit();
        } catch (\Exception $th) {

            DB::rollBack();
            throw $th;
        }


    }


    /*
    $objImagen -> el nuevo objeto imagen
    $imagen_id  -> id de la imagen a reemplazar
    */
    public function reemplazarImagen(
        $objImagen,
        $imagen_id
    )
    {
        try {
            DB::beginTransaction();
            $now = new DateTime();
            $imagen = new imagen();
            $imagen->recepcion_id = $objImagen->recepcion_id;
            $imagen->captura_id = $objImagen->captura_id;
            $imagen->documento_id = $objImagen->documento_id;

            $imagen->imagen_url = $objImagen->imagen_url;
            $imagen->imagen_pagina = $objImagen->imagen_pagina;
            $imagen->imagen_estado = $objImagen->imagen_estado;
            $imagen->created_at = $now;
            $imagen->updated_at = $now;

            $incidencia_imagen = new App\incidencia_imagen();

            $datos_incidencia = $incidencia_imagen
                ->join('imagen', 'imagen.imagen_id', 'incidencia_imagen.imagen_id')
                ->where('imagen.imagen_id', '=', $imagen_id)
                ->first();

            $imagenUpdate = imagen::where('imagen_id', '=', $imagen_id)->first();
            $imagenUpdate->imagen_estado = 0;
            $imagen->imagen_nombre = $imagenUpdate->imagen_nombre;
            $imagen->save();
            $imagenUpdate->save();

            $incidencia_imagen->incidencia_id = $datos_incidencia->incidencia_id;
            $incidencia_imagen->imagen_id = $imagen->imagen_id;
            $incidencia_imagen->tipo_asociado = $datos_incidencia->tipo_asociado;
            $incidencia_imagen->id_asociado = $datos_incidencia->id_asociado;
            $incidencia_imagen->estado = 1;
            $incidencia_imagen->save();

            DB::commit();
        } catch (\Exception $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function eliminarImagen(
        $imagen_id,
        $recepcion_id,
        $captura_id,
        $documento_id
    )
    {
        try {
            DB::beginTransaction();

            $imagenUpdate = imagen::where('imagen_id', '=', $imagen_id)->first();
            $imagenUpdate->imagen_estado = 0;
            $imagenUpdate->save();

            $datos = DB::select(
                "select * from imagen
                          where recepcion_id= :recepcion_id
                          and captura_id= :captura_id
                          and documento_id= :documento_id
                          and imagen_estado = 1
                          order by imagen_pagina::int asc
                          ",
                [
                    "recepcion_id" => $recepcion_id,
                    "captura_id" => $captura_id,
                    "documento_id" => $documento_id

                ]
            );

            $query = "";
            $last_pagina = 0;
            foreach ($datos as $key) {

                ++$last_pagina;
                $imagen_id = $key->imagen_id;
                $query .= "update imagen
                set imagen_pagina = $last_pagina
                where  recepcion_id= $recepcion_id
                and captura_id= $captura_id
                and documento_id= $documento_id
                and imagen_id=$imagen_id;";

            }

            DB::unprepared($query);
            DB::commit();
        } catch (\Exception $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function demo()
    {
        $imagen = new imagen();
        $imagen->recepcion_id = 2;
        $imagen->captura_id = 3;
        $imagen->documento_id = 3;
        $imagen->imagen_nombre = "vvvvvvv";
        $imagen->imagen_url = "trrrr";
        $imagen->imagen_pagina = 1;
        $imagen->imagen_estado = 1;
        //   self::reemplazarImagen($imagen, 3);

        $array[] = $imagen;
        $imagen2 = new imagen();
        $imagen2->recepcion_id = 2;
        $imagen2->captura_id = 3;
        $imagen2->documento_id = 3;
        $imagen2->imagen_nombre = "777";
        $imagen2->imagen_url = "565877";


        $array[] = $imagen2;


        return self::insertarImagenAntes($array, 2, 3, 3, 4);
    }


    public function experimental(
        $arrayObjImagen,
        $recepcion_id,
        $captura_id,
        $documento_id,
        $numero_pagina

    )
    {
        //obtener los registros que seran actualizados


        $datos = DB::select(
            "select * from imagen
                          where recepcion_id= :recepcion_id
                          and captura_id= :captura_id
                          and documento_id= :documento_id
                          and imagen_pagina::int >= :imagen_pagina
                          and imagen_estado = 1
                          order by imagen_pagina::int asc
                          ",
            [
                "recepcion_id" => $recepcion_id,
                "captura_id" => $captura_id,
                "documento_id" => $documento_id,
                "imagen_pagina" => $numero_pagina
            ]

        );
        // DB::statement('drop table users');
        $countLista = count($arrayObjImagen);
        $last_pagina = $numero_pagina + $countLista;
        //actualizar cada  registro obtenido con su nuevo numero de pagina
        $query = "";
        foreach ($datos as $key) {
            $imagen_id = $key->imagen_id;
            $query .= "update imagen
            set imagen_pagina = $last_pagina
            where  recepcion_id= $recepcion_id
            and captura_id= $captura_id
            and documento_id= $documento_id
            and imagen_id=$imagen_id;";
            $last_pagina++;
        }

        DB::unprepared($query);


        //registrar las nuevas imagenes con su pagina
        $imagenes = [];

        foreach ($arrayObjImagen as $cap) {
            $array_temporal = [];
            $now = new DateTime();
            $array_temporal['recepcion_id'] = $cap['recepcion_id'];
            $array_temporal['captura_id'] = $cap['captura_id'];
            $array_temporal['documento_id'] = $cap['documento_id'];
            $array_temporal['imagen_nombre'] = $cap['imagen_nombre'];
            $array_temporal['imagen_pagina'] = $numero_pagina;
            $array_temporal['imagen_url'] = $cap['imagen_url'];

            $array_temporal['imagen_estado'] = 1;
            $array_temporal['created_at'] = $now;
            $array_temporal['updated_at'] = $now;
            $imagenes[] = $array_temporal;
            $numero_pagina++;
        }
        return DB::table('imagen')->insert(
            $imagenes
        );
    }


    public function update_reproceso($documento_id)
    {

        return DB::update("
           update incidencia_imagen
            set estado = 2
            from incidencia_imagen a
            left join imagen b
                on a.imagen_id= b.imagen_id
            where b.documento_id = :documento_id
                and a.estado=1
                and incidencia_imagen.incidencia_imagen_id = a.incidencia_imagen_id
            returning incidencia_imagen.id_asociado, incidencia_imagen.tipo_asociado;
             ", ["documento_id" => $documento_id]);
    }

    public function update_tabla_asociada($id_asociado, $tipo_asociado, $captura_id)
    {

        $captura = new App\captura();


        switch ($tipo_asociado) {
            case 'cap':
                //pasa captura
                return DB::select("
                    UPDATE captura
                    SET captura_estado = 0, captura_estado_glb = 'cap'
                    WHERE captura_id = :id_asociado;
             ", ["id_asociado" => $id_asociado]);


                break;
            case 'ind':
                //pasa a indizacion

                $captura->where('captura_id', $captura_id)
                    ->update(['captura_estado_glb' => 'ind']);

                return DB::update("
                    UPDATE indizacion
                    SET indizacion_estado = 0
                    WHERE indizacion_id = :id_asociado
             ", ["id_asociado" => $id_asociado]);

                break;
            case 'cal':
                //pasa a control calidad

                $captura->where('captura_id', $captura_id)
                    ->update(['captura_estado_glb' => 'cal']);

                return DB::update("
                    UPDATE control_calidad
                    SET cc_estado = 0
                    WHERE cc_id = :id_asociado
             ", ["id_asociado" => $id_asociado]);

                break;

            case 'fed':
                //pasa a control calidad

                $captura->where('captura_id', $captura_id)
                    ->update(['captura_estado_glb' => 'fed']);

                return DB::update("
                    UPDATE fedatario
                    SET fedatario_estado = 0
                    WHERE fedatario_id = :id_asociado
             ", ["id_asociado" => $id_asociado]);

                break;

            default:

                break;
        }


    }

    //funcion
    public function imagesToPdf($arrayImagen, $rutaOutput)
    {
        $image = new Imagick($arrayImagen);
        $image->setImageFormat('pdf');
        $image->writeImages($rutaOutput, true);

        return $rutaOutput;
    }

    public function incidencia_imagen($imagen_id)
    {
        return DB::select("
                    select incidencia_nombre,imagen_id
                    from incidencia_imagen i_i
                    left join incidencia i on i_i.incidencia_id = i.incidencia_id
                    where estado = 1 and imagen_id = :imagen_id
             ", ["imagen_id" => $imagen_id]);
    }


}

