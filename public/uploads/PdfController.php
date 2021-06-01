<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use PDF;
use Illuminate\Http\Request;
use App\Models\User;
use Mail;
use View;
use App\Models\EmploymentAchievement;
use Dompdf\FontMetrics;
use Dompdf\Options;

class PdfController extends Controller
{
    public function sendTemplateEmail($user_id, $lang, $template_id, $to_email, $user, $is_demo){
        
        $today = date('YmdHi');
        $startDate = date('YmdHi', strtotime('2012-03-14 09:06:00'));
        $range = $today - $startDate;
        $rand = rand(0, $range);

        if($is_demo)
            $pdf_name = "cv_demo_".$template_id."_" . $user->id.($startDate + $rand).".pdf";
        else
            $pdf_name = "cv_".$template_id."_" . $user->id.($startDate + $rand).".pdf";

        /* Language Convert File */
        $lang_data    = include(app_path().'/lang/lang.php');
        /*To set language, send lang variable to tempate*/
        
        $html = View::make('template'.$template_id, ['user' => $user, 'lang' => $lang, 'user_id' => $user_id, 'is_demo' => $is_demo, 'lang_data' => $lang_data]);
        PDF::setOptions(['isFontSubsettingEnabled' =>true]);
        $pdf = PDF::loadHTML($html,'UTF-8')->setOptions(['isRemoteEnabled' => true,'isFontSubsettingEnabled' =>true]);
        
        // $pdf->setEncryption('','owner', array('print'));

        \Storage::disk('public')->put($pdf_name, $pdf->output());
        $path = \Storage::disk('public')->getAdapter()->getPathPrefix();
        // Mail::send('emails.cv_template_header', ['user' => $user], function ($message) use ($path, $user, $to_email,$pdf_name) {
        //     $message->to($to_email, $user->name)
        //         ->subject("CV");
        //     $message->from(config('mail.from.address'), 'CV Template');
        //     $message->attach($path . $pdf_name, [
        //         'as' => $pdf_name,
        //         'mime' => 'application/pdf',
        //     ]);
        // });
        
        return $pdf_name;
    }

    public function sendTemplate(Request $request){

        //Attributes that can used to fetch data
        //user_id, lang, template_id, email, is_demo
        $user_id      = $request->input('user_id');
        $lang         = $request->input('lang');
        $template_id  = $request->input('template_id');
        $email        = $request->input('email');
        $is_demo      = $request->input('is_demo');

        /**
         * First we have to fetch user complete data 
         */
        if(!empty($lang)){
            $user_id = $lang.$user_id;
        } else {
            $lang = 'default';
        }

        $user = User::where('id', $user_id)->first();
       
        $response = response()->json(['status'=> FALSE,'message' => 'No User found with this id']);
        $is_template_id_valid = in_array($template_id,[1,2,3,4,5]);

        if(!$is_template_id_valid){
            $response = response()->json(['status'=> FALSE,'message' => 'Template id must be in this range [1,2,3,4,5]']);
        }
        if(!empty($user) && empty($email)){
            $email = $user->email;
        }
        if(!empty($user) && $is_template_id_valid){
            if($is_demo == 0) {
                $path = $this->sendTemplateEmail($user_id, $lang, $template_id, $email, $user, $is_demo);

                $response = response()->json([
                    "version"=> "v2",
                    "content"=>[
                        "messages"=> [
                            ['type'=>'file','url'=>'http://cv.cvsetup.com/storage/public/'.$path]
                        ]
                    ]
                ]);
            }

            if($is_demo == 1) {
                if(($template_id == 2) || ($template_id == 3)) {
                    $path = $this->sendDemoTemplateEmail($user_id, $lang, $template_id, $email, $user, $is_demo);

                    $response = response()->json([
                        "version"=> "v2",
                        "content"=>[
                            "messages"=> [
                                ['type'=>'file','url'=>'http://cv.cvsetup.com/storage/public/'.$path]
                            ]
                        ]
                    ]);
                } else {
                    $path = $this->sendTemplateEmail($user_id, $lang, $template_id, $email, $user, $is_demo);

                    $response = response()->json([
                        "version"=> "v2",
                        "content"=>[
                            "messages"=> [
                                ['type'=>'file','url'=>'http://cv.cvsetup.com/storage/public/'.$path]
                            ]
                        ]
                    ]);
                }
            }
        }
        return $response;
    }

    public function sendDemoTemplateEmail($user_id, $lang, $template_id, $to_email, $user, $is_demo) {

        $today = date('YmdHi');
        $startDate = date('YmdHi', strtotime('2012-03-14 09:06:00'));
        $range = $today - $startDate;
        $rand = rand(0, $range);

        $pdf_name = "cv_demo_".$template_id."_" . $user->id.($startDate + $rand).".pdf";

        /* Language Convert File */
        $lang_data    = include(app_path().'/lang/lang.php');

        /*To set language, send lang variable to tempate*/
        $html = View::make('template'.$template_id, ['user' => $user, 'lang' => $lang, 'user_id' => $user_id, 'is_demo' => $is_demo, 'lang_data' => $lang_data]);
        PDF::setOptions(['isFontSubsettingEnabled' =>true]);
        $pdf = PDF::loadHTML($html,'UTF-8')->setOptions(['isRemoteEnabled' => true,'isFontSubsettingEnabled' =>true]);
        
        // $pdf->setEncryption('','owner', array('print'));
        
        /*DEMO Setting*/
        $options = new Options(); 
        $options->set('isPhpEnabled', 'true');
        $canvas = $pdf->getDomPDF()->getCanvas();
        $fontMetrics = new FontMetrics($canvas, $options);
        $font = $fontMetrics->getFont('calibri'); 
        $height = $canvas->get_height();
        $width  = $canvas->get_width();
        $canvas->set_opacity(.3,"Multiply");
       
        $canvas->page_text($width/5, $height/2, 'DEMO', $font, 190, array(0.85, 0.85, 0.85), 120, 40, 315);
        \Storage::disk('public')->put($pdf_name, $pdf->output());
        $path = \Storage::disk('public')->getAdapter()->getPathPrefix();

        // Mail::send('emails.cv_template_header', ['user' => $user], function ($message) use ($path, $user, $to_email,$pdf_name) {
        //     $message->to($to_email, $user->name)
        //         ->subject("CV");
        //     $message->from(config('mail.from.address'), 'CV Template');
        //     $message->attach($path . $pdf_name, [
        //         'as' => $pdf_name,
        //         'mime' => 'application/pdf',
        //     ]);
        // });
        return $pdf_name;
    }
}