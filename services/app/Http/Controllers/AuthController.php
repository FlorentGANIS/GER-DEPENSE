<?php

namespace App\Http\Controllers;

use App\Models\MailVerification;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'generateCodeOfVerification',
        'getCodeOfVerification', 'codeVerification', 'getNewCodeOfVerification']]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Login et/ou mot de passe invalides',
                'status' => 422
            ]);
        }
        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json([
                'message' => 'Login et/ou mot de passe invalides',
                'status' => 401
            ]);
        }
        return $this->createNewToken($token);
    }
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'last_name' => 'required|string|min:3|max:30',
            'first_name' => 'required|string|min:3|max:50',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->toJson(),
                'status' => 400
            ]);
        }
        $user = User::create(array_merge(
            $validator->validated(),
            ['id' => generateDBTableId(10, "App\Models\User"), 'password' => bcrypt($request->password)]
        ));
        return response()->json([
            'message' => 'Utilisateur enregistré avec succès',
            'user' => $user,
            'status' => 200
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Utilisateur déconnecté avec succès']);
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return response()->json(auth()->user());
    }
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'message' => 'Connexion réussie',
            'access_token' => $token,
            'expires_in' => auth()->factory()->getTTL() * 60,
            'status' => 200
        ]);
    }

    // Generation of verification mail
    public function generateCodeOfVerification()
    {
        $list_characters = '0123456789ABCDEF';
        $n = 6;
        $code_verif = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($list_characters) - 1);
            $code_verif .= $list_characters[$index];
        }

        return $code_verif;
    }

    // Get code 
    public function getCodeOfVerification(Request $request)
    {
        $email_exist = User::where('email', $request->email)->first();
        if($email_exist){
            return response()->json([
                'message' => 'Cette adresse mail existe déjà pour un compte dans le système.',
                'status' => 500
            ]);
        }
        
    
        try {
            if ($request->email) {
                //Generate verification email code
                $code = $this->generateCodeOfVerification();
                $response = MailVerification::create(['email' => $request->email, 'code' => $code]);
                if ($response) {
                    //Sendding Mail
                    sendMail(
                        [
                            env("ADMIN_MAIL_1"),
                            $request->email
                        ],
                        ['code' => $code],
                        'emails.emailVerification',
                        'Vérification de compte mail',
                        env("APP_NAME"),
                        'Merci de taper le code reçu sur la page de vérification de mail sur l\'application pour continuer
                        votre demande d\'inscription.
                        '
                    );
                }
            }
            return response()->json([
                'message' => 'Code de vérification envoyé par mail.',
                'status' => 200
            ]);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                'data' => [],
                'message' => 'Une erreur interne est survenue',
                'status' => 500
            ]);
        }
    }

    //Verification
    public function codeVerification(Request $request)
    {       
        $email = $request->email;
        $code = $request->code;
        
        try {
            $res = MailVerification::where('email', $email)->where('code', $code)->first();

            if ($res) {
                return response()->json([
                    'message' => 'Code valide',
                    'status' => 200
                ]);
            } else {
                return response()->json([
                    'message' => 'Code invalide',
                    'status' => 500
                ]);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'data' => [],
                'message' => 'Une erreur interne est survenue',
                'status' => 500
            ]);
        }
    }

    //Resend verification code
    public function getNewCodeOfVerification(Request $request)
    {
        try {
            if ($request->email) {
               
                //Generate verification email code
                $code = $this->generateCodeOfVerification();
                $response = MailVerification::where('email', $request->email)->update(['code' => $code]);
                if ($response) {
                    //Sendding Mail
                    sendMail(
                        [
                            env("ADMIN_MAIL_1"),
                            $request->email
                        ],
                        ['code' => $code],
                        'emails.emailVerification',
                        'Vérification de compte mail',
                        env("APP_NAME"),
                        'Merci de taper ce nouveau code reçu sur la page de vérification de mail sur l\'application pour continuer
                        votre demande d\'inscription.
                        '
                    );
                }
                return response()->json([
                    'message' => 'Code de vérification renvoyé par mail',
                    'data' => '',
                    'status' => 200
                ]);
            } else {
                return response()->json([
                    'message' => 'Adresse mail inexistant pour renvoyer le code',
                    'data' => '',
                    'status' => 300
                ]);
            }
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                'data' => [],
                'message' => 'Une erreur interne est survenue',
                'status' => 500
            ]);
        }
    }
}
