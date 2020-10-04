<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Exception;

class UsersController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
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
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(Request $request)
    {
        try {
            $this->validate($request, [
                'email'    => 'required|email',
                'password' => 'required',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }

        $token = app('auth')->attempt($request->only('email', 'password'));

        if ($token) {
            return response()->json([
                'success' => true,
                'message' => 'User authenticate',
                'token'   => $token
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials',
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $user = app('auth')->user();

        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'User profile found.',
                'user'    => $user
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
        ]);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = app('db')->table('users')->select('name', 'username', 'email')->where('id', $id)->first();

        if ($user) {
            return response()->json([
                'success' => true,
                'user'    => $user
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized',
        ]);
    }


    public function update(Request $request)
    {
        try {
            $user       = User::find($request->id);
            $user->name = $request->name;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Yay! User has been successfully updated!',
                'user'    => $user
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Oops! Unable to update user.',
            ]);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            # app('db')->table('users')->where('id', $id)->delete();

            $user = User::find($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Yay! User has been successfully removed!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Oops! Unable to delete this user.',
            ]);
        }
    }
}
