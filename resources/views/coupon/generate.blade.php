<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">

        <title>Coupon - {{$coupon->id}}</title>
  
        <style>

            .d-none{
                display:none;
            }

            .d-inline{
                display:inline;
            }


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

            .ml-3{
                margin-left: 3px;
            }

            .ml-5{
                margin-left: 5px;
            }


            .mb-3{
                margin-bottom:3px;
            }

            .mb-5{
                margin-bottom:5px;
            }

            .amount-15{
                width: 15ch !important;
            }

            .amount-13{
                width: 13ch !important;
            }
            
            .text-italic{
                font-style: italic;
            }

            .wrap{
                word-wrap: break-word;
            }

            .warning-text{
                color:rgb(234, 255, 5);
            }

            .pending-text{
                color:rgb(234, 255, 5);
            }

            .approved-text{
                color:rgb(11, 152, 1);
            }

            .rejected-text{
                color:rgb(255, 5, 5);
            }

            

            @media print {

                td, th{
                    font-size:10px;
                }

                .page-break{
                    break-before:always;
                }
                
            
            }
        </style>
    </head>
    <body>

        <table>
            <tr>
                <td class="text-center">
                    <img src="/storage/sys_images/header.png" style="width:500px"/>
                </td>
                <td class="text-center">
                    <img id="qr" src="/qrcode?d={{ url('/coupon/claim/'.$coupon->id.'/'.$coupon->code ) }}"style="width:200px;height:200px"/>
                </td>
            </tr>
        </table>

        <canvas id="canvas"></canvas>

        <button style="margin-top:20px" id="downloadBtn">Download</button>

        <script>
            
            const canvas = document.querySelector('#canvas');
            const download = document.querySelector('downloadBtn');

            const c_width = 400;
            const c_height = 600;

            canvas.width    = c_width;
            canvas.height  = c_height;

            const ctx    = canvas.getContext('2d');

            ctx.imageSmoothingEnabled = true;

            const qrImg     = new Image();
            const headerImg = new Image();
            

            ctx.fillStyle = "#FFFFFF";
            ctx.fillRect(0, 0, c_width, c_height);
            
            ctx.strokeStyle = 'black'; // Or any other color like '#FF0000' for red
            ctx.lineWidth = 5; 
            ctx.strokeRect(0, 0, c_width, c_height);

            qrImg.onload = ()=>{

                ctx.drawImage(qrImg, 300, 10, 80, 80);
                
                ctx.fillStyle           = 'black'; // Set fill color for the text
                ctx.font                = "14px Arial";

                let id_text             = String("{{$coupon->id}}").padStart(4, '0');
                
                ctx.fillText('#'+id_text,300,110);
            }
        
        
            headerImg.onload =  ()=>{

                const imageAspectRatio = headerImg.width / headerImg.height;
                const canvasAspectRatio = canvas.width / canvas.height;

                let newWidth;
                let newHeight;

                if (imageAspectRatio > canvasAspectRatio) {
                    // Image is wider than canvas, fit by width
                    newWidth = canvas.width;
                    newHeight = canvas.width / imageAspectRatio;
                } else {
                    // Image is taller than canvas, fit by height
                    newHeight = canvas.height;
                    newWidth = canvas.height * imageAspectRatio;
                }

                ctx.drawImage(headerImg, 10, 10, newWidth * .7,newHeight *.7);
                
                ctx.fillStyle           = 'black'; // Set fill color for the text
                ctx.font                = "16px Arial";

                @if($coupon->amount && $coupon->quantity <= 0)
                let amount_text             = "Amount ( P {{number_format($coupon->amount,2)}} )";
                
                ctx.fillText(amount_text, 115, 75 );

                @elseif($coupon->quantity && $coupon->amount <=0 )

                    let quantity_text           = "Quantity ( {{number_format($coupon->quantity,2)}} Ltrs )";
                    
                    ctx.fillText(quantity_text, 115, 75 );

                @endif
                
            }
            
            qrImg.src       = "/qrcode?d={{ url('/coupon/claim/'.$coupon->id.'/'.$coupon->code ) }}";
            headerImg.src   = "/storage/sys_images/header.png";
            

            downloadBtn.onclick = () => {
                const imageURL = canvas.toDataURL('image/png'); // For PNG format

                const downloadLink = document.createElement('a');

                downloadLink.href = imageURL;
                downloadLink.download = 'Coupon - {{$coupon->id}}'; // Or .jpg

                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            }
        </script>
    </body>
</html