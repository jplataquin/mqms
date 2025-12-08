@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/manpower_registry">
                    <span>
                        Manpower Registry
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                        Create
                    </span>	
                    <i class="ms-2 bi bi-file-earmark-plus"></i>	
                </a>
            </li>
        </ul>
    </div>
    <hr>

    <div class="form-container">
        <div class="form-header">
            Register Manpower
        </div>
        <div class="form-body">

            <div class="row mb-3">
                <div class="col-lg-4">
                    <div class="form-group">
                        <label>Firstname *</label>
                        <input type="text" id="firstname" class="form-control"/>
                    </div>
                </div>

                 <div class="col-lg-3">
                    <div class="form-group">
                        <label>Middlename</label>
                        <input type="text" id="middlename" class="form-control"/>
                    </div>
                </div>

                 <div class="col-lg-4">
                    <div class="form-group">
                        <label>Lastname *</label>
                        <input type="text" id="lastname" class="form-control"/>
                    </div>
                </div>

                 <div class="col-lg-1">
                    <div class="form-group">
                        <label>Suffix</label>
                        <input type="text" id="suffix" class="form-control"/>
                    </div>
                </div>
                
            </div>

            <div class="row mb-3">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Birthdate * </label>
                        <input type="date" class="form-control" id="birthdate"/> 
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Gender * </label>
                        <select class="form-select" id="gender">
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                        </select> 
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Mobile No. *</label>
                        <input type="text" class="form-control" id="mobile_no"/> 
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="text" class="form-control" id="email"/> 
                    </div>
                </div>
            </div>


            <div class="row mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Region</label>
                        <select class="form-select" id="region">
                            @foreach($region_options as $val => $text)
                                <option value="{{$val}}">{{$text}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>            
            </div>


            <div class="row mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>Province</label>
                        <select class="form-select" id="province">
                            <option value=""> - </option>
                            @foreach($province_options as $group=>$options)
                                @foreach($options as $val => $text)
                                <option value="{{$val}}" data-group="{{$group}}" class="option d-none">{{$text}}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                </div>            
            </div>
            <div class="row mb-3">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label>City/Municipality</label>
                        <select class="form-select" id="city_municipality">
                            <option value=""> - </option>
                            @foreach($city_municipality_options as $group=>$options)
                                @foreach($options as $val => $text)
                                <option value="{{$val}}" data-group="{{$group}}" class="option d-none">{{$text}}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                </div>            
            </div>

            <div class="row mb-3">
                <div class="col-lg-6 mb-3">
                    <div class="form-group">
                        <label>Type * </label>
                        <select class="form-select" id="type">
                            <option value="LABORER">Laborer</option>
                            <option value="SKILLED">Skilled Worker</option>
                        </select> 
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="form-group">
                        <label>Structure Category * </label>
                        <select class="form-select" id="structure_category">
                            <option value="VERT">Vertical</option>
                            <option value="HORI">Horizontal</option>
                            <option value="BOTH">Both Vertical & Horizontal</option>
                        </select> 
                    </div>
                </div>
            </div>

            <div class="">
            @foreach($skill_options as $val=>$text)

                
                <div class="row d-flex justify-content-between mb-1 border border-primary">
                    
                    <label>
                        {{$text}}
                    </label>

                    <input class="skill" type="checkbox" value="{{$val}}">
                    
                </div>

            @endforeach
            </div>
          

            <div class="row mt-5">
                <div class="col-12 text-end">
                <button class="btn btn-secondary" id="cancelBtn">Cancel</button>
                <button class="btn btn-primary" id="createBtn">Create</button>
                </div>
            </div>
        </div>
    </div>

</div>

<script type="module">
    import {$q} from '/adarna.js';

    const region_select             = $q('#region').first();
    const province_select           = $q('#province').first();
    const city_municipality_select  = $q('#city_municipality').first();

    console.log(province_select);
    region_select.onchange = (e)=>{

        let selected = region_select.value;

        Array.from(province_select.querySelectorAll('.option')).map((el)=>{
            
            let group = el.getAttribute('data-group');

            if(group != selected ){
                el.classList.add('d-none');
            }else{
                el.classList.remove('d-none');
            }
        });

        province_select.value = '';
        province_select.dispatchEvent((new Event('change', { bubbles: true })));
    }

    region_select.onchange();


    province_select.onchange = (e)=>{

        let selected = province_select.value;

        Array.from(city_municipality_select.querySelectorAll('.option')).map((el)=>{
            
            let group = el.getAttribute('data-group');

            if(group != selected ){
                el.classList.add('d-none');
            }else{
                el.classList.remove('d-none');
            }
        });

        
        city_municipality_select.value = '';
        city_municipality_select.dispatchEvent((new Event('change', { bubbles: true })));
    }

</script>
</div>
@endsection