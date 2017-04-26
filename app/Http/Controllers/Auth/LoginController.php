<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/client/index';
    public function getLogin(Request $request){
        return view('auth.index');
    }

    public function postLogin(Request $request){
        $res = $this->validateLogin($request);
        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }
        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }


    protected function validator(array $data)
    {
        return Validator::make($data, [
            'user_name' => 'required|unique:user',
            'user_pwd' => 'required|min:6',
        ]);
    }
    /**
     * @param Request $request
     */
    protected function validateLogin(Request $request)
    {
        $res = $this->validate($request,[
           'user_name' => 'required',
            'password' => 'required',
        ], [
            'user_name.required' => '用户名必须',
            'password.required' => '密码必须',
        ]);
        if(!$res){
            return false;
        }
        return true;
    }
    public function username()
    {
        return 'user_name';
    }
    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            return false;
        }
        return true;
    }
    protected function credentials(Request $request)
    {
        return $request->only('user_name', 'password');
    }
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->input('remember_me')
        );
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        $errors = [$this->username() => trans('用户名密码不匹配')];
        if ($request->expectsJson()) {
            return response()->json($errors, 422);
        }

        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors($errors);
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->flush();

        $request->session()->regenerate();

        return redirect('/auth/login');
    }

}
