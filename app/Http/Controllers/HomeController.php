<?php

namespace App\Http\Controllers;

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
                ->select('sellers.nama_seller as name','sellers.latitude as lat','sellers.longitude as lng','sellers.deskripsi','users.foto as image')
                ->leftJoin('users','users.id','sellers.id_user')->get();
        return response([
            'offices'=>$maps
        ]);
    }
}
