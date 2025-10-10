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

    
    <h1>Items</h1>
    <div id="result_container"></div>
    
    <hr>
    
    <h1>Payment Term Summary</h1>
    <div id="payment_terms_summary" class="d-flex justify-content-evenly"></div>
    
</div>
<script type="module">
    import {$q,Template,$el} from '/adarna.js';


    const result_container          = $q('#result_container').first();
    const payment_terms_summary     = $q('#payment_terms_summary').first();

    const t = new Template();


    function updateTotal(){

    }

    function updatePaymentTermsTotal(payment_terms){

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

        
        for(let id in summary){

            const summary_el = t.div({class:'text-center border border-secondary p-3'},()=>{
                t.h3(payment_terms[id].text);
                t.h4('P '+window.util.numberFormat(summary[id],2));
            });

            payment_terms_summary.appendChild(summary_el);
        }

    }

    function checkboxOnchangeController(payment_terms){

        updatePaymentTermsTotal(payment_terms);
       
        
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
                
                t.h5( projects[project_id].name );

                items.map(item => {

                    t.div({class:'border border-secondary mb-2 p-3'},()=>{

                        if(item.flag){
                        
                        
                        
                            t.div({class:'row'},()=>{
                                t.div({class:'col-11'},()=>{
                                    t.span({class:'text-success'},'[✔] ');
                                    t.txt(String(item.po.id).padStart(6,0) +' (P'+window.util.numberFormat(item.total,2)+')');
                                });
                                t.div({class:'col-1 text-end'},()=>{
                                    let chbx = t.input({class:'po ok form-check-input',dataPayment_term_id:item.po.payment_term_id, dataAmount: item.total, value:item.po.id, checked:true, type:'checkbox'});
                                    
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
                                    let chbx = t.input({class:'po invalid form-check-input', dataPayment_term_id:item.po.payment_term_id, dataAmount: item.total, value:item.po.id, type:'checkbox'});
                                    
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
                            t.span(item.po.created_at);
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