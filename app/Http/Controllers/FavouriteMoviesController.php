<?php

namespace App\Http\Controllers;

use App\Models\FavouriteMovie;
use Illuminate\Http\Request;
use Tmdb\Laravel\Facades\Tmdb;

class FavouriteMoviesController extends Controller
{

    public function __construct() {
        $this->middleware('auth:api');
    }

    public function all() {
        $me = auth()->user();

        $all = FavouriteMovie::query()
            ->where('user_id', $me->id)
            ->get();

        $movies = $all->map(function($favourite) {
            $movie = Tmdb::getMoviesApi()->getMovie($favourite->movie_id);

            return [
                'id' => $favourite->id,
                'timestamp' => $favourite->created_at->getTimestamp(),
                'movie_data' => $this->processMovie($movie)
            ];
        })->filter();

        return response()->json([
            'error' => false,
            'movies' => $movies
        ]);
    }

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
