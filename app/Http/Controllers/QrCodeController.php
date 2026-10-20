<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\GDLibRenderer;

class QrCodeController extends Controller
{
    public function index(Request $request)
    {
        // Data to encode in the QR code
        $data = $request->input('d') ?? null;

        if ($data === null) {
            $data = '{message:"No Data"}';
        }

        $size = (int) ($request->input('s') ?? 0);

        if (!$size || $size < 400) {
            $size = 400;
        }

        // Fallback autoloader in case composer classmap was stale
        if (!class_exists('BaconQrCode\Renderer\ImageRenderer')) {
            $baconPath = base_path('vendor/bacon/bacon-qr-code/src');
            if (file_exists($baconPath . '/Renderer/ImageRenderer.php')) {
                spl_autoload_register(function ($class) use ($baconPath) {
                    $prefix = 'BaconQrCode\\';
                    if (strncmp($prefix, $class, strlen($prefix)) === 0) {
                        $relativeClass = substr($class, strlen($prefix));
                        $file = $baconPath . '/' . str_replace('\\', '/', $relativeClass) . '.php';
                        if (file_exists($file)) {
                            require_once $file;
                        }
                    }
                });
            }
        }

        try {
            // 1. If imagick extension is loaded and ImagickImageBackEnd is available
            if (extension_loaded('imagick') && class_exists(ImagickImageBackEnd::class) && class_exists(ImageRenderer::class)) {
                $renderer = new ImageRenderer(
                    new RendererStyle($size, 0),
                    new ImagickImageBackEnd()
                );
                $writer = new Writer($renderer);
                return response($writer->writeString($data))->header('Content-Type', 'image/png');
            }

            // 2. If GD extension is loaded (standard in PHP) and GDLibRenderer is available
            if (extension_loaded('gd') && class_exists(GDLibRenderer::class)) {
                $renderer = new GDLibRenderer($size, 0);
                $writer = new Writer($renderer);
                return response($writer->writeString($data))->header('Content-Type', 'image/png');
            }

            // 3. SVG renderer (requires zero PHP extensions, pure PHP vector output)
            if (class_exists(ImageRenderer::class) && class_exists(SvgImageBackEnd::class)) {
                $renderer = new ImageRenderer(
                    new RendererStyle($size, 0),
                    new SvgImageBackEnd()
                );
                $writer = new Writer($renderer);
                return response($writer->writeString($data))->header('Content-Type', 'image/svg+xml');
            }
        } catch (\Throwable $e) {
            // Fallback to SVG if GD or Imagick failed at runtime
            try {
                if (class_exists(ImageRenderer::class) && class_exists(SvgImageBackEnd::class)) {
                    $renderer = new ImageRenderer(
                        new RendererStyle($size, 0),
                        new SvgImageBackEnd()
                    );
                    $writer = new Writer($renderer);
                    return response($writer->writeString($data))->header('Content-Type', 'image/svg+xml');
                }
            } catch (\Throwable $e2) {
                // Ignore and proceed to fallback SVG
            }
        }

        // Absolute fallback SVG placeholder if no renderer could be instantiated
        $escapedData = htmlspecialchars($data, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $fallbackSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}">
    <rect width="100%" height="100%" fill="#ffffff" stroke="#cccccc" stroke-width="2"/>
    <text x="50%" y="45%" text-anchor="middle" font-family="sans-serif" font-size="14" fill="#333333">QR Code</text>
    <text x="50%" y="55%" text-anchor="middle" font-family="sans-serif" font-size="10" fill="#666666">{$escapedData}</text>
</svg>
SVG;
        return response($fallbackSvg)->header('Content-Type', 'image/svg+xml');
    }
}
