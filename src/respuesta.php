<?php 

namespace Respuesta;

trait Respuesta
{
    /**
     * @param $estado: bool
     * @param $mensaje:String
     * @param $status:int
     * @return array
     */
    public static function crear($estado, $mensaje, $status, $payload = NULL)
    {
        if (isset($payload)) {
            return ["estado" => $estado, "mensaje" => $mensaje, "status" => $status, "payload" => $payload];
        } else {
            return ["estado" => $estado, "mensaje" => $mensaje, "status" => $status];
        }
    }

    public static function error($mensaje, $status = 500)
    {
        return self::crear(false, $mensaje, $status);
    }

    public static function ok($payload = NULL, $mensaje = "OK")
    {
        if (isset($payload)) {
            return self::crear(true, $mensaje, 200, $payload);
        } else {
            return self::crear(true, $mensaje, 200);
        }
    }
}
