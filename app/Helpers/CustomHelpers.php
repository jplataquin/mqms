<?php


if(!function_exists('checkAccessCode')){

    function checkAccessCode($code){

        $accessCodes = session('access_codes');

        return in_array($code,$accessCodes);
    }
}

if(!function_exists('generateComponentHash')){

    function generateComponentHash($project,$section,$component,$componentItems,$materialItems){

            //v1 hash algo start
            $code = $section->id.''.$project->id.''.$section->id.''.$component->id;

            foreach($componentItems as $item){
                
                $code .= $item->name.''.$item->quantity.''.$item->unit;
    
                foreach($item->materialQuantities as $mq){
    
                    $code .= $materialItems[$mq->material_item_id]->name;
                    $code .= $materialItems[$mq->material_item_id]->specification_unit_packaging;
                    $code .= $materialItems[$mq->material_item_id]->brand; 
                    $code .= $mq->equivalent;
                    $code .= $item->unit;
                    $code .= $mq->quantity;
                }
            }
    
            $hash = hash('sha256',$code);
            $hash = strtoupper(substr($hash,-6));
            
            return $hash;
    }
}