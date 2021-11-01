<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Unidade;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    public function login(Request $request)
    {
        $loginSuccess = Auth::attempt([
            'email'     => $request->email,
            'password'  => $request->password,
            'status'    => '1']);

        if (!$loginSuccess){
            return $this->handleLoginError ('Essas credenciais não existem ou seu usuário está inativo!');
        } else if (!Unidade::UnidadeIsActiveByUserEmail($request->email)) {
            return $this->handleLoginError ('Sua unidade está inativa! Contate o administrador.');
        }

        $request->session()->regenerate();

        session(['unidade' => Unidade::GetUnidadeByUserEmail($request->email)]);

        return redirect($this->redirectTo);
    }

    private function handleLoginError($message) {
        Auth::logout();
        return redirect('/login')->with('login_error', $message);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
