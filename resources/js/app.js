import './bootstrap';
import './htmx.min.js';
import { Modal } from 'bootstrap';
import {Template,$util} from '/node_modules/adarna/build/adarna.min.js';
import Datepicker from '/node_modules/vanillajs-datepicker/js/Datepicker';
import en_ca from '/node_modules/vanillajs-datepicker/js/i18n/locales/en-CA.js';

Object.assign(Datepicker.locales, en_ca);


window.util = {};
window.ui   = {};


window.util.Datepicker = Datepicker;

const t                   = new Template();
const primaryModalElement = document.getElementById('primary_modal');

if(primaryModalElement){
    window.ui.primaryModal = new Modal(primaryModalElement);

    window.ui.primaryModalTitle    = primaryModalElement.querySelector('#primary_modal_title');
    window.ui.primaryModalBody     = primaryModalElement.querySelector('#primary_modal_body');
    window.ui.primaryModalFooter   = primaryModalElement.querySelector('#primary_modal_footer');
}

window.ui.drawerModalBackground = document.querySelector('.drawer_modal_background');
window.ui.drawerModal           = document.querySelector('.drawer_modal');
window.ui.drawerModalBody       = document.querySelector('.drawer_modal_body');
window.ui.drawerModalTitle      = document.querySelector('.drawer_modal_title');

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


window.util.copyToClipboard = async function(textToCopy) {
    // Navigator clipboard api needs a secure context (https)
    return new Promise(async (resolve,reject)=>{

        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(textToCopy);
            resolve(true);

        } else {
            // Use the 'out of viewport hidden text area' trick
            const textArea = document.createElement("textarea");
            textArea.value = textToCopy;
                
            // Move textarea out of the viewport so it's not visible
            textArea.style.position = "none";
                
            document.body.prepend(textArea);
            textArea.select();

            try {
                document.execCommand('copy');
            } catch (error) {
                console.error(error);
                resolve(false);
            } finally {
                textArea.remove();
                resolve(true);
            }
        } 

    });
}


/** Drawer Modal */
if(window.ui.drawerModalBackground && window.ui.drawerModal && window.ui.drawerModalBody && window.ui.drawerModalTitle ){

    window.util.drawerModal = {
        isOpen: false,
        open:function(){
            window.ui.drawerModalBackground.classList.add('drawer_modal_open');
            window.ui.drawerModal.classList.add('drawer_modal_open');
            this.isOpen = true;
            return this;
        },
        close:function(){
            window.ui.drawerModalBackground.classList.remove('drawer_modal_open');
            window.ui.drawerModal.classList.remove('drawer_modal_open');
            this.isOpen = false;
            window.ui.drawerModalBody.innerHTML = '';
            return this;
        },
        content:function(title,elem){
            window.ui.drawerModalBody.innerHTML  = '';
            window.ui.drawerModalTitle.innerHTML = title;
            window.ui.drawerModalBody.appendChild(elem);
            return this;
        }
    };

    window.ui.drawerModalBackground.onclick = ()=>{
        window.util.drawerModal.close();
    }
}

window.util.prompt = (msg,callback) => {

    if(!callback){
        callback = function(a){ return a;};
    }

    let promise = new Promise((resolve,reject)=>{

        let t = new Template();

        window.ui.primaryModal.hide();

        window.ui.primaryModalTitle.innerHTML    = '';
        window.ui.primaryModalBody.innerHTML     = '';
        window.ui.primaryModalFooter.innerHTML   = '';

        window.ui.primaryModalTitle.innerText = 'Prompt';

        let input   = t.input({class:'form-control',type:'text'});
        let ok      = t.button({class:'btn btn-primary me-3'},'Submit');
        let cancel  = t.button({class:'btn btn-secondary'},'Cancel');

        ok.onclick = ()=>{
            resolve(input.value);
            callback(input.value);
            window.ui.primaryModal.hide();
        }

        cancel.onclick = ()=>{
            resolve('');
            callback('');
            window.ui.primaryModal.hide();
        }

        let footer = t.div({class:'text-end'},(el)=>{
            el.appendChild(ok);
            el.appendChild(cancel);
            
        });

        window.ui.primaryModalBody.appendChild(t.div({class:'form-group'},(el)=>{
            t.label(msg);
            el.append(input);
        }));
        
        window.ui.primaryModalFooter.appendChild(footer);

        window.ui.primaryModal.show();
    });

    return promise;
};


