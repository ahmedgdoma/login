<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiController extends Controller
{
    /**
     * Get a JWT via given credentials.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function register(Request $request)
    {
        //validate incoming request
        $this->validate($request, [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'country_code' => 'required|string|max:5',
            'phone_number' => ['required','unique:users,phone_number', 'regex:/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'],
            'gender' => "required|in:male,female",
            'birth_date' => 'required|date|before:today',
            'email' => 'required|email|string|max:255|unique:users,email',
            'avatar' => 'required|image',
        ]);
        $user = $request->all();
        $avatar = $request->file('avatar');
        $avatar->storeAs('avatar', $avatar->getClientOriginalName());
        $url = Storage::url('avatar/'. $avatar->getClientOriginalName());
        $user['avatar'] = $url;
        $user['birth_date'] = Carbon::createFromDate($user['birth_date']);

        try{
            User::create($user);
        }catch (\Exception $e){
            return response()->json(['message' => 'can not save user'], 500);
        }
//        $credentials = $request->only(['email', 'password']);
//
//        if (! $token = Auth::attempt($credentials)) {
//            return response()->json(['message' => 'email or password not valid'], 401);
//        }
        return response()->json(['message' => 'user saved'], 201);
//        return $this->respondWithToken($token);
    }

    public function setPassword(Request $request){
        $this->validate($request, [
            'password' => 'required|string|max:100',
            'phone_number' => ['required','exists:users,phone_number', 'regex:/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/'],

        ]);
        $user = User::where('phone_number', $request->get('phone_number'))->first();
        if(!$user){
            return response()->json(['message' => 'unauthorized or bad request'], 401);
        }
        $token = JWTAuth::fromUser($user);
        $user->password = Hash::make($request->get('password'));
        $user->save();
        return $this->respondWithToken($token);
    }

    public function setStatus(Request $request){
        $user = Auth::user();
        if ($user->phone_number != $request->get('phone_number'))
            return response()->json(['message' => 'unauthorized or bad request'], 401);
        $user->statuses()->create($request->all());

        return response()->json(['message' => 'status saved'], 201);
    }


    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
