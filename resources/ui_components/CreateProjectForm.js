import {Template,Component} from '/adarna.js';


class CreateProjectForm extends Component{

    view(){
        const t= new Template();

        return t.div(()=>{
            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-lg-6 mb-3'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Project Name');
                        this.el.project_name = t.input({class:'form-control'});
                    });//div
                });//div col

                t.div({class:'col-lg-6'},()=>{
                    t.div({class:'form-group'},()=>{
                        t.label('Status');
                        this.el.project_status = t.select({class:'form-select'},()=>{
                            t.option({value:'ACTV'},'Active');
                            t.option({value:'INAC'},'Inactive');
                        });
                    });//div
                });//div col
            });//div row

            t.div({class:'row mb-3'},()=>{
                t.div({class:'col-lg-12 text-end'},()=>{
                    this.el.btn_submit = t.button({class:'btn btn-primary me-3'},'Submit');
                    this.el.btn_cancel = t.button({class:'btn btn-secondary'},'Cancel');
                });
            });//div row
        });//div
    }

    controller(){

        this.el.btn_submit.onclick = ()=>{
            window.util.blockUI();
        }

        this.el.btn_cancel.onclick = ()=>{
            window.util.drawerModal.close();
        }
    }
}


export default (data)=>{
    return (new CreateProjectForm(data));
}