window.util.confirm = (msg,callback) => {

    if(!callback){
        callback = function(a){ return a;};
    }

    let promise = new Promise((resolve,reject)=>{

        const t = new Template();

        window.ui.primaryModal.hide();

        window.ui.primaryModalTitle.innerHTML    = '';
        window.ui.primaryModalBody.innerHTML     = '';
        window.ui.primaryModalFooter.innerHTML   = '';
    
        window.ui.primaryModalTitle.innerText = 'Confirm';
        window.ui.primaryModalBody.innerText = msg;
    
        let no  = t.button({class:'btn btn-danger me-3'},'No');
        let yes = t.button({class:'btn btn-primary'},'Yes');
    
        yes.onclick = (e)=>{
            callback(true,e);
            resolve(true);
            window.ui.primaryModal.hide();
        }
    
        no.onclick = (e)=>{
            callback(false,e);
            resolve(false);
            window.ui.primaryModal.hide();
        }
    
        let footer = t.div({class:'text-end'},(el)=>{
            el.appendChild(no);
            el.appendChild(yes);
        });
    
        window.ui.primaryModalFooter.appendChild(footer);
    
        window.ui.primaryModal.show();
    });
   
    return promise;
};


window.util.showMsg = (reply) => {
    
    let message = '';
    let title   = '';

    switch (reply.status){

        //Soft Error
        case 0:

            message = reply.message;
            title   = '🚩 Error';

            break;
        
        //Unauthenticated
        case -1:

            message = 'Please sign in';
            title   = '🔐 Authentication Required';
            
            break;

        //Input validation error
        case -2:

            title = '🚩 Validation Error';

            message = t.div();

            for(let name in reply.data){
                let msgs = reply.data[name];

                let error = t.div(()=>{
                    t.label({style:{fontWeight:'bold'}},name);
                    t.ul(()=>{
                        
                        msgs.map(msg =>{
                            t.li(msg);
                        });

                    });
                });

                message.appendChild(error);
            }

            break;

        //Unkown Error
        case -3:

            message = reply.message ?? 'Unknown';
            title   = '🚩 Unknown Error';

            break;

        case -403:

            message = reply.message ?? 'Unknown';
            title   = '🚩 Permission Error';
            break;
        
        //Resource not found
        case -404:

            message = 'Resource not found';
            title   = '🚩 Error';

            break;
        
        //Hard Error
        case -500:

            message = reply.message ?? 'Something went wrong';
            title   = '🚩 Server Error';
            break;

        case -302:
            message = reply.message ?? 'Something went wrong';
            title   = '🚩 Signed Out';
            break;

    }

    window.util.alert(title,message);
};


window.util.alert = ($title,$message,$footer) => {
    window.ui.primaryModal.hide();

    window.ui.primaryModalTitle.innerHTML    = '';
    window.ui.primaryModalBody.innerHTML     = '';
    window.ui.primaryModalFooter.innerHTML   = '';

    if($title instanceof Element || $title instanceof HTMLElement){
        window.ui.primaryModalTitle.appendChild($title);
    }else{
        window.ui.primaryModalTitle.innerText    = $title ?? 'Message';
    }
    
    if($message instanceof Element || $message instanceof HTMLElement){
        window.ui.primaryModalBody.appendChild($message);
    }else{
        window.ui.primaryModalBody.innerText     = $message ?? '';
    }
   
    if($footer instanceof Element || $footer instanceof HTMLElement){
        window.ui.primaryModalFooter.appendChild($footer);
    }else{
        window.ui.primaryModalFooter.innerText     = $footer ?? '';
    }

    window.ui.primaryModal.show();
}

