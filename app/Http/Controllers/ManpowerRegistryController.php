<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManpowerRegistry;

class ManpowerRegistryController extends Controller
{


    public function create(Request $request){


        return view('manpower_registry/create',[
            'region_options'                => ManpowerRegistry::region_options(),
            'province_options'              => ManpowerRegistry::province_options(),
            'city_municipality_options'     => ManpowerRegistry::city_municipality_options()
        ]);
    }

}
