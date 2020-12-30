<?php

namespace App\Http\Controllers;

use App\Favorite;
use App\Seller;
use App\User;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Image;
use Illuminate\Support\Facades\Storage;
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
            'data'=>$user,
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
            DB::table('users')
            ->insert([
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
            DB::table('users')
            ->where('no_hp', $request->no_hp)
            ->update([
                    'status' => TRUE,
                    'otp_status' => TRUE,
                    'updated_at' => date('Y-m-d H:m:s')
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
            $file = $request->file('foto');
            if($request->file('foto')){
                $namaFile=date('YmdHis').$file->getClientOriginalName();
                $normal = Image::make($file)->encode($file->extension());
                Storage::disk('s3')->put('/images/'.$namaFile, (string)$normal, 'public');
                $foto='https://lizartku.s3.us-east-2.amazonaws.com/images/'.$namaFile;
            }else{
                $foto='https://sman93jkt.sch.id/wp-content/uploads/2018/01/765-default-avatar.png';
            }

            $users = new User();
            $users = User::find($user_id->id);
            $users->name = $request->name;
            $users->email = $request->email;
            $users->tgl_lahir = $request->tgl_lahir;
            $users->alamat = $request->alamat;
            $users->provinsi = $request->provinsi;
            $users->kota = $request->kota;
            $users->foto = $foto;
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
            'data' => $users,
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
        $data = DB::table('users')->select('sellers.status', 'users.name', 'users.foto')
        ->leftJoin('sellers','sellers.id_user','users.id')
        ->where('users.id', $id)->first();
        $seller=Seller::where('id_user',$id)->first();
        $data->foto = "https://randomuser.me/api/portraits/men/1.jpg";
        if(!$seller){
            $data->status='Pembeli';
        }else{
            $data->status='Penjual';
        }
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

    public function myfavorite()
    {
        $favorites=DB::table('favorites')->select('sellers.nama_seller')
        ->leftJoin('sellers','sellers.id','favorites.id_seller')
        ->where('favorites.id_user',Auth::id())->get();
        return response()->json($favorites);
    }
    public function addfavorite(Request $request)
    {
        DB::table('favorites')
        ->updateOrInsert(
            ['id_user' => Auth::id(),'id_seller'=>$request->id_seller],
            [
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
                ]
            );
            return response()->json([
                'message'=>'sukses',
                'status'=>200,
            ]);
    }

    public function mapsApi()
    {
        $maps = array();
        $mapsApi = [
            $maps = [
                'lat' => -7.112254,
                'lng' => 112.408554,
                'image' => 'https://timurmedia.com/wp-content/uploads/2020/03/IMG_20200330_150055.jpg',
                'name' => 'Pedagang Cakwe dan Gorengan',
                'deskripsi' => 'Banyak penjual cakwe yang dijumpai dimasyarakat mulai di pinggir jalan hingga di tempat-tempat nongkrong. Penggemar cakwe goreng memang sangat banyak.',
                'rating' => 5,
            ],
            $maps = [
                'lat' => -7.112509,
                'lng' => 112.408468,
                'image' => 'https://www.borneonews.co.id/images/upload/2019/10/16/aHFfCnQO4NyrHxDXhMpG_4kv2cjERvPWyfgEH99GnzQ.jpeg',
                'name' => 'Pedagang Cireng Bogor',
                'deskripsi' => 'Deskripsi Cireng Bumbu Rujak, RTC by Delicio PERHATIAN!!! Freezy Fresh kini hadir di 3 lokasi lain (Pilih Toko Terdekat untuk Ongkir Lebih Murah) Jakarta Selatan : www.tokopedia.com/freezyfresh Jakarta Barat : www.tokopedia.com/freezyjakbar Depok : www.tokopedia.com/freezydepok',
                'rating' => 3.5,
            ],
            $maps = [
                'lat' => -7.113404,
                'lng' => 112.408404,
                'image' => 'https://awsimages.detik.net.id/community/media/visual/2018/10/19/29cb8e57-986f-47fd-bcbb-419dedc9917b.jpeg?w=700&q=90',
                'name' => 'Kue Tradisional',
                'deskripsi' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. A erat nam at lectus urna duis convallis convallis tellus.',
                'rating' => 3,
            ],
            $maps = [
                'lat' => -7.114814,
                'lng' => 112.408048,
                'image' => 'https://cdn0-production-images-kly.akamaized.net/U-s4KXRyPLrHKheF7zovtIQ4faE=/1231x710/smart/filters:quality(75):strip_icc():format(jpeg)/kly-media-production/medias/2793527/original/031112900_1556702766-20190501-Buruh-Aksi-Perayaan-Mayday_-Pedagang-Kaki-Lima-Panen-Rejeki-TEBE-3.jpg',
                'name' => 'Es Cendol Dawet',
                'deskripsi' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. A erat nam at lectus urna duis convallis convallis tellus.',
                'rating' => 3,
            ],
            $maps = [
                'lat' => -7.115569,
                'lng' => 112.407736,
                'image' => 'https://cdn2.tstatic.net/style/foto/bank/images/martabak-manis_20180219_205121.jpg',
                'name' => 'Terang Bulan 69 Bangke',
                'deskripsi' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. A erat nam at lectus urna duis convallis convallis tellus.',
                'rating' => 4.5,
            ],
            $maps = [
                'lat' => -7.115569,
                'lng' => 112.408322,
                'image' => 'https://manaberita.com/v1/uploads/2019/12/images-49.jpg',
                'name' => 'Pentol HellFire',
                'deskripsi' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. A erat nam at lectus urna duis convallis convallis tellus.',
                'rating' => 5,
            ],
            $maps = [
                'lat' => -7.115306,
                'lng' => 112.408690,
                'image' => 'https://lh3.googleusercontent.com/proxy/8g9P2Ktzq5hQgS_SOeuc0s0qluZfyJ2pM94XM13mWCaMiWiWd0Sckoy601EBpx0TiWoy54A9t5zjWbsF9jGAreBx4-H7r-nqqfvIpX_p0SBEWBr3FN867Sgh_X4Af-91kJb3LOPiNesGXDI7yqNjqfiaSCH9h2pZbTbW-3JAqgTo',
                'name' => 'Mas Bowo On The Way',
                'deskripsi' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. A erat nam at lectus urna duis convallis convallis tellus.',
                'rating' => 3,
            ],
            $maps = [
                'lat' => -7.115357,
                'lng' => 112.408861,
                'image' => 'https://cdn.idntimes.com/content-images/community/2019/12/57325334-527542737651046-5177539548100002288-n-3ac503368fe23cde3b71febf4eec8c72_600x400.jpg',
                'name' => 'Mie Ayam Jakarta',
                'deskripsi' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. A erat nam at lectus urna duis convallis convallis tellus.',
                'rating' => 3,
            ],
        ];

        return response([
            'offices'=>$mapsApi
        ]);
    }
}
