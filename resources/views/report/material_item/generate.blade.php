@section('content')
<div id="content">
    <div class="container">
        <div class="breadcrumbs">
            <ul>
                <li>
                    <a href="#">
                        <span>
                        Report
                        </span>
                    </a>
                </li>
                <li>
                    <a href="#" class="active">
                        <span>
                       Material Item
                        </span>
                    </a>
                </li>
            </ul>
        </div>
        <hr>


        @foreach($result as $material_item_id => $res1)

            <div class="mb-3 ps-3">
                <h2>{{ $material_item_options[$material_item_id]->text }}</h2>

                @foreach($res1 as $supplier_id => $res2)
                    <div class="mb-3 ps-3">
                        <h3>{{ $supplier_options[$supplier_id]->text }}</h3>

                        @foreach($res2 as $payment_term_id => $res3)
                            <div class="mb-3 ps-3">
                                <h4>{{ $payment_term_options[$payment_term_id] }}</h4>

                                <ul class="list-group">
                                    @foreach($res3 as $price => $res4)
                                        <li class="list-group-item">P {{number_format($price,2)}} - {{$res3->created_at}}
                                    @endforeach
                                
                                </ul>
                            </div>
                        @endforeach
                    </div>

                @endforeach

            </div>

        @endforeach
    

    </div>

    <script type="module">
        import {$q,Template,$el,$util} from '/adarna.js';

    </script>
</div>
@endsection