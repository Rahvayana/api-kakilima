<?php

namespace App\Http\Controllers;

use App\Post;
use App\Rating;
use App\Seller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Image;


class HomeController extends Controller
{
    public function index()
    {
        $data=DB::table('posts')->select('posts.judul','posts.deskripsi','posts.foto','sellers.latitude','sellers.longitude')
        ->leftJoin('sellers','sellers.id','posts.id_seller')
        ->orderBy('posts.created_at', 'desc')
        ->get();
        
        return response($data);
    }

    public function addPost(Request $request)
    {
       try{
            $id=Auth::id();
            $idSeller=Seller::where('id_user',$id)->first();

            $file = $request->file('image');
            if($request->file('image')){
                $namaFile=date('YmdHis').$file->getClientOriginalName();
                $normal = Image::make($file)->resize(512,512)->encode($file->extension());
                Storage::disk('s3')->put('/images/'.$namaFile, (string)$normal, 'public');


                $post=new Post();
                $post->judul=$request->judul;
                $post->deskripsi=$request->deskripsi;
                $post->id_seller=$idSeller->id;
                $post->foto='https://lizartku.s3.us-east-2.amazonaws.com/images/'.$namaFile;
                $post->save();
                return response()->json([
                    'message'=>'Sukses Tambah Post',
                    'status'=>200
                ]);
            }else{
                return response()->json([
                    'message'=>'Image Not Found',
                    'status'=>404
                ]);
            }
       }catch(Exception $e){
        return response()->json([
            'message'=>$e->getMessage(),
            'status'=>500
        ]);
       }
    }

    public function maps()
    {
        $id=Auth::id();
        $maps=DB::table('sellers')
                ->select('sellers.id','sellers.nama_seller as name','sellers.latitude as lat','sellers.longitude as lng','sellers.deskripsi','users.foto as image')
                ->leftJoin('users','users.id','sellers.id_user')->where('sellers.status',1)->get();
        $datamap=array();
        foreach($maps as $map){
            $rating=Rating::where('id_seller',$map->id)->avg('rating');
            $my_rating=Rating::select('rating')->where('id_user',$id)->where('id_seller',$map->id)->pluck('rating')->first();
            $my_review=Rating::select('review')->where('id_user',$id)->where('id_seller',$map->id)->pluck('review')->first();
            $datamap[]=[
                'id'=>$map->id,
                'name'=>$map->name,
                'lat'=>$map->lat,
                'lng'=>$map->lng,
                'deskripsi'=>$map->deskripsi,
                'image'=>$map->image,
                'rating'=>ceil($rating),
                'my_rating'=>$my_rating,
                'my_review'=>$my_review,
            ];
        }
        return response([
            'offices'=>$datamap
        ]);
    }

    public function rating(Request $request)
    {
        $id=Auth::id();
        $rating=DB::table('ratings')
        ->updateOrInsert(
            ['id_user' => $id,'id_seller' => $request->id_seller],
            [
                'rating' => $request->rating,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ]
        );

        return response()->json([
            'data'=>$rating,
            'status'=>200,
            'message'=>'Success'
        ]);
    }

    public function review(Request $request)
    {
        $id=Auth::id();
        $rating=DB::table('ratings')
        ->updateOrInsert(
            ['id_user' => $id,'id_seller' => $request->id_seller],
            [
                'review' => $request->review,
                'created_at' => date('Y-m-d H:m:s'),
                'updated_at' => date('Y-m-d H:m:s'),
            ]
        );

        return response()->json([
            'data'=>$rating,
            'status'=>200,
            'message'=>'Success'
        ]);
    }
}
