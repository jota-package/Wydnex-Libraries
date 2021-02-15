<?php

namespace file;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class file
{
    protected $primaryKey = "file_id";
    protected $table = "files";

    protected $fillable = ['recepcion_id', 'file_nombre', 'file_tipo', 'file_padre_id', 'file_captura_estado', 'file_captura_id', 'file_estado', 'file_usuario_id'];
    protected $post_require = ['recepcion_id', 'file_nombre', 'file_tipo', 'file_estado'];
}