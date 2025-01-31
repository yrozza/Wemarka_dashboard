<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Varient;
use App\Models\Order;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function getCustomOrderInfo(Request $request, $orderId)
    {
        try {
            // Fetch the order details
            $order = Order::select('id', 'client_name', 'client_phone', 'total_price', 'Cost_shipping_price', 'city_name', 'area_name', 'address', 'client_notes', 'created_at')
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

            // Prepare the response
            $response = [
                'Client Name' => $order->client_name,
                'Client Phone' => $order->client_phone,
                'Shipping address' => $shippingAddress,
                'Total items' => $totalItems,
                'Total price' => $order->total_price,
                'Shipping fee' => $order->Cost_shipping_price,
                'Total' => $order->total_price + $order->Cost_shipping_price,
                'Client notes' => $order->client_notes,
                'Order time' => $orderTime,
                'Order number' => $orderNumber,
                'Products' => $productDetails // Include product names with quantities
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Order not found or an error occurred.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }




    // Get an order by ID

    public function getOrderInfo(Request $request, $orderId)
    {
        try {
            // Fetch the order by ID, including necessary fields
            $order = Order::select('id', 'client_id', 'client_name', 'client_phone', 'additional_phone', 'total_price', 'city_name', 'area_name', 'address', 'client_notes')
            ->findOrFail($orderId); // Retrieve the order or throw an error if not found

            // Format the shipping address
            $shippingAddress = $order->city_name . ', ' . $order->area_name . ' - ' . $order->address;

            // Return the order with the selected data
            return response()->json([
                'Client Name' => $order->client_name,
                'Client Phonenumber' => $order->client_phone,
                'Additional Phone' => $order->additional_phone,
                'Shipping Address' => $shippingAddress,
                'Total price' => $order->total_price,
                'Client Notes' => $order->client_notes, // Include client notes if necessary
            ]);
        } catch (\Exception $e) {
            // Handle errors if the order is not found or any other exception
            return response()->json([
                'message' => 'Order not found or an error occurred.',
                'error' => $e->getMessage(),
            ], 404);
        }
    }



    

    public function getAllOrders(Request $request)
    {
        // Set the default number of orders per page
        $perPage = $request->query('per_page', 10); // 10 orders per page by default

        // Fetch paginated orders with their related order items
        $orders = Order::with('orderItems') // Eager load orderItems
        ->paginate($perPage);

        // Return the paginated orders using OrderResource
        return OrderResource::collection($orders);
    }


    public function getOrderById($id)
    {
        // Find the order by its ID and load related orderItems and client
        $order = Order::with('orderItems')->find($id);

        // Check if the order exists
        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        // Return the order using OrderResource
        return new OrderResource($order);
    }


    public function updateOrderStatus(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        // Find the order by ID
        $order = Order::with('orderItems')->find($id);

        // Check if the order exists
        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        try {
            // Begin transaction
            DB::beginTransaction();

            // If the status is being updated to "cancelled"
            if ($request->status === 'cancelled') {
                foreach ($order->orderItems as $orderItem) {
                    $variant = Varient::find($orderItem->varient_id);

                    if ($variant) {
                        // Restore stock for the variant
                        $variant->stock += $orderItem->quantity;
                        $variant->save();
                    }
                }

                // Update the order status to "cancelled"
                $order->status = 'cancelled';

                // Set the shipping status to "returned" when cancelled
                $order->shipping_status = 'returned';
                $order->save();

                // Commit the transaction
                DB::commit();

                return response()->json([
                    'message' => 'Order cancelled successfully. All items have been returned to stock.',
                    'order' => [
                        'id' => $order->id,
                        'client_id' => $order->client_id,
                        'status' => $order->status,
                        'shipping_status' => $order->shipping_status,
                    ],
                ], 200);
            }

            // Update the order status for non-cancellation scenarios
            $order->status = $request->status;

            // If status is completed, set shipping_status to "delivered"
            if ($request->status === 'completed') {
                $order->shipping_status = 'delivered';
            }

            $order->save();

            // Commit the transaction
            DB::commit();

            // Return message based on the status update
            $message = $request->status === 'completed' ? 'Item delivered' : 'Order status updated successfully.';

            return response()->json([
                'message' => $message,
                'order' => [
                    'id' => $order->id,
                    'client_id' => $order->client_id,
                    'status' => $order->status,
                    'shipping_status' => $order->shipping_status,
                ],
            ], 200);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update order status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function updateShippingStatus(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'shipping_status' => 'required|in:shipped,on_the_way,delivered,returned',
        ]);

        // Find the order by ID
        $order = Order::find($id);

        // Check if the order exists
        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        try {
            // Begin transaction
            DB::beginTransaction();

            // Handle the changes in shipping_status
            switch ($request->shipping_status) {
                case 'shipped':
                    $order->shipping_status = 'shipped';
                    $order->status = 'pending';  // Set status to "pending"
                    $message = 'The item is shipped, order status is now pending';
                    break;

                case 'on_the_way':
                    $order->shipping_status = 'on_the_way';
                    $order->status = 'pending';  // Set status to "pending"
                    $message = 'The item is on the way, order status is now pending';
                    break;

                case 'delivered':
                    $order->shipping_status = 'delivered';
                    $order->status = 'completed';  // Set status to "completed" when delivered
                    $message = 'Item delivered, order is now complete';
                    break;

                case 'returned':
                    $order->shipping_status = 'returned';
                    $order->status = 'cancelled';  // Set status to "cancelled" when returned
                    $message = 'Item returned, order is now cancelled';
                    break;

                default:
                    return response()->json(['message' => 'Invalid shipping status'], 400);
            }

            // Save the order with the updated shipping_status and status
            $order->save();

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => $message,
                'order' => [
                    'id' => $order->id,
                    'client_id' => $order->client_id,
                    'status' => $order->status,
                    'shipping_status' => $order->shipping_status,
                ],
            ], 200);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update shipping status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }











    // Delete an order by ID
    public function deleteOrder($orderId)
    {
        try {
            // Begin transaction to ensure atomic operations
            DB::beginTransaction();

            // Find the order by ID with its items
            $order = Order::with('orderItems')->find($orderId);

            // Check if the order exists
            if (!$order) {
                return response()->json(['message' => 'Order not found.'], 404);
            }

            // Restore stock for each order item
            foreach ($order->orderItems as $orderItem) {
                $variant = Varient::find($orderItem->varient_id); // Assuming Varient is the model for the variants table
                if ($variant) {
                    $variant->stock += $orderItem->quantity; // Restore the stock
                    $variant->save();
                }
            }

            // Delete order items
            $order->orderItems()->delete();

            // Delete the order
            $order->delete();

            // Commit the transaction
            DB::commit();

            return response()->json(['message' => 'Order deleted successfully.'], 200);
        } catch (\Exception $e) {
            // Rollback transaction if any exception occurs
            DB::rollBack();

            return response()->json([
                'message' => 'An error occurred while deleting the order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
