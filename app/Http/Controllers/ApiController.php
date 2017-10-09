<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use JWTAuth;
use Mockery\Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use App\User;

class ApiController extends Controller
{
    public function authenticate(Request $request){
        $credentials = $request->only('email','password');

        try{
            if (! ($token = JWTAuth::attempt($credentials))){
                return response()->json(['error'=>'Invalid Credential'],401);
            }
        }catch (JWTException $exception){
            return response()->json(['error'=>'GG'],500);
        }

        return response()->json(compact('token'),200);
    }

    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('user'));
    }

    public function refresh(Request $request){
        return response()->json($request);
    }


    public function create(Request $request){
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }


        try{
            $users = new User;
            $users->name = $request->name;
            $users->email = $request->email;
            $users->password = bcrypt($request->password);
            $users->save();
        }catch (Exception $exception){
            return response()->json(['status'=>false], $exception->getStatusCode());
        }

        $token = JWTAuth::getToken();

        $refresed = JWTAuth::refresh($token);
        return response()->json(['status'=>true,'token'=> $refresed],200);
    }
}
