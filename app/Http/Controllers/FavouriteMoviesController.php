<?php

namespace App\Http\Controllers;

use App\Models\FavouriteMovie;
use Illuminate\Http\Request;

class FavouriteMoviesController extends Controller
{

    public function __construct() {
        $this->middleware('auth:api');
    }

    public function all() {}

    public function add(Request $request) {
        $id = $request->get('id');
        if(!$id) {
            return $this->unexpectedError();
        }

        $me = auth()->user();

        $alreadyFavourite = FavouriteMovie::query()
            ->where('user_id', $me->id)
            ->where('movie_id', $id)
            ->exists();

        if($alreadyFavourite) {
            return response()->json([
                'error' => false
            ]);
        }

        // add
        $favourite = new FavouriteMovie();
        $favourite->user_id = $me->id;
        $favourite->movie_id = $id;
        $favourite->save();

        return response()->json([
            'error' => false
        ]);
    }

}
