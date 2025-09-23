<html>
    <head>
        <title>Coupon - {{$coupon->id}}</title>
    </head>
    <body>
        <canvas id="canvas" style="border: solid 1px #000000"></canvas>

        

        <script>
            
            const canvas = document.querySelector('#canvas');
            const c_width = 400;
            const c_height = 120;

            canvas.width    = c_width;
            canvas.height  = c_height;

            const ctx    = canvas.getContext('2d');

            ctx.imageSmoothingEnabled = true;

            const qrImg     = new Image();
            const headerImg = new Image();
            

            ctx.fillStyle = "#FFFFFF";
            ctx.fillRect(0, 0, c_width, c_height);

            qrImg.onload = ()=>{

                ctx.drawImage(qrImg, 10, 10, 80, 80);
                
                ctx.fillStyle           = 'black'; // Set fill color for the text
                ctx.font                = "14px Arial";

                let id_text             = String("{{$coupon->id}}").padStart(4, '0');
                
                ctx.fillText('#'+id_text,25,110);
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

                ctx.drawImage(headerImg, 110, 10, newWidth * .7,newHeight *.7);
                
                ctx.fillStyle           = 'black'; // Set fill color for the text
                ctx.font                = "14px Arial";

                let amount_text             = "Fuel Coupon ( P {{number_format($coupon->amount,2)}} )";
                
                ctx.fillText(amount_text, 10+90+10+75, 100 );
            }
            
            qrImg.src       = "/qrcode?d={{ url('/coupon/claim/'.$coupon->id.'/'.$coupon->code ) }}";
            headerImg.src   = "/storage/sys_images/header.png";
            
        </script>
    </body>
</html