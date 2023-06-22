<?php

namespace App\Http\Controllers;

use App\Models\WatchedMovie;
use Illuminate\Http\Request;
use Tmdb\Laravel\Facades\Tmdb;

class WatchedMoviesController extends Controller
{

    public function __construct() {
        $this->middleware('auth:api');
    }

    public function all(Request $request) {
        $me = auth()->user();

        $all = WatchedMovie::query()
            ->where('user_id', $me->id)
            ->get();

        $movies = $all->map(function($watched) {
            $movie = Tmdb::getMoviesApi()->getMovie($watched->movie_id);

            return [
                'id' => $watched->id,
                'timestamp' => $watched->created_at->getTimestamp(),
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

        $alreadyFavourite = WatchedMovie::query()
            ->where('user_id', $me->id)
            ->where('movie_id', $id)
            ->exists();

        if($alreadyFavourite) {
            return response()->json([
                'error' => false
            ]);
        }

        // add
        $favourite = new WatchedMovie();
        $favourite->user_id = $me->id;
        $favourite->movie_id = $id;
        $favourite->save();

        return response()->json([
            'error' => false
        ]);
    }

}
