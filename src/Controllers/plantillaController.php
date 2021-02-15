<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use View;
use App;

Trait plantillaController
{
    public function index()
    {

        // }

        $plantillas = App\plantilla::get();

        $inst_plantilla = new App\plantilla();

        return view::make('plantilla.index.content')
        ->with('plantillas',$plantillas)
        ;

    }

    public function registro_plantilla()
    {




        $plantilla_nombre = request('plantilla_nombre');

        if ($plantilla_nombre == "" || $plantilla_nombre == null) {

            $tipo = 'error';
            $mensaje = 'Ingrese el nombre de la plantilla';

            return Controller::crear_objeto($tipo, $mensaje);

        } else {




            $inst_plantilla = new App\plantilla();
          return  $inst_plantilla->crear_plantilla($plantilla_nombre);

           /* $tipo = 'ok';
            $mensaje = 'Registro de plantilla correcto';

            return Controller::crear_objeto($tipo, $mensaje);*/

        }

    }

    public function actualizar_plantilla()
    {

        $plantilla_nombre = request('plantilla_nombre');
        $plantilla_id = request('plantilla_id');

        if (($plantilla_nombre == null && $plantilla_nombre == '') || ($plantilla_id == null && $plantilla_id == '')) {
            $tipo = 'error';
            $mensaje = 'Ingrese el nombre de la plantilla';
            return Controller::crear_objeto($tipo, $mensaje);

        } else {
            $inst_plantilla = new App\plantilla();
            $inst_plantilla->actualizar_plantilla($plantilla_id, $plantilla_nombre);




            $tipo = 'ok';
            $mensaje = 'Su plantilla ha sido Actualizada';

            return Controller::crear_objeto($tipo, $mensaje);
        }

    }

    public function eliminar_plantilla()
    {
        $plantilla_id = request('plantilla_id');

        $validacion_plantilla_asignada =
            App\proyecto::where("plantilla_id",$plantilla_id)
                            ->count();

        if( $validacion_plantilla_asignada > 0 ){

            return Controller::crear_objeto("Error", "No se puede borrar esta plantilla, se encuentra asignada a un proyecto");

        }

        $inst_plantilla = new App\plantilla();
        $inst_plantilla->eliminar_plantilla($plantilla_id);

        $inst_elemento = new sub_elementoController();
        $inst_elemento->eliminar_sub_elemento( $plantilla_id );

        $tipo = 'ok';
        $mensaje = 'Su plantilla ha sido eliminada';

        return Controller::crear_objeto($tipo, $mensaje);

    }

    public function listar_plantilla()
    {

        $inst_plantilla = new App\plantilla();
        return $inst_plantilla->listar_plantilla();

    }


    public function guardar_array()
    {



        $array_plantilla = request('array_plantilla');
        $plantilla_id = request('plantilla_id');





         $plantilla = App\plantilla::join('proyecto as p','p.plantilla_id','=','plantilla.plantilla_id')
            ->where('plantilla.plantilla_id',$plantilla_id)
            ->get()
            ->count();

        if ($plantilla > 0 || $plantilla != null){
            return $this->crear_objeto('warning','No se puede editar esta plantilla, se encuentra asignada a un proyecto');
        }


        $inst_plantilla = new App\plantilla();

        $controller_sub_elemento = new sub_elementoController();
        $controller_elemento = new elementoController();
        $controller_elemento_opciones = new elemento_opcionesController();
        $controller_tipo_elemento = new tipo_elementoController();


        $controller_elemento->eliminar_elemento($plantilla_id);
        $controller_sub_elemento->eliminar_sub_elemento($plantilla_id);

        App\simple::where("plantilla_id",$plantilla_id)->delete();

        //   foreach ($array_plantilla as $plantilla) {

        //  $plantilla_nombre = $plantilla['plantilla_nombre'];
        $plantilla_nombre = $array_plantilla['plantilla_nombre'];


        if (($plantilla_nombre == null || $plantilla_nombre == '') ) {

            $tipo = 'warning';
            $mensaje = 'Ingrese el nombre de la plantilla';
            return Controller::crear_objeto($tipo, $mensaje);

        }


        //Guardando Plantilla
        // $this->registro_plantilla($plantilla_nombre);

        foreach ($array_plantilla['elementos'] as $key =>  $elemento) {



            $elemento_nombre = $elemento['elemento_nombre'];
            $plantilla_id_1 = $plantilla_id;
            $te_abreviacion = $elemento['te_abreviacion'];
            $te_id = $elemento['te_id'];
            $te_nombre = $elemento['te_nombre'];









            //Guardando elemento
            $wa = $controller_elemento->crear_elemento($te_id, $elemento_nombre, $plantilla_id_1);
            $elemento_id = $wa['elemento_id'];

            //Guardando tipo elemento
            // $lala = $controller_tipo_elemento->crear_tipo_elemento($te_nombre, $te_abreviacion,
            //     $elemento_id, $plantilla_id);


            $eo_guia = $elemento['elemento_opciones']['eo_guia'];
            $eo_incremental = $elemento['elemento_opciones']['eo_incremental'];
            $eo_multipagina = $elemento['elemento_opciones']['eo_multipagina'];
            $eo_obligatorio = $elemento['elemento_opciones']['eo_obligatorio'];

            //Guardando elemento opciones
            $var = $controller_elemento_opciones->crear_elemento_opciones($eo_incremental,
                $eo_obligatorio, $eo_multipagina,
                $eo_guia, $elemento_id, $plantilla_id_1);




                $elemento['subelemento']['elemento_id'] = $elemento_id;


            if ($te_abreviacion == "s") {

                $tipo_subelemento = 1;
                $simple_id = $elemento['subelemento'] ['simple_id'];

                $simple_tipo_dato = $elemento['subelemento']['simple_tipo_dato'];


                $simple_tipo_formato = $elemento['subelemento'] ['simple_tipo_formato'];

                $controller_sub_elemento->crear_sub_elemento($tipo_subelemento, $elemento['subelemento'],$plantilla_id);

            } elseif ($te_abreviacion == "c") {

                $tipo_subelemento = 2;


                $dada = $controller_sub_elemento->crear_sub_elemento($tipo_subelemento, $elemento['subelemento'],$plantilla_id);


            }




        }
        return $this->crear_objeto('ok','Plantilla Actualizada');

    }


    public function devolver_plantilla()
    {

        $plantilla_id = request("plantilla_id");

        $plantilla = App\plantilla::where("plantilla_id",$plantilla_id)
                        ->select(
                            "plantilla_id",
                            "plantilla_nombre"


                            )
                        ->first();


        $elemento = App\elemento::
                        where("plantilla_id",$plantilla_id)
                        ->select(
                            "elemento_id",
                            "elemento_nombre",
                            "plantilla_id"
                            )
                        ->get();

        foreach ($elemento as $key => $value) {

            $elemento_opciones = App\elemento_opciones::
                                    where("elemento_id",$value->elemento_id)
                                    ->select(
                                        "elemento_opciones_incremental as eo_incremental",
                                        "elemento_opciones_guia as eo_guia",
                                        "elemento_opciones_multipagina as eo_multipagina",
                                        "elemento_opciones_obligatorio as eo_obligatorio"


                                        )
                                    ->first();

            $value['elemento_opciones'] = $elemento_opciones;

            $combo = App\combo::where("elemento_id",$value->elemento_id)
                                    ->first();

            $simple = App\simple::where("elemento_id",$value->elemento_id)
                                    ->first();

            if( $simple != "" && $simple != null ){

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

            }

            else if( $combo != "" && $combo != null ){

                $opciones = App\opcion::where("combo_id",$combo->combo_id)
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


}
