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

    



      <div class="d-flex flex-wrap justify-content-between mb-5">
        <h2 class="mb-3">PO List</h2>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="" id="all_not_ok"/>
            <label class="form-check-label text-danger">
            All [✖]
            </label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" value="" id="all_ok"/>
            <label class="form-check-label text-success">
            All [✔]
            </label>
        </div>   
    </div>

    <div id="result_container"></div>
    
    <hr>
    
  
    <h1 class="mb-3">Payment Summary</h1>
      

    <div id="payment_terms_summary" class="d-flex flex-wrap justify-content-between"></div>
    

    <div class="row mt-5">
        <div class="col-lg-12 text-end shadow bg-white rounded footer-action-menu p-2">
            <button id="rejectBtn" class="btn btn-danger">Reject</button>
            <button id="approveBtn" class="btn btn-primary">Arppove</button>
            <button id="cancelBtn" class="btn btn-secondary">Cancel</button>
        </div>
    </div>
</div>
<script type="module">
    import {$q,Template,$el} from '/adarna.js';


    const result_container          = $q('#result_container').first();
    const payment_terms_summary     = $q('#payment_terms_summary').first();
    const all_ok                    = $q('#all_ok').first();
    const all_not_ok                = $q('#all_not_ok').first();
    const approveBtn                = $q('#approveBtn').first();
    const rejectBtn                 = $q('#rejectBtn').first();
    const cancelBtn                 = $q('#cancelBtn').first();

    const t = new Template();


    approveBtn.onclick = async ()=>{

        await approveSelection();
      
    }


    rejectBtn.onclick = async ()=>{

        await rejectSelection();
      
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


    async function submitAction(action,els){

        let ids = [];

        els.map(el=>{
            ids.push(el.value);
        });

        const form = t.form({
            method:'POST',
            action:'/review/bulk/purchase_order/action'
        });

        ids.map(id=>{
            
            const input = t.input({type:'hidden',name:'ids[]',value:id});

            form.appendChild(input);
        });

        form.appendChild(
            t.input({type:'hidden',name:'action',value:'APRV'})
        );

        result_container.appendChild(form);

        setTimeout(()=>{
            form.submit();
        },100);
    
    }

    async function approveSelection(){
        const po = $q('input[type="checkbox"].po:checked').items();

        let ans = await window.util.confirm('Are you sure you want to APPROVE '+po.length+' items?');

        if(!ans){
            return false;
        }

        submitAction('APRV',po);
    }

    async function rejectSelection(){
        const po = $q('input[type="checkbox"].po:checked').items();
    }


    all_ok.onchange = ()=>{

        let last_item = null;

        $q('.ok').items().map(item=>{
            item.checked = all_ok.checked;
            last_item = item;
        });

        
        if(last_item){
            last_item.onchange();
        }
    }

    all_not_ok.onchange = ()=>{

        let last_item = null;

        $q('.invalid').items().map(item=>{
            item.checked = all_not_ok.checked;
            last_item = item;
        });

        if(last_item){
            last_item.onchange();
        }
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
                
                t.div({class:'d-flex justify-content-between'},()=>{
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
                

                t.div({class:'ps-3'},()=>{

                
                    items.map(item => {

                        t.div({class:'border border-secondary mb-2 p-3'},()=>{

                            if(item.flag){
                            
                            
                            
                                t.div({class:'row'},()=>{
                                    t.div({class:'col-6'},()=>{
                                        t.span({class:'text-success'},'[✔] ');
                                        t.a({href:'/review/purchase_order/'+item.po.id,target:'_blank'},String(item.po.id).padStart(6,0));
                                    });
                                    t.div({class:'col-6 d-flex justify-content-end'},()=>{

                                        t.label({class:'me-3'},'P '+window.util.numberFormat(item.total,2));

                                        let chbx = t.input({class:'po ok form-check-input project_'+project_id, dataPayment_term_id:item.po.payment_term_id, dataAmount: item.total, value:item.po.id, checked:true, type:'checkbox'});
                                        
                                        chbx.onchange = ()=>{
                                            checkboxOnchangeController(payment_terms);
                                        }
                                    });
                                });


                        
                            }else{


                                t.div({class:'row'},()=>{
                                    t.div({class:'col-6'},()=>{
                                        t.span({class:'text-danger'},'[✖] ');
                                        t.a({href:'/review/purchase_order/'+item.po.id,target:'_blank'},String(item.po.id).padStart(6,0));
                                    });
                                    t.div({class:'col-6 d-flex justify-content-end'},()=>{

                                        t.label({class:'me-3'},'P '+window.util.numberFormat(item.total,2));

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