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
        <div class="form-body pt-3">

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
                        <label>Structure Speciality Category * </label>
                        <select class="form-select" id="structure_category">
                            <option value="BOTH">Both Vertical & Horizontal</option>
                            <option value="VERT">Vertical</option>
                            <option value="HORI">Horizontal</option>
                        </select> 
                    </div>
                </div>
            </div>

            <div id="skill_select" class="mb-3">
                <h3>Skills</h3>
                <div class="m-3">
                    @foreach($skill_options as $val=>$text)


                        <div class="row">
                            
                            <div class="col-lg-12">
                                <div class="d-flex justify-content-between mb-3 border border-secondary">
                                    <div class="w-50 h4 p-1">
                                        {{$text}}
                                    </div>

                                    <div class="w-50 pt-2 text-center">
                                        <input class="skill" style="transform: scale(1.5);" id="skill_{{$val}}" type="checkbox" value="{{$val}}">
                                    </div>
                                </div>
                            </div>

                        </div>

                    @endforeach
                </div>
            </div>
          

            <div class="row mt-5">
                <div class="col-lg-12 text-end shadow bg-white rounded footer-action-menu p-2">
                
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
    const skill_select              = $q('#skill_select').first();
    const type_select               = $q('#type').first();
    const structural_category       = $q('#structural_category').first();

    const createBtn                 = $q('#createBtn').first();
    const cancelBtn                 = $q('#cancelBtn').first();

    const firstname     = $q('#firstname').first();
    const middlename    = $q('#middlename').first();
    const lastname      = $q('#lastname').first();
    const suffix        = $q('#suffix').first();
    const birthdate     = $q('#birstdate').first();
    const gender        = $q('#gender').first();
    const mobile_no     = $q('#mobile_no').first();
    const email         = $q('#email').first();


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

    type_select.onchange = (e)=>{

        $q('.skill').items().map((el)=>{
            el.checked = false;
        });

        if(type_select.value == "SKILLED"){
            skill_select.classList.remove('d-none');
        }else{
            skill_select.classList.add('d-none');
        }
    }

    type_select.dispatchEvent((new Event('change', { bubbles: true })));

    createBtn.onclick = ()=>{
        window.util.blockUI();

        window.util.$post('/api/create',{
            firstname: firstname.value,
            middlename: middlename.value,
            lastname: lastname.value,
            suffix: suffix.value,
            birthdate: birthdate.value,
            gender: gender.value,
            mobile_no: mobile_no.value,
            email: email.value,

            region: region_select.value,
            province: province_select.value,
            city_municipality: city_municipality_select.value,

            type: type_select.value,
            structure_category: structure_category.value,

            @foreach($skill_options as $val=>$text)
                {{$val}}: skill_{{$val}}.selected ,
            @endforeach

        }).then(reply=>{

            window.,util.unblockUI();

        });
    }

</script>
</div>
@endsection