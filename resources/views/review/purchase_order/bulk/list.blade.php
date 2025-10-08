@extends('layouts.app')

@section('content')
<div id="content">
<div class="container">
    <div class="breadcrumbs" hx-boost="true" hx-select="#content" hx-target="#main">
        <ul>
            <li>
                <a href="/review/purchase_orders">
                    <span>
                       Review
                    </span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <span>
                       Bulk PO
                    </span>
                    <i class="ms-2 bi bi-list-ul"></i>
                </a>
            </li>
        </ul>
    </div>
    <hr>

    
  
    <div id="result_container"></div>   
 
</div>
<script type="module">
    import {$q,Template,$el} from '/adarna.js';


    const result_container = $q('#result_container').first();

    const t = new Template();


    function updateTotal(){

    }

    function updatePaymentTermsTotal(){

        let checkboxes = $q('.po[type="checkbox"]:checked').items();

        let payment_term_summary = {};

        checkboxes.map(c=>{
            let payment_term_id = c.getAttribute('data-payment_term_id');

            if(typeof payment_term_summary[payment_term_id] == 'undefined'){
                payment_term_summary[payment_term_id] = parseFloat( c.getAttribute('data-amount') );
            }

            payment_term_summary[payment_term_id] += parseFloat( c.getAttribute('data-amount') );
        });

        console.log(payment_term_summary);
    }

    function checkboxOnchangeController(){

        updatePaymentTermsTotal();
       
        
    }

    window.util.$get('/api/review/bulk/purchase_order/list',{}).then(reply=>{

        
        window.util.unblockUI();

        if(reply.status <= 0 ){
            
            window.util.showMsg(reply);
            return false;
        };

        let result          = reply.data.result;
        let projects        = reply.data.projects;
        let suppliers       = reply.data.suppliers;
        let payment_terms   = reply.data.payment_terms;

        for(let project_id in result){

            let items = result[project_id];

            const project_div = t.div({class:'mb-3'},()=>{
                
                t.h3( projects[project_id].name );

                items.map(item => {

                    t.div({class:'border border-secondary mb-2 p-3'},()=>{

                        if(item.flag){
                        
                        
                        
                            t.div({class:'row'},()=>{
                                t.div({class:'col-11'},()=>{
                                    t.span({class:'text-success'},'[✔] ');
                                    t.txt(item.po.id);
                                });
                                t.div({class:'col-1 text-end'},()=>{
                                    let chbx = t.input({class:'po ok form-check-input',dataPayment_term_id:item.po.payment_term_id, dataAmount: 1, value:item.po.id, checked:true, type:'checkbox'});
                                    
                                    chbx.onchange = checkboxOnchangeController;

                                });
                            });


                    
                        }else{


                            t.div({class:'row'},()=>{
                                t.div({class:'col-11'},()=>{
                                    t.span({class:'text-danger'},'[✖] ');
                                    t.txt(item.po.id);
                                });
                                t.div({class:'col-1 text-end'},()=>{
                                    let chbx = t.input({class:'po invalid form-check-input', dataPayment_term_id:item.po.payment_term_id, dataAmount: 1, value:item.po.id, type:'checkbox'});
                                    
                                    chbx.onchange = checkboxOnchangeController;
                                });
                            });
                        }

                        t.div({class:'row'},()=>{
                            t.span(item.po.created_at);
                            t.span(suppliers[item.po.supplier_id].name);
                            t.span(payment_terms[item.po.payment_term_id].text);
                        });

                    });
                    
                });//items


            });


            result_container.appendChild(project_div);
        }

    });//End http call
</script>
</div>
@endsection