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
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return [
                'status' => 404,
                'message' => $validator->errors()->first()
            ];
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'status' => 404,
                'message' => "Akun Tidak Ditemukan"
            ]);
        }

        return response([
            'token' => $user->createToken('token')->plainTextToken,
            'status' => '200',
            'message' => 'Sukses'
        ]);
    }
    //register fungsi
    public function sendOTP(Request $request)
    {
        $rules = array(
            'no_hp' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return [
                'status' => 404,
                'message' => $validator->errors()->first()
            ];
        }
        $cek = DB::table('users')->where('no_hp', $request->no_hp)->first();
        if (!$cek) { //isEmpty
            $status = 200;
            $message = "Sukses";
            $data['otp'] = "55555";
            $data['no_hp'] = $request->no_hp;
            DB::table('users')->insert([
                [
                    'no_hp' => $request->no_hp,
                    'otp' => $data['otp'],
                    'otp_status' => FALSE,
                    'created_at' => date('Y-m-d H:m:s'),
                    'updated_at' => date('Y-m-d H:m:s')
                ]
            ]);
        } else {
            $status = 400;
            $message = "Nomor Hp Sudah Terdaftar";
            $data = NULL;
        }
        return response([
            'data' => $data,
            'status' => $status,
            'message' => $message
        ]);
    }

    public function resendOTP(Request $request)
    {
        $rules = array(
            'no_hp' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return [
                'status' => 404,
                'message' => $validator->errors()->first()
            ];
        }
        $cek = DB::table('users')
            ->select('email', 'password')
            ->where('no_hp', $request->no_hp)->first();
        if (($cek->email || $cek->password) == NULL) { //isEmpty
            $status = 200;
            $message = "Sukses";
            $data['otp'] = "12345";
            $data['no_hp'] = $request->no_hp;
            DB::table('users')->where('no_hp', $request->no_hp)
                ->update([
                    'otp' => $data['otp'],
                    'otp_status' => FALSE,
                    'created_at' => date('Y-m-d H:m:s'),
                    'updated_at' => date('Y-m-d H:m:s')
                ]);
        } else {
            $status = 400;
            $message = "Nomor Hp Sudah Terdaftar";
            $data = NULL;
        }
        return response([
            'data' => $data,
            'status' => $status,
            'message' => $message
        ]);
    }



    public function cekOTP(Request $request)
    {
        $rules = array(
            'no_hp' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return [
                'status' => 404,
                'message' => $validator->errors()->first()
            ];
        }
        $OTP = DB::table('users')->select('otp')->where('no_hp', $request->no_hp)->first();
        if ($request->otp == $OTP->otp) {
            DB::table('users')->insert([
                [
                    'status' => TRUE,
                    'otp_status' => TRUE,
                    'created_at' => date('Y-m-d H:m:s'),
                    'updated_at' => date('Y-m-d H:m:s')
                ]
            ]);
            $data = $request->no_hp;
            $message = "Sukses";
            $status = 200;
        } else {
            $status = 400;
            $message = "Nomor OTP Salah";
            $data = $request->no_hp;
        }
        return response([
            'data' => $data,
            'message' => $message,
            'status' => $status

        ]);
    }
    public function addProfil(Request $request)
    {
        $rules = array(
            'email' => 'required|email',
            'password' => 'required',
            'name' => 'required',
            'tgl_lahir' => 'required',
            'alamat' => 'required',
            'provinsi' => 'required',
            'kota' => 'required',
            'kecamatan' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return [
                'status' => 404,
                'message' => $validator->errors()->first()
            ];
        }
        $user_id = User::where('no_hp', $request->no_hp)->first();
        $cekEmail = DB::table('users')->select('*')->where('email', $request->email)->first();
        if (!$cekEmail) {
            $users = new User();
            $users = User::find($user_id->id);
            $users->name = $request->name;
            $users->email = $request->email;
            $users->tgl_lahir = $request->tgl_lahir;
            $users->alamat = $request->alamat;
            $users->provinsi = $request->provinsi;
            $users->kota = $request->kota;
            $users->kecamatan = $request->kecamatan;
            $users->password = bcrypt($request->password);
            $users->save();
            $message = 'Sukses';
            $status = 200;
        } else {
            $message = 'Email Sudah Terpakai';
            $status = 400;
        }

        return response([
            'message' => $message,
            'status' => $status
        ]);
    }
    public function addFoto(Request $request)
    {
        dd($request);
    }
    public function statusUser(Request $request)
    {
        $id = $request->user()->id;
        $data = DB::table('users')->select('status', 'name', 'foto')->where('id', $id)->first();
        $data->foto = "https://randomuser.me/api/portraits/men/1.jpg";
        return response([
            'data' => $data,
            'message' => 'sukses',
            'status' => 200
        ]);
    }
    public function profile(Request $request)
    {
        $data['user'] = $request->user();
        $data['user']->foto = "https://randomuser.me/api/portraits/men/1.jpg";
        $data['foto'] = 'www.google.com/jaya.jpg';
        return response([
            'data' => $data,
            'message' => 'Sukses',
            'status' => 200
        ]);
    }

    public function mapsApi()
    {
        // -7.112254, 112.408554
        // -7.112509, 112.408468
        // -7.113404, 112.408404
        // -7.114814, 112.408048
        // -7.115569, 112.407736
        // -7.115277, 112.408322
        // -7.115306, 112.408690
        // -7.115357, 112.408861
        // -7.115016, 112.407145
        // -7.115016, 112.406914
        // -7.114984, 112.406689
        $maps = array();
        $mapsApi = [
            $maps = [
                'lat' => '-7.112254',
                'lng' => '112.408554',
                'image' => 'https://timurmedia.com/wp-content/uploads/2020/03/IMG_20200330_150055.jpg',
                'name' => 'Pedagang Cakwe dan Gorengan',
                'deskripsi' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Amet facilisis magna etiam tempor.',
                'rating' => 5,
            ],
            $maps = [
                'lat' => '-7.112509',
                'lng' => '112.408468',
                'image' => 'https://www.borneonews.co.id/images/upload/2019/10/16/aHFfCnQO4NyrHxDXhMpG_4kv2cjERvPWyfgEH99GnzQ.jpeg',
                'name' => 'Pedagang Cireng Bogor',
                'deskripsi' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. A erat nam at lectus urna duis convallis convallis tellus.',
                'rating' => 3.5,
            ],
            $maps = [
                'lat' => '-7.113404',
                'lng' => '112.408404',
                'image' => 'https://awsimages.detik.net.id/community/media/visual/2018/10/19/29cb8e57-986f-47fd-bcbb-419dedc9917b.jpeg?w=700&q=90',
                'name' => 'Kue Tradisional',
                'deskripsi' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. A erat nam at lectus urna duis convallis convallis tellus.',
                'rating' => 3,
            ],
        ];

        return response([
            'offices'=>$mapsApi
        ]);
    }
}
