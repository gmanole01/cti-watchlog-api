<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = request(['email', 'password']);

        $fcmToken = $request->get('fcm_token');
        if(!$fcmToken) {
            return response()->json([
                'error' => true,
                'error_msg' => 'FCM token is required!'
            ]);
        }

        if (! $token = auth()->attempt($credentials)) {
            return response()->json([
                'error' => true,
                'error_msg' => 'Email address or password incorrect!'
            ]);
        }

        $user = auth()->user();
        $user->fcm_token = $fcmToken;
        $user->save();

        return $this->respondWithToken($token);
    }

    public function register(Request $request): JsonResponse {
        $data = $request->all();
        $validator = Validator::make($data, [
            'email_address' => 'required|email|unique:users,email',
            'username' => 'required|regex:/^[A-Za-z0-9-_]{3,24}$/i',
            'password' => [
                'required',
                Password::min(8)
            ],
            'repeat_password' => 'required|same:password',
            'fcm_token' => 'required'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'error' => true,
                'error_msg' => $errors->first()
            ]);
        }

        $validated = $validator->validated();

        $hash = Hash::make($validated['password']);

        // Create user.
        $user = User::query()->create([
            'username'  => $validated['username'],
            'password'  => $hash,
            'email'     => $validated['email_address'],
            'fcm_token' => $validated['fcm_token'],
            'language'  => '',
            'profile_picture' => '',
        ]);

        return response()->json([
            'error' => false,
            'data' => [
                'email_address' => $validated['email_address'],
                'username' => $validated['username']
            ],
        ]);
    }

    /**
     * Get the authenticated User.
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        /** @var JWTGuard $auth */
        $auth = auth();
        return $this->respondWithToken($auth->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return JsonResponse
     */
    protected function respondWithToken(string $token): JsonResponse {
        /** @var JWTGuard $auth */
        $auth = auth();
        /** @var User $user */
        $user = $auth->user();

        return response()->json([
            'error' => false,
            'data' => [
                'email_address' => $user->email,
                'username' => $user->username,
                'profile_picture' => $user->profile_picture,

                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => $auth->factory()->getTTL() * 60
            ],
        ]);
    }
}
