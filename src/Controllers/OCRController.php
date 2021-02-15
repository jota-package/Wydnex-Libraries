<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use App;
use View;
use Imagick;
use DB;

Trait OCRController
{


    public function path_file_ws_ocr($ruta){

        //Aquí se consume el WS de OCR 😵
        $url_put = "http://127.0.0.1:5000/apiv1/ocr_pdf";

        //Objeto que se enviará para el Update
        $data_put = [
            "path_image" => $ruta
        ];

        //Cambiando formato de objeto
        $var = json_encode($data_put, true);

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => "5000",
            CURLOPT_URL => $url_put,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 1800000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $var,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Postman-Token:  f6383e44-afc1-47c7-a62d-cbe4820c6fb7",
                "cache-control: no-cache"
            ),
        ));


        $response = curl_exec($curl);
        $valor = json_decode($response, true);

        $err = curl_error($curl);

        curl_close($curl);

        //return $valor["data"]["full_path_txt"];

    }

}
