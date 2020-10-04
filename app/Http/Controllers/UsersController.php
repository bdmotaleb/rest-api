<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Exception;

class UsersController extends Controller
{
    public function index()
    {
        try {
            $users = app('db')->table('users')->get();
            return response()->json([
                'success' => true,
                'users'   => $users
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false
            ], 200);
        }

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request)
    {
        try {
            $this->validate($request, [
                'name'     => 'required|min:4|max:40',
                'email'    => 'required|email',
                'username' => 'required|min:6|max:20',
                'password' => 'required|alpha_num|between:6,12',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }


        try {
            $lastId = app('db')->table('users')->insertGetId([
                'name'       => trim($request->input('name')),
                'username'   => strtolower(trim($request->input('username'))),
                'email'      => strtolower(trim($request->input('email'))),
                'password'   => app('hash')->make($request->input('password')),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            $user = app('db')->table('users')->select('name', 'username', 'email')->where('id', $lastId)->first();

            return response()->json([
                'id'       => $lastId,
                'name'     => $user->name,
                'username' => $user->username,
                'email'    => $user->email
            ], 201);
        } catch (\PDOException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
