<?php

namespace App\Http\Controllers;

use GoogleMaps\GoogleMaps;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
        if (Cache::has($request->input('place'))) { //get cache if request complete
            $cache = Cache::get($request->input('place'));
            if (json_decode($cache)->chkLastReq)
                return $cache;
        }

        $response = $this->GoogleMaps->load('textsearch') //call api google map for search restaurants
            ->setParam([
                'query' => 'restaurants in ' . $request->input('place'),
                'type' => 'restaurant',
                'pagetoken' => $request->input('next_page_token') ? $request->input('next_page_token') : ""
            ])->get();

        $res = json_decode($response);
        $modelRes = (object)[
            'status' => $res->status,
            'next_page_token' => isset($res->next_page_token) ? $res->next_page_token : "",
            'datas' => [],
            'chkLastReq' => false
        ];
        $newRes = clone $modelRes;

        if ($res->status === "OK") { //set datas response
            foreach ($res->results as $x) {
                array_push($newRes->datas, (object)[
                    'formatted_address' => $x->formatted_address,
                    'name' => $x->name,
                    'location' => (object)[
                        'lat' => $x->geometry->location->lat,
                        'lng' => $x->geometry->location->lng
                    ]
                ]);
            }
        }

        if (isset($res->next_page_token) || count($newRes->datas) > 1) { //set cache for stack datas if response has param next_page_token
            $cache = Cache::has($request->input('place')) ? json_decode(Cache::get($request->input('place'))) : $modelRes;
            $cache->datas = array_merge($cache->datas, $newRes->datas);
            if (!isset($res->next_page_token)) {
                $cache->chkLastReq = true;
                $cache->next_page_token = "";
            }
            Cache::set($request->input('place'), json_encode($cache));
        }
        return response()->json($newRes);
    }
}
