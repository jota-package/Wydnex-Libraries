<?php
/*   __________________________________________________
    |  Obfuscated by YAK Pro - Php Obfuscator  2.0.12  |
    |              on 2021-02-18 13:04:15              |
    |    GitHub: https://github.com/pk-fr/yakpro-po    |
    |__________________________________________________|
*/
 namespace Fedatario\Controllers; use Illuminate\Http\Request; use App\Http\Controllers\respuesta; use View; use App; use Illuminate\Support\Facades\DB; use App\proyecto; use App\Http\Controllers\tipo_calibradorController; use App\medio_exportacion; use App\generacion_medio; use App\files; use App\generacion_medio_detalle_captura; use App\generacion_medio_detalle; use App\recepcion; trait generacionmedios_generarController { public function index() { goto uaUbB; uwEOt: $WSfDe = new tipo_calibradorController(); goto LIK32; mhpuE: $zwsTz = $eSkuk->proyecto_usuario(); goto uwEOt; M8ty2: $dEczO = $WSfDe->listar_tipo_calibrador(); goto min3B; min3B: return view::make("\x67\x65\156\145\162\x61\x63\151\157\156\137\x6d\x65\144\x69\157\x73\137\147\x65\x6e\145\162\141\x72\x2e\x69\x6e\144\145\x78\x2e\x63\157\156\164\x65\x6e\x74")->with("\160\x72\x6f\171\x65\143\x74\157\x73", $zwsTz)->with("\x6d\145\x64\151\157\x73\x5f\147\x65\156\145\x72\141\144\157\163", $zRYiW)->with("\x74\x69\x70\157\x5f\143\x61\x6c\x69\x62\x72\x61\x64\x6f\162", $dEczO); goto Aklpz; GHS0E: $zRYiW = $zF1_H->select("\x6d\145\137\x69\x64", "\x6d\x65\137\144\145\x73\143\x72\151\160\x63\x69\157\156", "\155\x65\137\x63\x61\x70\x61\x63\151\x64\x61\x64")->get(); goto M8ty2; uaUbB: $eSkuk = new proyecto(); goto mhpuE; LIK32: $zF1_H = new medio_exportacion(); goto GHS0E; Aklpz: } public function index_personalizado() { goto PyNuJ; er_JW: $zF1_H = new medio_exportacion(); goto PoHAm; dWmfw: $dEczO = $WSfDe->listar_tipo_calibrador(); goto u0u_0; e538u: $zwsTz = $eSkuk->select("\x70\162\x6f\x79\x65\143\x74\x6f\137\x69\x64", "\x70\162\x6f\x79\x65\143\164\x6f\x5f\156\x6f\x6d\142\x72\145")->get(); goto sdTKu; u0u_0: return view::make("\147\x65\x6e\x65\162\141\143\151\x6f\x6e\137\155\145\144\151\x6f\163\137\x70\145\x72\163\157\x6e\141\154\x69\x7a\x61\144\157\56\x69\x6e\x64\145\x78\56\143\x6f\x6e\164\x65\156\x74")->with("\160\x72\157\171\x65\143\x74\x6f\163", $zwsTz)->with("\155\145\x64\151\x6f\x73\x5f\x67\145\156\145\162\x61\144\x6f\x73", $zRYiW)->with("\164\x69\x70\157\x5f\x63\141\x6c\151\x62\x72\141\144\157\162", $dEczO); goto fXIcE; PyNuJ: $eSkuk = new proyecto(); goto e538u; sdTKu: $WSfDe = new tipo_calibradorController(); goto er_JW; PoHAm: $zRYiW = $zF1_H->select("\155\145\137\x69\144", "\x6d\145\137\x64\145\x73\143\162\151\160\143\x69\157\156", "\155\x65\137\x63\x61\x70\141\x63\151\x64\141\x64")->get(); goto dWmfw; fXIcE: } public function proyecto_lista_gm(Request $a2KoW) { goto pHW6Y; b6qsU: return $UGeDd; goto eUFu1; pHW6Y: $NMy6V = request("\160\x72\x6f\171\x65\x63\164\x6f\137\x69\144"); goto ncJWP; ncJWP: $UGeDd = DB::select("\xa\x20\x20\40\x20\x20\40\x20\x20\40\x20\x20\x20\x77\x69\164\x68\x20\x64\x61\164\157\163\40\x61\163\40\x28\xa\x20\x20\x20\x20\x20\x20\40\40\x20\40\40\40\x20\x20\x20\40\x73\145\x6c\x65\143\x74\x20\x64\x69\163\x74\x69\x6e\143\x74\xa\x20\40\x20\40\40\40\40\x20\x20\x20\x20\x20\40\x20\40\x20\40\x20\x20\40\55\55\162\x2e\162\145\143\x65\160\x63\x69\157\x6e\137\x69\144\x2c\x72\x2e\x72\145\143\x65\x70\143\x69\157\x6e\137\156\x6f\155\142\162\145\xa\40\40\40\x20\40\x20\40\x20\x20\x20\40\40\x20\x20\x20\x20\x20\40\x20\x20\147\x6d\x72\x2e\162\x65\x63\x65\x70\143\x69\157\x6e\137\x69\x64\xa\x20\40\x20\40\40\40\40\x20\40\x20\40\x20\40\40\x20\x20\x20\40\40\40\54\162\157\167\137\x6e\165\155\x62\x65\162\50\51\40\x6f\166\145\162\x28\x70\141\x72\x74\x69\164\151\157\156\40\x62\x79\40\147\x6d\x64\56\x67\x6d\144\137\x69\x64\x29\40\141\163\40\x66\x69\154\164\x72\x6f\xa\x20\x20\x20\40\40\40\x20\x20\40\x20\40\40\x20\40\40\40\x20\40\x20\x20\x2c\147\155\56\x67\155\137\x69\x64\x2c\x20\147\155\56\147\x6d\x5f\160\x72\x65\x66\x69\x6a\x6f\xa\40\x20\40\40\x20\x20\x20\40\40\x20\x20\x20\40\40\40\40\40\x20\x20\x20\x2c\x67\155\x2e\x67\155\x5f\143\x6f\x72\162\145\154\x61\164\x69\x76\157\54\40\x67\155\56\x67\155\137\145\163\x74\x61\x64\157\12\40\40\x20\40\40\x20\40\40\40\40\x20\40\40\40\40\40\40\x20\40\40\54\147\155\x64\x2e\147\x6d\144\x5f\151\x64\12\40\x20\40\40\x20\x20\40\40\x20\40\x20\40\40\x20\40\40\40\40\40\40\54\164\x6f\x5f\143\150\x61\162\50\147\155\x64\x2e\x67\155\x64\137\x70\145\x73\157\x5f\x6d\141\x78\x69\x6d\x6f\54\47\x46\115\71\71\71\x2c\x39\71\71\x2c\x39\71\71\54\x39\x39\x39\54\x39\71\x30\104\60\x30\x27\51\x7c\x7c\x27\x20\115\x62\x27\40\x61\x73\x20\x67\155\x64\x5f\x70\145\x73\157\x5f\x6d\141\x78\151\155\x6f\xa\x20\x20\x20\x20\x20\x20\40\40\40\40\40\40\x20\x20\x20\x20\40\40\x20\40\x2c\147\155\x64\137\x65\163\164\141\x64\x6f\12\40\x20\x20\40\40\40\x20\x20\x20\40\x20\x20\40\x20\x20\40\x20\x20\x20\40\54\x74\x6f\x5f\x63\150\141\162\50\147\x6d\x64\x5f\160\x65\163\157\x5f\157\x63\x75\x70\x61\x64\157\x2c\x27\106\115\x39\x39\x39\54\x39\x39\71\x2c\x39\x39\71\54\71\71\71\x2c\x39\71\x30\x44\x30\60\47\x29\x7c\x7c\x27\40\x4d\x62\47\x20\141\x73\40\147\x6d\x64\x5f\160\145\x73\x6f\137\x6f\x63\165\160\141\144\x6f\xa\x20\40\x20\x20\x20\x20\40\40\40\x20\40\x20\x20\x20\x20\x20\40\40\x20\40\54\x67\x6d\144\137\164\x6f\x74\141\x6c\137\x64\157\x63\x75\x6d\145\x6e\164\x6f\xa\40\x20\40\x20\x20\40\40\40\x20\40\40\40\x20\40\40\40\x20\x20\x20\40\x2c\147\x6d\x64\137\x70\141\x72\164\145\163\137\160\162\x6f\x63\145\163\x61\x64\141\x73\12\40\x20\40\x20\40\x20\40\x20\40\x20\40\x20\40\40\40\x20\40\40\40\x20\x2c\x67\x6d\144\x5f\160\x61\162\x74\x65\163\x5f\x74\x6f\x74\141\154\12\x20\40\x20\x20\x20\x20\40\x20\40\40\x20\40\x20\x20\x20\x20\x20\x20\40\40\54\x28\x43\101\123\124\x20\50\x67\x6d\144\x5f\160\141\162\164\145\163\137\x70\x72\x6f\143\145\163\141\x64\x61\163\x20\x41\123\40\104\117\x55\x42\114\105\x20\120\122\x45\x43\x49\x53\111\x4f\116\x29\51\x2f\50\103\101\x53\124\x20\x28\147\x6d\144\137\x70\141\162\x74\145\x73\x5f\x74\157\x74\141\x6c\40\x41\x53\x20\x44\117\x55\x42\x4c\105\x20\x50\122\x45\x43\111\x53\111\117\116\51\51\52\61\x30\x30\x20\x61\163\x20\x70\157\162\x63\x65\156\164\141\152\145\12\x20\x20\x20\x20\40\40\x20\40\x20\40\40\x20\40\40\40\x20\x20\40\x20\x20\54\147\155\x64\137\x6e\x6f\155\142\162\145\12\x20\40\x20\x20\x20\40\x20\40\40\40\40\40\x20\40\x20\x20\x20\40\x20\x20\54\x6d\x65\x2e\155\x65\x5f\x64\x65\163\143\x72\151\160\x63\151\157\156\12\x20\40\x20\40\x20\x20\40\40\x20\x20\x20\40\x20\x20\x20\x20\40\40\x20\40\x2c\x67\x6d\144\137\143\141\156\164\137\x70\x61\147\151\156\x61\137\x74\157\x74\x61\154\xa\x20\40\40\x20\40\x20\x20\x20\x20\40\x20\x20\40\40\x20\x20\40\40\x20\40\x2c\147\x6d\x64\56\x63\x72\145\141\x74\145\144\137\141\164\xa\40\40\40\40\40\x20\40\40\x20\x20\x20\40\x20\x20\40\40\x66\x72\157\155\x20\160\x72\x6f\171\145\143\164\x6f\40\x70\xa\40\40\x20\x20\40\x20\40\40\x20\40\40\40\x20\40\40\x20\x20\x20\152\x6f\x69\x6e\x20\162\145\143\145\160\x63\x69\x6f\156\40\162\x20\x6f\156\40\160\x2e\160\x72\x6f\171\x65\x63\164\x6f\137\x69\x64\40\75\40\x72\56\160\162\157\171\145\143\164\x6f\137\x69\x64\12\40\40\40\40\x20\x20\40\x20\40\x20\x20\x20\x20\x20\x20\x20\40\40\152\x6f\151\x6e\40\147\x65\x6e\x65\162\141\143\x69\157\156\x5f\155\x65\144\x69\x6f\137\x72\x65\x63\145\160\143\x69\x6f\156\40\x67\155\x72\x20\157\x6e\40\x67\155\x72\56\162\x65\143\145\x70\x63\151\x6f\156\x5f\151\x64\x20\75\x20\x72\x2e\162\145\143\x65\160\143\151\157\156\x5f\151\144\xa\x20\x20\x20\40\40\40\40\40\40\40\40\x20\40\40\x20\40\40\x20\x6a\157\x69\156\40\x67\145\x6e\145\x72\x61\143\x69\157\156\137\155\145\x64\151\157\x20\x67\155\x20\x6f\x6e\x20\x67\155\56\147\x6d\137\x69\x64\40\75\40\x67\155\x72\56\147\x6d\x5f\x69\x64\xa\x20\40\x20\x20\x20\40\40\40\x20\x20\40\x20\x20\40\40\40\40\40\x6a\157\151\156\40\x67\145\156\145\x72\141\x63\151\157\x6e\x5f\x6d\x65\144\x69\x6f\137\144\x65\x74\141\x6c\x6c\x65\40\147\155\144\x20\x6f\x6e\40\147\x6d\144\x2e\x67\155\x5f\151\144\40\x3d\40\x67\x6d\x2e\147\155\137\151\144\12\40\x20\x20\40\x20\40\x20\40\x20\40\x20\40\x20\x20\x20\x20\40\40\x6a\157\x69\x6e\x20\155\x65\144\x69\x6f\x5f\x65\170\160\157\162\164\x61\x63\151\x6f\156\x20\x6d\x65\40\157\x6e\40\155\145\56\155\x65\x5f\151\x64\x20\75\40\x67\155\56\x6d\145\x5f\x69\x64\12\x20\x20\40\40\x20\40\x20\40\x20\40\40\40\40\40\40\40\167\x68\145\162\x65\x20\x67\x6d\137\145\x73\x74\141\x64\157\40\75\40\61\40\141\x6e\144\x20\160\x2e\160\x72\157\x79\145\x63\164\157\x5f\x69\x64\x3d\x20\x3a\160\x72\157\x79\145\x63\164\x6f\137\151\144\12\40\x20\x20\40\x20\40\40\40\x20\x20\40\x20\40\40\40\40\x6f\x72\144\145\162\x20\x62\171\x20\147\x6d\56\x67\x6d\137\x69\x64\x2c\147\x6d\x64\56\147\155\x64\137\151\x64\12\x20\40\40\40\x20\x20\40\x20\x20\x20\x20\x20\x29\12\40\x20\x20\x20\x20\x20\40\40\40\x20\x20\40\x73\x65\x6c\x65\143\164\40\x2a\x20\x66\162\x6f\x6d\x20\x64\141\x74\157\163\x20\x77\x68\x65\x72\145\x20\x66\151\154\164\162\157\x20\x3d\40\61\73\xa\40\40\40\x20\40\x20\x20\x20\x20\40\x20\x20", ["\x70\x72\x6f\x79\145\x63\x74\157\137\151\144" => $NMy6V]); goto b6qsU; eUFu1: } public function proyecto_lista_gm_total(Request $a2KoW) { $UGeDd = DB::select("\xa\x20\40\40\x20\40\x20\40\40\40\40\x20\x20\167\x69\164\x68\x20\144\141\164\x6f\163\x20\141\x73\x20\x28\12\x20\x20\40\x20\40\x20\x20\40\x20\40\x20\40\x20\x20\40\40\x73\145\x6c\145\143\164\40\144\151\163\164\x69\156\x63\164\xa\40\40\40\x20\x20\40\x20\40\x20\40\40\40\40\x20\x20\40\40\x20\40\40\55\x2d\x72\x2e\162\x65\x63\145\160\x63\x69\157\x6e\x5f\x69\144\54\x72\56\x72\x65\143\x65\160\x63\x69\157\x6e\x5f\156\157\155\x62\162\145\xa\40\x20\x20\40\40\40\40\40\40\40\x20\40\x20\40\40\40\x20\x20\40\x20\x67\155\162\56\162\145\x63\145\x70\x63\151\x6f\156\137\x69\x64\12\40\40\x20\x20\40\40\40\40\x20\40\40\40\x20\x20\x20\x20\40\x20\40\x20\x2c\162\157\167\x5f\156\165\155\x62\145\x72\x28\x29\x20\157\166\x65\162\x28\x70\141\162\x74\x69\164\x69\157\x6e\x20\142\171\x20\x67\x6d\144\56\x67\x6d\x64\137\151\144\x29\40\x61\x73\x20\146\151\154\164\x72\157\xa\x20\x20\40\40\x20\40\40\x20\x20\x20\40\x20\x20\x20\40\40\40\40\x20\40\54\x67\155\56\147\155\x5f\151\x64\54\x20\147\155\x2e\147\x6d\x5f\x70\x72\x65\x66\x69\x6a\157\12\40\40\x20\x20\40\40\40\40\x20\40\40\40\x20\x20\40\40\x20\x20\40\x20\x2c\147\x6d\x2e\x67\x6d\x5f\143\x6f\162\162\x65\154\x61\164\151\166\x6f\x2c\x20\x67\155\56\147\x6d\x5f\145\163\x74\141\x64\157\12\x20\40\x20\40\40\x20\x20\x20\x20\40\x20\40\x20\40\40\40\40\x20\x20\x20\x2c\147\155\144\x2e\x67\155\144\137\x69\x64\12\x20\x20\40\x20\40\40\x20\x20\x20\x20\40\40\x20\40\40\x20\40\40\x20\40\54\x74\157\x5f\x63\150\x61\162\50\147\155\144\56\147\155\144\137\160\x65\x73\157\137\x6d\x61\170\x69\x6d\157\x2c\47\x46\115\71\x39\x39\x2c\71\71\x39\x2c\x39\71\71\x2c\x39\x39\x39\54\x39\71\x30\104\x30\60\x27\x29\174\x7c\x27\40\x4d\x62\x27\40\x61\x73\x20\x67\155\x64\x5f\160\x65\x73\x6f\x5f\155\141\170\x69\155\157\12\x20\x20\40\40\40\x20\40\40\x20\x20\x20\x20\x20\x20\40\x20\x20\40\x20\x20\x2c\x67\155\x64\137\x65\x73\x74\141\x64\157\12\x20\x20\x20\x20\x20\40\40\40\40\x20\x20\40\40\40\x20\40\40\x20\40\x20\54\164\157\137\x63\150\x61\x72\50\147\155\144\x5f\x70\145\163\x6f\x5f\x6f\143\x75\160\x61\x64\157\x2c\47\106\x4d\71\71\71\x2c\x39\x39\71\x2c\x39\x39\71\x2c\x39\71\x39\54\x39\x39\x30\104\x30\x30\x27\x29\174\174\x27\x20\x4d\142\47\x20\141\163\x20\147\x6d\x64\137\x70\x65\x73\x6f\137\157\143\x75\160\x61\144\157\xa\x20\x20\40\x20\40\40\x20\40\40\40\x20\40\40\40\x20\40\x20\40\40\x20\x2c\147\155\144\137\164\157\x74\x61\x6c\137\144\x6f\143\165\155\145\156\164\x6f\12\x20\x20\x20\x20\x20\40\x20\40\x20\x20\x20\40\x20\40\x20\40\x20\x20\40\x20\54\x67\x6d\144\137\x70\141\x72\164\145\163\x5f\160\162\157\x63\145\163\x61\x64\x61\163\12\x20\x20\40\x20\40\40\40\x20\x20\x20\40\x20\x20\x20\x20\x20\x20\x20\40\40\54\x67\155\x64\x5f\x70\141\162\x74\145\163\137\164\157\x74\x61\154\12\x20\40\40\40\40\x20\x20\40\40\40\x20\x20\x20\x20\40\x20\x20\x20\x20\x20\x2c\50\103\x41\x53\124\x20\x28\x67\x6d\144\137\160\x61\x72\x74\145\x73\x5f\x70\x72\157\x63\x65\163\141\144\x61\x73\40\x41\123\40\104\117\125\102\114\x45\40\120\x52\105\x43\111\123\x49\x4f\116\51\51\57\x28\x43\101\x53\124\40\x28\147\x6d\x64\x5f\160\141\162\164\x65\163\137\164\157\164\x61\x6c\x20\101\123\40\104\117\x55\102\114\x45\40\x50\x52\x45\103\111\123\111\117\x4e\51\51\52\x31\x30\60\40\x61\163\x20\160\157\162\143\x65\x6e\x74\141\x6a\x65\12\40\x20\40\40\x20\40\40\40\40\40\40\40\40\x20\x20\40\40\x20\x20\x20\x2c\x67\x6d\144\137\x6e\x6f\155\142\x72\145\xa\40\40\x20\40\x20\x20\x20\x20\x20\40\40\40\x20\x20\40\40\40\40\40\40\x2c\155\x65\x2e\155\x65\137\144\145\x73\x63\x72\x69\x70\x63\151\x6f\x6e\xa\40\40\x20\40\x20\40\40\40\40\x20\40\40\x20\x20\x20\x20\40\x20\40\x20\54\x67\x6d\x64\x5f\143\141\x6e\x74\137\x70\141\147\x69\156\x61\137\164\157\164\x61\154\12\40\x20\40\x20\x20\x20\x20\40\x20\x20\40\40\40\40\x20\40\x20\x20\x20\40\54\x67\155\144\56\x63\x72\145\141\164\145\144\x5f\141\x74\xa\40\x20\x20\x20\x20\40\40\40\x20\x20\40\x20\x20\40\x20\x20\x66\x72\x6f\x6d\x20\x70\x72\157\171\x65\x63\164\x6f\x20\x70\xa\x20\x20\40\40\40\40\x20\x20\x20\x20\x20\x20\40\x20\40\x20\40\40\152\157\x69\x6e\40\162\145\143\145\x70\143\151\157\x6e\x20\x72\x20\x6f\x6e\40\x70\x2e\x70\x72\157\x79\x65\x63\164\157\x5f\151\x64\40\x3d\40\x72\56\x70\162\157\171\x65\143\164\x6f\137\x69\144\xa\x20\x20\40\40\40\40\x20\40\x20\x20\x20\40\x20\x20\40\40\x20\40\x6a\x6f\151\x6e\x20\x67\145\156\x65\x72\x61\143\x69\x6f\156\137\155\x65\x64\x69\x6f\x5f\162\x65\x63\x65\x70\x63\x69\157\156\40\147\x6d\x72\x20\157\156\40\x67\x6d\x72\x2e\x72\x65\x63\145\160\143\151\x6f\x6e\x5f\x69\x64\40\x3d\x20\162\56\162\x65\143\145\x70\x63\x69\x6f\156\137\x69\x64\12\40\x20\x20\40\40\40\x20\x20\40\40\x20\x20\x20\x20\x20\x20\40\x20\152\157\151\x6e\x20\x67\x65\x6e\145\x72\x61\143\x69\157\156\x5f\x6d\x65\x64\151\x6f\x20\x67\155\x20\157\156\x20\x67\x6d\56\x67\155\x5f\x69\x64\x20\75\40\x67\x6d\x72\56\x67\155\137\x69\x64\xa\x20\40\40\40\x20\40\40\x20\40\40\x20\40\x20\40\40\40\x20\40\152\x6f\151\156\x20\x67\145\x6e\x65\x72\141\143\151\x6f\x6e\x5f\155\145\144\x69\x6f\x5f\144\145\164\x61\154\x6c\145\40\147\x6d\144\40\x6f\156\40\147\155\x64\56\147\155\137\x69\x64\x20\75\x20\x67\155\x2e\x67\155\137\x69\x64\12\x20\40\x20\x20\x20\x20\x20\x20\x20\x20\40\x20\x20\x20\x20\x20\40\x20\x6a\x6f\151\156\40\x6d\x65\x64\x69\x6f\x5f\145\x78\x70\x6f\162\x74\141\143\151\x6f\156\x20\155\145\40\x6f\156\x20\x6d\145\x2e\x6d\145\137\x69\144\x20\x3d\x20\x67\155\x2e\x6d\x65\137\x69\144\xa\40\x20\40\x20\x20\40\x20\40\x20\x20\40\x20\x20\40\40\x20\x77\150\145\x72\145\x20\147\x6d\x5f\145\163\x74\x61\x64\157\40\x3d\x20\61\12\x20\40\40\x20\x20\x20\x20\40\x20\40\40\40\x20\40\40\40\157\x72\x64\145\162\40\x62\171\40\x67\x6d\x2e\x67\155\x5f\151\144\54\147\x6d\144\x2e\147\x6d\144\137\x69\144\xa\x20\40\40\x20\x20\x20\40\x20\40\x20\x20\40\51\xa\40\x20\x20\40\40\x20\40\40\40\x20\40\40\x73\x65\154\x65\x63\164\40\x2a\40\146\x72\157\155\x20\x64\x61\164\157\x73\x20\167\150\145\x72\145\40\x66\151\154\164\162\x6f\40\x3d\x20\x31\73\xa\40\x20\x20\x20\x20\x20\x20\x20\40\x20\40\40", []); return $UGeDd; } public function listar_captura_organizar() { goto ud2hF; SzJ3l: $zF1_H = new medio_exportacion(); goto b9BCR; b9BCR: $Qbbvx = $zF1_H->select("\155\145\137\143\141\160\x61\143\151\x64\x61\x64")->where("\155\145\x5f\x69\144", $ZRdQ5)->first(); goto fEuWL; dYE49: return $xMK18; goto StfVA; zOqyQ: $xMK18 = $THurt->organizar($qWOWh, $Y8HK3, $Qbbvx["\155\x65\x5f\143\141\160\141\x63\x69\x64\x61\x64"], $qy8rI); goto dYE49; yweiF: $ZRdQ5 = request("\x6d\x65\x64\151\157\x5f\x69\144"); goto SzJ3l; fEuWL: $THurt = new generacion_medio(); goto zOqyQ; ud2hF: $qy8rI = request("\x61\162\x72\x61\171\x5f\x63\150\x65\x63\153"); goto FixRo; FixRo: $qWOWh = request("\x6e\x6f\155\x62\162\x65"); goto rBSQA; rBSQA: $Y8HK3 = request("\143\x6f\x72\162\145\154\x61\x74\x69\166\157"); goto yweiF; StfVA: } public function generar_json($InNiT, $klxTy, $Bvs8W) { goto LNbXj; W1gcL: if (!($zbIDR < count($eElQN))) { goto WdYIB; } goto ID6vn; E_q0B: goto Cf3Qh; goto Lf8GH; sCiPe: $BufTn[] = $c2fdG; goto U9TVI; GxSfp: $vmja_ = $O5ErV->json_plantilla($InNiT); goto jN5bp; p0UFh: $BufTn = $O5ErV->obtener_files_recepcion_final($lZY3w, $klxTy, $S93MS, $InNiT); goto elmbD; swp72: $O5ErV = new files(); goto t8rL1; U9TVI: return $BufTn; goto p4NY3; ID6vn: $lZY3w[] = $eElQN[$zbIDR]->Ml4BR; goto c8vE7; t8rL1: $VlLb9 = $O5ErV->prueba_arbol_final($InNiT, $klxTy, $S93MS, $Bvs8W); goto N4QGt; jN5bp: $c2fdG = $O5ErV->guardar_file_json($klxTy, "\160\x6c\x61\156\164\151\x6c\154\141\x5f" . $InNiT . "\x2e\x6a\163\x6f\x6e", $vmja_); goto sCiPe; yG8fK: $eElQN = $E2nqA->recepciones_x_gmd($InNiT); goto WED_T; k_65S: $qg3tE = "\x6d\141\x69\x6e\137" . $InNiT . "\56\x6a\x73\157\156"; goto vCmHO; elmbD: $BufTn[] = $Z3vub; goto GxSfp; WED_T: $lZY3w = []; goto xfISj; c8vE7: P3Q84: goto LmFgH; LNbXj: $E2nqA = new generacion_medio(); goto yG8fK; Lf8GH: WdYIB: goto k_65S; LComj: Cf3Qh: goto W1gcL; xfISj: $zbIDR = 0; goto LComj; LmFgH: $zbIDR++; goto E_q0B; vCmHO: $S93MS = "\162\145\143\145\160\143\x69\157\x6e\137"; goto swp72; N4QGt: $Z3vub = $O5ErV->guardar_file_json($klxTy, $qg3tE, [$VlLb9]); goto p0UFh; p4NY3: } public function confirmar_medio(Request $a2KoW) { goto EQiOS; qrUnf: JFuZT: goto kT22E; k8rsr: return $this->crear_objeto("\145\162\162\x6f\162", "\116\157\x20\143\157\155\x70\x6c\x65\x74\x61\x64\157\x2c\40\141\154\x67\x6f\x20\163\141\x6c\151\303\xb3\x20\155\141\x6c"); goto fhGQy; CbybR: WlKqw: goto GVna6; osI6G: $IQ571 = $a2KoW->ip(); goto gHHNz; fhGQy: goto NSMei; goto bro0T; D1D19: $THurt = new generacion_medio(); goto EsDmD; EQiOS: $qy8rI = request("\141\162\x72\141\x79\137\x63\150\x65\143\x6b"); goto rgUPT; rgUPT: $BAULi = 0; goto D1D19; EsDmD: $eDsL1 = storage_path() . "\x2f\x61\x70\160\57\144\157\143\x75\x6d\145\x6e\x74\157\163\57"; goto GJMRF; GVna6: if ($BAULi == 0) { goto okODQ; } goto k8rsr; kT22E: $tstHz = session("\165\x73\x75\x61\162\151\x6f\x5f\151\144"); goto osI6G; TLlQb: return $this->crear_objeto("\x6f\153", "\123\x65\x20\145\163\164\303\xa1\x20\x63\x6f\160\151\141\x6e\144\x6f\40\x6c\x6f\163\40\141\x72\x63\x68\x69\x76\x6f\x73\56"); goto ODT9a; S_Z04: $eGQtQ = $eDsL1; goto d4JaV; V1rRI: foreach ($qy8rI as $InNiT) { goto HsePn; MnqB5: if (!(count($Z22Op) == 0)) { goto lTqM2; } goto f66Ez; x_qyu: $Z22Op = $w2olO->validador_captura_listar_ac($InNiT, 5); goto MnqB5; HsePn: $w2olO = new generacion_medio_detalle_captura(); goto x_qyu; f66Ez: lTqM2: goto ZGKqE; ZGKqE: UAT3P: goto Hu40j; Hu40j: } goto qrUnf; ODT9a: NSMei: goto ji34H; GJMRF: $UtTs5 = "\x64\x6f\x63\165\155\145\x6e\164\x6f\163\57"; goto S_Z04; gHHNz: foreach ($qy8rI as $InNiT) { goto CLiiS; NZKmr: $ple8J = curl_error($ifRr6); goto KjgMw; qmsxW: $dCAx9 = curl_exec($ifRr6); goto jcwpE; oojDA: $KnCxh = substr($sd1Sq, stripos($sd1Sq, "\52\52\52\52") + 4); goto SATzk; P9o6t: $OkIj6 = env("\x52\x55\x54\x41\x5f\105\112\105\x43\x55\124\x41\102\114\x45"); goto HbPT3; bGE7c: fclose($DncZ8); goto KotPC; Zivp8: $BAULi++; goto LYn1r; KotPC: $nmvPG = "\x68\164\x74\160\72\57\x2f\154\157\143\x61\x6c\150\157\163\x74\72\63\x30\60\60\x2f\x6d\157\x76\145\162"; goto uRfe_; xvszH: $DncZ8 = fopen($eGQtQ . "\x70\162\165\x65\142\x61\137" . $InNiT . "\x2e\x74\x78\164", "\167"); goto Dn0K1; cIDPR: mQVPa: goto jl8gc; ep2oH: $FzIjk = storage_path() . "\x2f\x61\160\x70\x2f\x64\157\143\165\x6d\145\156\x74\x6f\x73\57\142\154\x61\164\164\163\143\141\x6e\56\145\x78\145"; goto ioLNu; bfTWI: if (!($gXAUv["\x65\x73\164\x61\x64\x6f"] == false)) { goto x4GG8; } goto Zivp8; N8TBf: $JNUKH = []; goto rF9MD; CLiiS: $MCSHB = $THurt->listar_rutas($eDsL1, $UtTs5, $InNiT, $tstHz, $IQ571); goto N8TBf; XMf2p: $klxTy = $eGQtQ; goto jSF3U; rF9MD: $MCSHB = array_merge($MCSHB, $JNUKH); goto XMf2p; QmltG: $MCSHB[] = (object) ["\x72\165\x74\141" => $OHkQ0 . "\x2a\x2a\x2a\52" . $jmTe3 . "\57\166\151\163\157\x72\x2f\x76\151\163\165\x61\154\151\172\141\144\x6f\x72\57"]; goto ep2oH; m9Nc8: $jmTe3 = substr($KnCxh, 0, $pLSFf) . "\57"; goto vUeEj; SATzk: $pLSFf = stripos($KnCxh, "\57", stripos($KnCxh, "\x2f") + 1); goto m9Nc8; sJRyK: $Bvs8W = env("\x46\117\114\x44\x45\122\137\107\105\x4e\105\x52\x41\x44\x4f"); goto xvszH; LYn1r: x4GG8: goto NZKmr; HbPT3: $MCSHB[] = (object) ["\162\165\x74\x61" => $OkIj6 . "\x2a\x2a\x2a\52" . $jmTe3]; goto sJRyK; jcwpE: $gXAUv = json_decode($dCAx9, true); goto bfTWI; uRfe_: $rfUyV = ["\151\144" => $InNiT, "\162\x75\x74\141\x5f\x69\156\151\x63\x69\157" => $eGQtQ, "\x72\x75\x74\141\137\x64\x65\x73\x74\151\x6e\157" => $Bvs8W, "\x72\165\164\x61\137\x6c\x69\x73\164\141" => $eGQtQ . "\x70\x72\165\145\x62\141\137" . $InNiT . "\x2e\164\170\164"]; goto pegjf; ioLNu: $RzaFz = env("\x46\x4f\x4c\104\x45\x52\137\107\105\116\105\x52\x41\104\117") . $jmTe3 . "\x62\154\141\164\164\x73\143\x61\x6e\56\145\x78\145"; goto P9o6t; Tzv_g: $OHkQ0 = env("\x52\125\x54\101\137\126\111\x53\117\122"); goto QmltG; jSF3U: $sd1Sq = $MCSHB[0]->kat_5; goto oojDA; vUeEj: $ZT3zj = $this->generar_json($InNiT, $klxTy, "\57\x76\x69\x73\157\162\x2f\x64\141\x74\141\x62\141\x73\145\x2f"); goto MI2Si; rZi5v: Pbtjk: goto Tzv_g; BDtz1: $ifRr6 = curl_init(); goto ZZZ0m; pegjf: $kWmnU = json_encode($rfUyV, true); goto BDtz1; ZZZ0m: curl_setopt_array($ifRr6, array(CURLOPT_PORT => "\63\60\60\x30", CURLOPT_URL => $nmvPG, CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', CURLOPT_MAXREDIRS => 10, CURLOPT_TIMEOUT => 3000, CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "\120\x4f\x53\x54", CURLOPT_POSTFIELDS => $kWmnU, CURLOPT_HTTPHEADER => array("\x43\x6f\x6e\x74\145\156\x74\x2d\124\171\x70\x65\72\40\x61\x70\x70\x6c\x69\x63\x61\x74\151\x6f\156\x2f\152\x73\x6f\x6e", "\120\x6f\163\164\155\141\x6e\55\124\x6f\153\x65\156\x3a\x20\40\146\x36\63\x38\63\x65\x34\x34\x2d\x61\x66\143\x31\x2d\x34\x37\143\67\x2d\141\66\62\144\55\143\142\145\64\x38\x32\60\143\x36\x66\142\x37", "\143\x61\x63\150\x65\x2d\x63\157\x6e\164\x72\x6f\154\x3a\x20\156\x6f\55\143\x61\x63\150\145"))); goto qmsxW; MI2Si: foreach ($ZT3zj as $dE1Mk) { $MCSHB[] = (object) ["\x72\x75\x74\141" => str_replace($klxTy, '', "\57" . $dE1Mk) . "\x2a\52\x2a\52" . $jmTe3 . "\x2f\166\x69\x73\157\162\x2f\144\141\x74\x61\142\x61\x73\145\57" . str_replace($klxTy, '', "\57" . $this->retirar_gmd_id_filename($dE1Mk))]; p_i0s: } goto rZi5v; qZyCl: sM78q: goto bGE7c; Dn0K1: foreach ($MCSHB as $vV4jE) { fwrite($DncZ8, $vV4jE->kat_5 . "\12"); N7hHs: } goto qZyCl; KjgMw: curl_close($ifRr6); goto cIDPR; jl8gc: } goto CbybR; d4JaV: $Bvs8W = env("\106\117\x4c\x44\x45\122\x5f\107\x45\x4e\x45\x52\101\104\x4f"); goto V1rRI; bro0T: okODQ: goto TLlQb; ji34H: } public static function retirar_gmd_id_filename($ct4hK) { goto J9S6Y; KR9Xn: array_pop($rUQ1k); goto jRamK; jRamK: $nmPjx[count($nmPjx) - 2] = implode("\137", $rUQ1k); goto qPal1; qPal1: return implode("\56", $nmPjx); goto k8oWg; J9S6Y: $nmPjx = explode("\56", $ct4hK); goto nMJoQ; nMJoQ: $rUQ1k = explode("\x5f", $nmPjx[count($nmPjx) - 2]); goto KR9Xn; k8oWg: } public function ver_generacion_medio() { goto vqHkm; KtJUd: $wIi07["\147\x6d\137\x70\x65\163\x6f\137\157\x74\162\157\x73"] = $e89Ro["\x67\155\x5f\160\x65\x73\157\x5f\x6f\164\x72\x6f\163"]; goto mSzcm; CvyQW: $vYteJ = new generacion_medio_detalle(); goto zD858; WnLH5: foreach ($jeSbe as $m6C9V) { goto T0RDR; eRWNJ: $Q05lD["\147\155\x5f\151\144"] = $m6C9V->l2cGu; goto nT3vk; AxaiO: $Q05lD["\147\x6d\144\x5f\x69\144"] = $m6C9V->x6Hj9; goto eRWNJ; Q1T4T: In4uK: goto tYB0b; nT3vk: $Q05lD["\x67\x6d\x64\x5f\156\x6f\155\x62\x72\x65"] = $m6C9V->x3ASw; goto P7148; ZmDSr: $Q05lD["\147\155\x64\137\x70\145\163\x6f\x5f\157\x63\x75\160\x61\x64\x6f"] = $m6C9V->LuwhW; goto ZvId_; P7148: $Q05lD["\147\155\x64\x5f\x70\145\x73\157\x5f\155\x61\170\x69\155\157"] = $m6C9V->wlHKW; goto ZmDSr; yaAvL: $Q05lD["\147\x6d\x64\x5f\x65\x73\x74\x61\x64\x6f"] = $m6C9V->gYjP7; goto iWdQL; iWdQL: $o_TFP[] = $Q05lD; goto Q1T4T; T0RDR: $Q05lD = []; goto AxaiO; ZvId_: $Q05lD["\147\155\x64\137\164\157\x74\141\x6c\x5f\144\x6f\143\x75\155\145\156\164\157"] = $m6C9V->Y4UoH; goto yaAvL; tYB0b: } goto pbYP_; s0dSy: $wIi07["\147\155\137\x63\x6f\162\162\x65\x6c\x61\x74\151\166\157"] = $e89Ro["\147\x6d\x5f\143\x6f\162\162\145\x6c\141\164\x69\166\x6f"]; goto c1cjz; vqHkm: $mye7P = request("\147\155\137\151\144"); goto hHyeM; dMlR4: $wIi07 = []; goto Uk98U; x34N2: $c2o1S[] = $wIi07; goto yHvQT; Uk98U: $wIi07["\x67\155\x5f\x70\162\145\146\x69\x6a\x6f"] = $e89Ro["\x67\x6d\137\x70\162\x65\x66\151\x6a\157"]; goto s0dSy; yHvQT: $c2o1S[] = $o_TFP; goto hKXT0; mSzcm: $o_TFP = []; goto WnLH5; hKXT0: return $c2o1S; goto XfIlG; c1cjz: $wIi07["\x6d\145\x5f\151\x64"] = $e89Ro["\x6d\145\x5f\151\144"]; goto KtJUd; hHyeM: $THurt = new generacion_medio(); goto e0qTK; e0qTK: $e89Ro = $THurt->select("\x67\155\x5f\160\162\x65\146\151\x6a\157", "\x67\x6d\137\143\x6f\162\x72\145\154\141\x74\x69\x76\x6f", "\155\x65\137\x69\144", "\x67\155\x5f\x70\x65\x73\x6f\x5f\x6f\x74\x72\157\x73")->where("\147\155\137\151\144", $mye7P)->first(); goto CvyQW; pbYP_: u55Lo: goto x34N2; zD858: $jeSbe = $vYteJ->select("\147\x6d\144\x5f\151\144", "\147\x6d\x5f\151\144", "\x67\x6d\x64\137\x6e\157\155\x62\x72\145", "\x67\155\144\137\x70\x65\x73\157\137\x6d\x61\x78\151\155\x6f", "\x67\x6d\x64\x5f\x70\x65\x73\157\x5f\x6f\143\165\160\x61\144\157", "\x67\155\144\x5f\x74\157\x74\141\154\x5f\x64\157\x63\x75\x6d\x65\x6e\x74\x6f", "\x67\155\144\x5f\x67\x72\x75\160\x6f", "\x67\x6d\x64\137\x65\x73\164\141\x64\x6f")->where("\x67\155\137\x69\144", $mye7P)->get(); goto dMlR4; XfIlG: } public function modal_acta_cierre_gmd() { goto Zf9Sn; FRc8s: return $j9vLy; goto ywffA; bUncS: $w2olO = new generacion_medio_detalle_captura(); goto IKNzy; IKNzy: $j9vLy = $w2olO->captura_listar_acta_cierre($InNiT); goto FRc8s; Zf9Sn: $InNiT = request("\147\x6d\144\x5f\151\144"); goto bUncS; ywffA: } public function modal_calibradora_gmd() { goto nSvuh; eep0S: doOmq: goto HGkbP; nSvuh: $InNiT = request("\x67\155\144\x5f\151\144"); goto ksCld; e7wVc: if (!(count($Z22Op) == 0)) { goto doOmq; } goto tRZaR; r53tk: $Z22Op = $w2olO->validador_captura_listar_ac($InNiT, 4); goto e7wVc; tRZaR: return $this->crear_objeto("\x45\162\x72\157\x72", "\x43\157\155\x70\x72\165\145\142\145\40\163\151\40\x63\x75\145\x6e\x74\141\40\x63\157\x6e\x20\x41\x63\x74\141\x20\x2e"); goto eep0S; Mip4J: return $j9vLy; goto MdSJl; HGkbP: $j9vLy = $w2olO->captura_listar_calibradora_cierre($InNiT); goto Mip4J; ksCld: $w2olO = new generacion_medio_detalle_captura(); goto r53tk; MdSJl: } public function verify_path_capturas($W6Khq) { goto TEDta; KYQbJ: if (!(isset($RW6dL["\x70\162\x6f\171\145\143\x74\157\137\156\x6f\x6d\x62\162\145"]) && isset($RW6dL["\x72\x65\x63\x65\160\x63\151\157\x6e\137\156\x6f\x6d\x62\162\x65"]))) { goto YP03u; } goto k32IL; nGI0_: $OzsFN .= "\57" . $RW6dL["\x70\162\157\171\145\x63\x74\157\x5f\156\157\155\142\x72\x65"]; goto Tqgox; wBh_a: GzJmD: goto Fgd1m; Cd0r2: Xf5Y5: goto mvEKQ; O4d4b: goto dML8O; goto Cd0r2; mvEKQ: dML8O: goto wBh_a; tv9qk: return $OzsFN; goto ezj0F; f55Wd: YP03u: goto tv9qk; gDsnz: $RW6dL = recepcion::where("\x72\145\x63\145\x70\143\x69\157\156\x5f\x69\x64", $W6Khq)->join("\160\x72\x6f\x79\x65\x63\164\157\40\141\x73\40\160", "\x70\56\x70\162\x6f\171\x65\x63\164\157\x5f\x69\144", "\x72\145\143\x65\160\x63\x69\x6f\x6e\56\160\x72\157\x79\145\143\164\157\x5f\x69\x64")->first(); goto KYQbJ; JtKQI: $OzsFN .= "\x2f" . $RW6dL["\162\145\143\x65\x70\x63\151\x6f\x6e\137\156\x6f\155\142\162\x65"]; goto U6Qtv; Fgd1m: u4XYv: goto f55Wd; k32IL: if (!self::ensure_path_directory($D82YK . $OzsFN . "\57" . $RW6dL["\x70\x72\157\x79\x65\143\x74\157\x5f\x6e\157\155\142\162\145"])) { goto u4XYv; } goto nGI0_; TEDta: $D82YK = storage_path() . "\x2f\141\160\160\x2f"; goto f6cDa; U6Qtv: if (self::ensure_path_directory($D82YK . $OzsFN . "\x2f\x69\x6d\141\147\x65\156\145\x73")) { goto Xf5Y5; } goto XE1u7; Tqgox: if (!self::ensure_path_directory($D82YK . $OzsFN . "\x2f" . $RW6dL["\162\145\143\x65\160\143\x69\x6f\156\x5f\156\x6f\155\x62\162\145"])) { goto GzJmD; } goto JtKQI; XE1u7: return false; goto O4d4b; f6cDa: $OzsFN = "\x64\157\x63\165\155\x65\156\164\157\x73"; goto gDsnz; ezj0F: } public function podergreen_cont() { goto z9KDA; z9KDA: $O5ErV = new files(); goto tFLBs; kWQig: return $O5ErV->podergreen("\62\61", $eDsL1); goto p1_xi; tFLBs: $eDsL1 = storage_path() . "\x2f\x61\x70\160\x2f\x64\157\x63\165\x6d\x65\156\164\157\x73\57"; goto kWQig; p1_xi: } public function podergreenv2_cont() { goto y1FJJ; TDyex: $eDsL1 = storage_path() . "\x2f\x61\x70\x70\x2f\x64\157\143\165\x6d\145\x6e\x74\157\x73\57"; goto CXb5a; y1FJJ: $O5ErV = new files(); goto TDyex; CXb5a: return $O5ErV->podergreenv2("\62\61", "\x34\61", "\x33\x36", $eDsL1); goto LSgmv; LSgmv: } public function generar_json_ocr() { return respuesta::ok(); } public function generar_json_database() { return respuesta::ok(); } public function generar_discos_independientes() { return respuesta::ok(); } }
