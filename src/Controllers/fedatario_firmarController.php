<?php
/*   __________________________________________________
    |  Obfuscated by YAK Pro - Php Obfuscator  2.0.12  |
    |              on 2021-02-18 13:04:15              |
    |    GitHub: https://github.com/pk-fr/yakpro-po    |
    |__________________________________________________|
*/
 namespace Fedatario\Controllers; use Illuminate\Http\Request; use App\fedatario_firmar; use App\recepcion; use App; use View; use Illuminate\Support\Facades\DB; use Carbon\Carbon; use Illuminate\Support\Facades\File; trait fedatario_firmarController { public function index() { return view::make("\x66\145\x64\x61\x74\141\x72\151\x6f\137\x66\x69\x72\x6d\141\162\56\151\x6e\x64\145\x78\x2e\x63\157\156\164\x65\156\164"); } public function arbol_fedatario() { goto qgXj2; XM1e7: return $V7Bc2; goto fr09_; qD0cb: $qgdp2 = ''; goto uaqDN; RNTI_: $MCJwS = "\x63\x61\x70\x74\165\x72\x61\x5f"; goto Ir5jZ; p8Zzd: $amP75 = ''; goto RNTI_; n0_MK: $oViTd = array(); goto DcmRu; Ir5jZ: foreach ($pmu5n as $V_4ji) { goto hAUWN; TuivK: rNObH: goto dtdcp; go_Wg: $xAJPq = array(); goto CSdqc; hAUWN: if (!($V_4ji->Ml4BR != $YxJ_4 && $YxJ_4 != "\60")) { goto H3Ll3; } goto xLUu4; TX6P_: $amP75 = $V_4ji->QF1yw; goto VAvgP; ILGIP: $YxJ_4 = $V_4ji->Ml4BR; goto zmtIJ; xLUu4: $oViTd[] = ["\x69\144\137\x72\x65\x63\145\x70\x63\151\x6f\x6e" => $YxJ_4, "\x72\145\x63\x65\x70\x63\x69\157\156\x5f\x74\x69\x70\157" => $qgdp2, "\x74\145\x78\164" => $vncFU]; goto go_Wg; cjmf9: $O07BY = $V_4ji->J1yuX; goto TX6P_; dtdcp: H3Ll3: goto ILGIP; zmtIJ: $qgdp2 = $V_4ji->HFPIs; goto sROCR; CSdqc: if (!($V_4ji->J1yuX != $O07BY && $O07BY != "\x30")) { goto rNObH; } goto lUHO5; XLCG8: $oViTd = array(); goto TuivK; sROCR: $vncFU = $V_4ji->U8elX; goto cjmf9; lUHO5: $V7Bc2[] = ["\x69\x64\137\x70\162\157\171\x65\143\x74\x6f" => $O07BY, "\164\x65\170\x74" => $amP75, "\143\150\151\x6c\x64\x72\x65\156" => $oViTd]; goto XLCG8; VAvgP: JJdBp: goto YhKzZ; YhKzZ: } goto SuxX2; ToF_E: $oViTd[] = ["\151\144\x5f\x72\145\x63\x65\x70\143\x69\x6f\x6e" => $YxJ_4, "\x72\145\143\145\160\143\x69\x6f\156\x5f\164\x69\x70\x6f" => $qgdp2, "\x74\x65\x78\164" => $vncFU]; goto tNYod; uaqDN: $O07BY = "\x30"; goto p8Zzd; xnrJY: if (!($YxJ_4 != "\60" && $O07BY != "\x30")) { goto p5SO1; } goto ToF_E; SuxX2: CF_PW: goto xnrJY; tNYod: $V7Bc2[] = ["\x69\144\137\160\x72\x6f\x79\x65\x63\x74\x6f" => $O07BY, "\164\x65\170\x74" => $amP75, "\x63\150\x69\154\x64\x72\x65\156" => $oViTd]; goto qGQ7w; qgXj2: $pmu5n = (new fedatario_firmar())->arbol_fedatario_firmar(); goto iE_Xj; qGQ7w: p5SO1: goto XM1e7; ptuew: $vncFU = ''; goto qD0cb; DcmRu: $xAJPq = array(); goto htEKL; htEKL: $YxJ_4 = "\x30"; goto ptuew; iE_Xj: $V7Bc2 = array(); goto n0_MK; fr09_: } public function listar_documentos_fedatario() { goto Pfh7K; bd3xO: $t_WVr = request("\x63\x61\160\164\165\162\x61\x5f\145\x73\164\141\x64\157"); goto lyxoP; qcdLK: return $pmu5n; goto r3254; Pfh7K: $W6Khq = request("\x72\145\x63\x65\160\x63\x69\157\x6e\x5f\x69\144"); goto bd3xO; rwhZv: $pmu5n = DB::select("\12\x20\40\40\x20\40\x20\40\40\40\x20\x20\40\167\x69\164\x68\x20\x64\157\x63\x75\x6d\x65\x6e\164\157\137\x69\155\141\147\x65\x6e\40\101\x53\40\x28\12\x20\40\40\40\40\40\x20\x20\x20\x20\x20\40\x20\x20\x20\40\163\145\x6c\x65\143\x74\xa\40\x20\40\x20\40\40\40\40\40\40\x20\x20\40\x20\x20\40\x20\x20\x20\40\x20\143\56\144\157\x63\x75\155\x65\156\164\157\x5f\151\144\x2c\12\40\x20\x20\x20\40\40\x20\x20\40\40\40\40\40\40\x20\40\40\x20\x20\40\x20\155\141\x78\50\x63\x61\x73\164\x28\151\155\x61\147\145\156\x5f\x70\141\x67\x69\156\141\40\x61\x73\x20\151\x6e\x74\x65\x67\x65\x72\x29\x29\40\141\x73\x20\x74\157\164\141\x6c\x5f\160\x61\x67\x2c\xa\40\40\40\40\x20\x20\x20\40\40\x20\40\40\40\40\x20\x20\x20\40\40\x20\x20\143\56\144\x6f\143\x75\x6d\x65\156\x74\x6f\137\145\x73\x74\141\144\x6f\54\xa\40\x20\x20\x20\40\40\40\x20\x20\40\40\x20\x20\x20\40\x20\x20\x20\x20\x20\x20\x63\x2e\x64\x6f\143\x75\x6d\145\156\x74\x6f\x5f\156\x6f\155\x62\x72\145\54\xa\40\40\x20\40\40\x20\x20\40\40\x20\x20\x20\40\x20\40\x20\40\x20\x20\x20\40\x63\56\143\x61\160\x74\165\162\141\x5f\151\144\54\12\x20\x20\x20\x20\40\x20\40\40\x20\x20\x20\x20\x20\40\40\x20\40\x20\40\x20\40\143\56\141\x64\x65\x74\141\x6c\154\x65\137\151\x64\xa\x20\x20\x20\x20\x20\40\x20\40\40\x20\x20\40\40\40\40\40\x66\162\x6f\155\x20\144\x6f\143\165\x6d\145\156\164\157\40\143\xa\40\40\x20\x20\40\40\x20\x20\40\x20\x20\40\x20\40\x20\x20\152\157\x69\156\x20\x69\x6d\x61\x67\x65\156\x20\x69\x20\x6f\156\x20\143\56\x64\x6f\143\165\x6d\145\156\164\x6f\x5f\151\144\x20\75\40\x69\x2e\144\x6f\x63\x75\155\145\156\x74\x6f\x5f\151\144\xa\x20\40\40\x20\x20\40\x20\40\x20\x20\x20\40\40\40\x20\40\x67\162\157\x75\x70\x20\x62\171\40\143\56\x64\x6f\143\165\155\145\156\x74\157\137\151\144\x29\54\xa\40\40\x20\x20\40\40\40\40\40\40\x20\x20\145\x73\161\165\145\x72\171\40\101\123\40\x28\12\x20\x20\x20\x20\x20\40\x20\x20\x20\40\40\x20\x20\40\163\x65\154\145\x63\x74\x20\x64\56\x61\x64\x65\164\141\154\x6c\145\137\x69\144\54\12\x20\40\40\40\x20\x20\x20\40\40\40\40\40\40\x20\40\x20\x20\x20\144\x2e\x61\144\x65\x74\x61\x6c\x6c\x65\x5f\x6e\157\155\x62\162\x65\x2c\xa\40\40\x20\x20\40\x20\x20\x20\40\40\x20\x20\x20\40\x20\40\x20\40\144\x2e\141\x64\145\164\x61\154\x6c\145\137\x70\145\x73\157\x2c\12\40\x20\40\x20\x20\40\x20\40\40\x20\x20\x20\x20\40\40\40\40\40\144\x2e\x61\x64\145\164\141\x6c\154\x65\x5f\x75\x72\154\x2c\12\x20\40\x20\x20\40\x20\x20\x20\40\40\40\40\x20\x20\x20\x20\40\x20\x62\x2e\143\x61\160\x74\165\x72\141\137\145\163\164\x61\144\x6f\x2c\12\40\40\40\x20\x20\40\x20\40\40\40\40\x20\x20\x20\40\x20\x20\40\x62\x2e\143\x61\x70\x74\x75\x72\141\x5f\145\x73\x74\141\144\157\x5f\x67\154\142\54\12\x20\40\x20\40\x20\x20\x20\40\40\40\40\40\x20\x20\40\x20\40\40\142\56\x63\x61\160\164\165\162\x61\x5f\146\x69\x6c\145\137\151\144\54\12\x20\x20\x20\40\x20\40\40\40\40\40\x20\40\x20\x20\40\x20\x20\40\142\56\143\x61\x70\x74\165\x72\141\137\151\144\x2c\12\40\40\40\x20\x20\x20\x20\40\40\x20\40\x20\40\x20\x20\40\x20\40\x62\56\x63\154\x69\145\156\x74\x65\137\151\144\54\12\40\40\40\x20\40\40\x20\x20\40\x20\40\x20\x20\40\x20\x20\40\40\143\x2e\x64\157\143\x75\155\145\156\164\157\x5f\145\163\164\141\144\157\54\xa\x20\40\x20\x20\x20\x20\x20\40\x20\40\x20\40\40\40\40\40\x20\x20\x63\x2e\x64\157\143\x75\155\145\x6e\164\x6f\137\x69\144\54\12\40\40\x20\40\40\40\x20\x20\x20\x20\40\40\x20\40\40\40\x20\x20\x63\x2e\x64\157\x63\165\x6d\x65\x6e\164\x6f\x5f\x6e\157\155\142\x72\x65\54\12\x20\x20\x20\40\x20\x20\40\x20\x20\40\x20\x20\40\40\x20\x20\40\40\x63\56\x74\x6f\164\x61\x6c\x5f\x70\x61\147\54\12\40\x20\40\40\x20\40\40\x20\40\x20\40\x20\40\x20\x20\x20\x20\40\x65\56\x66\x65\x64\141\164\141\162\151\157\137\x66\x69\162\155\141\x72\x5f\x65\163\164\141\144\157\54\xa\x20\40\40\40\40\40\40\x20\x20\40\x20\40\40\40\x20\x20\40\40\x65\x2e\146\145\x64\x61\x74\141\x72\151\157\x5f\146\151\x72\x6d\x61\162\137\151\144\x2c\xa\x20\40\x20\x20\40\40\x20\40\40\40\40\40\x20\x20\x20\x20\x20\40\x65\x2e\146\x65\144\141\164\141\x72\151\157\137\x69\x64\54\12\40\x20\40\x20\40\40\x20\x20\x20\40\40\x20\x20\x20\x20\40\x20\40\x62\x2e\x66\154\x75\x6a\157\137\x69\x64\137\141\x63\164\165\141\154\x2c\xa\40\40\x20\40\40\x20\40\x20\x20\x20\40\40\x20\x20\40\x20\x20\40\141\56\160\x72\157\171\x65\143\x74\157\137\151\144\x2c\12\x20\x20\40\x20\x20\40\40\40\x20\40\x20\40\40\x20\x20\x20\x20\40\x61\x2e\x72\145\143\x65\x70\x63\x69\157\156\137\151\144\54\12\x20\40\x20\40\x20\x20\x20\x20\x20\x20\40\40\40\x20\x20\x20\x20\40\141\56\162\x65\143\x65\160\x63\151\157\x6e\137\164\151\x70\x6f\12\x20\40\40\x20\x20\40\x20\40\40\x20\40\40\x20\x20\x66\x72\157\x6d\40\x72\x65\143\x65\160\x63\151\x6f\156\40\141\12\40\x20\40\40\x20\x20\40\x20\x20\40\40\40\40\x20\x6c\x65\146\x74\40\152\157\151\x6e\x20\x63\x61\160\164\x75\x72\141\x20\142\40\x6f\x6e\x20\x61\56\162\145\143\x65\x70\143\151\x6f\156\x5f\151\144\40\75\40\x62\x2e\x72\x65\x63\x65\x70\143\151\x6f\x6e\x5f\x69\x64\xa\40\40\40\40\40\x20\x20\40\40\x20\x20\x20\40\x20\x6c\x65\x66\164\x20\x6a\157\x69\156\x20\144\x6f\143\165\155\x65\x6e\164\157\137\151\x6d\x61\147\145\156\x20\x63\40\157\x6e\x20\142\56\x63\x61\160\164\165\x72\141\137\151\144\40\x3d\x20\x63\x2e\143\x61\160\x74\165\162\141\x5f\x69\144\xa\40\x20\x20\x20\40\40\x20\40\40\x20\40\x20\x20\40\154\145\146\164\x20\152\x6f\151\156\40\x61\x64\145\x74\x61\154\x6c\x65\40\144\40\x6f\156\40\x64\56\141\144\145\164\x61\154\x6c\145\137\151\x64\x20\x3d\40\x63\56\x61\144\145\x74\141\x6c\x6c\145\137\x69\x64\xa\40\40\40\x20\x20\40\40\40\x20\40\x20\x20\40\x20\152\157\x69\156\40\146\x65\x64\x61\x74\141\162\151\157\137\x66\x69\162\155\141\x72\x20\x65\x20\157\156\40\x65\x2e\x63\x61\x70\164\x75\162\x61\x5f\151\144\40\75\40\142\x2e\x63\141\x70\x74\165\162\x61\137\x69\144\12\x20\x20\40\x20\x20\x20\x20\x20\40\x20\40\40\x20\x20\167\x68\x65\162\x65\40\x61\x2e\x72\x65\x63\x65\160\x63\x69\x6f\156\x5f\x69\x64\40\75\x20\72\x72\x65\x63\x65\160\x63\151\x6f\156\137\x69\144\12\x20\40\40\40\40\40\x20\40\40\x20\x20\x20\40\40\40\x20\141\156\x64\40\x62\56\x63\141\x70\164\x75\162\141\137\x65\x73\x74\141\144\x6f\40\75\x20\x3a\143\x61\160\164\x75\162\141\137\145\x73\x74\141\144\x6f\12\x20\40\40\x20\x20\x20\40\40\40\x20\40\x20\40\40\40\40\x61\156\144\40\142\x2e\143\x61\x70\x74\x75\162\x61\137\145\x73\164\x61\x64\x6f\x5f\147\154\142\x20\75\40\x27\146\145\x64\x5f\x66\x69\162\47\xa\x20\x20\x20\x20\40\40\x20\x20\x20\40\40\40\x20\40\x20\40\157\x72\x64\145\x72\40\x62\x79\xa\40\x20\x20\40\40\x20\x20\x20\x20\40\x20\x20\40\40\40\x20\x63\141\x73\145\x20\x77\x68\145\156\40\x62\56\x63\141\x70\x74\165\162\x61\x5f\x6f\162\x64\145\156\x20\x69\163\40\156\165\x6c\x6c\x20\x74\x68\x65\x6e\x20\x62\56\143\141\x70\x74\165\162\141\x5f\151\x64\x20\x65\154\x73\145\40\x62\56\143\x61\x70\164\x75\162\141\137\157\x72\x64\x65\156\40\145\156\x64\12\x20\x20\x20\x20\40\40\40\40\x20\x20\40\40\40\x20\x29\xa\40\40\40\40\x20\40\x20\40\x73\145\154\x65\x63\164\40\x2a\12\40\x20\40\x20\40\x20\40\x20\146\162\157\x6d\x20\x65\163\161\x75\145\x72\171\x3b", ["\143\141\160\164\x75\162\x61\137\145\x73\x74\141\x64\157" => $t_WVr, "\162\145\x63\x65\160\143\151\157\x6e\137\151\x64" => $W6Khq]); goto qcdLK; lyxoP: $xIxSu = new recepcion(); goto rwhZv; r3254: } public function validador_archivos_firmados() { goto Y3Qnd; TbZmz: $IL3IR = ''; goto Mudgh; iVUgJ: oiVUM: goto NjBB8; hKdF9: $aKEnc = DB::select("\163\145\x6c\145\143\x74\40\x64\x6f\x63\165\155\145\x6e\164\x6f\x5f\x6e\157\x6d\142\x72\x65\40\x66\x72\x6f\x6d\x20\x72\x65\143\x65\160\143\x69\x6f\156\40\141\xa\40\40\40\40\40\x20\40\40\x20\x20\40\40\40\40\x20\40\x6c\x65\x66\x74\x20\152\157\x69\156\40\143\x61\x70\164\x75\x72\x61\x20\x62\x20\157\x6e\x20\141\x2e\162\145\143\x65\160\x63\x69\157\156\137\x69\144\x20\75\x20\142\x2e\x72\x65\x63\x65\x70\143\x69\x6f\156\x5f\x69\144\12\40\40\40\40\40\40\x20\x20\x20\40\x20\x20\40\40\40\40\154\x65\x66\x74\40\152\157\151\156\40\x64\157\x63\165\x6d\x65\156\x74\x6f\x20\x63\40\x6f\156\40\x63\x2e\x63\x61\x70\164\x75\162\x61\137\x69\x64\40\x3d\40\142\x2e\143\141\x70\x74\165\x72\x61\137\x69\144\xa\40\40\x20\x20\x20\40\x20\40\x20\x20\40\40\40\40\40\40\x6c\145\x66\164\40\x6a\x6f\x69\x6e\x20\x61\144\145\164\x61\154\x6c\x65\40\144\x20\x6f\156\40\x64\56\141\x64\145\x74\x61\x6c\x6c\145\x5f\x69\x64\40\75\40\143\x2e\141\144\145\164\x61\154\x6c\145\x5f\151\144\12\x20\40\40\40\x20\40\40\x20\40\x20\40\x20\x20\x20\x20\x20\167\150\x65\x72\145\x20\141\x2e\x72\x65\x63\145\x70\x63\x69\x6f\x6e\137\x69\144\x20\75\x20\x3a\x72\145\x63\x65\x70\x63\151\157\156\137\x69\144\12\x20\x20\x20\x20\x20\40\40\40\x20\40\40\40\x20\x20\x20\x20\x20\40\141\156\144\x20\142\x2e\143\x61\160\x74\165\162\141\137\x65\163\164\x61\x64\157\x20\75\x20\x31\12\40\x20\x20\40\x20\x20\40\40\40\x20\x20\x20\x20\40\40\40\40\x20\x61\156\144\x20\x28\40\x62\x2e\143\141\160\164\x75\x72\141\137\x65\163\164\141\x64\x6f\x5f\x67\x6c\142\40\75\47\x66\x65\x64\x27\x29", ["\162\145\x63\145\160\x63\151\x6f\x6e\137\151\144" => $W6Khq]); goto UANgM; nCzIo: $eYqc3 = self::validar_tercer_caso($JCPMB, $aKEnc); goto uSRdZ; xTKIC: $QnVu6 = self::validar_primer_caso($JCPMB, $aKEnc); goto AF9u6; TbBaQ: if (!$IL3IR) { goto oiVUM; } goto iVUgJ; UANgM: $FaVmE = File::Files(storage_path() . "\57\141\x70\160\57\x64\157\x63\x75\x6d\145\156\164\157\163\x2f\x70\x72\157\171\145\x63\x74\x6f\x20\x30\60\x31\x2f\x53\151\155\160\x6c\x65\x2f"); goto LRaE_; LRaE_: foreach ($FaVmE as $EufTV) { $JCPMB[] = $EufTV->getFilename(); Xa5qZ: } goto eoD3N; TJNsp: $EdFKs = ''; goto kXWkQ; eoD3N: OyOVj: goto xTKIC; Y3Qnd: $W6Khq = 4; goto fzIF3; kXWkQ: $eYqc3 = ''; goto TbZmz; Mudgh: $JCPMB = []; goto hKdF9; A7mFj: $IL3IR = true; goto NTliN; uSRdZ: if (!($QnVu6 || $EdFKs || $eYqc3)) { goto GDniu; } goto A7mFj; fzIF3: $QnVu6 = ''; goto TJNsp; NTliN: GDniu: goto TbBaQ; AF9u6: $EdFKs = self::validar_segundo_caso($JCPMB, $aKEnc); goto nCzIo; NjBB8: return $IL3IR; goto L5wqm; L5wqm: } public function validar_primer_caso($JCPMB, $aKEnc) { goto vBK4e; QALSE: njZu2: goto w00pN; w00pN: $aHyIP = array_diff($dPy2S, $JCPMB); goto aZ9kp; aZ9kp: return $QnVu6 = empty($aHyIP) ? true : false; goto BsHjf; KiqOo: $aHyIP = []; goto ZZtTd; ZZtTd: foreach ($aKEnc as $k_8mW) { $dPy2S[] = $k_8mW->Cnks6; n1KIX: } goto QALSE; vBK4e: $dPy2S = []; goto KiqOo; BsHjf: } public function validar_segundo_caso($JCPMB, $aKEnc) { goto KVb5F; y65su: $aHyIP = []; goto vyPy7; HllpW: return $EdFKs = empty($aHyIP) ? true : false; goto FRYFW; NSu0c: $aHyIP = array_diff($dPy2S, $JCPMB); goto HllpW; CrVja: $dPy2S = []; goto y65su; JO08s: JcMTc: goto NSu0c; vyPy7: foreach ($aKEnc as $k_8mW) { goto iYxY6; Ac4GR: jpaa4: goto swfz7; qKe6E: $dPy2S[] = $k_8mW->Cnks6 . "\x2e\x65\163\x69\147"; goto Ac4GR; iYxY6: $dPy2S[] = $k_8mW->Cnks6 . "\56\162\x61\162\56\x65\163\151\147"; goto qKe6E; swfz7: } goto JO08s; KVb5F: $aHyIP = []; goto CrVja; FRYFW: } public function validar_tercer_caso($JCPMB, $aKEnc) { goto TCJrJ; xReWi: $aHyIP = []; goto b62WA; wuWnS: return $eYqc3 = empty($aHyIP) ? true : false; goto w14lR; d5BkX: $dPy2S = []; goto xReWi; TAJMo: $aHyIP = array_diff($dPy2S, $JCPMB); goto wuWnS; Cx874: DW7YH: goto TAJMo; b62WA: foreach ($aKEnc as $k_8mW) { $dPy2S[] = $k_8mW->Cnks6 . "\x2e\145\x73\x69\147"; W35Z1: } goto Cx874; TCJrJ: $aHyIP = []; goto d5BkX; w14lR: } public function firmar_registro_fed_fir() { goto UXYOE; UXYOE: $qy8rI = request("\141\162\x72\141\x79\137\x63\150\x65\x63\x6b"); goto ig4E0; e2Isc: $atDRh = new fedatario_firmar(); goto sP2tZ; ig4E0: $L8Alm = request("\x65\170\x74\145\x6e\x73\151\157\x6e"); goto HcQ6v; sP2tZ: $atDRh->iniciar_fedatario_firmar($qy8rI, 3, $TxdJv); goto KlWyh; HcQ6v: $TxdJv = env("\106\x4f\x4c\104\105\x52\x5f\x58\x5f\106\111\x52\115\x41\122"); goto e2Isc; KlWyh: } public function validar_firmar_fed_fir() { goto tew5S; tew5S: $atDRh = new fedatario_firmar(); goto rRWOk; Hi5ts: $bLjeZ = env("\x46\117\114\104\105\x52\137\x58\137\x46\x49\122\115\x41\x52"); goto aSC15; f00Ip: $L8Alm = request("\145\170\164\145\x6e\163\x69\157\156"); goto qSoaL; OotDV: return $atDRh->registrar_documentos($lZY3w, $TxdJv, $bLjeZ, "\56" . $L8Alm); goto kngKl; rRWOk: $lZY3w = request("\x61\162\x72\141\171\x5f\143\x68\145\x63\153"); goto f00Ip; qSoaL: $TxdJv = env("\106\x4f\114\104\105\122\x5f\x46\111\x52\115\101\x44\x4f"); goto Hi5ts; aSC15: $atDRh = new fedatario_firmar(); goto OotDV; kngKl: } }
