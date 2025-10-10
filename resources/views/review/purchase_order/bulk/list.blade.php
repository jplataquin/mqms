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

    
    <h1 class="mb-3">Items</h1>
    <div id="result_container"></div>
    
    <hr>
    
    <h1 class="mb-3">Payment Summary</h1>
    <div id="payment_terms_summary" class="d-flex flex-wrap jalign-content-center"></div>
    

    <div class="row mt-5">
        <div class="col-lg-12 text-end shadow bg-white rounded footer-action-menu p-2">
            <button id="rejectButton" class="btn btn-danger">Reject</button>
            <button id="aproveButton" class="btn btn-primary">Arppove</button>
            <button id="cancelBtn" class="btn btn-secondary">Cancel</button>
        </div>
    </div>
</div>
<script type="module">
    import {$q,Template,$el} from '/adarna.js';


    const result_container          = $q('#result_container').first();
    const payment_terms_summary     = $q('#payment_terms_summary').first();

    const t = new Template();


    function updateTotal(){

    }

    function updatePaymentTermsTotal(payment_terms){

        payment_terms_summary.innerHTML = '';

        const t = new Template();

        let checkboxes = $q('.po[type="checkbox"]:checked').items();

        let summary = {};

        checkboxes.map(c=>{
            let payment_term_id = c.getAttribute('data-payment_term_id');

            if(typeof summary[payment_term_id] == 'undefined'){
                summary[payment_term_id] = parseFloat( c.getAttribute('data-amount') );
            }

            summary[payment_term_id] += parseFloat( c.getAttribute('data-amount') );
        });

        
        let grand_total = 0;

        for(let id in summary){

            const summary_el = t.div({class:'text-center border border-secondary p-3 m-3'},()=>{
                t.h3(payment_terms[id].text);
                t.h4('P '+window.util.numberFormat(summary[id],2));
            });

            grand_total += summary[id];

            payment_terms_summary.appendChild(summary_el);
        }

        payment_terms_summary.appendChild(t.div({class:'text-center p-3 border border-warning m-3'},()=>{

            t.h3('Grand Total');
            t.h4('P '+window.util.numberFormat(grand_total,2));
        }));

    }

    function checkboxOnchangeController(payment_terms){

        updatePaymentTermsTotal(payment_terms);
       
        
    }

    window.util.blockUI();

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

            const project_div = t.div({class:'mb-5'},()=>{
                
                t.div({class:'d-flex justify-content-center'},()=>{
                    t.h5( projects[project_id].name );

                    let chbx_project = t.input({class:'form-check-input', type:'checkbox'});
                    
                    chbx_project.onchange = ()=>{
                       
                        $q('.project_'+project_id).items().map(item=>{

                            if(chbx_project.checked){
                                item.checked = true;
                            }else{
                                item.checked = false;
                            }
                        });


                        updatePaymentTermsTotal(payment_terms);
                    }
                });
                

                items.map(item => {

                    t.div({class:'border border-secondary mb-2 p-3'},()=>{

                        if(item.flag){
                        
                        
                        
                            t.div({class:'row'},()=>{
                                t.div({class:'col-11'},()=>{
                                    t.span({class:'text-success'},'[✔] ');
                                    t.txt(String(item.po.id).padStart(6,0) +' (P'+window.util.numberFormat(item.total,2)+')');
                                });
                                t.div({class:'col-1 text-end'},()=>{
                                    let chbx = t.input({class:'po ok form-check-input project_'+project_id, dataPayment_term_id:item.po.payment_term_id, dataAmount: item.total, value:item.po.id, checked:true, type:'checkbox'});
                                    
                                    chbx.onchange = ()=>{
                                        checkboxOnchangeController(payment_terms);
                                    }
                                });
                            });


                    
                        }else{


                            t.div({class:'row'},()=>{
                                t.div({class:'col-11'},()=>{
                                    t.span({class:'text-danger'},'[✖] ');
                                    t.txt(String(item.po.id).padStart(6,0) +' (P'+window.util.numberFormat(item.total,2)+')');
                                });
                                t.div({class:'col-1 text-end'},()=>{
                                    let chbx = t.input({class:'po invalid form-check-input project_'+project_id, dataPayment_term_id:item.po.payment_term_id, dataAmount: item.total, value:item.po.id, type:'checkbox'});
                                    
                                    chbx.onchange = ()=>{
                                        checkboxOnchangeController(payment_terms);
                                    }
                                });
                            });

                            t.div({class:'row'},()=>{
                                item.failed.map(fail=>{
                                    t.span({class:'text-danger text-sm'},fail);
                                
                                });
                            })
                        }

                        t.div({class:'row'},()=>{
                            t.span(item.created_at);
                            t.span(item.po.status);
                            t.span(suppliers[item.po.supplier_id].name);
                            t.span(payment_terms[item.po.payment_term_id].text);

                            
                        });

                    });
                    
                });//items


            });


            result_container.appendChild(project_div);
        }//For

        setTimeout(()=>{
            updatePaymentTermsTotal(payment_terms);
        },500);
        

    });//End http call
</script>
</div>
@endsection