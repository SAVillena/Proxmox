<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Passport\Passport;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\MultiAuth\Authenticator;


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

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    private $auth;

    public function __construct(Authenticator $auth)
    {
        $this->auth = $auth;
    }

    public function loginUser(Request $request)
    {
        Log::info(["requestllegueeeee"]);
            Log::info(["request" => $request]);
            $request->request->add(['provider' => 'users']);
            //$credenciales = [];
            //$request->email=strtolower($request->email);
            $request->merge([
                'email' => strtolower($request->email)
            ]);
            $credenciales = array_values($request->only('email', 'password', 'provider'));
            Log::info(["credenciales" => $credenciales]);
            //$credenciales ['email'] = strtolower($credenciales['email']);


            if (!$user = $this->auth->attempt(...$credenciales)) {

                return view('auth.login', ['error' => 'Credenciales incorrectas']);
            }
            Passport::personalAccessTokensExpireIn(Carbon::now()->addDays(7));
            $tokenResult = $user->createToken($user->name);
/*             Passport::token()->where('id', $tokenResult->token['id'])->first()->update(['sistema_id' => $request->sistema_id]);
 */            $success = [
                'token' => $tokenResult->accessToken,
                'token_type' => 'Bearer Token',
                'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString()
            ];
            Log::info("success", $success);
            return redirect()->route('proxmox.index');

    }
}
