<?php

namespace Fedatario\Controllers;

use Illuminate\Http\Request;

Trait DocumentController
{
    public function msword(Request $request)
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();

        $texto_prueba = "lalalalalal panderino";
        $section->addText($texto_prueba,array('name'=>'Arial','size' => 20,'bold' => true));

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try{
            $objWriter->save(storage_path('Appdividend.docx'));
        }catch (Exception $e){

        }

        return response()->download(storage_path('Appdividend.docx'));
    }
}
