<?php

namespace App\Http\Controllers;

use App\Models\FavouriteMovie;
use App\Models\FavouriteShow;
use Illuminate\Http\Request;
use Tmdb\Laravel\Facades\Tmdb;

class FavouriteShowsController extends Controller
{

    public function __construct() {
        $this->middleware('auth:api');
    }

    public function all() {
        $me = auth()->user();

        $all = FavouriteShow::query()
            ->where('user_id', $me->id)
            ->get();

        $shows = $all->map(function($favourite) {
            $show = Tmdb::getTvApi()->getTvshow($favourite->show_id);

            return $this->processMovie($show);
        })->filter();

        return response()->json([
            'error' => false,
            'tv_shows' => $shows
        ]);
    }

    public function add(Request $request) {
        $id = $request->get('id');
        if(!$id) {
            return $this->unexpectedError();
        }

        $me = auth()->user();

        $alreadyFavourite = FavouriteShow::query()
            ->where('user_id', $me->id)
            ->where('show_id', $id)
            ->exists();

        if($alreadyFavourite) {
            return response()->json([
                'error' => false
            ]);
        }

        // add
        $favourite = new FavouriteShow();
        $favourite->user_id = $me->id;
        $favourite->show_id = $id;
        $favourite->save();

        return response()->json([
            'error' => false
        ]);
    }

}
