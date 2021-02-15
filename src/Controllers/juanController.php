<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use View;
use Response;
use App;

Trait juanController
{
    /**
     * Itera sobre una direccion para listar los directorio y archivos
     * @param string $dir Es la ruta que sera analizada iterativamente
     * @param int $restJ Es el numero de niveles de jerarquia qde directorio ue se desean analizar
     * @param int $sf Es un flag para mostrar(1) o no(0) los archivos a parte de los directorios, por defecto toma 1
     * @return array El listado iterado del directorio indicado y todas sus carpetas internas
     * @author Juan Ignacio Basilio Flores
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function getContentDir($dir, $restJ, $sf=1){
        $restJ--;
        $content = array();
        $files = scandir($dir);
        natcasesort($files);
        if( count($files) > 2 ) { /* The 2 accounts for . and .. */
            foreach( $files as $i => $file ) {
                if( file_exists($dir.$file) && $file != '.' && $file != '..' ) {
                    if(is_dir($dir.$file)){
                        $children = array();
                        if($restJ>0){
                            $direccion = $dir.$file."/";
                            $children = self::getContentDir($direccion, $restJ, $sf);
                        }
                        $tmp = array(
                            /*"id" => $restJ."_".$i,*/
                            "text" => $file,
                            "children" => $children,
                        );
                        array_push($content, $tmp);
                    } else if($sf == 1){
                        $tmp = array(
                            /*"id" => $restJ."_".$i,*/
                            "text" => $file,
                        );
                        array_push($content, $tmp);
                    } else {
                        // Es un archivo, pero no se desea ver los archivos.
                    }
                } else {
                    // El archivo no existe o es el directorio . o ..
                }
            }
        }
        return $content;
    }

    /**
     * Retorna el listado de directorios de hasta dos niveles
     * @return JSON Listado de los directorios
     * @author Juan Ignacio Basilio Flores
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function listarCarpetas2Niveles(){
        $root = '../storage/app/documentos/';
        $direccion = urldecode($_POST['dir']);
        $direccion = $root.$direccion;
        $niveles_jerarquia = 2;
        $see_file=0; // solo listara carpetas

        $retorno = array();
        if( file_exists($directorio) ) {
            $retorno = self::getContentDir($directorio, $niveles_jerarquia, $see_file);
        }
        //var_dump($retorno);
        header('Content-type: application/json; charset=utf-8');
        //Enviando respuesta
        echo json_encode($retorno);
    }

    /**
     * Retorna un JSON con el formato para JSTree para la visualizacion de directorios y crea los directorios que no existen
     * @param array $client Contiene la informacion de los proyectos y recepciones, puede ver la estructura en $data_default
     * @return JSON Listado de los directorios
     * @author Juan Ignacio Basilio Flores
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function verifyDirectoryTreeCliente($client, $demo=0){
        $data_default = array(
            [
            "text" => "Proyecto 1",
            "id_proyecto" => 123,
            "children" => array([
                "text" => "Recepcion 1.1",
                "id_recepcion" => 122,
                ],[
                "text" => "Recepcion 1.2",
                "id_recepcion" => 121,
                ]
            ),
            ],[
            "text" => "Proyecto 2",
            "id_proyecto" => 120,
            "children" => array([
                "text" => "Recepcion 2.1",
                "id_recepcion" => 119,
                ],[
                "text" => "Recepcion 2.2",
                "id_recepcion" => 118,
                ]
            ),
            ]
        );
        if($demo == 1){
            $client = $data_default;
        }

        $root = '../storage/app/documentos/';
        $retorno = array();

        foreach ($client as $i => $proyecto) {
            $estado = 0;
            if( !file_exists($root.$proyecto["text"]."/")){
                if(mkdir($root.$proyecto["text"], 0777, true)){
                    $estado = 1;
                } else {
                    die('Fallo al crear las carpetas...');
                }
            } else {
                $estado = 1;
            }
            if($estado == 1){
                $children = array();
                $estado = 0;
                foreach ($proyecto["children"] as $j => $recepcion) {
                    if( !file_exists($root.$proyecto["text"]."/".$recepcion["text"]."/")){
                        if(mkdir($root.$proyecto["text"]."/".$recepcion["text"], 0777, true)){
                            $estado = 1;
                        } else {
                            die('Fallo al crear las carpetas...');
                        }
                    } else {
                        $estado = 1;
                    }
                    if($estado == 1){
                        array_push($children, array(
                            "text" => $recepcion["text"],
                            "id_recepcion" => $recepcion["id_recepcion"],
                        )); 
                    }
                }
                array_push($retorno, array(
                    "text" => $proyecto["text"],
                    "children" => $children,
                )); 
            }
        }
        //var_dump($retorno);
        //echo json_encode($retorno);
        return $retorno;
    }


    /**
     * Devuelve el arbol de directorios verificado para un determinado cliente
     * @return JSON Listado de los directorios
     * @author Juan Ignacio Basilio Flores
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function seeDirectoryClient(){
        // Consultas SQL aqui
        $waka = new wakaController();
        $tree = $waka->lista_proyecto_recepcion();
        //return self::verifyDirectoryTreeCliente([], 1); // Data de test
        return self::verifyDirectoryTreeCliente($tree);
        
    }

    /**
     * Devuelve la vista de archivos
     * @return HTML Vista de la consulta para testeo
     * @author Juan Ignacio Basilio Flores
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function index(){

        return view::make('captura.index.jotatest');
    }

    /**
     * Verifica el archivo imagen recibido y convierte a jpg cualquier archivo
     * @return boolean resultado del guardado
     * @author Juan Ignacio Basilio Flores
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function recibirimg(Request $request){
        /*
        $subir_archivo = request()->file('file')->store('archivos'); // guarda en el servidor con un nombre modificado (HASH)
        $nombre_original = request()->file('file')->getClientOriginalName(); // Captura el nombre del archivo enviado de front-end

        self::convert_to_jpeg($subir_archivo);
        return $nombre_original;
        */
        // NUEVO AVANCE
        //$recepcion_id = request("recepcion_id");
        $recepcion_id = 1;
        //$path_dir = self::verify_path_capturas($id_recepcion);
        $path = self::verify_path_capturas($recepcion_id);
        $subir_archivo = request()->file('file')->store($path); // guarda en el servidor con un nombre modificado (HASH)
        $nueva_ruta = self::convert_to_jpeg($subir_archivo, $path."/imagenes");
        return $nueva_ruta;
    }

    /**
     * Convierte las imagenes de termnacion jpg,png,bmp,gif a formato JPEG
     * @param string $image_path Direccion de de la imagen en el proyecto
     * @param string $dir_path Directorio en el que se guardara la nueva imagen JPEG
     * @param int $quality Calidad de la imagen que puede estar entre 0 y 100
     * @return string direccion del nuevo archivo o false en caso de que el formato no sea correcto
     * @author Juan Ignacio Basilio Flores
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function convert_to_jpeg($image_path, $dir_path="", $quality=100){
        $root = "../storage/app/";
        $image_path = $root.$image_path;
        $exploded = explode('.',$image_path);
        $ext = $exploded[count($exploded) - 1];
        $exploded[count($exploded) - 1] = "jpeg";
        $exploded[count($exploded) - 2] .= "_1";
        $nuevo_path = implode(".",$exploded);

        if($dir_path != ""){
            $dir_path = $root.$dir_path;
            if(self::ensure_path_directory($dir_path)){
                $exploded = explode('/',$nuevo_path);
                $nuevo_path = $dir_path."/".$exploded[count($exploded) - 1];
            } else {
                return false;
            }
        }

        if (preg_match('/jpg|jpeg/i',$ext)){
            if (copy($image_path, $nuevo_path)) {
                return $nuevo_path; 
            } else {
                return false;
            }
        } else if (preg_match('/png/i',$ext)){
            $input = imagecreatefrompng($image_path);
            list($width, $height) = getimagesize($image_path);
            $image_tmp = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($image_tmp,  255, 255, 255);
            imagefilledrectangle($image_tmp, 0, 0, $width, $height, $white);
            imagecopy($image_tmp, $input, 0, 0, 0, 0, $width, $height);
        } else if (preg_match('/gif/i',$ext)){
            $image_tmp=imagecreatefromgif($image_path);
        } else if (preg_match('/bmp/i',$ext)){
            $image_tmp=self::imagecreatefrombmp($image_path);
        } else {
            return false;
        }

        // quality is a value from 0 (worst) to 100 (best)
        imagejpeg($image_tmp, $nuevo_path, $quality);
        imagedestroy($image_tmp);

        return $nuevo_path;
    }

    /**
     * Reemplaza a la funcion de php imagecreatefrombmp que no funciono en las pruebas
     * @param string $dir Direccion del archivo en el proyecto
     * @return image Retorna el identificador del recurso de imagen
     * @author Juan Ignacio Basilio Flores
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function imagecreatefrombmp($dir){
        $bmp = "";
        if (file_exists($dir)) {
            $file = fopen($dir,"r");
            while(!feof($file)) $bmp .= fgets($file,filesize($dir));
            if (substr($bmp,0,2) == "BM") {
                // Lecture du header
                $header = unpack("vtype/Vlength/v2reserved/Vbegin/Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant", $bmp);
                extract($header);
                // Lecture de l'image
                $im = imagecreatetruecolor($width,$height);
                $i = 0;
                $diff = floor(($imagesize - ($width*$height*($bits/8)))/$height);
                for($y=$height-1;$y>=0;$y--) {
                    for($x=0;$x<$width;$x++) {
                        if ($bits == 32) {
                            $b = ord(substr($bmp,$begin+$i,1));
                            $v = ord(substr($bmp,$begin+$i+1,1));
                            $r = ord(substr($bmp,$begin+$i+2,1));
                            $i += 4;
                        } else if ($bits == 24) {
                            $b = ord(substr($bmp,$begin+$i,1));
                            $v = ord(substr($bmp,$begin+$i+1,1));
                            $r = ord(substr($bmp,$begin+$i+2,1));
                            $i += 3;
                        } else if ($bits == 16) {
                            $tot1 = decbin(ord(substr($bmp,$begin+$i,1)));
                            while(strlen($tot1)<8) $tot1 = "0".$tot1;
                            $tot2 = decbin(ord(substr($bmp,$begin+$i+1,1)));
                            while(strlen($tot2)<8) $tot2 = "0".$tot2;
                            $tot = $tot2.$tot1;
                            $r = bindec(substr($tot,1,5))*8;
                            $v = bindec(substr($tot,6,5))*8;
                            $b = bindec(substr($tot,11,5))*8;
                            $i += 2;
                        }
                        $col = imagecolorexact($im,$r,$v,$b);
                        if ($col == -1) $col = imagecolorallocate($im,$r,$v,$b);
                        imagesetpixel($im,$x,$y,$col);
                    }
                    $i += $diff;
                }
                // retourne l'image
                return $im;
                imagedestroy($im);
            } else return false;
        } else return false;
    }

    /**
     * Verifica la existencia de los directorios a partir del id_recepcion, para asegurar las carpetas
     * @param int $id_recepcion Identificador de la recepcion
     * @return string Direccion de donde se debe guardar los archivos
     * @author Juan Ignacio Basilio Flores
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function verify_path_capturas($recepcion_id){
        $root = "../storage/app/";
        $path_base = "documentos";
        $info = App\recepcion::where('recepcion_id',$recepcion_id)
                ->join("proyecto as p","p.proyecto_id","recepcion.proyecto_id")
                ->first();
        if(isset($info["proyecto_nombre"]) && isset($info["recepcion_nombre"])){    
            if(self::ensure_path_directory($root.$path_base."/".$info["proyecto_nombre"])){
                $path_base .= "/".$info["proyecto_nombre"];
                if(self::ensure_path_directory($root.$path_base."/".$info["recepcion_nombre"])){
                    $path_base .= "/".$info["recepcion_nombre"];
                    if(self::ensure_path_directory($root.$path_base."/imagenes")){

                    } else {
                        return false;
                    }
                }
            }
        }
        return $path_base;
    }

    /**
     * Asegura la existencia de la ruta $path
     * @param string $path Ruta que sera verificada
     * @return boolean Devuelve TRUE, en caso de que haya ocurrido algun error retorna FALSE
     * @author Juan Ignacio Basilio Flores
     * @copyright 2019 Wydnex S.A.C.
     * @version v0.01.0
     */
    public function ensure_path_directory($path){
        if( !file_exists($path."/")){
            if(mkdir($path, 0777, true)){
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * Retorna un mensaje de verdadero
     */
    public function ejecucion_limpia(Request $request){
        return $request->all();
    }

}
