<?php

namespace App\Http\Controllers;

use App\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        
    }

    public function maps()
    {
        $maps=DB::table('sellers')
                ->select('sellers.id','sellers.nama_seller as name','sellers.latitude as lat','sellers.longitude as lng','sellers.deskripsi','users.foto as image')
                ->leftJoin('users','users.id','sellers.id_user')->get();
        $datamap=array();
        foreach($maps as $map){
            $rating=Rating::where('id_seller',$map->id)->avg('rating');
            $datamap[]=[
                'name'=>$map->name,
                'lat'=>$map->lat,
                'lng'=>$map->lng,
                'deskripsi'=>$map->deskripsi,
                'image'=>$map->image,
                'rating'=>$rating,
            ];
        }
        return response([
            'offices'=>$datamap
        ]);
    }
}
