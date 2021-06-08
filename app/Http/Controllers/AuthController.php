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

    public function registerReader(Request $request)
    {
        $reader_email = $request -> reader_email;
        $reader_name = $request -> reader_name;
        $reader_pwd = $request -> reader_pwd;
        $pdf_id = $request -> pdf_id;
        $pdf_name = $request -> pdf_name;
        $uploader_id = $request -> uploader_id;

        $register = $this -> auth -> registerReadeer($reader_email, $reader_name, $reader_pwd, $pdf_id, $pdf_name, $uploader_id);
        return $register;
    }

    public function loginReader(Request $request)
    {
        $reader_email = $request -> reader_email;
        $uploader_id = $request -> uploader_id;
        $login = $this -> auth -> loginReader($reader_email, $uploader_id);
        return $login;
    }

    public function changeInfo(Request $request)
    {
        $user_id = $request -> u_id;
        $user_name = $request -> u_name;
        $user_email = $request -> u_email;
        $current_pwd = $request -> u_current_pass;
        $new_pwd = $request -> u_new_pass;

        $change = $this -> auth -> changeInfo($user_id, $user_name, $user_email, $current_pwd, $new_pwd);
        return $change;
    }

}
