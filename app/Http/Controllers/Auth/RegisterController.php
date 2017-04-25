<?php

namespace App\Http\Controllers\Auth;

use App\Model\Users;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/client/index';
    public $timestamps = false;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function postRegister(Request $request)
    {
        $checkRes = $this->checkDate($request);
        if($checkRes['code'] != "100"){
            //return redirect('/home/login');
        }
        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);
        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }
    public function checkDate(Request $request){
        $user_name = $request->input('user_name');
        $email = $request->input('email');
        $res = $this->returnMessage($user_name,$email);
        return $res;

    }
    public function returnMessage($user_name,$email){
        if(!$user_name){
            return ['code'=>102, 'message'=> '用户名不能为空'];
        }
        if(!$email){
            return ['code'=>102, 'message'=> '邮箱不能为空'];
        }
        $checkUserName = $this->checkUserName($user_name);
        if($checkUserName){
            return ['code'=>'104', 'message'=> '用户名已存在'];
        }
        $checkEmail = $this->checkEmail($email);
        if(!$checkEmail){
            return ['code'=>'104', 'message'=> '邮箱格式错误'];
        }
        return ['code'=>'100', 'message'=> '验证通过'];

    }
    public function checkUserName($user_name){
        $res = Users::where(array('user_name' => $user_name))->first();
        if($res){
            return true;
        }
        return false;
    }
    public function checkEmail($email){
        $pattern="/([a-z0-9]*[-_.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[.][a-z]{2,3}([.][a-z]{2})?/i";
        if(preg_match($pattern,$email)){
            return true;
        }
        return false;
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'user_name' => 'required|max:255|unique:users',
            'email' => 'required|email|max:255',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return Users::create([
            'user_name' => $data['user_name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'ctime'=> date('Y-m-d H:i:s',time()),
            'login_time'=> date('Y-m-d H:i:s',time()),
        ]);
    }
}
