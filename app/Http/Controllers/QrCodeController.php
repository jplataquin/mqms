<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeController extends Controller{

    public function index(Request $request){

        $renderer = new ImageRenderer(
            new RendererStyle(
                400, //size px
                0 //margin
            ),
            new ImagickImageBackEnd()
        );

        //Data to encode in the QR code
        $data = $request->input('d') ?? null;

        if($data == null){
            $data = '{message:"No Data"}';
        }
        
        
        // Create a Writer instance
        $writer = new Writer($renderer);

     


        return response($writer->writeString($data))->header('Content-type','image/png');

    }
}
