<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class UserController extends Controller
{
	public function index()
	{
		return response()->json(User::all());
	}

	public function register(Request $request)
	{
		try{
    		$validator = Validator::make($request->all(), [
    			'name' => ['required','string','max:255'],
	            'email' => ['required', 'email', Rule::unique(User::class)],
	            'password' => ['required','confirmed','max:8','min:8']
    		]);

    		if($validator->fails()) {
    			return response()->json([
    				'errors' => $validator->errors()
    			], 422);
    		}

    		$request['password'] = bcrypt($request->password);
    		
    		$user = User::create($validator->validate());

    		$token = $user->createToken('API Token')->accessToken;

    		return response()->json([
    			'message' => 'User created successfully!', 
    			'token' => $token,
                'validate' => $request->all()
    		]);
    	}catch(\Exception $ex) {
    		return response()->json([
    			'message' => 'Что то не так!',
    			'errors' => $ex->getMessage()
    		], 422);
    	}
	}
    public function login(Request $request) 
    {
    	try{
    		$validator = Validator::make($request->all(), [
	            'email' => 'required|string',
	            'password' => 'required|string|max:8|min:8'
    		]);

    		if($validator->fails()) {
    			return response()->json([
    				'errors' => $validator->errors()
    			], 422);
    		}

    		$data = request(['email', 'password']);

    		if(!auth()->attempt($data)){	
    			return response()->json(['message'=>'Пароль или логин не верный!'],401);
    		}

    		$token = auth()->user()->createToken('API token')->accessToken;
    		return response()->json(['user' => auth()->user(), 'token' => $token]);
    	}catch(\Exception $ex) {
    		return response()->json([
    			'message' => 'Что то не так!',
    			'errors' => $ex->getMessage()
    		], 422);
    	}
    }
}
