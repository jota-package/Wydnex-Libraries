<?php

namespace Fedatario\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App;
use DateTime;
use Request;

Trait fedatario_firmar
{

    protected $primaryKey = "fedatario_firmar_id";
    protected $table = "fedatario_firmar";

    public function crear_fedatario_firmar_from_fedatario($captura_id, $usuario_creador, $recepcion_id, $fedatario_id, $cc_id, $proyecto_id, $indizacion_id, $cliente_id)
    {

        return DB::insert("
            insert into fedatario_firmar(
                captura_id
                ,indizacion_id
                ,proyecto_id
                ,recepcion_id
                ,cc_id
                ,fedatario_id
                ,cliente_id
                ,fedatario_firmar_estado
                ,usuario_creador
                ,created_at
                ,updated_at
                )
                values (:captura_id,
                :indizacion_id,
                :proyecto_id,
                :recepcion_id,
                :cc_id,
                :fedatario_id,
                :cliente_id,
                0,
                :usuario_creador,
                now(),
                now()
                )", [
            "captura_id" => $captura_id, "recepcion_id" => $recepcion_id, "fedatario_id" => $fedatario_id, "cc_id" => $cc_id, "proyecto_id" => $proyecto_id, "indizacion_id" => $indizacion_id, "cliente_id" => $cliente_id, "usuario_creador" => $usuario_creador
        ]);
    }

    public function arbol_fedatario_firmar()
    {
        $is_admin = user::is_admin();
        if($is_admin){
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
        }else{
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
            ", ["usuario_id"=>$usuario_id]);
        }



    }

    public function crear_fedatario_firmar_inicial_from_captura($captura_id, $usuario_creador)
    {


        return DB::select(
            DB::raw("
                    insert into fedatario_firmar(
                        captura_id
                        ,proyecto_id
                        ,recepcion_id
                        ,cliente_id
                        ,fedatario_firmar_estado
                        ,usuario_creador

                        ,created_at
                        ,updated_at
                        )
                        select
                            a.captura_id,
                            a.proyecto_id,
                            a.recepcion_id,
                            a.cliente_id,
                            0,
                            :usuario_creador::int,--usuario creador
                            now(),
                            now()
                        from captura a
                        where a.captura_id=:captura_id
                        RETURNING fedatario_firmar_id;
                        "),
            ["usuario_creador" => $usuario_creador, "captura_id" => $captura_id]
        )[0]->fedatario_firmar_id;
    }


    public function crear_fedatario_firmar_inicial_from_fedatario_grupo($fedatario_id, $usuario_creador, $captura_estado_glb)

    {


        return DB::select(
            DB::raw("
                with
                insert_fedatario_firmar as (
                    insert into fedatario_firmar(
                        captura_id
                        ,proyecto_id
                        ,recepcion_id
                        ,cliente_id
                        ,fedatario_firmar_estado
                        ,usuario_creador
                        ,created_at
                        ,updated_at
                        )
                    select b.captura_id
                    ,b.proyecto_id
                    ,b.recepcion_id
                    ,b.cliente_id
                    ,0
                    ,:usuario_creador::int,--usuario creador
                    now(),
                    now()
                    from fedatario a
                    join fedatario b
                        on b.proyecto_id = a.proyecto_id
                        and b.recepcion_id= a.recepcion_id
                        and b.fedatario_grupo = a.fedatario_grupo
                        and b.fedatario_tipo = a.fedatario_tipo
                    where a.fedatario_id=:fedatario_id
                        RETURNING fedatario_firmar_id,captura_id
                ),
                updated_captura as (
                    update captura
                        set captura_estado_glb=:captura_estado_glb
                    from insert_fedatario_firmar a
                    where captura.captura_id = a.captura_id
                )
                select * from insert_fedatario_firmar;"),
            ["usuario_creador" => $usuario_creador, "fedatario_id" => $fedatario_id, "captura_estado_glb" => $captura_estado_glb]
        );
    }
    //part 1
    private  function actualizar_estado_firmar($ids, $estado)
    {
        // $ids = [1, 2, 3, 4, 5, 6];
        $operador = self::whereIn('fedatario_firmar_id', $ids)
            ->update(array('fedatario_firmar_estado' => $estado));

        return  $operador;
    }


    private function get_urls($ids)
    {
        $array_variable = "{";
        $count = count($ids);
        $contador = 0;
        foreach ($ids as $key) {
            $contador++;
            if ($count === $contador) {
                $array_variable .= $key;
            } else {
                $array_variable .= $key . ",";
            }
        }
        $array_variable .= "}";

        $datos = DB::select(
            "with data_ini as (
                select
                ad.adetalle_url,
                ad.adetalle_nombre,
                ff.fedatario_firmar_id,
                row_number() over(partition by ad.adetalle_nombre order by ff.fedatario_firmar_id) as correlativo
                from
                fedatario_firmar ff join captura c
                on ff.captura_id = c.captura_id
                join documento d
                on c.captura_id = d.captura_id
                join adetalle ad
                on d.adetalle_id = ad.adetalle_id
                where ff.fedatario_firmar_id  = ANY(:id::INT[])
            )
            select
                a.adetalle_url,
                case
                    when a.correlativo=1
                        then a.adetalle_nombre
                    else
                        a.adetalle_nombre||'-'||a.correlativo::varchar(4)||'/'||a.adetalle_nombre
                end as adetalle_nombre,
                case
                    when a.correlativo=1
                        then ''
                    else
                        a.adetalle_nombre||'-'||a.correlativo::varchar(4)
                end as subcarpeta,
                a.fedatario_firmar_id
                from data_ini a;
            ",
            [
                "id" => $array_variable
            ]
        );

        return $datos;
    }


    private function copiar_archivos($array_rutas, $rutaOutput)
    {
        $directorio = storage_path() . "/app/";
        foreach ($array_rutas as $key) {
            if($key->subcarpeta != ''){
                mkdir($rutaOutput . $key->subcarpeta,0777, true);
            }
            copy($directorio . $key->adetalle_url, $rutaOutput . $key->adetalle_nombre);
        }
    }

    // $ids -> array de ids
    // $out  -> directorio de salida
    public function iniciar_fedatario_firmar($ids, $estado, $out)
    {

        self::actualizar_estado_firmar($ids, $estado);
        $resultado =  self::get_urls($ids);
        self::copiar_archivos($resultado, $out);
        //grabada de log
        self::grabar_log_fedatario_firma($ids,0);

    }

    public function reg()
    {
        $ids = [1, 2];
        $out = "storage/";
        self::iniciar_fedatario_firmar($ids, 3, $out);
    }



    // part 2  (en desarrollo)


    private function get_fedatario_firmar($ids)
    {
        $array_variable = "{";
        $count = count($ids);
        $contador = 0;
        foreach ($ids as $key) {
            $contador++;
            if ($count === $contador) {
                $array_variable .= $key;
            } else {
                $array_variable .= $key . ",";
            }
        }
        $array_variable .= "}";

        $datos = DB::select(
            "select
        ad.adetalle_nombre,
        ff.fedatario_firmar_id,
        ff.proyecto_id,
        ad.adetalle_url
        from
        fedatario_firmar ff join captura c
        on ff.captura_id = c.captura_id
        join documento d
        on c.captura_id = d.captura_id
        join adetalle ad
        on d.adetalle_id = ad.adetalle_id
        where ff.fedatario_firmar_id  = ANY(:id::INT[])
        and ff.fedatario_firmar_estado = 3;",
            [
                "id" => $array_variable
            ]
        );

        return $datos;
    }



    public function   buscar_acrhivos_firmados($ids, $ruta_firmados, $ruta_sin_firmar, $ext)
    {

        $array_documentos = [];
        $array_firmados = self::get_fedatario_firmar($ids);
        $array_archivos_no_encontrados = [];
        $array_documentos_final = (object) [];
        foreach ($array_firmados as $key) {
            $array_file = [];
            $file_nombre = $key->adetalle_nombre;
            $id =  $key->fedatario_firmar_id;
            $proyecto_id = $key->proyecto_id;
            $documentos = (object) [];
            $documentos->id = $id;
            $documentos->proyecto_id = $proyecto_id;
            $exist = file_exists($ruta_firmados . $file_nombre);
            if ($exist) {
                $file = (object) [];
                $file->nombre_fake =  $file_nombre;
                $file->ruta_fake = $ruta_firmados . $file_nombre;
                $file->ruta_inicial = $ruta_sin_firmar . $file_nombre;
                $file->ruta_final =  self::get_ruta($key->adetalle_url) . $file->nombre_fake;
                $file->tipo_ext = "A";

                array_push($array_file, $file);

                // $fileName = pathinfo($file_nombre, PATHINFO_FILENAME);
                //$validar_esig = file_exists($ruta_firmados . $file_nombre . $ext);
                //$validar_esig = self::existe_archivo($ruta_firmados.$file_nombre,$ext);
                $res_existe_archivo = self::existe_archivo($ruta_firmados.$file_nombre,$ext);
                $validar_esig = (count($res_existe_archivo)>0);
                if ($validar_esig  && $ext!=".") {
                    $file = (object) [];
                    $file->nombre_fake =  $file_nombre . $ext;
                    //$file->$ruta_firmados  . $file_nombre . $ext;
                    $file->ruta_fake = $res_existe_archivo[0];
                    $file->tipo_ext = "B";
                    $file->ruta_final =  self::get_ruta($key->adetalle_url) . $file->nombre_fake;
                    $file->ruta_inicial = null;
                    array_push($array_file, $file);
                }

                $documentos->files = $array_file;
                array_push($array_documentos, $documentos);
            } else {
                //$exist = file_exists($ruta_firmados . $file_nombre . $ext);
                //$exist = self::existe_archivo($ruta_firmados.$file_nombre,$ext);
                $res_existe_archivo = self::existe_archivo($ruta_firmados.$file_nombre,$ext);
                $exist = (count($res_existe_archivo)>0);
                if ($exist && $ext!=".") {

                    $file = (object) [];
                    $file->nombre_fake =  $file_nombre . $ext;
                    //$file->ruta_fake = $ruta_firmados  . $file_nombre . $ext;
                    $file->ruta_fake = $res_existe_archivo[0];
                    $file->tipo_ext = "C";
                    $file->ruta_final =  self::get_ruta($key->adetalle_url) . $file->nombre_fake;
                    array_push($array_file, $file);
                    $file->ruta_inicial = $ruta_sin_firmar . $file_nombre;
                    $documentos->files = $array_file;
                    array_push($array_documentos, $documentos);
                } else {
                    $file = (object) [];
                    $file->nombre = $file_nombre;
                    array_push($array_archivos_no_encontrados, $file);
                }
            }
        }
        $array_documentos_final->encontrados = $array_documentos;
        $array_documentos_final->no_encontrados = $array_archivos_no_encontrados;
        return   $array_documentos_final;
    }


    // ext -> extension del file , ejemplo -> '.xxx'
    public function registrar_documentos($ids, $ruta_firmados, $ruta_sin_firmar, $ext)
    {


        $extension_minuscula = strtolower($ext);

        $obj_resultado = self::buscar_acrhivos_firmados($ids, $ruta_firmados, $ruta_sin_firmar, $extension_minuscula);


        $resultado = $obj_resultado->encontrados;
        $res = null;
        $array_id = [];
        $array_files_firmados = [];
        foreach ($resultado as $key) {

            $archivo = new App\archivo();
            $archivo->proyecto_id = $key->proyecto_id;
            $archivo->save();
            $array_files = $key->files;
            $id_archivo = $archivo->archivo_id;
            $elementos_exp = [];
            $id = $key->id;

            self::where('fedatario_firmar_id', $id)
                ->update(array('fedatario_documento_id' => $id_archivo));

            array_push($array_id, $id);

            foreach ($array_files as $item) {
                $peso = filesize($item->ruta_fake);

                $path_general = storage_path("/app/");
                $ruta_sin_path = str_replace($path_general,'',$item->ruta_final);

                array_push($elementos_exp, [

                    'archivo_id' => $id_archivo,
                    //'adetalle_url' => $item->ruta_final,
                    'adetalle_url' => $ruta_sin_path,
                    'adetalle_nombre' =>  $item->nombre_fake,
                    'adetalle_peso' => $peso,
                    'created_at' => new DateTime(),
                    'updated_at' => new DateTime()
                ]);

                $file_delete = (object) [];
                $file_delete->ruta_fake = $item->ruta_fake;
                $file_delete->ruta_inicial = $item->ruta_inicial;
                $file_delete->ruta_final = $item->ruta_final;

                array_push($array_files_firmados, $file_delete);
            }

            $res = DB::table('adetalle')->insert(
                $elementos_exp
            );
        }


        if ($res) {
            self::actualizar_estado_firmar($array_id, 4);
            self::copiar_archivos_firmados($array_files_firmados);
            self::eliminar_files_firmados($array_files_firmados);

            //grabada de log
            self::grabar_log_fedatario_firma($array_id,1);
        }


        return  $obj_resultado->no_encontrados;
    }


    public function eliminar_files_firmados($array_files)
    {

        foreach ($array_files as $item) {

            if (file_exists($item->ruta_fake)) {
                unlink($item->ruta_fake);
            }
            if (file_exists($item->ruta_inicial)) {
                unlink($item->ruta_inicial);
            }
        }
    }



    private function copiar_archivos_firmados($array_rutas)
    {

        foreach ($array_rutas as $key) {

            copy($key->ruta_fake, $key->ruta_final);
        }
    }


    public function probar()
    {
        $ids = [1];
        $out = "storage/";
        $ruta = "storage/logs/";
        $ext = ".esig";
        self::registrar_documentos($ids, $out, $ruta, $ext);
    }

    public function get_ruta($path)
    {
        //$directorio = storage_path() . "/app/";
        $directorio = storage_path("/app/");
        //$directorio = str_replace("\\","/",$directorio);
        // $path =  "documentos/proyecto 123/Simple 02/imagenes/cLoQzY2zz1cO42GIg2jqHOi05l4rLanE1wmdHFMj_1.jpg";
        $pos = strrpos($path, "/");
        $nueva_ruta = substr($path, 0,  $pos);
        $path =  $directorio .  $nueva_ruta . "/firmados/";
        self::exist_capeta($path);
        return $path;
    }

    function exist_capeta($path)
    {
        return is_dir($path) || mkdir($path, 0777, true);
    }

    public function a()
    {
        $path =  "documentos/proyecto 123/Simple 02/imagenes/cLoQzY2zz1cO42GIg2jqHOi05l4rLanE1wmdHFMj_1.jpg";
        return self::get_ruta($path);
    }

    public function existe_archivo($ruta,$extension){
        //$extension = 'abC';
        $patrón = '/(\w)/i';
        $extension_parseada = preg_replace_callback($patrón, function($word){return "[".strtolower($word[1]).",".strtoupper($word[1])."]"; }, $extension);
        //return count(glob($ruta.$extension_parseada))>0;
        return glob($ruta.$extension_parseada);
    }

    public function grabar_log_fedatario_firma($fedatario_firmar_ids,$flag_inicio = 0)
    {
        $usuario_creador= session("usuario_id");
        $ip = Request::ip();

        $proceso="FED-FIR-INICIO";
        $descripcion = "Envío de Documentos para Firma";
        if($flag_inicio == 1 ){
            $proceso="FED-FIR-FIN";
            $descripcion = "Validación de documentos Firmados";
        }


        $array_variable = "{";
        if(is_null($fedatario_firmar_ids)) {

            $array_variable= "{}";

        }else{
            $count = sizeof($fedatario_firmar_ids);
            $contador = 0;
            foreach ($fedatario_firmar_ids as $key) {
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
                    ,a.fedatario_firmar_id
                    ,5
                    ,'fedatario_firmar'
                    ,:proceso
                    ,null
                    ,:descripcion
                    ,''
                    ,now()
                    ,now()
                from fedatario_firmar a
                where a.fedatario_firmar_id = ANY(:array_variable::INT[]);
                        "),
            ["usuario_creador" => $usuario_creador,"ip" => $ip, "array_variable" => $array_variable
            ,"proceso"=>$proceso, "descripcion"=>$descripcion]
        );
    }

    public function elegir_usuario_fedatario_firmar($usuario_id,$captura_id){

        return DB::select(
            "with
                 proyecto_get_id as (
                    select proyecto_id,captura_id
                    from captura where captura_id = :captura_id
                ),
                captura_base as (
                  select distinct
                                  c.*
                      ,count(i.imagen_id) over(partition by i.documento_id) as cant_paginas
                  from captura c
                         left join documento d
                           on d.captura_id = c.captura_id
                         left join imagen i
                           on i.documento_id = d.documento_id and i.imagen_estado = 1
                         join proyecto_get_id p_g_i
                            on p_g_i.proyecto_id = c.proyecto_id
                  where c.captura_estado = 1 and c.captura_id = :captura_id
              )
              ,persona_base as (
                select *
                from persona p
            ),
            fedatario_firmas as (
                select distinct
                p.persona_nombre||' '||p.persona_apellido as usuario
                ,count(ff.fedatario_firmar_id) over(partition by c.usuario_asignado_control_calidad) as control_calidades
                ,sum(c.cant_paginas) over(partition by c.usuario_asignado_control_calidad) as cant_paginas
                ,c.usuario_asignado_control_calidad as usuario_id
                from captura_base c
                join persona_base p
                on c.usuario_asignado_control_calidad = p.usuario_id
                join fedatario_firmar ff
                on ff.captura_id = c.captura_id
                where c.usuario_asignado_control_calidad is not null
            )
            select * from fedatario_firmas
            where usuario_id = :usuario_id
            " , ["usuario_id"=>$usuario_id,"captura_id" => $captura_id]);
    }


}
