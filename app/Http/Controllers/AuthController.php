<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function register(RegisterRequest $request){
    	$user = User::create($request->only('first_name', 'last_name', 'email')
    		+ [
    			'password' => \Hash::make($request->input('password')),
    			'is_admin' => $request->path() === 'api/admin/register' ? 1 : 0
    		]
    	);

    	return response($user, Response::HTTP_CREATED);
    }

    public function login(Request $request){
    	if(!\Auth::attempt($request->only('email', 'password'))){
    		return response([
    			'error' => 'Invalid Credentials'
    		], Response::HTTP_UNAUTHORIZED);
    	}

    	$user = \Auth::user();
        $adminLogin = $request->path() === 'api/admin/login';
        if($adminLogin && !$user->is_admin){
            return response([
                'error' => 'Unauthorized'
            ], Response::HTTP_UNAUTHORIZED);
        }
        $scope = $request->path() === 'api/admin/login' ? 'admin' : 'ambassador';
    	$jwt = $user->createToken('token', [$scope])->plainTextToken;
    	$cookie = cookie('jwt', $jwt, 60*24);
    	return response([
    		'message' => 'success'
    	])->withCookie($cookie);
    }

    public function user(Request $request){
    	$user = $request->user();
        return new UserResource($user);
    }

    public function logout(Request $request){
    	$cookie = \Cookie::forget('jwt');
    	return response([
    		'message' => 'success'
    	])->withCookie($cookie);
    }

    public function updateInfo(UpdateRequest $request){
    	$user = $request->user();
    	$user->update($request->only('first_name', 'last_name', 'email'));
    	return response($user, Response::HTTP_ACCEPTED);
    }

    public function updatePassword(UpdatePasswordRequest $request){
    	$user = $request->user();
    	$user->update([
    		'password' => \Hash::make($request->input('password'))
    	]);
    	return response($user, Response::HTTP_ACCEPTED);
    }
}
