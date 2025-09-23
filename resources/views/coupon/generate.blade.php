<html>
    <head>
        <title>Coupon</title>
    </head>
    <body>
        <canvas id="canvas" style="border: solid 1px #000000"></canvas>

        

        <script>
            
            const canvas = document.querySelector('#canvas');
            const c_width = 300;
            const c_height = 200;

            canvas.width    = c_width;
            canvas.height  = c_height;

            const ctx    = canvas.getContext('2d');

            ctx.imageSmoothingEnabled = true;

            const qrImg     = new Image();
            const headerImg = new Image();
            

            qrImg.onload = ()=>{

                ctx.drawImage(qrImg, 10, 10, 90, 90);
                
                ctx.fillStyle           = 'black'; // Set fill color for the text
                ctx.font                = "14px Arial";

                let id_text             = String("{{$coupon->id}}").padStart(4, '0');
                
                ctx.fillText(id_text,60,110);
            }


            headerImg.onload =  ()=>{
                ctx.drawImage(headerImg, 110, 10, 200,80);
                
                ctx.fillStyle           = 'black'; // Set fill color for the text
                ctx.font                = "14px Arial";

                let amount_text             = "P {{number_format($coupon->amount,2)}}";
                
                ctx.fillText(amount_text, 10+90+10+75, 110 );
            }
            
            qrImg.src       = "/qrcode?d={{ url('/coupon/claim/'.$coupon->id.'/'.$coupon->code ) }}";
            headerImg.src   = "/storage/sys_images/header_with_address.png";
            
        </script>
    </body>
</html