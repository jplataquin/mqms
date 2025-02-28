import {ComponentV2,Template} from '/adarna.js';

class ContextMenu extends ComponentV2{

    model(){
        return {
            backdropZIndex:5,
            menuZIndex:6,
            menuBackgroundColor:'rgb(153, 152, 152)',
            itemHighlightColor:'rgb(223, 220, 220)',
            items:[]
        }
    }

    view(){

        const t = new Template();

        this.el.backdrop = t.div({style:{
            minWidth:'100%',
            minHeight:'100%',
            width:'100%',
            height:'100%',
            position:'absolute',
            top:'0px',
            left:'0px',
            zIndex:this._model.backdropZIndex,
            backgroundColor:'rgba(255, 255, 255, 0)'
        }});

        this.el.menu = t.div({
            style:{
                backgroundColor:this._model.menuBackgroundColor,
                position:'absolute',
                zIndex:this._model.menuZIndex,
                height:'200px',
                maxWidth:'200px',
                minWidth:'200px'
            }
        });

        this._model.items.map(item=>{
            this.el.menu.appendChild(this.parseItem(item));
        });

        this.el.backdrop.appendChild(this.el.menu);

        return this.el.backdrop;
    }

    parseItem(data){
        const t = new Template();

        const item = t.div({
            class:'contextmenu-item',
            style:{
                borderBottom:'1px solid rgba(25,25,25,0.5)',
                width:'100%',
                padding:'2px',
                cursor:'pointer'
            }
        },data.name);

        item.onclick = data.onclick;

        return item;
    }

    controller(){

        this._dom.handler.setMenuPos = (x,y)=>{
            
            this.el.menu.style.left = x+'px';
            this.el.menu.style.top  = y+'px';
        }

        this._dom.handler.show = (x,y)=>{

            this._dom.handler.setMenuPos(x,y);

            document.body.appendChild(this._dom);
        }

        this._dom.handler.hide = ()=>{
            document.body.removeChild(this._dom);
        }

        this.el.backdrop.onclick = ()=>{
            this._dom.handler.hide();
        }
    }

    style(){
        return {
            '.contextmenu-item:hover' :{
                backgroundColor: this._model.itemHighlightColor
            }
        }
    }
}

export default (data)=>{
    return (new ContextMenu(data));
}