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
            <h1 class="mb-3">Request To Purchase Timeframe KPI</h1>
            <table class="table">
                <tr>
                    <td>
                        <h3>Request</h3>
                        <h3>
                            {{ number_format($request_count,2) }}
                        </h3>
                    </td>
                    <td>
                        <h3>Hit</h3>
                        <h3>
                        {{ number_format($target_hit,2) }}
                        </h3>
                    </td>
                    <td>
                        <h3>Missed</h3>
                        <h3>
                            {{ number_format($target_missed,2) }}
                        </h3>
                
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="text-center">
                        <h2>Percentage</h2>
                        <h2>{{$percentage}}%</h2>
                    </td>
                </tr>
            </table>    
        </div>
        

      


    </body>
</html>