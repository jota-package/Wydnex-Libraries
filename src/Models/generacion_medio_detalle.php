<?php
/*   __________________________________________________
    |  Obfuscated by YAK Pro - Php Obfuscator  2.0.12  |
    |              on 2021-02-18 16:34:05              |
    |    GitHub: https://github.com/pk-fr/yakpro-po    |
    |__________________________________________________|
*/
 namespace Fedatario\Models; use Illuminate\Database\Eloquent\Model; use DB; trait generacion_medio_detalle { protected $primaryKey = "\147\x6d\144\x5f\x69\x64"; protected $table = "\147\x65\x6e\145\x72\141\x63\x69\x6f\x6e\137\x6d\145\144\x69\x6f\x5f\x64\x65\164\x61\154\x6c\x65"; public function confirmar_gmd($GgKQ7) { return $M1GfZ = $this->join("\147\145\156\x65\162\141\143\x69\157\156\137\x6d\145\144\x69\157\x5f\144\145\164\141\154\154\x65\137\x63\x61\x70\x74\x75\162\141\40\x61\x73\x20\147\x6d\x64\x63", "\x67\x6d\144\143\56\147\x6d\x64\137\151\144", "\x67\145\x6e\145\x72\141\x63\151\157\156\x5f\x6d\145\144\x69\x6f\137\x64\x65\164\x61\x6c\154\145\56\x67\155\x64\137\151\144")->leftjoin("\143\141\160\164\165\x72\141\x20\x61\163\40\x63\x61\160", "\x63\x61\x70\56\x63\141\160\x74\165\x72\x61\x5f\x69\x64", "\x67\155\144\x63\56\x63\141\x70\x74\x75\x72\141\137\151\144")->leftjoin("\144\x6f\143\x75\x6d\x65\156\164\157\40\141\x73\40\144\x6f\143", "\x64\x6f\x63\56\x63\141\x70\164\165\x72\141\x5f\151\x64", "\143\141\x70\x2e\x63\x61\x70\164\165\x72\141\x5f\x69\x64")->leftjoin("\141\x64\x65\164\x61\x6c\154\x65\40\x61\163\40\x61\144", "\141\x64\x2e\141\x64\x65\x74\x61\x6c\154\x65\137\x69\x64", "\144\157\x63\x2e\141\x64\145\164\x61\x6c\154\x65\137\151\144")->select("\147\145\156\x65\x72\141\x63\x69\x6f\x6e\x5f\155\145\x64\x69\157\x5f\x64\x65\x74\141\154\x6c\x65\x2e\x67\155\144\137\151\x64", "\x67\155\144\x63\x5f\x69\x64", "\141\144\145\x74\141\x6c\x6c\145\137\x75\162\x6c")->where("\x67\145\x6e\145\162\141\143\x69\157\x6e\137\155\x65\x64\151\x6f\137\x64\145\x74\x61\154\x6c\145\56\147\155\144\x5f\151\x64", $GgKQ7)->get(); } }
