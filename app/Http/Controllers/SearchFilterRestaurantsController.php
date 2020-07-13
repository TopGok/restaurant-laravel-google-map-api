<?php

namespace App\Http\Controllers;

use GoogleMaps\GoogleMaps;
use Illuminate\Http\Request;

class SearchFilterRestaurantsController extends Controller
{
    protected $GoogleMaps;

    public function __construct(GoogleMaps $_GoogleMaps)
    {
        $this->GoogleMaps = $_GoogleMaps;
    }

    function index()
    {
        return view('SearchFilterRestaurants');
    }

    function getRestaurants(Request $request)
    {
        $response = $this->GoogleMaps->load('textsearch')
            ->setParam([
                'query' => 'restaurants in ' . $request->input('place'),
                'type' => 'restaurant',
                'pagetoken' => $request->input('next_page_token') ? $request->input('next_page_token') : ""
            ])->get();
        // if ($request->has('next_page_token'))
        //     $response->setParamByKey('pagetoken', $request->input('next_page_token'));
        // $response->get();
        // ->get('results.name');
        return response($response);
    }
}
