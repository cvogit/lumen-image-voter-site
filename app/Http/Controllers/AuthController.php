<?php

namespace App\Http\Controllers;

use App\User;
use App\UserVerification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Cvogit\LumenJWT\JWT;
use \Exception;

class AuthController extends Controller
{
	/*
	 * The JWT factory
	 *
	 */
	private $jwt;

	public function __construct(JWT $jwt)
	{
		$this->jwt = $jwt;
	}

	/**
	 * Check user login is valid and active
	 *
	 * @param \Illuminate\Http\Request
	 *
	 * @return mixed 
	 */
	public function login(Request $request)
	{
		$data = $request->all();
		$validation = $this->loginValidator($data);
		
		if($validation->fails())
			return response()->json(["message" => $validation->errors()], 422);

		// Find user from db
		$user = User::where('email', $request->input('email'))->first();

		// Check user exist and correct
		if($user == null)
			return response()->json(['message' => "Incorrect login."], 404);
		if (!Hash::check($request->input('password'), $user->password))
			return response()->json(['message' => "Incorrect login."], 404);

		// Check if user is verified
		if (!$user->isVerified)
			return response()->json(['message' => "The email is not verified."], 404);

		// Create and return JWT to user, signed with user id
		try {
			$token = $this->jwt->create($user->id);
		} catch(\Exception $e){
			return response()->json(['message' => "Could not create JWT, cvogit/lumen-jwt errors."], 404);
		}

		date_default_timezone_set('America/Los_Angeles');
		$user->lastLogin = date('m/d/Y h:i:s a');
		$user->save();

		return response()->json([
			'message' => "Login Successful.",
			'result'  => [
				'token' => $token,
				'userId'=> $user->id,
			]
		], 200);
	}

	/**
	 * Validate user inputs
	 * @param array
	 * @return boolean
	 */
	public function loginValidator(array $data)
	{

		return Validator::make($data, [
			'email' 		=> 'required|email',
			'password'  => 'required|string|min:6',
		]);
	}

	/**
	 * Validate user inputs
	 * @param array
	 * @return boolean
	 */
	public function refresh(Request $request)
	{
		// Find user from db
		$user = $request['req']->getUser();

		// Check user exist and correct
		if($user == null)
			return response()->json(['message' => "Incorrect login."], 404);

		// Create and return JWT to user, signed with user id
		try {
			$token = $this->jwt->create($user->id);
		} catch(\Exception $e){
			return response()->json(['message' => "Could not create JWT, cvogit/lumen-jwt errors."], 404);
		}

		date_default_timezone_set('America/Los_Angeles');
		$user->lastLogin = date('m/d/Y h:i:s a');
		$user->save();

		return response()->json([
			'message' => "Login Successful.",
			'result'  => [
				'token' => $token,
				'userId'=> $user->id,
			]
		], 200);
	}

	/**
	 * Check user login is valid and active
	 *
	 * @param \Illuminate\Http\Request
	 *
	 * @return mixed 
	 */
	public function verify(Request $request, $verificationCode)
	{
		$userVerification = UserVerification::where('code', $verificationCode)->first();

		$user = User::find($userVerification->user_id);

		if( !$user ) {
			return response()->json(['message' => "Cannot verify user or user does not exist."], 422);
		}

		$user->isVerified = true;
		$user->save();

		return response()->json(['message' => "Verification success!"], 200);
	}
}