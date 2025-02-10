<?php



namespace App\Http\Controllers;

use Mpdf\Mpdf;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
class PDFController extends Controller
{
    public function generatePdf($orderId)
    {
        try {
            // Fetch the order details
            $order = Order::select('id', 'client_name', 'client_phone', 'total_price', 'Cost_shipping_price', 'city_name', 'area_name', 'address', 'client_notes', 'created_at', 'is_discount', 'discount')
                ->with(['orderItems.varient.product']) // Load order items and related product details
                ->findOrFail($orderId);

            // Handle null values for shipping address
            $city = $order->city_name ?? 'Unprovided';
            $area = $order->area_name ?? 'Unprovided';
            $address = $order->address ?? 'Unprovided';

            // Format the shipping address
            $shippingAddress = "{$city}, {$area} - {$address}";

            // Calculate total items
            $totalItems = $order->orderItems->sum('quantity');

            // Format order number
            $orderNumber = "WM{$order->id}";

            // Format order time (day/month/year)
            $orderTime = $order->created_at->format('d/m/Y');

            // Collect product names with their quantities
            $productDetails = $order->orderItems->map(function ($item) {
                return [
                    'Product' => $item->varient->product->Product_name ?? 'Unprovided',
                    'Quantity' => $item->quantity
                ];
            });

            // Handle discount logic
            $discountInfo = 'Not Applied';
            $finalTotal = $order->total_price + $order->Cost_shipping_price;

            if ($order->is_discount && !is_null($order->discount)) {
                $discountInfo = $order->discount;
                $finalTotal -= $order->discount; // Apply discount
            }

            // Prepare data to pass to the view
            $data = [
                'clientName' => $order->client_name,
                'clientPhone' => $order->client_phone,
                'shippingAddress' => $shippingAddress,
                'totalItems' => $totalItems,
                'totalPrice' => $order->total_price,
                'shippingFee' => $order->Cost_shipping_price,
                'discountInfo' => $discountInfo,
                'finalTotal' => $finalTotal,
                'clientNotes' => $order->client_notes,
                'orderTime' => $orderTime,
                'orderNumber' => $orderNumber,
                'productDetails' => $productDetails // Include product names with quantities
            ];

            // Create new instance of mPDF
            $mpdf = new Mpdf();

            // Load the view and convert it to HTML
            $html = view('orders.pdf', $data)->render();

            // Write the HTML content into the PDF
            $mpdf->WriteHTML($html);

            // Output the PDF to the browser
            return $mpdf->Output('order_' . $order->id . '.pdf', 'D');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generating PDF',
                'error' => $e->getMessage()
            ], 404);
        }
    }




    public function generateReportPdf($data)
    {
        try {
            // Initialize mPDF
            $mpdf = new Mpdf();

            // Generate HTML from the Blade view (passing data correctly)
            $html = view('pdf.report', $data)->render();

            // Write HTML to PDF
            $mpdf->WriteHTML($html);

            // Output PDF (download the file)
            return response()->stream(
                function () use ($mpdf) {
                    $mpdf->Output('order_report.pdf', 'D'); // 'D' for download
                },
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="order_report.pdf"',
                ]
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while generating the report',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function generateOrderReportPdf($orderData)
    {
        try {
            $pdf = new \Mpdf\Mpdf();

            // Pass the entire data array to the Blade template
            $html = view('pdf.report', ['data' => $orderData])->render();

            $pdf->WriteHTML($html);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->Output('', 'S');
            }, 'order_report.pdf');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}


