<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManpowerRegistry;
use Illuminate\Support\Facades\Validator;

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

        $firstname  = $request->input('firstname');
        $middlename = $request->input('middlename');
        $lastname   = $request->input('lastname');
        $suffix     = $request->input('suffix');
        $gender     = $request->input('gender');
        $mobile_no  = $request->input('mobile_no');
        $birthdate  = $request->input('birthdate');
        $email      = $request->input('email');
        
        $region             = $request->input('region');
        $province           = $request->input('province');
        $city_municipality  = $request->input('city_municipality');

        $type                   = $request->input('type');
        $structure_category    = $request->input('structure_category');


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
            'structure_category' => $structure_category,
            'region' => $region,
            'province' => $province,
            'city_municipality' => $city_municipality
        ];

        foreach($skill_options as $val => $text){

            $data[$val] = $$val;
        }

        $validate = $this->_validate_create_entry($data);
        

        if($validate['status'] <= 0 ){
            return response()->json($validate);    
        }

        return response()->json([
            'status'    => 1,
            'message'   => '',
            'data'      => $data
        ]);
    }


    private function _validate_create_entry($data){


        $rules = [
            'firstname'             => ['required','max:255'],
            'lastname'              => ['required','max:255'],
            'gender'                => ['required'],
            'email'                 => ['nullable','email'],
            'mobile_no'             => ['required'],
            'type'                  => ['required'],
            'structure_category'    => ['required'],
            'birthdate'             => ['required'],
            'region'                => ['required'],
            'province'              => ['required'],
            'city_municipality'     => ['required']
        ];

        //todo validate area

        //validate uniqueness

        //validate skillset

        $validator = Validator::make([[$data]],$rules);

        if ($validator->fails()) {
            
            return [
                'status'    => -2,
                'message'   => 'Failed Validation',
                'data'      => $validator->messages(),
                'input'     => $data
            ];
        }


        return [
            'status'    => 1,
            'message'   => '',
            'data'      => []
        ];

    }

}
