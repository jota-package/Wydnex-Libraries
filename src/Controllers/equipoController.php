<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use DB;
use App;
use Datetime;

Trait equipoController
{

    public function retornar_equipo()
    {

        $proyecto_actual = request("proyecto_actual");

        $equipo = App\equipo::where("proyecto_id",$proyecto_actual)
                    ->get();

        return $equipo;

    }

    public function asignar_equipo()
    {

        $equipo_captura = request("equipo_captura");
        $equipo_indexador = request("equipo_indexador");
        $equipo_calidad = request("equipo_calidad");
        $equipo_fedatario = request("equipo_fedatario");
        $equipo_generador = request("equipo_generador");
        
        $proyecto_actual = request("proyecto_actual");

        if( $equipo_captura == '' || $equipo_indexador == '' || $equipo_calidad == '' || $equipo_fedatario == '' || $equipo_generador == '' ){

            return $this->crear_objeto("error","Favor de ingresar por lo menos un integrante en cada equipo");


        }


        
        $array_captura = $this->convertir_array(2,$proyecto_actual,$equipo_captura);
        $array_indexador = $this->convertir_array(3,$proyecto_actual,$equipo_indexador);
        $array_calidad = $this->convertir_array(4,$proyecto_actual,$equipo_calidad);
        $array_fedatario = $this->convertir_array(5,$proyecto_actual,$equipo_fedatario);
        $array_generador = $this->convertir_array(6,$proyecto_actual,$equipo_generador);

        App\equipo::where("proyecto_id",$proyecto_actual)
            ->delete();

        if( !empty($array_captura) ){

            DB::table('equipo')->insert(
                $array_captura
            );

        }
        
        
        if( !empty($array_indexador) ){

            DB::table('equipo')->insert(
                $array_indexador
            );

        }
        
        if( !empty($array_calidad) ){

            DB::table('equipo')->insert(
                $array_calidad
            );

        }
        
        if( !empty($array_fedatario) ){

            DB::table('equipo')->insert(
                $array_fedatario
            );

        }
        
        if( !empty($array_generador) ){

            DB::table('equipo')->insert(
                $array_generador
            );

        }


        return $this->crear_objeto("ok","Equipo registrado correctamente");

        



    }

    public function convertir_array($perfil_id,$proyecto_actual,$array)
    {

        if( !empty( $array ) ){

            $now = new DateTime();

            $array_final = [];

            foreach ($array as $key => $value) {

                $objeto = [
                
                    'proyecto_id' => $proyecto_actual,
                    'usuario_id' => $value,
                    'perfil_id' => $perfil_id,
                    'equipo_estado' => 1,
                    'created_at' => $now,
                    'updated_at' => $now

                ];

                $array_final[] = $objeto;

            }

            return $array_final;


        }

        

    }
}
