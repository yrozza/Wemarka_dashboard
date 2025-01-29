<?php

namespace App\Http\Controllers;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QRCodeController extends Controller
{
    public function generateQRCode($orderId)
    {
        // Generate the URL for the order details
        $url = url("/api/orders/{$orderId}");

        // Generate the QR code as a PNG
        $qrCode = QrCode::format('svg') // Ensure PNG format
            ->size(500)       // Adjust size for better clarity
            ->margin(10)      // Add margin
            ->errorCorrection('H') // High error correction level
            ->generate($url);

        // Return the QR code image as a response
        return response($qrCode)
            ->header('Content-Type', 'image/svg+xml');
    }
    public function showOrderQRCode($orderId)
    {
        // Generate the URL for the order details
        $url = url("/api/orders/{$orderId}");

        // Generate the QR code
        $qrCode = QrCode::size(500)
            ->margin(10)
            ->errorCorrection('H')
            ->generate($url);

        // Pass the QR code data to the Blade view
        return view('qrcode.show', compact('qrCode'));
    }
}

    



