import {$q,Template,ComponentV2} from '/adarna.js';
import contextMenu from '/ui_components/ContextMenu.js';

class Item extends ComponentV2 {

    state(){
        return {
            skipClose:{
                value:false
            },
            loading: {
                value:false
            },
            open: {
                value: false,
                target: this.el.label,
                events:['click'],
                getValue: (val)=>{
                    
                    return val;
                },
                onEvent: (e,val)=>{

                    return this.toggleContainer(val);
                               
                }
            }
        }
    }

    model(){
        return {
            id:'',
            name: '',
            status: '',
            type:'',
            open: ()=>{ return ''; },
            onScreen: ()=>{ console.log('On Screen') },
            parentContainer: null
        }
    }

    view(){

        const t     = new Template();

        const svg = this.icon(this._model.type);

        this.el.indicator = t.label({
            style:{
                fontSize:'20px',
            }
        },'>');

        this.el.status = t.label({
            style:{
                fontSize:'20px',
                color:'green',
            }
        },'•');

        this.el.label = t.label({
            style:{
                cursor:'pointer',
                display:'inline'
            }
        },this._model.name);

        this.el.container = t.div({
            style:{
                marginLeft:'24px',
                display:'none',
                borderLeft:'1px solid rgba(0,0,0,.55)',
            }
        });

       
        this.el.contextMenu = this.createContextMenu(this._model.type);
 
        const v = t.div({
            style:{
              position:'relative',
              zIndex:'2',
              width:'max-content',
              minWidth:'max-content'
            }
        },(el)=>{
            
            this.el.test = t.div({
                style:{
                    maxHeight:'24px'
                }
            },(el)=>{
                el.appendChild(this.el.status);
                el.appendChild(this.el.indicator);
            
                el.appendChild(svg);
                el.appendChild(this.el.label);

               
            });
            
            el.appendChild(this.el.container);
        });

        return v;
    }

    controller(){

        const t = new Template();

        const highlight = t.div({
            class:'highlight-o',
            style:{
                position:'relative',
                minHeight:'24px',
                backgroundColor:'rgba(81, 83, 100, 0.25)',
                width:'100%',
                left:'0px',
                top:'-26px'
            }
        },);

        this.el.label.onclick = ()=>{
            
            let label_bound = this.el.label.getBoundingClientRect();

            let parent_bound = this._model.parentContainer.getBoundingClientRect();

            console.log(label_bound,parent_bound);
            console.log(label_bound.y,parent_bound.y);
            console.log(this.el.label.offsetTop);
            
            //highlight.style.top     = (label_bound.y-2)+'px';
            highlight.style.left    = '0px';
            highlight.style.width   = '100%';//parent_bound.width+'px';

            $q('.highlight-o').items().map(item=>{
                item.style.display = 'none';
            });

            this.el.test.appendChild(highlight);

            highlight.style.display = 'block';

            //this.el.test.style.backgroundColor = 'yellow';
           
        }

        this.el.label.oncontextmenu = (e)=>{
            e.preventDefault();
            
            console.log('context menu',e.clientX,e.clientY);

            this.el.contextMenu.handler.show(e.clientX,e.clientY);
        }
        
    }


    createContextMenu(type){
        
        let cm = null;

        switch(type){

            case 'project':

                cm = contextMenu({
                    items:[
                        {
                            name:'Add Section',
                            onclick:()=>{
                                console.log('yeah')
                            }
                        }
                    ]
                });
                break;
            
            case 'section':
                cm = contextMenu({
                    items:[
                        {
                            name:'Add Contract Item',
                            onclick:()=>{
                                console.log('yeah')
                            }
                        }
                    ]
                });
                break;

            case 'contract_item':
                cm = contextMenu({
                    items:[
                        {
                            name:'Add Component',
                            onclick:()=>{
                                console.log('yeah')
                            }
                        }
                    ]
                });
                break;

            case 'component':
                cm = contextMenu({
                    items:[
                        {
                            name:'Add Component Item',
                            onclick:()=>{
                                console.log('yeah')
                            }
                        }
                    ]
                });
                break;
            
            case 'component_item':

                break;
        }


    }

    spinner(){
        let c               = ["⣾","⣽","⣻","⢿","⡿","⣟","⣯","⣷"];
        let start           = 0;
        let length          = c.length - 1;
        let framesPerSecond = 10;

        const step  = () => {
            
            this.el.indicator.innerText = c[start];
            
            start++;

            if(start > length){
                start = 0;
            }

            if(this.getState('loading') == true){
                setTimeout(()=>{
                    requestAnimationFrame(step);
                },1000 / framesPerSecond);
            }else{
                this.el.indicator.innerText = '⌄';
            }
        }

        step();
        
    }

