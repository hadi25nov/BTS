<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
class ApiController extends Controller
{
    public function register(Request $request)
    {
    	//Validate data
        $data = $request->only('username', 'email', 'ecrypted_password','phone','address','city','country','name','postcode');
        $validator = Validator::make($data, [
            'username' => 'required|string',
            'email' => 'required|email|unique:users',
            'ecrypted_password' => 'required|string|min:6|max:50',
            'phone' => 'required|string',
            'address' => 'required|string|max:255',
            'city' => 'required|string',
            'country' => 'required|string',
            'name' => 'required|string',
            'postcode' => 'required|string',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is valid, create new user
        $user = User::create([
        	'username' => $request->username,
        	'email' => $request->email,
        	'password' => bcrypt($request->ecrypted_password),
        	'phone' => $request->phone,
        	'address' => $request->address,
        	'city' => $request->city,
        	'country' => $request->country,
        	'name' => $request->name,
        	'postcode' => $request->postcode,
        ]);

        //User created, return success response
        
        return response()->json([
            // 'success' => true,
            // 'message' => 'User created successfully',
            'user' => [
            'username' => $request->username,
        	'email' => $request->email,
        	'password' => ($request->ecrypted_password),
        	'phone' => $request->phone,
        	'address' => $request->address,
        	'city' => $request->city,
        	'country' => $request->country,
        	'name' => $request->name,
        	'postcode' => $request->postcode,
            ]
        ], Response::HTTP_OK);
    }
 
    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);
        $credentials = request(['email', 'password']);
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
         
        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }
 
    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

		//Request is validated, do logout        
        try {
            JWTAuth::invalidate($request->token);
 
            return response()->json([
                'success' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
 
    public function get_user(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);
 
        $user = JWTAuth::authenticate($request->token);
 
        return response()->json(['user' => $user]);
    }
}
