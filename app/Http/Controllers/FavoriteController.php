<?php

namespace App\Http\Controllers;

use App\Favorite;
use App\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    public function totalFavorite()
    {
        $id_seller=Seller::find(Auth::id());
        $data['favorite']=DB::table('favorites')->where('id_seller',$id_seller)->get()->count();
        dd($data);
    }
}
