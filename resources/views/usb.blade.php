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
    </head>
    <body>
        <h1>Hello World</h1>
        <!--[if lt IE 7]>
            <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="#">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
        <input type="text" id="vendorId" />
        <button id="connectButton">Connect</button>
        <link rel="stylesheet" type="text/css" href="/" media="print" />
        <script>
            //let connectButton = document.querySelctor('#connectButton');

            let device;


          async function connect(){


                // device = await navigator.usb.requestDevice({ filters: [{ vendorId: vendorId.value }] }).then(device => {
                //     console.log(device);
                //     console.log('Product Name',device.productName);      // "Arduino Micro"
                //     console.log('Manufacturer',device.manufacturerName); // "Arduino LLC"

                //     return device.open();
                // }).catch(error => { console.error(error); });

                // return device;


                let device;

                navigator.usb.requestDevice({ filters: [{ vendorId: vendorId.value }] })
                .then(selectedDevice => {
                    device = selectedDevice;
                    return device.open(); // Begin a session.
                })
                .then(() => device.selectConfiguration(1)) // Select configuration #1 for the device.
                .then(() => device.claimInterface(2)) // Request exclusive control over interface #2.
                .then(() => device.controlTransferOut({
                    requestType: 'class',
                    recipient: 'interface',
                    request: 0x22,
                    value: 0x01,
                    index: 0x02})) // Ready to receive data
                .then(() => device.transferIn(5, 64)) // Waiting for 64 bytes of data from endpoint #5.
                .then(result => {
                    const decoder = new TextDecoder();
                    console.log('Received: ' + decoder.decode(result.data));
                })
                .catch(error => { console.error(error); });
            }
            
            connectButton.onclick = async () => {
                console.log('click');

                device = await connect();

                console.log(device);
           
            };
        </script>
    </body>
</html>