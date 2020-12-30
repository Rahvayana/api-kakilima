<?php

namespace App\Http\Controllers;

use App\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellerController extends Controller
{
    public function index(Request $request)
    {
        $id=$request->user()->id;
        $data['seller']=DB::table('sellers')->select('sellers.*','users.name')
        ->leftJoin('users','users.id','sellers.id_user')
        ->where('sellers.id_user',$id)
        ->first();
        $seller=Seller::where('id_user',$id)->first();
        // dd($seller->id);
        $data['favorite']=DB::table('favorites')->where('id_seller',$seller->id)->get()->count();
        $data['post']=DB::table('posts')->where('id_seller',$seller->id)->get()->count();
        // dd($data);

        return response([
            'data'=>$data,
            'message'=>'sukses',
            'status'=>200
        ]);
    }

    public function status(Request $request)
    {
        // dd($request);
        $id=$request->user()->id;
        DB::table('sellers')->where('id_user', $id)->update(['status' => $request->status]);
        return response([
            'message'=>'sukses',
            'status'=>200
        ]);
    }
    
    public function addSeller(Request $request)
    {
        $id=$request->user()->id;
        DB::table('sellers')
        ->updateOrInsert(
            ['id_user' => $id],
            [
                'kategori' => $request->kategori,
                'nama_seller' => $request->nama_seller,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'deskripsi' => $request->deskripsi,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ]
        );
        return response([
            'message'=>'sukses',
            'status'=>200
        ]);
    }
}
