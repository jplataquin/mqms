import './bootstrap';
import { Modal } from 'bootstrap';
import {Template,$util} from '/node_modules/adarna/dist/adarna.js';

window.util = {};
window.ui   = {};

let primaryModalElement = document.getElementById('primary_modal');

window.ui.primaryModal = new Modal(primaryModalElement);

window.ui.primaryModalTitle    = primaryModalElement.querySelector('#primary_modal_title');
window.ui.primaryModalBody     = primaryModalElement.querySelector('#primary_modal_body');
window.ui.primaryModalFooter   = primaryModalElement.querySelector('#primary_modal_footer');

/** Feeze UI **/
/*

#The MIT License (MIT)

Copyright (c) 2017 Alex Radulescu

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

*/
(() => {

    /**
     * Setup the freeze element to be appended
     */
    let freezeHtml = document.createElement('div');
        freezeHtml.classList.add('freeze-ui');

    /** 
    * Freezes the UI
    * options = { 
    *   selector: '.class-name' -> Choose an element where to limit the freeze or leave empty to freeze the whole body. Make sure the element has position relative or absolute,
    *   text: 'Magic is happening' -> Choose any text to show or use the default "Loading". Be careful for long text as it will break the design.
    * }
    */
    window.util.blockUI = (options = {}) => {
       
        let parent = document.querySelector(options.selector) || document.body;
        
        freezeHtml.setAttribute('data-text', options.text || 'Loading');
       
        if (document.querySelector(options.selector)) {
            freezeHtml.style.position = 'absolute';
        }
       
        parent.appendChild(freezeHtml);
    };
    
    /**
     * Unfreezes the UI.
     * No options here.
     */
    window.util.unblockUI = () => {
        let element = document.querySelector('.freeze-ui');
        if (element) {
 
           element.classList.add('is-unfreezing');
            setTimeout(() => {
                if (element) {
                    element.classList.remove('is-unfreezing');
                    
                    if(element.parentElement){
                        element.parentElement.removeChild(element);
                    }
                }
            }, 250);
        }
    };

})();



window.util.showMsg = ($message) => {
    alert($message);
}

window.util.$get = async (url,data,headers) => {

    headers                 = headers ?? {};
    headers['X-CSRF-Token'] =  document.querySelector('meta[name="csrf-token"]').content;
    headers['Accept']       = 'application/json';

    let status = '';
    
    return fetch(url+'?'+ new URLSearchParams(data),
    {
        headers: headers,
        method: "GET"
    }).then((response) => {
        
        status = response.status;

        if(response.status == 401){
            return {
                    status:-1,
                    message:'Please sign in',
                    data:{}
            }
        };

        if(response.status == 500){

            console.error(response);
            return {
                    status:0,
                    message:'Something went wrong',
                    data:{}
            }
        };

        return response.json();
    }).catch(e=>{

        return {
            status:0,
            message:e.message,
            data:{
                httpStatus: status
            }
        }
    });
}

window.util.$post = async (url,data,headers) => {

    headers                 = headers ?? {};
    headers['X-CSRF-Token'] =  document.querySelector('meta[name="csrf-token"]').content
    headers['Accept']       = 'application/json';
    
    let formData = new FormData();

    for(let key in data){
        formData.append(key,data[key]);
    }

    return fetch(url,
    {
        headers: headers,
        body: formData  ?? {},
        method: "POST"
    }).then((response) => {
        

        switch (response.status) {
            case 401:
                return {
                        status:-1,
                        message:'Please sign in',
                        data:{}
                }
            break;
            case 500:
                return {
                        status:0,
                        message:'Something went wrong',
                        data:{}
                }
            break;
            case 404:
                return {
                        status:0,
                        message:'Resource not found',
                        data:{}
                }
            break;

        }
      

        return response.json();

    }).catch(e=>{

        return {
            status:0,
            message:e,
            data:{}
        }
    });
}


window.util.createComponent = function(model,callback){
    //Attributes
    //Payload
    //Model
    
    return function(attr,inner){

        if(typeof attr['model'] == 'undefined' || typeof attr['model'] != 'object'){
            
            attr['model'] = model;

        }else{


            let a = attr.model;
            
            let b = $util.object.structure(model,{});

            for(let key in a){
                b[key] = a[key];
            }

            attr.model = b;
        }

        let m = attr.model;

        delete attr.model;
        const t = new Template();


        let component = callback(m,t,inner);

        for(let key in attr){
            let val = component.getAttribute(key);

            component.setAttribute(key,val+' '+attr[key]);    
        }


        return component;
    }
}


window.util.inputNumber = function(txt,evt,decimalPlaces,negativeFlag){

    let charCode = (evt.which) ? evt.which : evt.keyCode;
    
    
    console.log(negativeFlag, charCode, txt.value.indexOf('-'),txt.value);

    //do not allow negative sign at the start
    if(negativeFlag && charCode == 45){

        console.log('a');

        if (txt.value.indexOf('-') === -1 && txt.value == '') {        
            return true;
        } else {
            return false;
        }
    }

    //point
    if (charCode == 46) {
        
        console.log('b');

        //Check if the text already contains the . character
        if (txt.value.indexOf('.') === -1 && decimalPlaces != 0) {
            return true;
        } else {
            return false;
        }

    } else {

        
        
        console.log('c');
        
        if (charCode > 31 && (charCode < 48 || charCode > 57)){
            
            console.log('d');
            return false;    
        }
        
    }

    //if one is true then it's good
    if(decimalPlaces){

        let r = "^\\d+\\.\\d{0,"+(decimalPlaces-1)+"}$";
      
        let a = (new RegExp(r,'gi')).test(txt.value);
        let b = /^\d+$/.test(txt.value);
        let c = /^\d+\.$/.test(txt.value);
        
        if(!a && !b && !c && txt.value != ''){

            console.log('e');
            return false;
        }
    }
    

    return true;
    
}