<html>
    <head>
        <title>RPT KPI</title>

        <style>
            table, tr, td, th {
                border: solid 1px #000000;
                border-collapse: collapse;
                font-size: 11px;
            }
            
            table {
                width:100%;
            }

            th{
                text-align: center;
            }

            td, th {
                padding: 5px;
            }

            .text-end{
                text-align:right !important;
            }

            .text-start{
                text-align:left !important;
            }

            .text-center{
                text-align:center !important;
            }

            .w-100{
                width: 100%;
            }

            .mb-3{
                margin-bottom:3px;
            }

            .mb-5{
                margin-bottom:5px;
            }

        
            
            .text-italic{
                font-style: italic;
            }

            .wrap{
                word-wrap: break-word;
            }

            
            @media print {

                td, th{
                    font-size:10px;
                }

                .page-break{
                    break-before:always;
                }
                
                thead{
                    background-color:silver;
                }
            }
        </style>
    </head>
    <body>
    

       
           

        <div class="mb-5">
            <table class="table">
                <tr>
                    <th>
                        <img src="/storage/sys_images/header.png" style="width:500px"/>
                    </th>
                    <th>
                        <h2>Request To Purchase Timeframe KPI</h2>
                    </th>
                </tr>
                <tr>
                    <td colspan="2">
                        <h3>Material Request to Material Purchase within 7 days (Target: 90%)</h3>           
                    </td>
                </tr>
                <tr>
                    <th colspan="2">
                        <h3>Date Scope: {{$from}} - {{$to}}</h3>
                    </td>
                </tr>
            </table>

            <table class="table mt-5">
                <tr>
                    <td class="text-center">
                        <h1>Request</h1>
                        <h1>
                            {{ number_format($request_count,2) }}
                        </h2>
                    </td>
                    <td class="text-center">
                        <h1>Hit</h1>
                        <h1>
                        {{ number_format($target_hit,2) }}
                        </h1>
                    </td>
                    <td class="text-center">
                        <h1>Missed</h1>
                        <h1>
                            {{ number_format($target_missed,2) }}
                        </h1>
                
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="text-center">
                        <h1>Percentage</h1>
                        <h1>{{$percentage}}%</h1>
                    </td>
                </tr>
            </table>    
        </div>
        

      


    </body>
</html>