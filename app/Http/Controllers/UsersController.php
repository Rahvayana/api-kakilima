<?php

namespace App\Http\Controllers;

use App\User;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response([
                'email' => ['The provided credentials are incorrect.'],
            ],404);
        }

        return response([
            'token'=>$user->createToken('token')->plainTextToken,
            'status'=>'200',
            'massage'=>'Sukses'
        ]) ;
    }
    public function profile(Request $request)
    {
        $data['user']=$request->user();
        $data['foto']='www.google.com/jaya.jpg';
        return response([
            'data'=>$data,
            'massage'=>'Sukses',
            'status'=>200
        ]);
    }
}
