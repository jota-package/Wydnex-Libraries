<?php
/*   __________________________________________________
    |  Obfuscated by YAK Pro - Php Obfuscator  2.0.12  |
    |              on 2021-02-18 13:04:15              |
    |    GitHub: https://github.com/pk-fr/yakpro-po    |
    |__________________________________________________|
*/
 namespace Fedatario\Controllers; use Illuminate\Http\Request; use App\Http\Controllers\respuesta; use App\Http\Controllers\dangoController; use Carbon\Carbon; use App; use App\files; use App\User; use App\proyecto; trait filesController { public function test() { goto UuCV9; hhers: return respuesta::ok(); goto hajKU; JNaDh: $uHsl_ = new files(); goto hhers; UuCV9: $pmu5n = ["\162\x65\143\x65\160\143\x69\157\156\137\x69\144" => 1, "\x66\151\154\x65\x5f\x6e\x6f\155\x62\162\145" => "\116\165\145\x76\x61\40\101\x70\x65\162\164\x75\162\x61\x20\x34", "\146\151\154\x65\137\x70\x61\144\x72\145\x5f\x69\144" => 6, "\146\151\154\145\x5f\x74\151\160\x6f" => 2, "\x66\151\x6c\x65\x5f\165\163\x75\x61\162\151\157\137\x69\x64" => 1]; goto JNaDh; hajKU: } public function create_node(Request $a2KoW) { goto Zzh28; ntWuq: $pmu5n = ["\x72\145\x63\145\160\143\x69\x6f\156\137\151\144" => $iIJ_Z["\x72\x65\143\145\x70\143\x69\x6f\x6e\137\x69\144"], "\146\151\154\x65\x5f\156\157\155\x62\162\x65" => $a2KoW->input("\156\157\x6d\142\x72\x65", ''), "\146\x69\x6c\145\x5f\x70\x61\x64\162\145\137\x69\144" => $a2KoW->input("\160\141\x64\x72\145\137\151\144", 0), "\146\x69\x6c\x65\137\x63\x61\x70\164\x75\x72\x61\x5f\x65\163\164\x61\144\157" => $iIJ_Z["\x66\x69\154\145\137\143\141\x70\x74\x75\162\x61\x5f\145\x73\x74\141\144\157"], "\x66\151\154\x65\x5f\x74\x69\160\157" => "\144", "\x66\151\x6c\x65\x5f\165\x73\165\141\x72\151\x6f\x5f\151\144" => session("\165\x73\x75\141\x72\151\x6f\137\x69\x64")]; goto HSFNX; PWS0B: AN0qJ: goto mpsGL; SQAtQ: $iIJ_Z = files::where("\146\151\154\x65\137\x69\x64", $iIJ_Z)->where("\x72\145\143\145\160\x63\151\x6f\156\137\151\144", intval($a2KoW->input("\x72\145\x63\x65\x70\x63\x69\157\x6e\137\151\x64", 0)))->where("\146\x69\x6c\x65\x5f\x63\x61\x70\164\x75\162\141\137\145\x73\164\x61\144\157", intval($a2KoW->input("\143\141\160\x74\x75\x72\141\137\x65\x73\164\x61\x64\157", 0)))->first(); goto JN7bj; l0_Fp: $iIJ_Z = $a2KoW->input("\160\x61\144\x72\x65\x5f\151\x64", 0); goto ITyR3; Zzh28: $uHsl_ = new files(); goto l0_Fp; rDCsH: goto R3QcO; goto PWS0B; WMP7y: return respuesta::error("\x55\x6e\x6f\40\144\145\40\x6c\x6f\163\x20\x70\x61\x72\303\xa1\x6d\x65\x74\x72\157\163\x20\x6e\157\40\145\163\40\x63\x6f\162\162\145\x63\164\157\x20\x70\141\162\141\x20\x70\x6f\x64\145\x72\x20\143\x72\145\141\162\40\145\154\x20\141\162\x63\x68\x69\x76\157\x20\157\x20\144\x69\x72\x65\x63\x74\x6f\x72\151\157\56", 500); goto oA4qo; JN7bj: if ($iIJ_Z) { goto V_DVo; } goto WMP7y; P7yYI: V_DVo: goto ntWuq; NXaA6: EBBu9: goto rDCsH; s_RRj: R3QcO: goto iPdy7; oA4qo: goto EBBu9; goto P7yYI; mpsGL: $pmu5n = ["\162\145\x63\x65\x70\x63\151\157\x6e\x5f\151\144" => intval($a2KoW->input("\x72\145\143\x65\x70\143\x69\157\x6e\x5f\x69\x64", 0)), "\x66\151\154\145\x5f\156\x6f\155\142\x72\145" => $a2KoW->input("\x6e\x6f\x6d\x62\162\x65", ''), "\x66\151\x6c\x65\137\x63\x61\160\164\x75\162\141\137\x65\163\x74\x61\x64\157" => intval($a2KoW->input("\143\x61\x70\164\165\x72\141\137\x65\163\x74\x61\144\157", 0)), "\146\x69\x6c\x65\x5f\164\151\160\157" => "\x64", "\146\151\x6c\x65\x5f\165\163\165\x61\x72\151\x6f\x5f\x69\x64" => session("\165\163\x75\141\162\151\157\x5f\x69\x64")]; goto d2_VE; ITyR3: if ($iIJ_Z == 0) { goto AN0qJ; } goto SQAtQ; d2_VE: return $uHsl_->crear($pmu5n); goto s_RRj; HSFNX: return $uHsl_->crear($pmu5n); goto NXaA6; iPdy7: } public static function create_captura($pmu5n, $qWOWh = '') { goto kp7r3; ZxG8X: goto B3F9e; goto fBq4g; njiEB: Et7aV: goto lkAIR; lkAIR: $dJoX7 = $uHsl_->crear(["\x72\145\143\145\160\x63\x69\157\x6e\137\151\x64" => $iIJ_Z["\x72\x65\143\x65\x70\143\151\x6f\x6e\x5f\151\x64"], "\x66\x69\x6c\x65\x5f\x6e\157\155\x62\x72\x65" => !empty($pmu5n["\x6e\x6f\155\x62\162\145"]) ? $pmu5n["\156\157\x6d\x62\162\145"] : $qWOWh, "\146\151\154\145\137\x70\x61\x64\x72\145\x5f\x69\x64" => intval(!empty($pmu5n["\160\141\144\x72\145\137\151\x64"]) ? $pmu5n["\x70\141\144\162\x65\137\x69\144"] : 0), "\146\x69\x6c\145\x5f\x63\141\160\x74\165\x72\141\137\x65\163\x74\x61\144\157" => $iIJ_Z["\146\151\x6c\145\x5f\143\x61\x70\x74\165\162\x61\x5f\x65\163\x74\x61\x64\x6f"], "\146\x69\154\145\x5f\164\151\x70\x6f" => "\146", "\x66\x69\x6c\145\137\165\x73\165\x61\x72\151\157\x5f\151\x64" => session("\x75\x73\165\141\x72\x69\157\x5f\x69\x64")]); goto Vp9f6; DLHZR: $iIJ_Z = intval(!empty($pmu5n["\x70\x61\144\162\145\137\151\144"]) ? $pmu5n["\x70\x61\144\162\x65\x5f\151\144"] : 0); goto eh0dN; Vp9f6: qhbDE: goto QBGiv; ICCqH: N8lis: goto bRIc8; vD3R_: epw6d: goto InmzM; bRIc8: return $dJoX7; goto uRJh7; miFO4: return response($dJoX7["\155\x65\156\x73\x61\152\145"], 500); goto oU_wr; QBGiv: goto N8lis; goto vD3R_; eh0dN: if ($iIJ_Z == 0) { goto epw6d; } goto WnIFK; InmzM: $dJoX7 = $uHsl_->crear(["\x72\x65\143\x65\160\x63\x69\x6f\156\x5f\151\x64" => intval(!empty($pmu5n["\x72\145\143\x65\160\x63\151\157\156\x5f\x69\x64"]) ? $pmu5n["\162\x65\x63\x65\x70\143\x69\157\x6e\137\x69\144"] : 0), "\146\x69\x6c\x65\x5f\x6e\x6f\x6d\142\162\x65" => !empty($pmu5n["\156\157\x6d\x62\162\145"]) ? $pmu5n["\156\x6f\x6d\x62\162\145"] : $qWOWh, "\x66\151\154\145\137\x63\x61\160\164\x75\162\x61\137\x65\x73\164\x61\x64\x6f" => intval(!empty($pmu5n["\143\141\160\x74\x75\x72\141\x5f\145\x73\164\x61\144\157"]) ? $pmu5n["\143\141\160\164\x75\x72\x61\137\x65\163\x74\x61\x64\x6f"] : 0), "\146\x69\x6c\145\x5f\x74\x69\160\x6f" => "\x66", "\146\x69\x6c\145\137\x75\x73\x75\141\162\x69\x6f\x5f\151\144" => session()->get("\165\163\x75\x61\162\151\157\x5f\x69\144", 0)]); goto ICCqH; kp7r3: $uHsl_ = new files(); goto DLHZR; oU_wr: B3F9e: goto x5_do; GZu1R: if ($iIJ_Z) { goto Et7aV; } goto tqGhB; yiKjr: goto qhbDE; goto njiEB; tqGhB: return respuesta::error("\x55\x6e\x6f\40\144\145\x20\154\x6f\x73\x20\x70\141\162\xc3\xa1\155\x65\164\162\x6f\163\40\x6e\157\x20\x65\x73\x20\x63\157\x72\162\x65\143\x74\x6f\40\x70\x61\162\141\40\160\157\144\x65\x72\x20\143\x72\x65\x61\162\x20\x6c\141\40\x63\x61\x70\164\165\162\x61\x2e", 500); goto yiKjr; fBq4g: VRPSj: goto miFO4; KL9uU: $dJoX7 = $dJoX7["\x70\x61\171\x6c\x6f\141\x64"]; goto ZxG8X; WnIFK: $iIJ_Z = files::where("\146\x69\x6c\145\137\x69\x64", $iIJ_Z)->where("\162\145\143\x65\160\143\151\x6f\x6e\x5f\x69\x64", intval(!empty($pmu5n["\x72\x65\143\145\160\143\151\x6f\156\x5f\x69\x64"]) ? $pmu5n["\162\x65\143\145\x70\143\x69\x6f\x6e\137\151\x64"] : 0))->where("\146\x69\x6c\145\x5f\143\141\160\164\x75\x72\x61\137\x65\163\x74\141\144\x6f", intval(!empty($pmu5n["\143\x61\x70\x74\165\x72\141\x5f\145\x73\x74\x61\144\157"]) ? $pmu5n["\143\x61\160\x74\x75\x72\141\137\x65\163\x74\141\144\x6f"] : 0))->first(); goto GZu1R; uRJh7: if (!$dJoX7["\145\x73\164\x61\144\x6f"]) { goto VRPSj; } goto KL9uU; x5_do: } public function rename_node(Request $a2KoW) { $uHsl_ = new files(); return $uHsl_->renombrar($a2KoW->input("\x66\151\x6c\x65\137\x69\x64", 0), $a2KoW->input("\x6e\x6f\155\142\x72\145", '')); } public function delete_node(Request $a2KoW) { $uHsl_ = new files(); return $uHsl_->borrar_directorio($a2KoW->input("\146\x69\x6c\x65\137\151\144", 0)); } public function move_node(Request $a2KoW) { goto L5FZJ; iCtP8: FUi9m: goto Nm3gs; dFK7n: $a13iU = intval($a2KoW->input("\156\x65\x77\137\160\141\x64\162\x65\137\x69\x64", 0)); goto GrecK; NxUri: FI4Wi: goto Ylufz; o6_64: return $uHsl_->mover_a_directorio($Y0nwI, $a13iU); goto WcJ2n; ZDbrR: if ($a13iU == 0) { goto FI4Wi; } goto o6_64; WcJ2n: goto FUi9m; goto NxUri; GrecK: $W6Khq = intval($a2KoW->input("\162\145\143\x65\x70\143\x69\157\156\x5f\151\144", 0)); goto BMiGp; L5FZJ: $uHsl_ = new files(); goto JToLh; Ylufz: return $uHsl_->mover_a_recepcion($Y0nwI, $W6Khq, $t_WVr); goto iCtP8; BMiGp: $t_WVr = intval($a2KoW->input("\x63\141\x70\164\165\x72\x61\x5f\145\x73\164\141\x64\x6f", 0)); goto ZDbrR; JToLh: $Y0nwI = intval($a2KoW->input("\x66\151\154\145\x5f\151\x64", 0)); goto dFK7n; Nm3gs: } public function load_tree(Request $a2KoW) { goto DLBcd; DLBcd: $Y0nwI = request("\x66\151\154\x65\137\x69\144"); goto fxdQd; Hvm63: goto xj_R0; goto xsMBU; eC4gI: return $this->load_main_tree(); goto St4LE; xsMBU: b4S1q: goto eC4gI; fxdQd: if ($Y0nwI == "\x23") { goto b4S1q; } goto hJPjB; jgkcT: $Y0nwI = intval($Y0nwI); goto DMvv5; hJPjB: $t_WVr = intval($a2KoW->input("\x63\141\160\x74\165\162\x61\x5f\145\x73\x74\x61\x64\x6f", 0)); goto nvhPC; St4LE: xj_R0: goto gSgYy; nvhPC: $W6Khq = $a2KoW->input("\x72\x65\143\x65\x70\143\151\x6f\156\x5f\x69\144", 0); goto jgkcT; DMvv5: return $this->load_node($W6Khq, $Y0nwI, $t_WVr); goto Hvm63; gSgYy: } public function load_tree_admin(Request $a2KoW) { goto BB9ZS; GKnsv: THow4: goto UiTJ4; BB9ZS: $Y0nwI = request("\x66\151\x6c\x65\137\151\x64"); goto GYKlU; GYKlU: if ($Y0nwI == "\x23") { goto THow4; } goto zj0Xr; Nu2UX: $Y0nwI = intval($Y0nwI); goto LHJll; UiTJ4: return $this->load_main_tree_admin(); goto xjIz6; xjIz6: xTDdK: goto keZkb; iY_Y4: $W6Khq = $a2KoW->input("\162\145\x63\145\160\143\151\157\x6e\137\x69\144", 0); goto Nu2UX; LHJll: return $this->load_node($W6Khq, $Y0nwI, $t_WVr); goto yY3J9; yY3J9: goto xTDdK; goto GKnsv; zj0Xr: $t_WVr = intval($a2KoW->input("\x63\141\x70\164\x75\162\141\x5f\x65\163\x74\x61\144\x6f", 0)); goto iY_Y4; keZkb: } public function load_tree_documento(Request $a2KoW) { goto SUPRk; CVe3t: $t_WVr = intval($a2KoW->input("\x63\x61\160\164\x75\162\141\137\145\163\164\141\x64\x6f", 0)); goto IzvFR; MSyE2: goto dxtBd; goto coYio; xM8lF: $Y0nwI = intval($Y0nwI); goto H2JEO; coYio: Y46ZY: goto T3tIM; T3tIM: return $this->load_main_tree_documento(); goto lIq12; SUPRk: $Y0nwI = request("\x66\x69\x6c\145\x5f\151\x64"); goto cSyfb; H2JEO: return $this->load_node_documento($W6Khq, $Y0nwI, $t_WVr); goto MSyE2; IzvFR: $W6Khq = $a2KoW->input("\162\x65\143\x65\160\143\151\x6f\x6e\x5f\x69\144", 0); goto xM8lF; cSyfb: if ($Y0nwI == "\x23") { goto Y46ZY; } goto CVe3t; lIq12: dxtBd: goto tE5EY; tE5EY: } public function load_node($W6Khq, $Y0nwI, $t_WVr) { goto r2lxd; r2lxd: $uHsl_ = new files(); goto D6AR2; LYRFJ: goto MNNlq; goto uDjwP; pp56a: RtzOD: goto xkDIk; FcU7k: if ($XiQel["\145\163\164\141\144\157"]) { goto RtzOD; } goto Zkp5a; IjOGS: $NpOYL = []; goto FcU7k; iTfrS: rP2c_: goto ixGMP; D6AR2: if ($Y0nwI == 0) { goto uwhim; } goto msvY_; u4sry: MNNlq: goto IjOGS; apLjZ: $XiQel = $uHsl_->listar_desde_recepcion($W6Khq, $t_WVr); goto u4sry; uDjwP: uwhim: goto apLjZ; OJCN6: paUv3: goto XZrjt; BIogQ: foreach ($XiQel as $zbIDR => $PtPs1) { goto nBFnz; DGHlG: if (!($PtPs1->HFPIs == "\155")) { goto QmFJB; } goto VN8Wx; nBFnz: if ($PtPs1->qU5jR == "\x64") { goto N_skq; } goto k21ls; An5ot: Vv1Tm: goto IZnko; IZnko: Tw33_: goto vTlsX; XNwqa: RWbFp: goto TIdy3; VN8Wx: array_push($NpOYL, ["\x74\x65\170\x74" => $PtPs1->Cnks6, "\x69\143\157\x6e" => $this->obtener_icon_file($PtPs1->LpOm1), "\146\x69\154\145\x5f\151\x64" => $PtPs1->Yuvqp, "\146\x69\154\145\x5f\x74\x69\x70\x6f" => $PtPs1->qU5jR, "\162\x65\x63\x65\160\x63\x69\157\156\x5f\x69\144" => $PtPs1->Ml4BR, "\x72\145\143\x65\160\x63\x69\x6f\156\137\164\151\160\x6f" => $PtPs1->HFPIs, "\x63\141\160\x74\x75\x72\141\137\x65\163\x74\x61\144\x6f" => $PtPs1->FI3ri, "\x61\x64\x65\164\x61\154\154\145\137\151\x64" => $PtPs1->zxl9i, "\144\157\143\165\x6d\145\x6e\164\x6f\137\x69\144" => $PtPs1->wRRGr, "\x63\x6c\151\x65\156\164\145\137\x69\x64" => $PtPs1->DcVTR, "\143\141\x70\164\x75\162\141\137\x69\x64" => $PtPs1->NETgL, "\x63\141\160\164\165\162\141\x5f\145\x73\164\141\144\157\137\x67\154\x62" => $PtPs1->L11wf, "\x70\x72\x6f\171\x65\143\x74\x6f\137\151\144" => $PtPs1->J1yuX, "\x64\x6f\x63\165\155\x65\x6e\164\x6f\137\156\x6f\155\142\162\145" => $PtPs1->Cnks6, "\x70\141\x64\162\145\x5f\x69\x64" => !empty($PtPs1->MfEtc) ? $PtPs1->MfEtc : 0]); goto jyn_l; jyn_l: QmFJB: goto fOogv; xk6TX: N_skq: goto sGO76; eHBkS: aMEGZ: goto AwT8a; k21ls: if ($PtPs1->qU5jR == "\x66") { goto RWbFp; } goto GnkQY; ah5MX: mi_G0: goto MearJ; TIdy3: if ($PtPs1->HFPIs == "\x73" && !empty($PtPs1->zxl9i)) { goto aMEGZ; } goto DGHlG; MearJ: PxMwn: goto vyN1R; fOogv: goto mi_G0; goto eHBkS; sGO76: array_push($NpOYL, ["\164\145\x78\164" => $PtPs1->hkGoF, "\146\x69\154\145\x5f\x69\x64" => $PtPs1->Yuvqp, "\146\x69\x6c\145\x5f\x74\151\x70\x6f" => $PtPs1->qU5jR, "\162\x65\x63\145\x70\143\x69\157\156\137\151\x64" => $PtPs1->Ml4BR, "\162\145\x63\145\160\x63\x69\x6f\156\137\x74\x69\x70\x6f" => $PtPs1->HFPIs, "\x63\x61\160\x74\x75\x72\x61\137\x65\163\x74\141\144\x6f" => $PtPs1->FI3ri, "\143\x61\x70\164\165\162\141\137\151\144" => $PtPs1->NETgL, "\160\x72\157\x79\145\x63\x74\x6f\x5f\151\144" => $PtPs1->J1yuX, "\143\x6c\x69\145\156\x74\145\x5f\151\144" => $PtPs1->DcVTR, "\143\150\x69\154\144\162\x65\156" => true]); goto An5ot; vyN1R: goto Vv1Tm; goto xk6TX; AwT8a: array_push($NpOYL, ["\x74\x65\170\x74" => $PtPs1->Cnks6, "\151\x63\157\156" => $this->obtener_icon_file($PtPs1->LpOm1), "\146\151\x6c\x65\137\151\x64" => $PtPs1->Yuvqp, "\146\151\154\x65\x5f\x74\x69\x70\157" => $PtPs1->qU5jR, "\x72\145\x63\145\160\x63\x69\x6f\156\137\x69\x64" => $PtPs1->Ml4BR, "\x72\x65\x63\145\160\143\151\157\156\x5f\x74\x69\x70\157" => $PtPs1->HFPIs, "\x63\141\160\x74\165\x72\x61\x5f\145\163\164\141\144\x6f" => $PtPs1->FI3ri, "\141\x64\x65\164\141\154\x6c\145\137\151\x64" => $PtPs1->zxl9i, "\144\x6f\x63\165\155\145\156\164\157\x5f\x69\x64" => $PtPs1->wRRGr, "\143\154\151\145\x6e\164\145\137\x69\144" => $PtPs1->DcVTR, "\x63\x61\x70\164\165\162\141\x5f\151\x64" => $PtPs1->NETgL, "\x63\x61\x70\164\165\162\141\137\145\163\x74\x61\144\x6f\137\147\154\142" => $PtPs1->L11wf, "\160\x72\x6f\x79\145\x63\164\x6f\137\151\144" => $PtPs1->J1yuX, "\144\x6f\143\x75\x6d\x65\156\164\157\x5f\156\x6f\x6d\x62\x72\x65" => $PtPs1->Cnks6, "\x70\141\x64\x72\145\x5f\151\x64" => !empty($PtPs1->MfEtc) ? $PtPs1->MfEtc : 0]); goto ah5MX; GnkQY: goto PxMwn; goto XNwqa; vTlsX: } goto iTfrS; msvY_: $XiQel = $uHsl_->listar_desde_padre($W6Khq, $Y0nwI); goto LYRFJ; CTdmN: goto paUv3; goto pp56a; ixGMP: return $NpOYL; goto OJCN6; Zkp5a: return []; goto CTdmN; xkDIk: $XiQel = $XiQel["\160\141\x79\x6c\x6f\x61\144"]; goto BIogQ; XZrjt: } public function load_node_admin($W6Khq, $Y0nwI, $t_WVr) { goto dC0vj; w7dfy: g3Y9d: goto vhPAN; SIcfA: BbWA2: goto jZA93; jZA93: $XiQel = $uHsl_->listar_todo_desde_recepcion_admin($W6Khq, $t_WVr); goto tytm_; t8Iqh: goto Wetjn; goto SIcfA; VDRhM: $XiQel = $XiQel["\x70\x61\x79\x6c\157\141\x64"]; goto j27t2; dC0vj: $uHsl_ = new files(); goto GXCmh; tUmKr: if ($XiQel["\x65\163\x74\x61\144\x6f"]) { goto uDiJv; } goto SbdW9; WFDq1: return $NpOYL; goto w7dfy; dmRFL: g_knf: goto WFDq1; SvKjX: $NpOYL = []; goto tUmKr; tytm_: Wetjn: goto SvKjX; j27t2: foreach ($XiQel as $zbIDR => $PtPs1) { goto OPrlF; YzQtj: KU7ZB: goto HyzqZ; OPrlF: if ($PtPs1->qU5jR == "\144") { goto FWZre; } goto D1sRu; S301h: Cn50f: goto TG9cP; CAaBn: GXcru: goto YzQtj; HyzqZ: goto Cn50f; goto R3qJb; MaoMj: array_push($NpOYL, ["\164\x65\x78\x74" => $PtPs1->Cnks6, "\151\x63\157\x6e" => $this->obtener_icon_file($PtPs1->LpOm1), "\x66\x69\154\145\137\151\x64" => $PtPs1->Yuvqp, "\146\x69\154\145\137\164\x69\x70\x6f" => $PtPs1->qU5jR, "\x72\x65\x63\145\x70\143\151\x6f\156\137\151\144" => $PtPs1->Ml4BR, "\x72\x65\x63\145\160\x63\x69\157\x6e\137\x74\x69\160\157" => $PtPs1->HFPIs, "\143\141\x70\164\x75\162\141\137\145\163\164\x61\x64\x6f" => $PtPs1->FI3ri, "\141\x64\145\164\x61\x6c\x6c\145\137\151\144" => $PtPs1->zxl9i, "\x64\157\143\165\155\145\156\x74\x6f\137\151\x64" => $PtPs1->wRRGr, "\x63\154\x69\x65\x6e\164\145\x5f\151\x64" => $PtPs1->DcVTR, "\143\x61\160\x74\x75\162\141\x5f\151\x64" => $PtPs1->NETgL, "\x63\141\x70\164\165\162\141\137\x65\x73\164\141\x64\157\137\147\154\142" => $PtPs1->L11wf, "\x70\x72\x6f\171\145\143\x74\x6f\137\x69\144" => $PtPs1->J1yuX, "\144\x6f\143\x75\x6d\x65\x6e\164\x6f\x5f\156\157\x6d\x62\x72\x65" => $PtPs1->Cnks6, "\x70\141\144\x72\145\x5f\151\x64" => !empty($PtPs1->MfEtc) ? $PtPs1->MfEtc : 0]); goto mgEqf; D1sRu: if ($PtPs1->qU5jR == "\x66") { goto cYQJn; } goto LcPbN; T7Gj3: U_bs4: goto s332m; ulCGU: array_push($NpOYL, ["\164\x65\x78\x74" => $PtPs1->hkGoF, "\x66\x69\154\145\137\x69\x64" => $PtPs1->Yuvqp, "\x66\x69\154\145\x5f\x74\151\160\157" => $PtPs1->qU5jR, "\162\x65\x63\145\x70\x63\151\x6f\x6e\137\x69\x64" => $PtPs1->Ml4BR, "\162\x65\143\145\x70\143\x69\157\x6e\137\164\x69\x70\x6f" => $PtPs1->HFPIs, "\x63\x61\x70\164\x75\162\x61\137\x65\163\164\141\144\x6f" => $PtPs1->FI3ri, "\x63\x61\160\164\x75\162\141\x5f\x69\x64" => $PtPs1->NETgL, "\x70\162\x6f\171\x65\x63\x74\157\x5f\151\144" => $PtPs1->J1yuX, "\143\x6c\151\145\156\x74\x65\x5f\151\144" => $PtPs1->DcVTR, "\x63\x68\x69\154\144\162\x65\x6e" => true]); goto S301h; PthoQ: if ($PtPs1->HFPIs == "\x73" && !empty($PtPs1->zxl9i)) { goto U_bs4; } goto a_n05; mgEqf: hgqP7: goto HeVGt; hvcBF: cYQJn: goto PthoQ; TG9cP: Qrc_u: goto TBiXb; HeVGt: goto GXcru; goto T7Gj3; LcPbN: goto KU7ZB; goto hvcBF; R3qJb: FWZre: goto ulCGU; s332m: array_push($NpOYL, ["\x74\145\x78\164" => $PtPs1->Cnks6, "\x69\x63\x6f\x6e" => $this->obtener_icon_file($PtPs1->LpOm1), "\x66\x69\x6c\145\137\x69\x64" => $PtPs1->Yuvqp, "\x66\x69\x6c\x65\x5f\x74\151\x70\x6f" => $PtPs1->qU5jR, "\162\x65\143\x65\160\x63\x69\x6f\156\x5f\x69\x64" => $PtPs1->Ml4BR, "\162\145\x63\x65\x70\x63\151\x6f\x6e\137\x74\x69\x70\157" => $PtPs1->HFPIs, "\143\141\160\164\165\x72\x61\137\x65\163\x74\141\144\x6f" => $PtPs1->FI3ri, "\x61\144\145\164\141\x6c\x6c\145\137\x69\144" => $PtPs1->zxl9i, "\144\x6f\x63\165\x6d\145\x6e\x74\157\137\151\x64" => $PtPs1->wRRGr, "\x63\154\151\145\156\x74\145\137\151\x64" => $PtPs1->DcVTR, "\x63\x61\160\164\x75\162\141\x5f\x69\x64" => $PtPs1->NETgL, "\x63\141\160\164\165\162\141\137\145\x73\x74\x61\x64\x6f\x5f\x67\154\x62" => $PtPs1->L11wf, "\160\x72\157\171\x65\143\x74\x6f\137\151\144" => $PtPs1->J1yuX, "\144\x6f\x63\165\155\145\x6e\164\157\x5f\x6e\x6f\x6d\142\162\145" => $PtPs1->Cnks6, "\160\x61\x64\x72\145\137\x69\144" => !empty($PtPs1->MfEtc) ? $PtPs1->MfEtc : 0]); goto CAaBn; a_n05: if (!($PtPs1->HFPIs == "\x6d")) { goto hgqP7; } goto MaoMj; TBiXb: } goto dmRFL; J8V7O: uDiJv: goto VDRhM; GXCmh: if ($Y0nwI == 0) { goto BbWA2; } goto wUdZM; SbdW9: return []; goto hIrq0; hIrq0: goto g3Y9d; goto J8V7O; wUdZM: $XiQel = $uHsl_->listar_desde_padre_admin($W6Khq, $Y0nwI); goto t8Iqh; vhPAN: } public function load_node_documento($W6Khq, $Y0nwI, $t_WVr) { goto WS0nN; JblPC: j4cPf: goto WgR39; TtXQL: foreach ($XiQel as $zbIDR => $PtPs1) { goto k6oBF; Q3EdQ: array_push($NpOYL, ["\164\145\170\x74" => $PtPs1->Cnks6, "\x69\143\157\156" => $this->obtener_icon_file($PtPs1->LpOm1), "\146\x69\x6c\x65\137\151\144" => $PtPs1->Yuvqp, "\146\151\154\x65\137\164\151\x70\x6f" => $PtPs1->qU5jR, "\162\x65\x63\145\x70\x63\x69\157\156\x5f\x69\x64" => $PtPs1->Ml4BR, "\162\x65\143\145\x70\143\x69\x6f\x6e\137\x74\x69\160\157" => $PtPs1->HFPIs, "\143\141\160\x74\x75\162\x61\x5f\x65\163\164\141\x64\157" => $PtPs1->FI3ri, "\141\x64\145\x74\141\x6c\x6c\145\137\x69\144" => $PtPs1->zxl9i, "\x64\x6f\143\x75\155\145\x6e\164\x6f\x5f\x69\144" => $PtPs1->wRRGr, "\143\154\151\145\156\x74\145\137\x69\144" => $PtPs1->DcVTR, "\143\141\160\x74\165\162\141\137\151\144" => $PtPs1->NETgL, "\143\141\x70\x74\165\x72\141\x5f\x65\163\x74\x61\144\x6f\x5f\147\x6c\x62" => $PtPs1->L11wf, "\160\x72\x6f\171\145\143\x74\157\137\151\x64" => $PtPs1->J1yuX, "\x64\x6f\143\165\155\x65\x6e\164\x6f\137\x6e\x6f\155\x62\162\145" => $PtPs1->Cnks6, "\160\x61\x64\x72\145\137\151\144" => !empty($PtPs1->MfEtc) ? $PtPs1->MfEtc : 0]); goto tx9EO; bCRDX: array_push($NpOYL, ["\164\145\170\x74" => $PtPs1->Cnks6, "\x69\143\157\x6e" => $this->obtener_icon_file($PtPs1->LpOm1), "\x66\151\x6c\x65\137\x69\144" => $PtPs1->Yuvqp, "\146\151\154\x65\x5f\164\151\x70\157" => $PtPs1->qU5jR, "\162\x65\143\x65\x70\x63\151\157\x6e\137\151\x64" => $PtPs1->Ml4BR, "\162\145\x63\145\x70\143\x69\x6f\156\x5f\164\x69\160\x6f" => $PtPs1->HFPIs, "\143\141\x70\x74\x75\162\x61\x5f\145\163\x74\x61\x64\x6f" => $PtPs1->FI3ri, "\x61\x64\x65\x74\141\x6c\x6c\145\137\151\x64" => $PtPs1->zxl9i, "\x64\x6f\x63\x75\155\x65\x6e\164\x6f\x5f\151\x64" => $PtPs1->wRRGr, "\x63\154\151\145\x6e\x74\x65\x5f\x69\144" => $PtPs1->DcVTR, "\x63\141\160\x74\165\x72\141\x5f\151\x64" => $PtPs1->NETgL, "\x63\141\x70\164\x75\x72\141\137\145\163\164\x61\144\157\x5f\147\x6c\x62" => $PtPs1->L11wf, "\x70\162\157\171\145\143\164\157\137\151\x64" => $PtPs1->J1yuX, "\x64\157\x63\x75\155\x65\x6e\164\x6f\137\156\157\155\x62\x72\145" => $PtPs1->Cnks6, "\160\141\x64\x72\145\x5f\151\144" => !empty($PtPs1->MfEtc) ? $PtPs1->MfEtc : 0]); goto ZiVUQ; XfIv5: K9d9I: goto hTOQ6; KaAUg: xzwEc: goto N1xwx; tx9EO: HM9et: goto hKQat; h07xc: goto HM9et; goto WPiWc; ZG41m: if ($PtPs1->qU5jR == "\146") { goto xzwEc; } goto DXqzY; YLDFj: goto Yzmkj; goto ZoMB2; WPiWc: EOJDL: goto Q3EdQ; DiWDD: if (!($PtPs1->HFPIs == "\155")) { goto d65io; } goto bCRDX; sfRuW: array_push($NpOYL, ["\164\x65\x78\164" => $PtPs1->hkGoF, "\146\x69\x6c\x65\x5f\151\x64" => $PtPs1->Yuvqp, "\x66\151\x6c\x65\137\164\x69\160\x6f" => $PtPs1->qU5jR, "\x72\x65\x63\145\x70\143\x69\157\156\137\x69\144" => $PtPs1->Ml4BR, "\162\145\143\145\x70\143\x69\x6f\x6e\x5f\164\151\x70\x6f" => $PtPs1->HFPIs, "\x63\x61\x70\164\x75\x72\x61\137\145\x73\164\141\144\x6f" => $PtPs1->FI3ri, "\x63\141\160\164\165\x72\141\137\x69\x64" => $PtPs1->NETgL, "\x70\162\x6f\171\x65\143\x74\x6f\x5f\151\144" => $PtPs1->J1yuX, "\143\x6c\x69\145\x6e\x74\145\137\151\x64" => $PtPs1->DcVTR, "\x63\x68\151\x6c\x64\162\x65\x6e" => true]); goto CBJzR; hKQat: WVLC3: goto YLDFj; DXqzY: goto WVLC3; goto KaAUg; N1xwx: if ($PtPs1->HFPIs == "\163" && !empty($PtPs1->zxl9i)) { goto EOJDL; } goto DiWDD; ZoMB2: Amncc: goto sfRuW; CBJzR: Yzmkj: goto XfIv5; k6oBF: if ($PtPs1->qU5jR == "\x64") { goto Amncc; } goto ZG41m; ZiVUQ: d65io: goto h07xc; hTOQ6: } goto vZmJ2; YNiOm: $XiQel = $uHsl_->listar_todo_desde_recepcion_documento($W6Khq, $t_WVr); goto LBQJL; vZmJ2: hRou6: goto BiJor; mwmF4: $XiQel = $uHsl_->listar_desde_padre_documento($W6Khq, $Y0nwI); goto iaH5r; Rb2M6: goto j4cPf; goto Z2DEa; WowCx: ofsL8: goto YNiOm; hnZ0f: if ($XiQel["\145\163\x74\141\x64\x6f"]) { goto iWgkT; } goto s8lGm; Nc8Nc: $XiQel = $XiQel["\x70\x61\x79\x6c\x6f\x61\x64"]; goto TtXQL; iaH5r: goto zbxcH; goto WowCx; LBQJL: zbxcH: goto vVwfL; s8lGm: return []; goto Rb2M6; Z2DEa: iWgkT: goto Nc8Nc; bNz10: if ($Y0nwI == 0) { goto ofsL8; } goto mwmF4; WS0nN: $uHsl_ = new files(); goto bNz10; vVwfL: $NpOYL = []; goto hnZ0f; BiJor: return $NpOYL; goto JblPC; WgR39: } public function obtener_icon_file($D3rlk) { goto MXyJf; toRFZ: OxmKT: goto r7DIw; GPZ2r: x9AMV: goto toRFZ; uHi2_: return "\146\x61\40\146\141\x2d\146\x69\x6c\x65"; goto S6GGk; FF8y5: if (sizeof($DqII5) > 1) { goto MLhma; } goto uHi2_; MXyJf: $DqII5 = explode("\x2e", $D3rlk); goto FF8y5; Iipee: switch ($DqII5[sizeof($DqII5) - 1]) { case "\x6a\160\147": case "\152\x70\x65\147": case "\x4a\120\x47": case "\112\120\105\107": return "\146\141\40\146\141\x2d\146\151\154\x65\55\151\155\141\x67\x65"; goto OxmKT; case "\x70\144\146": case "\120\104\106": return "\x66\x61\x20\x66\x61\x2d\146\151\154\145\x2d\x70\144\146"; goto OxmKT; default: return "\x66\141\40\146\x61\x2d\146\x69\154\x65"; goto OxmKT; } goto GPZ2r; r7DIw: UtZ89: goto X1MVd; S6GGk: goto UtZ89; goto BFjOl; BFjOl: MLhma: goto Iipee; X1MVd: } public function listar_capturas(Request $a2KoW) { goto vg9Qk; z5vOs: $XiQel = $XiQel["\x70\141\x79\154\157\141\x64"]; goto AGbWq; jjxVa: goto MwJ_z; goto yxaJR; CN548: return respuesta::ok($NpOYL); goto dQ9BF; cf_NL: lJf8l: goto CN548; AGbWq: foreach ($XiQel as $zbIDR => $PtPs1) { goto ePCDb; JNDPC: array_push($NpOYL, $PtPs1); goto ZZyie; ZZyie: ysTZf: goto XA07W; TDzmR: yYMVC: goto JNDPC; jXqqV: goto ysTZf; goto TDzmR; XA07W: vKpGY: goto NGznJ; ePCDb: if ($PtPs1->qU5jR == "\x66") { goto yYMVC; } goto jXqqV; NGznJ: } goto cf_NL; svcCa: $t_WVr = $a2KoW->input("\143\x61\160\x74\x75\162\x61\137\x65\x73\x74\x61\x64\157", 0); goto MLDvW; wkyKn: $XiQel = $uHsl_->listar_todo_desde_recepcion($W6Khq, $t_WVr); goto UJeg2; MLDvW: $uHsl_ = new files(); goto eFcx9; UJeg2: Ot36Y: goto fOGBn; qjtAz: goto Ot36Y; goto pSkYP; pSkYP: oN97M: goto wkyKn; XeIGo: if ($XiQel["\145\163\x74\x61\144\x6f"]) { goto Wy4ap; } goto UMUfX; LqjRc: $XiQel = $uHsl_->listar_desde_padre($W6Khq, $Y0nwI); goto qjtAz; dQ9BF: MwJ_z: goto mXnT7; fOGBn: $NpOYL = []; goto XeIGo; UMUfX: return $XiQel; goto jjxVa; vg9Qk: $W6Khq = $a2KoW->input("\162\145\x63\145\160\x63\151\x6f\156\x5f\151\144", 0); goto Un5Bc; eFcx9: if ($Y0nwI == 0) { goto oN97M; } goto LqjRc; Un5Bc: $Y0nwI = $a2KoW->input("\146\151\x6c\145\137\151\144", 0); goto svcCa; yxaJR: Wy4ap: goto z5vOs; mXnT7: } public function listar_captura_admin(Request $a2KoW) { goto u5P3h; F9UNb: if ($Y0nwI == 0) { goto mnXCi; } goto MiVDK; TPWEz: return $XiQel; goto EY43V; hQ_34: $Y0nwI = $a2KoW->input("\146\x69\x6c\145\137\151\x64", 0); goto Utwvc; MhmBw: hcpR5: goto NN0K5; Y8Lqt: $XiQel = $uHsl_->listar_todo_desde_recepcion_admin($W6Khq, $t_WVr); goto v3kdh; MFJL3: $NpOYL = []; goto gl0cG; zC85z: MwRLi: goto SSoqQ; u5P3h: $W6Khq = $a2KoW->input("\x72\145\x63\145\x70\143\151\x6f\156\137\x69\x64", 0); goto hQ_34; v3kdh: qaX_O: goto MFJL3; Utwvc: $t_WVr = $a2KoW->input("\143\x61\160\x74\x75\x72\x61\x5f\x65\x73\164\x61\144\157", 0); goto fJx93; IcdQF: mnXCi: goto Y8Lqt; EY43V: goto To0z7; goto zC85z; jvYcr: foreach ($XiQel as $zbIDR => $PtPs1) { goto oHpk8; u2eVv: nVREr: goto HeKh9; ywEZL: tO_ca: goto HHaCN; HHaCN: array_push($NpOYL, $PtPs1); goto u2eVv; oHpk8: if ($PtPs1->qU5jR == "\146") { goto tO_ca; } goto lUSK4; HeKh9: XM26K: goto E9z4w; lUSK4: goto nVREr; goto ywEZL; E9z4w: } goto MhmBw; A_yCQ: goto qaX_O; goto IcdQF; NN0K5: return respuesta::ok($NpOYL); goto jZxEo; SSoqQ: $XiQel = $XiQel["\160\141\171\x6c\x6f\141\x64"]; goto jvYcr; MiVDK: $XiQel = $uHsl_->listar_desde_padre_admin($W6Khq, $Y0nwI); goto A_yCQ; gl0cG: if ($XiQel["\145\x73\164\x61\144\157"]) { goto MwRLi; } goto TPWEz; jZxEo: To0z7: goto Jsb27; fJx93: $uHsl_ = new files(); goto F9UNb; Jsb27: } public function listar_captura_documento(Request $a2KoW) { goto hwHbm; GhPDd: $XiQel = $uHsl_->listar_desde_padre_documento($W6Khq, $Y0nwI); goto W2p4Q; sqw79: znNmj: goto D2iU_; Bwask: goto k5zJX; goto k6JJf; eHIou: $XiQel = $XiQel["\160\141\x79\154\x6f\141\x64"]; goto deIQl; aSaOC: $Y0nwI = $a2KoW->input("\146\151\154\145\137\151\144", 0); goto aLbqp; aLbqp: $t_WVr = $a2KoW->input("\x63\x61\x70\x74\165\162\x61\x5f\145\x73\164\141\x64\157", 0); goto dREqJ; dREqJ: $uHsl_ = new files(); goto QoRp0; EN0cv: oILi5: goto oZSYZ; hwHbm: $W6Khq = $a2KoW->input("\162\x65\143\145\x70\x63\x69\x6f\x6e\x5f\151\144", 0); goto aSaOC; tX2sN: k5zJX: goto lxm8B; U81An: if ($XiQel["\145\x73\164\141\144\x6f"]) { goto mUIOz; } goto t3I_l; QoRp0: if ($Y0nwI == 0) { goto LHOaF; } goto GhPDd; k6JJf: mUIOz: goto eHIou; nwcNj: $XiQel = $uHsl_->listar_todo_desde_recepcion_documento($W6Khq, $t_WVr); goto sqw79; BQBgP: LHOaF: goto nwcNj; W2p4Q: goto znNmj; goto BQBgP; oZSYZ: return respuesta::ok($NpOYL); goto tX2sN; t3I_l: return $XiQel; goto Bwask; D2iU_: $NpOYL = []; goto U81An; deIQl: foreach ($XiQel as $zbIDR => $PtPs1) { goto nIcHz; nIcHz: if ($PtPs1->qU5jR == "\146") { goto pJfJj; } goto V6cwC; JzAfh: jRrBg: goto D5B2w; g7V_S: pJfJj: goto MEb06; MEb06: array_push($NpOYL, $PtPs1); goto JzAfh; V6cwC: goto jRrBg; goto g7V_S; D5B2w: YvICe: goto sGUhS; sGUhS: } goto EN0cv; lxm8B: } public function load_main_tree() { goto PNBzO; fWYwF: $pmu5n = proyecto::select("\160\162\157\x79\145\143\x74\157\x2e\160\162\x6f\171\x65\x63\164\157\137\x69\x64", "\x70\162\157\171\145\x63\x74\x6f\x2e\160\162\157\x79\x65\x63\164\x6f\x5f\x6e\x6f\x6d\x62\x72\145\40\141\x73\x20\x74\145\170\164", "\145\x71\x75\151\x70\157\56\165\163\165\141\x72\x69\157\x5f\151\144")->leftJoin("\x65\161\165\151\x70\x6f", "\145\161\x75\151\x70\x6f\x2e\x70\x72\x6f\x79\x65\143\x74\x6f\137\x69\x64", "\x70\162\157\171\145\x63\164\x6f\56\160\x72\x6f\x79\145\x63\164\x6f\x5f\151\x64")->with("\143\150\151\x6c\144\x72\145\x6e\137\143\x61\160\164\x75\162\x61")->where("\x75\x73\x75\141\162\151\157\137\151\x64", $OuzsJ)->orderBy("\x70\162\x6f\x79\x65\143\x74\157\x5f\x69\144")->get(); goto LpgBn; h_2l7: $OuzsJ = session("\165\163\165\141\162\x69\157\x5f\x69\x64"); goto fWYwF; EcnKq: $pmu5n = proyecto::select("\x70\x72\x6f\171\145\x63\x74\157\x5f\x69\x64", "\x70\162\157\x79\145\143\x74\x6f\x5f\x6e\x6f\x6d\x62\x72\145\x20\x61\x73\40\x74\x65\170\164")->with("\143\150\x69\154\x64\162\145\156\137\143\x61\x70\x74\165\162\x61")->orderBy("\160\162\157\171\145\x63\164\157\137\x69\x64")->get(); goto SGKvm; PNBzO: $aUkLf = User::is_admin(); goto Z4Vjb; Z4Vjb: if ($aUkLf) { goto ESMI2; } goto h_2l7; LpgBn: goto OHzt_; goto YiKMz; YiKMz: ESMI2: goto EcnKq; EF0zR: return dangoController::verifyDirectoryTreeClienteCaptura($pmu5n, true); goto MMbV1; SGKvm: OHzt_: goto EF0zR; MMbV1: } public function load_main_tree_admin() { $pmu5n = proyecto::select("\x70\162\x6f\x79\145\x63\164\x6f\137\x6e\157\x6d\142\x72\145\x20\141\163\40\x74\x65\170\x74", "\x70\x72\x6f\171\145\x63\164\x6f\137\x69\x64")->with("\143\x68\x69\154\144\162\145\x6e\137\143\x61\160\164\165\x72\141")->orderBy("\x70\162\157\x79\145\x63\x74\157\x5f\x69\x64")->get(); return dangoController::verifyDirectoryTreeClienteCapturaAdmin($pmu5n, true); } public function load_main_tree_documento() { $pmu5n = proyecto::select("\x70\162\x6f\x79\145\x63\x74\x6f\137\x6e\157\155\x62\x72\145\40\141\163\40\x74\x65\x78\x74", "\x70\162\x6f\171\x65\143\164\x6f\137\x69\144")->with("\143\x68\x69\154\144\x72\145\x6e\x5f\x63\x61\160\164\165\162\141")->orderBy("\x70\162\x6f\171\x65\x63\x74\157\137\x69\144")->get(); return dangoController::verifyDirectoryTreeClienteCapturaDocumento($pmu5n, true); } }