    toggleContainer(val){
        
        if(this.getState('loading',true)){
            console.log('Loading');
            return false;
        }

        val = !val;

        this._model.onScreen();

        
        if(this.getState('skipClose')){
            this.setState('skipClose',false);
            return true;
        }

        if(val){

            if(this.el.container.innerHTML == ''){
                    

                (async ()=>{

                    this.setState('loading',true);
                    this.spinner();

                    let content = await this._model.open();

                    //Abort
                    if(content === false){
                        this.setState('loading',false);
                        this.setState('open',false);
                        return false;
                    }

                    if(content instanceof HTMLElement){
                        this.el.container.appendChild(content);
                    
                    }else if(Array.isArray(content)){
                        
                        content.map(item => {
                            this.el.container.appendChild(item);
                        });
                    }
                    
                    this.setState('loading',false);

                    this.el.container.style.display = 'block';
                    this.el.indicator.innerText = '⌄';
                

                })();
            
            }else{
                
                this.el.container.style.display = 'block';
                this.el.indicator.innerText = '⌄';
                
            }

            this.setState('skipClose',true);
            

        }else{ //Close

            this.el.container.style.display = 'none';
            this.el.indicator.innerText = '>';                    
         
        }

        return val;
    }

    icon(type = ''){
        
        const svg   = document.createElementNS('http://www.w3.org/2000/svg', 'svg');

        svg.setAttribute('width','20px');
        svg.setAttribute('height','20px');
        svg.setAttribute('style','display:inline');

        const path1  = document.createElementNS('http://www.w3.org/2000/svg','path');
        const path2 = document.createElementNS('http://www.w3.org/2000/svg','path');
        const path3 = document.createElementNS('http://www.w3.org/2000/svg','path');
        
        switch(type){

            case 'project':
                svg.setAttribute('viewBox','0 0 512 512');
                path1.setAttribute('d',"M192,7.10542736e-15 L384,110.851252 L384,332.553755 L192,443.405007 L1.42108547e-14,332.553755 L1.42108547e-14,110.851252 L192,7.10542736e-15 Z M42.666,157.654 L42.6666667,307.920144 L170.666,381.82 L170.666,231.555 L42.666,157.654 Z M341.333,157.655 L213.333,231.555 L213.333,381.82 L341.333333,307.920144 L341.333,157.655 Z M192,49.267223 L66.1333333,121.936377 L192,194.605531 L317.866667,121.936377 L192,49.267223 Z");
                svg.appendChild(path1);

                break;

            case 'section':
                svg.setAttribute('viewBox','0 0 24 24');
                svg.setAttribute('fill','none');
                path1.setAttribute('d',"M17.4 10L21 12L17.4 14M17.4 10L12 13L6.6 10M17.4 10L21 8L12 3L3 8L6.6 10M6.6 10L3 12L6.6 14M17.4 14L21 16L12 21L3 16L6.6 14M17.4 14L12 17L6.6 14");
                path1.setAttribute('stroke-linecap','round');
                path1.setAttribute('stroke-width','2');
                path1.setAttribute('stroke-linejoin','round');
                path1.setAttribute('stroke','#000000');
                svg.appendChild(path1);

                break;

            case 'contract_item':
                svg.setAttribute('viewBox','0 0 512 512');
                svg.setAttribute('width','12px');
                svg.setAttribute('height','12px');

                path1.setAttribute('d','M493.268,0H18.732C8.387,0,0,8.387,0,18.732v474.537C0,503.613,8.387,512,18.732,512h307.572c0.004,0,0.009,0,0.013,0 c4.757,0,9.671-1.906,13.248-5.487l166.948-166.949c3.413-3.41,5.487-8.208,5.487-13.245V18.732C512,8.387,503.613,0,493.268,0z M345.052,448.046V345.051h102.994C418.242,374.855,374.855,418.242,345.052,448.046z M474.537,307.587H326.32 c-10.345,0-18.732,8.387-18.732,18.732v148.218H37.463V37.463h437.073V307.587z');
                
                path2.setAttribute('d','M118.026,157.043h275.949c10.345,0,18.732-8.387,18.732-18.732c0-10.345-8.387-18.732-18.732-18.732H118.026 c-10.345,0-18.732,8.387-18.732,18.732C99.294,148.656,107.681,157.043,118.026,157.043z');
                
                path3.setAttribute('d','M118.026,256.579h275.949c10.345,0,18.732-8.387,18.732-18.732c0-10.345-8.387-18.732-18.732-18.732H118.026 c-10.345,0-18.732,8.387-18.732,18.732C99.294,248.193,107.681,256.579,118.026,256.579z');
                
                
                svg.appendChild(path1);
                svg.appendChild(path2);
                svg.appendChild(path3);

                svg.style.marginRight = '3px';

                break;
            case 'component':
                svg.style.marginRight = '3px';
                path1.setAttribute('d',"m3.553 18.895 4 2a1.001 1.001 0 0 0 .894 0L12 19.118l3.553 1.776a.99.99 0 0 0 .894.001l4-2c.339-.17.553-.516.553-.895v-5c0-.379-.214-.725-.553-.895L17 10.382V6c0-.379-.214-.725-.553-.895l-4-2a1 1 0 0 0-.895 0l-4 2C7.214 5.275 7 5.621 7 6v4.382l-3.447 1.724A.998.998 0 0 0 3 13v5c0 .379.214.725.553.895zM8 12.118l2.264 1.132-2.913 1.457-2.264-1.132L8 12.118zm4-2.5 3-1.5v2.264l-3 1.5V9.618zm6.264 3.632-2.882 1.441-2.264-1.132L16 12.118l2.264 1.132zM8 18.882l-.062-.031V16.65L11 15.118v2.264l-3 1.5zm8 0v-2.264l3-1.5v2.264l-3 1.5zM12 5.118l2.264 1.132-2.882 1.441-2.264-1.132L12 5.118z");
                svg.appendChild(path1);

                break;


            case 'component_item':
                
                svg.setAttribute('viewBox','0 0 24 24');
       
                path1.setAttribute('d','M17.5777 4.43152L15.5777 3.38197C13.8221 2.46066 12.9443 2 12 2C11.0557 2 10.1779 2.46066 8.42229 3.38197L6.42229 4.43152C4.64855 5.36234 3.6059 5.9095 2.95969 6.64132L12 11.1615L21.0403 6.64132C20.3941 5.9095 19.3515 5.36234 17.5777 4.43152Z');
                path2.setAttribute('d','M21.7484 7.96435L12.75 12.4635V21.904C13.4679 21.7252 14.2848 21.2965 15.5777 20.618L17.5777 19.5685C19.7294 18.4393 20.8052 17.8748 21.4026 16.8603C22 15.8458 22 14.5833 22 12.0585V11.9415C22 10.0489 22 8.86558 21.7484 7.96435Z');
                path3.setAttribute('d','M11.25 21.904V12.4635L2.25164 7.96434C2 8.86557 2 10.0489 2 11.9415V12.0585C2 14.5833 2 15.8458 2.5974 16.8603C3.19479 17.8748 4.27063 18.4393 6.42229 19.5685L8.42229 20.618C9.71524 21.2965 10.5321 21.7252 11.25 21.904Z');
                
                svg.appendChild(path1);
                svg.appendChild(path2);
                svg.appendChild(path3);

                break;
            
            default:
                path1.setAttribute('d',"m3.553 18.895 4 2a1.001 1.001 0 0 0 .894 0L12 19.118l3.553 1.776a.99.99 0 0 0 .894.001l4-2c.339-.17.553-.516.553-.895v-5c0-.379-.214-.725-.553-.895L17 10.382V6c0-.379-.214-.725-.553-.895l-4-2a1 1 0 0 0-.895 0l-4 2C7.214 5.275 7 5.621 7 6v4.382l-3.447 1.724A.998.998 0 0 0 3 13v5c0 .379.214.725.553.895zM8 12.118l2.264 1.132-2.913 1.457-2.264-1.132L8 12.118zm4-2.5 3-1.5v2.264l-3 1.5V9.618zm6.264 3.632-2.882 1.441-2.264-1.132L16 12.118l2.264 1.132zM8 18.882l-.062-.031V16.65L11 15.118v2.264l-3 1.5zm8 0v-2.264l3-1.5v2.264l-3 1.5zM12 5.118l2.264 1.132-2.882 1.441-2.264-1.132L12 5.118z");
                svg.appendChild(path1);
        }
        

        return svg;
    }

    style(){
        return {
            label:{
                fontSize:'16px'
            }
        }
    }
}

export default function NodeItem(data){
    return new Item(data);
}
