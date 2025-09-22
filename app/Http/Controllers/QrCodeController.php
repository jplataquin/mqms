<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeController extends Controller{

    public function index(Request $request){

        //Data to encode in the QR code
        $data = $request->input('d') ?? null;

        if($data == null){
            $data = '{message:"No Data"}';
        }

        $size = (int) $request->input('s') ?? 0;

        if(!$size || $size < 400){
            $size = 400;
        }

        $renderer = new ImageRenderer(
            new RendererStyle(
                $size, //size px
                0 //margin
            ),
            new ImagickImageBackEnd()
        );

        
        
        // Create a Writer instance
        $writer = new Writer($renderer);

     


        return response($writer->writeString($data))->header('Content-type','image/png');

    }
  
}
