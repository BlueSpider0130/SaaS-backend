<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Models\AuthModel;
// use Barryvdh\DomPDF\Facade as PDF;

class UploadController extends Controller
{
    //
    public function __construct(AuthModel $authmodel)
    {
        $this->auth = $authmodel;
    }

    public function singleUp(Request $request)//single upload feature with file and uploader id and uploader email, name and ... in controller
    {
        $pdf = $request -> pdf;
        // $pdf->PDF::setEncryption('password','owner', array('print'));
        $user_id = $request -> user_id;
        // echo  $user_id;
        $user_email = $request -> user_email;
        $user_name = $request -> user_name;
        $uploadPDF = $this -> auth -> registerUpload($pdf, $user_id, $user_email, $user_name);
        return $uploadPDF;
    }

    public function download(Request $request)
    {
        // Check if file exists in app/storage/file folder
        $filename = $request -> file_name;
        $reader_email = $request -> reader_email;
        $file_path = "uploads/" . $filename;//this is uploaded pdf url from front end using axios
        
        //password
        $pdf = new \setasign\FpdiProtection\FpdiProtection();
        $pagecount = $pdf->setSourceFile($file_path);
        // return $pagecount;
        for ($i = 1; $i <= $pagecount; $i++) {
            // import each page from the original PDF
            $tplidx = $pdf->importPage($i);
          
            // get the size, orientation etc of each imported page
            $specs = $pdf->getTemplateSize($tplidx);
          
            // set the correct orientatil, width and height (allows for mixed page type PDFs)
            $pdf->addPage($specs['orientation'], [ $specs['width'], $specs['height'] ]);
            $pdf->useTemplate($tplidx);
        }
        $pdf->setProtection([], $reader_email, "masterpwd");
        
        $pdf->SetCreator("My Awesome App");
        //----------------//--------------

        $headers = array(
            'Content-Type: application/pdf',
            'Content-Disposition: attachment; filename='.$filename,
        );
        if ( file_exists( $file_path ) ) {
            // Send Download
            return $pdf->Output("S", "jkk");
            // return \Response::download( $file_path, $filename, $headers );//this is download code 
        } else {
            // Error
            exit( 'Requested file does not exist on our server!' );
        }
        //This is  laravel back-end and
    }

    public function getReaderData(Request $request)
    {
        $user_email = $request -> user_email;
        $user_name = $request -> user_name;
        $user_id = $request -> user_id;
        $get_table = $this -> auth -> getReaderData($user_email, $user_name, $user_id);
        return $get_table;
    }

    public function setActiveAccount(Request $request)
    {
        $action_token = $request -> status;
        $reader_id = $request -> reader_id;
        $set = $this -> auth -> setActiveAccount($action_token, $reader_id);
        return $set;
    }

    public function getPdfData(Request $request)
    {
        $user_id = $request -> user_id;
        $get = $this -> auth -> getPdfData($user_id);
        return $get;
    }

}
