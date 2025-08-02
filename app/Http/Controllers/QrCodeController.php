<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeController extends Controller{

    public function index(){

        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new ImagickImageBackEnd()
        );

        $writer = new Writer($renderer);
        
        // Create a Writer instance
        $writer = new Writer($renderer);

        // Set the Content-Type header
        header('Content-Type: image/png');

        // Output the QR code image directly to the browser
        echo $writer->writeString($qrData);

    }
}
