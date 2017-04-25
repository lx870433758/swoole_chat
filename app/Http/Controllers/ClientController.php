<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientController extends Controller
{
    //
    public function index(Request $request){
        session('user', $request->user());
        return view('Client.index',['request' => $request]);
    }
}
