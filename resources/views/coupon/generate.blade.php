<html>
    <head>
        <title>Coupon</title>
    </head>
    <body>
        <canvas id="canvas"></canvas>

        
        <img width="300px" src="/qr_code?d={{ url('/coupon/claim/'.$coupon->id.'/'.$coupon->code ) }}"/>
    </body>
</html>