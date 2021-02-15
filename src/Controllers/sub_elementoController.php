<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use App;

Trait sub_elementoController
{
    public function crear_sub_elemento($tipo_subelemento, $objeto_subelemento,$plantilla_id)
    {
        

        $elemento_id = $objeto_subelemento["elemento_id"];
      //  $plantilla_id = $objeto_subelemento["plantilla_id"];

            
        
        // return $objeto_subelemento;
        switch ($tipo_subelemento) {

            case 1:
                $simple_tipo_dato = $objeto_subelemento["simple_tipo_dato"];
                $simple_tipo_formato = $objeto_subelemento["simple_tipo_formato"];

                
                $simple = new App\simple();
                $simple->crear_simple($plantilla_id, $elemento_id, $simple_tipo_dato, $simple_tipo_formato);
            break;

            case 2:

            $opciones = $objeto_subelemento["opciones"];
            
        
                    

                    if (count($opciones) == 0) {

                        $combo = new App\combo();
                        $combo->crear_combo($elemento_id, $plantilla_id);

                    } else {
                        
                        foreach ($opciones as $key =>  $o) {

                            if($key == 0 ){

                                $combo = new App\combo();
                                $combo = $combo->crear_combo($elemento_id, $plantilla_id);


                            }

                            
                            $opcion_nombre = $o['opcion_nombre'];
                            $combo_id = $combo['combo_id'];
                            
                            $opcion = new App\opcion();
                            $opcion->crear_opcion($plantilla_id, $elemento_id, $combo_id, $opcion_nombre);
                        }
                        
                    }
                    break;
            
            
            }


        }


    public function actualizar_sub_elemento($tipo_subelemento, $objeto_subelemento)
    {
        switch ($tipo_subelemento) {
            case 1:
                $simple = new App\simple();
                $simple_tipo_dato = $objeto_subelemento["simple_tipo_dato"];
                $simple_tipo_formato = $objeto_subelemento["simple_tipo_formato"];
                $elemento_id = $objeto_subelemento["elemento_id"];
                $plantilla_id = $objeto_subelemento["plantilla_id"];
                $simple_id = $objeto_subelemento["simple_id"];
                $simple->actualizar_simple($plantilla_id, $elemento_id, $simple_id, $simple_tipo_dato, $simple_tipo_formato);

            case 2:
                $elemento_id = $objeto_subelemento["elemento_id"];
                $plantilla_id = $objeto_subelemento["plantilla_id"];
                $opciones = $objeto_subelemento["opciones"];
                $combo_id = $objeto_subelemento["combo_id"];
                if ($opciones == null || $opciones == "") {

                    $combo = new App\combo();
                    $combo->actualizar_combo($elemento_id, $plantilla_id, $combo_id);
                } else {
                    $opcion_nombre = $objeto_subelemento["opcion_nombre"];
                    $opcion_id = $objeto_subelemento["opcion_id"];
                    $combo = new App\combo();
                    $combo->actualizar_combo($elemento_id, $plantilla_id, $combo_id);

                    $opcion = new App\opcion();
                    $opcion->actualizar_opcion($plantilla_id, $elemento_id, $combo_id, $opcion_id, $opcion_nombre);
                }
        }


    }

    public function eliminar_sub_elemento($plantilla_id)
    {
        
        $simple = new App\simple();
        $validar_simple = $simple->buscar_simple_por_plantilla($plantilla_id)->count();

        

        // if ($validar_simple > 0) {
            $simple = new App\simple();
            $simple->eliminar_simple($plantilla_id);
            
            
        // }

        

            $combo = new App\combo();
            $validar_combo = $combo->buscar_combo_por_plantilla($plantilla_id)->count();

            // if ($validar_combo > 0) {
                $combo = new App\combo();
                $combo->eliminar_combo($plantilla_id);
            // }

            $opcion = new App\opcion();
            $validar_opcion = $opcion->buscar_opcion_por_plantilla($plantilla_id)->count();

            // if ($validar_opcion > 0) {
                $opcion = new App\opcion();
                $opcion->eliminar_opcion($plantilla_id);
            // }

            return true;
     
    }

    public function listar_sub_elemento()
    {
        $plantilla_id = 3;
        $simple = new App\simple();
        $buscar_simple = $simple->buscar_simple_por_plantilla($plantilla_id);
        $validar_simple = $buscar_simple->count();


        $c = collect(new $simple);


        return dd($c);

        if ($validar_simple > 0) {

            $listar_simple = $buscar_simple->get();


            return $opcion;

        } else {

            $simple = new App\simple();
            $buscar_simple = $simple->buscar_simple_por_plantilla($plantilla_id);
            $validar_simple = $buscar_simple->count();

            if ($validar_combo > 0) {
                $combo = new App\combo();
                $combo->eliminar_combo($plantilla_id);
            }

            $opcion = new App\opcion();
            $validar_opcion = $opcion->buscar_opcion_por_plantilla($plantilla_id)->count();

            if ($validar_opcion > 0) {
                $opcion = new App\opcion();
                $opcion->eliminar_opcion($plantilla_id);
            }

            return true;
        }

    }


}
