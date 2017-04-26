<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
class ClientController extends Controller
{

    public function index(Request $request){

        return view('Client.index',['request' => $request]);
    }
}
