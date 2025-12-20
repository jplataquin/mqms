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
            'city_municipality_options'     => ManpowerRegistry::city_municipality_options(),
            'skill_options'                 => ManpowerRegistry::skill_options()
        ]);
    }

    public function _create(Request $request){

        $firstname  = $requerst->input('firstname');
        $middlename = $request->input('middlename');
        $lastname   = $request->input('lastname');
        $suffix     = $request->input('suffix');
        $gender     = $requerst->input('gender');
        $mobile_no  = $request->input('mobile_no');
        $birthdate  = $request->input('birthdate');
        $email      = $request->input('email');
        
        $region             = $request->input('region');
        $province           = $request->input('province');
        $city_municipality  = $request->input('city_municipality');

        $type                   = $request->input('type');
        $structural_category    = $request->input('structural_category');


        $skill_options = ManpowerRegistry::skill_options();

        foreach($skill_options as $val => $text){

            $$val = $request->input($val);
        }


        $data = [
            'firstname' => $firstname,
            'middlename' => $middlename,
            'lastname' => $lastname,
            'suffix' => $suffix,
            'mobile_no' => $mobile_no,
            'gender' => $gender,
            'email' => $email,
            'birthdate' => $birthdate,
            'type' => $type,
            'structural_category' => $structural_category,
            'region' => $region,
            'province' => $province,
            'city_municipality' => $city_municipality
        ];

        foreach($skill_options as $val => $text){

            $data[$val] = $$val;
        }

        
        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => $data
        ]);
    }

}
