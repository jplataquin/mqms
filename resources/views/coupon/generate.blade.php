<html>
    <head>
        <title>Coupon - {{$coupon->id}}</title>
    </head>
    <body>
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