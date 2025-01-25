<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Varient;
use App\Models\Order;9
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    // Get an order by ID
    

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
            $order->save();

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Order status updated successfully.',
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
