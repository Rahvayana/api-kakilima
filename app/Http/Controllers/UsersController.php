<?php

namespace App\Http\Controllers;

use App\User;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    public function login(Request $request)
    {
        $rules = array(
            'email' => 'required|email',
            'password' => 'required',
        );
        $validator = Validator::make( $request->all(), $rules);
        if ( $validator->fails())
            {
                return [
                    'status' => 404,
                    'message' => $validator->errors()->first()
                ];
            }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response([
                'status' => 404,
                'message' => "Akun Tidak Ditemukan"
            ]);
        }

        return response([
            'token'=>$user->createToken('token')->plainTextToken,
            'status'=>'200',
            'message'=>'Sukses'
        ]) ;
    }
    //register fungsi
    public function sendOTP(Request $request)
    {
        $cek=DB::table('users')->where('no_hp',$request->no_hp)->first();
        if(!$cek){ //isEmpty
            $status=200;
            $message="Sukses";
            $data['otp']="55555";
            $data['no_hp']=$request->no_hp;
            DB::table('users')->insert([
                [
                    'no_hp' => $request->no_hp,
                    'otp' => $data['otp'],
                    'otp_status'=>FALSE,
                    'created_at' => date('Y-m-d H:m:s'),
                    'updated_at' => date('Y-m-d H:m:s')
                 ]
            ]);
        }else{
            $status=400;
            $message="Nomor Hp Sudah Terdaftar";
            $data=NULL;
        }
        return response([
            'data'=>$data,
            'status'=>$status,
            'message'=>$message
        ]);
    }

    public function resendOTP(Request $request)
    {
        $cek=DB::table('users')
        ->select('email','password')
        ->where('no_hp',$request->no_hp)->first();
        if(($cek->email||$cek->password)==NULL){ //isEmpty
            $status=200;
            $message="Sukses";
            $data['otp']="12345";
            $data['no_hp']=$request->no_hp;
            DB::table('users')->where('no_hp', $request->no_hp)
            ->update([
                'otp' => $data['otp'],
                'otp_status'=>FALSE,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s')
            ]);
        }else{
            $status=400;
            $message="Nomor Hp Sudah Terdaftar";
            $data=NULL;
        }
        return response([
            'data'=>$data,
            'status'=>$status,
            'message'=>$message
        ]);
    }



    public function cekOTP(Request $request)
    {
        $OTP=DB::table('users')->select('otp')->where('no_hp',$request->no_hp)->first();
        if($request->otp==$OTP->otp){
            DB::table('users')->insert([
                [
                    'status' =>TRUE,
                    'otp_status'=>TRUE,
                    'created_at' => date('Y-m-d H:m:s'),
                    'updated_at' => date('Y-m-d H:m:s')
                 ]
            ]);
            $data=$request->no_hp;
            $message="Sukses";
            $status=200;
        }else{
            $status=400;
            $message="Nomor OTP Salah";
            $data=NULL;
        }
        return response([
            'data'=>$data,
            'message'=>$message,
            'status'=>$status

        ]);
    }
    public function addProfil(Request $request)
    {
        $user_id=User::where('no_hp',$request->no_hp)->first();
        $cekEmail=DB::table('users')->select('*')->where('email',$request->email)->first();
        if(!$cekEmail){
            $users=new User();
            $users=User::find($user_id->id);
            $users->name=$request->name;
            $users->email=$request->email;
            $users->tgl_lahir=$request->tgl_lahir;
            $users->alamat=$request->alamat;
            $users->provinsi=$request->provinsi;
            $users->kota=$request->kota;
            $users->kecamatan=$request->kecamatan;
            $users->password=bcrypt($request->password);
            $users->save();
            $message='Sukses';
            $status=200;
        }else{
            $message='Email Sudah Terpakai';
            $status=400;
        }

        return response([
            'message'=>$message,
            'status'=>$status
        ]);

    }
    public function addFoto(Request $request)
    {
        dd($request);
    }

    public function profile(Request $request)
    {
        $data['user']=$request->user();
        $data['foto']='www.google.com/jaya.jpg';
        return response([
            'data'=>$data,
            'message'=>'Sukses',
            'status'=>200
        ]);
    }
}
