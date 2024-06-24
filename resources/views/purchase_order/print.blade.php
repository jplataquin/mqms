<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en-US"> <!--<![endif]-->
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="pingback" href="https: //domainname.com/xmlrpc.php" />
        <title></title>
        <!-- style and script resources -->
        <link rel="stylesheet" href="" media="all">
        <script src=""></script>
        <!--meta properties -->
        <meta name="description" content=" Your site description." />
        <!--detailed robots meta https://developers.google.com/search/reference/robots_meta_tag -->
        <meta name="robots" content="index, follow, max-snippet: -1, max-image-preview:large, max-video-preview: -1" />
        <link rel="canonical" href="" />
        <!--open graph meta tags for social sites and search engines-->
        <meta property="og:locale" content="en_US" />
        <meta property="og:type" content="website" />
        <meta property="og:title" content="  Opengraph content 25 char are best" />
        <meta property="og:description" content="  #description." />
        <meta property="og:url" content="" />
        <meta property="og:site_name" content="" />
        <meta property="og:image" content="images//hom-banner-compressed.jpg" />
        <meta property="og:image:secure_url" content="images//hom-banner-compressed.jpg" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="660" />
        <!--twitter description-->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:description" content="." />
        <meta name="twitter:title" content="" />
        <meta name="twitter:site" content="@" />
        <meta name="twitter:image" content="images/hom-banner-compressed.jpg" />
        <meta name="twitter:creator" content="@" />
        <!--opengraph tags for location or address for information panel in google-->
        <meta name="og:latitude" content="" />
        <meta name="og:longitude" content="" />
        <meta name="og:street-address" content="" />
        <meta name="og:locality" content="" />
        <meta name="og:region" content="" />
        <meta name="og:postal-code" content="" />
        <meta name="og:country-name" content="" />
        <!--search engine verification-->
        <meta name="google-site-verification" content="" />
        <meta name="yandex-verification" content="" />
        <!--powered by meta-->
        <meta name="generator" content="" />
        <!-- Site fevicon icons -->
        <link rel="icon" href="images/icon/cropped-cropped-favicon-1-1-32x32.png" sizes="32x32" />
        <link rel="icon" href="images/icon/cropped-cropped-favicon-1-1-192x192.png" sizes="192x192" />
        <link rel="apple-touch-icon-precomposed" href="images/icon/cropped-cropped-favicon-1-1-180x180.png" />
        <meta name="msapplication-TileImage" content="images/icon/cropped-cropped-favicon-1-1-270x270.png" />
        <!--complete list of meta tags at - https://gist.github.com/lancejpollard/1978404 -->

        <style>
            @media print {
                table {
                    border: solid #000 !important;
                    border-width: 1px 0 0 1px !important;
                }
                th, td {
                    border: solid #000 !important;
                    border-width: 0 1px 1px 0 !important;
                }
            }
        </style>
    </head>
    <body>
        <!--[if lt IE 7]>
            <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="#">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
        <div style="font-size:11px"> 
            @foreach(['Supplier','Site','Accounting'] as $copy)
            <table
                width="100%"
                height="50%" 
                style="margin-bottom:50px; margin-left:auto; margin-right:auto; border-collapse: collapse" 
                border="1"
            >
                <thead>
                    <tr>
                        <td colspan="4" align="center">
                            <img width="500px"src="{{ asset('storage/sys_images/header.png') }}">
                        <td>
                    </tr>
                    <tr>
                        <th colspan="4" align="center" colspan="4">
                            Purchase Order
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th align="left">PO#</th>
                        <td>{{$purchase_order->id}}</td>
                        <th align="left">Supplier</th>
                        <td>{{$supplier->name}}</td>
                    </tr>
                    <tr>
                        <th align="left">Mat. Qty Req #</th>
                        <td>{{$material_quantity_request->id}}</td>
                        <th align="left">Payment Terms</th>
                        <td>{{$payment_term->text}}</td>
                    </tr>
                    <tr>
                        <th align="left">Copy</th>
                        <td>{{$copy}}</td>
                        <th align="left">Contact Person</th>
                        <td>{{$supplier->primary_contact_person}}</td>
                    </tr>
                    <tr>
                        <th align="left">Date</th>
                        <td>{{$purchase_order->approved_at}}</td>
                        <th align="left">Contact No:</th>
                        <td>{{$supplier->primary_contact_no}}</td>
                    </tr>

                    <tr>
                        <th align="left">Project</th>
                        <td>{{$project->name}}</td>
                        <th align="left">Section / Component </th>
                        <td>{{$section->name}} - {{$component->name}}</td>
                    </tr>
                    
                    
                    <tr>
                        <th colspan="4">
                            Items
                        </th>
                    </tr>

                    <tr>
                        <th>Material</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>

                    @php $subtotal = 0; @endphp
                    @foreach($items as $item)
                        <tr>
                            <td>{{$materialItemArr[ $item->material_item_id]->brand}} {{$materialItemArr[ $item->material_item_id]->name}} {{$materialItemArr[ $item->material_item_id]->specification_unit_packaging}}</td>
                            <td align="center">{{number_format($item->quantity,2)}}</td>
                            <td align="right">{{number_format($item->price,2)}}</td>
                            <td align="right">{{number_format($item->quantity*$item->price,2)}}</td>
                        </tr>
                        @php $subtotal = $subtotal + ($item->quantity*$item->price); @endphp
                    @endforeach
                    
                    <tr>
                        <td colspan="2"></td>
                        <th align="right" >Sub Total</th>
                        <td align="right">{{number_format($subtotal,2)}}</td>
                    </tr>

                    @php $grandtotal = $subtotal; @endphp
                    @foreach($extras as $extra)

                        <tr>
                            <td colspan="2"></td>
                            <th align="right">{{$extra->text}}</th>
                            <td align="right">{{ number_format($extra->value,2) }}</td>
                        </tr>
                        
                        
                        @php $grandtotal = $grandtotal + $extra->value; @endphp
                    @endforeach

                    <tr>
                        <td colspan="2"></td>
                        <th align="right">Grand Total</th>
                        <td align="right">{{number_format($grandtotal,2)}}</td>
                    </tr>

                </tbody>
                <tfoot>
                
                </tfoot>
            </table>
        
        @endforeach

        </div>
        
        <link rel="stylesheet" type="text/css" href="/" media="print" />
        <script src="" async defer></script>
    </body>
</html>