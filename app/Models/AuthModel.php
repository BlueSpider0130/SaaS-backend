<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use DB;

class AuthModel extends Model
{
    use HasFactory;
    
    public function registerUsers($user_email, $user_pwd, $user_name)
    {
        $hash_pwd = Hash::make($user_pwd);
        $already = DB::table('user_tbl')
                        ->select('*')
                        ->where('user_email', '=', $user_email)
                        ->get();
        if (count($already)>0) {
            return "already";
        } else {
            $register = ['user_email' => $user_email, 'user_pwd' => $hash_pwd, 'user_name' => $user_name];
            DB::table('user_tbl')->insert($register);
            $get_data = DB::table('user_tbl')->select('*')->where('user_email', $user_email)->first();
            $return_val = [
                "success", $get_data->user_id
            ];
            return $return_val;
        }
    }
    
    public function loginUsers($user_email, $user_pwd)
    {

        $hash_pwd = Hash::make($user_pwd);
        $login = DB::table('user_tbl')
                        ->select('*')
                        ->where('user_email', '=', $user_email)
                        ->first();

        if ($login && Hash::check($user_pwd, $login -> user_pwd)) {
            $return_val = [
                "success", $login->user_name, $login->user_id
            ];
            return $return_val; 
        } elseif ($login && !Hash::check($user_pwd, $login -> user_pwd)) {
            return "wrong_password";
        } else {
            return "no_member";
        }
                        
    }

    public function registerUpload($pdf, $user_id, $user_email, $user_name)
    {
        $original_name = $pdf -> getClientOriginalName();
        $extension = $pdf -> getClientOriginalExtension();
        $name = Str::random(30);
        $pdf -> move("uploads/", $name.".".$extension);
        $pdf_data = [ 'pdf_original_name' => $original_name, 'pdf_random_name' => $name, 'upload_user_id' => $user_id, 'upload_user_email' => $user_email, 'upload_user_name' => $user_name ];
        $result = DB::table('pdf_tbl')
                        ->insert($pdf_data);
                        
        $onePDF = DB::table('pdf_tbl')
                        ->select('*')
                        ->where('pdf_random_name', '=', $name)
                        ->first();
        $pdf_id = $onePDF -> pdf_id;
        $link = "http://165.227.94.117:5700/" . $pdf_id . "/" . $name . "/" . $user_id;
        $add_link = ['download_link' => $link];
        DB::table('pdf_tbl')
                        ->where('pdf_id', '=', $pdf_id)
                        ->update($add_link);
        return ["success",$onePDF];
        

    }

    public function registerReadeer($reader_email, $reader_name, $reader_pwd, $pdf_id, $pdf_name, $uploader_id)
    {
        date_default_timezone_set('America/Los_Angeles');
        $timezone = date_default_timezone_get();

        $hash_pwd = Hash::make($reader_pwd); 
        $already = DB::table('reader_tbl')
                        ->select('*')
                        ->where('reader_email', '=', $reader_email)
                        ->where('upload_user_id', '=', $uploader_id)
                        ->get();
        $active = $already[0]->reader_available;
        if (count($already)>0 && $active == 1) {
            return "success";
        }else if(count($already)>0 && $active == 0){
            return "disable";
        }
        else {
            $register = ['reader_email' => $reader_email, 'reader_name' => $reader_name, 'reader_pwd' => $hash_pwd, 'pdf_id' => $pdf_id, 'upload_user_id' => $uploader_id, 'reader_available' => '1'];
            DB::table('reader_tbl')->insert($register);
            return "success";
        }

    }

    public function loginReader($reader_email, $uploader_id)
    {
        $loginReader = DB::table('reader_tbl')
                            ->select('*')
                            ->where('reader_email', '=', $reader_email)
                            ->where('upload_user_id', '=', $uploader_id)
                            // ->where('reader_available', '=', 1)  //account disabled!!!!!!!
                            ->get();
        

        if (count($loginReader) > 0 && $loginReader[0] -> reader_available == 1) {
            return "success";
        } else if (count($loginReader) > 0 && $loginReader[0] -> reader_available == 0) {
            return "disable";
        }else{
            return "no_member";
        }
    }

    public function getReaderData($user_email, $user_name, $user_id)
    {
        $get = DB::table('reader_tbl')
                    ->select('reader_id','reader_email', 'reader_name', 'reader_available', 'date')
                    ->where('upload_user_id', '=', $user_id)
                    ->get();
        return $get;
    }

    public function setActiveAccount($action_token, $reader_id)
    {
        if ($action_token == true) {
            $set = DB::table('reader_tbl')
                        ->where('reader_id','=', $reader_id)
                        ->update(['reader_available' => 1]);
            return $set;
        }else{
            $set = DB::table('reader_tbl')
                        ->where('reader_id','=', $reader_id)
                        ->update(['reader_available' => 0]);
            return $set;
        }
    }

    public function getPdfData($user_id)
    {
        $get = DB::table('pdf_tbl')
                    ->select('*')
                    ->where('upload_user_id', '=', $user_id)
                    ->get();

        return $get;
    }

    public function changeInfo($user_id, $user_name, $user_email, $current_pwd, $new_pwd)
    {
        $new_hash = Hash::make($new_pwd);
        $current_pwd_tbl = DB::table('user_tbl')
                            ->select('user_pwd')
                            ->where('user_id', '=', $user_id)
                            ->get();
        if ($current_pwd_tbl && Hash::check($current_pwd, $current_pwd_tbl[0] -> user_pwd)) {
            DB::table('user_tbl')
                    ->where('user_id','=', $user_id)
                    ->update(['user_pwd' => $new_hash, 'user_email' => $user_email]);
            DB::table('pdf_tbl')
                    ->where('upload_user_id', '=', $user_id)
                    ->update(['upload_user_email' => $user_email]);

            return "success";
        }elseif ($current_pwd_tbl && !Hash::check($current_pwd, $current_pwd_tbl[0] -> user_pwd)) {
            return "wrong_pwd";
        }else{
            return "err";
        }
    }

}