window.util.$content = async (url,data,headers)=>{

    headers                 = headers ?? {};
    headers['X-CSRF-Token'] =  document.querySelector('meta[name="csrf-token"]').content;

    let status = '';
    
    return fetch(url+'?'+ new URLSearchParams(data),
    {
        headers: headers,
        method: "GET"
    }).then((response) => {
        
        status = response.status;

        //Access Denied
        if(status == 401){
            return {
                    status:-1,
                    message:'Please sign in',
                    data:{}
            }
        };


        if(status == 404){
            return {
                    status:-404,
                    message:'Resource not found',
                    data:{}
            }
        };

        //Logout
        if(status == 302){
            return {
                status: -302,
                message:'Signed out',
                data:{}
            }
        }

        //Server error
        if(status == 500){

            console.error(response);
            return {
                    status:-500,
                    message:'Something went wrong',
                    data:{}
            }
        };

        return response.text();

    }).then(text=>{

        let div = document.createElement('div');
        div.innerHTML = text;

        return {
            status:1,
            message:'',
            data: div
        }

    }).catch(e=>{

        return {
            status:-3,
            message:e.message,
            data:{
                httpStatus: status
            }
        }
    });

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

        //Access Denied
        if(status == 401){
            return {
                    status:-1,
                    message:'Please sign in',
                    data:{}
            }
        };

        if(status == 404){
            return {
                    status:-4,
                    message:'Resource not found',
                    data:{}
            }
        };

        //Server error
        if(status == 500){

            console.error(response);
            return {
                    status:-5,
                    message:'Something went wrong',
                    data:{}
            }
        };

        return response.json();
    }).catch(e=>{

        return {
            status:-3,
            message:e.message,
            data:{
                httpStatus: status
            }
        }
    });
};

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
                        status:-5,
                        message:'Something went wrong',
                        data:{}
                }
            break;
            case 404:
                return {
                        status:-4,
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



window.util.roundUp = function(num,decimalPlaces = 0){
   
    if (num < 0)
        return -round(-num, decimalPlaces);
    var p = Math.pow(10, decimalPlaces);
    var n = num * p;
    var f = n - Math.floor(n);
    var e = Number.EPSILON * n;

    // Determine whether this fraction is a midpoint value.
    return (f >= .5 - e) ? Math.ceil(n) / p : Math.floor(n) / p;
}

window.util.numberFormat = function(val,fractionDigits){

    if(!fractionDigits){
        fractionDigits = 2;
    }

    return (new Intl.NumberFormat('en-US', {
        minimumFractionDigits: fractionDigits,
        maximumFractionDigits: fractionDigits
    })).format(val);
   
}

window.util.pureNumber = function(val,fractionDigits = null){

    val = String(val).replace(/[^\d.-]/g,'');

    val = parseFloat(val);

    if( isNaN(val) ){
        val = 0.0;
    }
    
    if(fractionDigits != null){
        
        val = window.util.roundUp(val,fractionDigits);

    }

    return val;
}


window.util.numbersOnlyInput = function(arr,options){

    if(!Array.isArray(arr)){
        arr = [arr];
    }

    let negativeFlag    = options.negative ?? false;
    let decimalPlaces   = options.precision ?? 0;

    arr.map(el => {

        el.addEventListener('keypress',(evt)=>{
            
            let charCode = (evt.which) ? evt.which : evt.keyCode;
            

             //do not allow negative sign at the start
            if(negativeFlag && charCode == 45){


                if (el.value.indexOf('-') === -1 && el.value == '') {        
                    return true;
                } else {
                    evt.preventDefault();
                    return false;
                }
            }


            //point
            if (charCode == 46) {
                

                //Check if the text already contains the . character
                if (el.value.indexOf('.') === -1 && decimalPlaces != 0) {
                    return true;
                } else {
                    evt.preventDefault();
                    return false;
                }

            }else if (charCode > 31 && (charCode < 48 || charCode > 57)){
                
                evt.preventDefault();
                return false;    
            }


             //if one is true then it's good
            if(decimalPlaces){
                
                if(el.value == '-') return true;
                
                let r = "^-?\\d+\\.\\d{0,"+(decimalPlaces)+"}$";
                
                
                let a = (new RegExp(r,'gi')).test(el.value);

                let b = /^-?\d+$/.test(el.value);
                let c = /^-?\d+\.$/.test(el.value);

                if(!a && !b && !c && el.value != ''){

                    evt.preventDefault();
                    return false;
                }
            }
            
            return true;

        }); //keypress


         el.addEventListener('keyup',(evt)=>{
            
            if(decimalPlaces){
                  
                let r = "^-?\\d+\\.\\d{0,"+(decimalPlaces)+"}$";
                let a = (new RegExp(r,'gi')).test(el.value);
                let b = /^-?\d+$/.test(el.value);


                if(!a && !b && el.value != ''){
                    el.value = el.value.slice(0, -1); 
                }
            }

         });


         el.addEventListener('blur',()=>{
            el.value = window.util.pureNumber(el.value);
         });

         el.addEventListener('paste',(evt)=>{
            setTimeout(()=>{
                let val = window.util.pureNumber(el.value);

                if(decimalPlaces){
                    let r = "^-?\\d+\\.\\d{0,"+(decimalPlaces)+"}$";
                    let a = (new RegExp(r,'gi')).test(val);
                    let b = /^-?\d+$/.test(val);
                    
                    if(!a && !b){
                        val = 0;
                    }
                }

                el.value = val;
            },0);
         });

    });
}


window.util.inputNumber = function(txt,evt,decimalPlaces,negativeFlag){

    let charCode = (evt.which) ? evt.which : evt.keyCode;
    

    //do not allow negative sign at the start
    if(negativeFlag && charCode == 45){


        if (txt.value.indexOf('-') === -1 && txt.value == '') {        
            return true;
        } else {
            return false;
        }
    }

    //point
    if (charCode == 46) {
        

        //Check if the text already contains the . character
        if (txt.value.indexOf('.') === -1 && decimalPlaces != 0) {
            return true;
        } else {
            return false;
        }

    } else {

        
        
        
        if (charCode > 31 && (charCode < 48 || charCode > 57)){
            
            return false;    
        }
        
    }

    

    //if one is true then it's good
    if(decimalPlaces){
    
        if(txt.value == '-') return true;
    
        
        let r = "^-?\\d+\\.\\d{0,"+(decimalPlaces)+"}$";
       
        let a = (new RegExp(r,'gi')).test(txt.value);
        let b = /^-?\d+$/.test(txt.value);
        let c = /^-?\d+\.$/.test(txt.value);

        if(!a && !b && !c && txt.value != ''){

            console.log('c');
            return false;
        }
    }
    

    return true;
    
}


window.util.navTo = function(url,evt){
    let el = document.getElementById('#__nav_helper');

    if(!url){
        console.error('URL not defined');
        return false;
    }

    if(evt){
        if(evt.ctrlKey){
            window.open(url,'_blank').focus();
            return false
        }
    }

    if(el){
        el.href = url;
        htmx.process(el);
        el.click();
    }else{
        console.error('Nav helper element not found');
    }

    // setTimeout(()=>{
    //     window.scrollTo(0,0);
    // },1000);
}


window.util.navReload = function(){
    let el = document.getElementById('#__nav_helper');

    if(el){
        el.href = document.location.href;
        htmx.process(el);
        el.click();
    }else{
        console.error('Nav helper element not found');
    }

    setTimeout(()=>{
        window.scrollTo(0,0);
    },1000);
}






window.util.throttle = (fn, delay) => {   
    // Capture the current time   
    let time = Date.now();    
    // Here's our logic   
    return () => {     
        if((time + delay - Date.now()) <= 0) {       
            // Run the function we've passed to our throttler,       
            // and reset the `time` variable (so we can check again).       
            fn();       
            time = Date.now();     
        }   
    } 
};