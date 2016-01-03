<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;

class AuthController extends Controller
{
   public function __construct()
   {
      // Apply the jwt.auth middleware to all methods in this controller
      // except for the authenticate method. We don't want to prevent
      // the user from retrieving their token if they don't already have it
      $this->middleware('jwt.auth', ['except' => ['auth', 'register']]);
   }

   /**
    * Authenticate User
    *
    * @param  array  $req(email, password)
    * @return Token
    */
   public function auth(Request $req)
   {
      $credentials = $req->only('email', 'password');
      //return response()->json(['error' => $req->email], 401);

      try {
      // verify the credentials and create a token for the user
         if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'invalid_credentials'], 401);
            //return Response::json(false, HttpResponse::HTTP_UNAUTHORIZED);
         }
      } catch (JWTException $e) {
         // something went wrong
         return response()->json(['error' => 'could_not_create_token'], 500);
      }

      // if no errors are encountered we can return a JWT
      return response()->json(compact('token'));
   }

   /**
    * Get User authenticate
    *
    * @return User
    */
   public function show()
   {
      try {
         if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['user_not_found'], 404);
         }
      } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
         return response()->json(['token_expired'], $e->getStatusCode());
      } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
         return response()->json(['token_invalid'], $e->getStatusCode());
      } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
         return response()->json(['token_absent'], $e->getStatusCode());
      }
      // the token is valid and we have found the user via the sub claim
      return response()->json(compact('user'));
   }

   /**
     * Create a new user instance
     *
     * @param  array  $req
     * @return User
     */
   public function register(Request $req)
   {
      return User::create([
         'name' => $req->name,
         'email' => $req->email,
         'password' => $req->password
      ]);
   }

}