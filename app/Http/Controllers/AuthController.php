<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuthModel;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function __construct(AuthModel $authmodel)
    {
        $this->auth = $authmodel;
    }

    public function register (Request $request) 
    {
        $user_email = $request -> u_email;
        $user_pwd = $request -> u_pass;
        $user_name = $request -> u_name;
        $user_confirm = $request -> u_confirmpass;
        // $auth = new AuthModel();
        $register = $this -> auth -> registerUsers($user_email, $user_pwd, $user_name);

        return $register;
    }

    public function login (Request $request) 
    {
        $user_email = $request -> u_email;
        $user_pwd = $request -> u_pass;
        // $auth = new AuthModel();
        $login = $this -> auth -> loginUsers($user_email, $user_pwd);

        return $login;
    }

    public function registerReader(Request $request){
        $reader_email = $request -> reader_email;
        $reader_pwd = $request -> reader_pwd;
        $pdf_id = $request -> pdf_id;
        $pdf_name = $request -> pdf_name;
        $uploader_id = $request -> uploader_id;

        $register = $this -> auth -> registerReadeer($reader_email, $reader_pwd, $pdf_id, $pdf_name, $uploader_id);
        return $register;
    }

    public function loginReader(Request $request){
        $reader_email = $request -> reader_email;

        $login = $this -> auth -> loginReader($reader_email);
        return $login;
    }

}
