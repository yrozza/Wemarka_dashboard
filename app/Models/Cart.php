<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cart extends Model
{
    protected $fillable = [
        'cart_id',      // Foreign key to the cart
        'varient_id',   // Foreign key to the variant
        'quantity',     // Quantity of the variant
        'price',        // Price of the variant
    ];
    public function checkout()
    {
        if ($this->cartItems()->count() === 0) {
            throw new \Exception('Cart is empty and cannot be checked out.');
        }

        return DB::transaction(function () {
            // Create the order
            $order = Order::create([
                'client_id' => $this->client_id,
                'cart_id' => $this->id,
                'total_price' => $this->cartItems->sum(fn($item) => $item->quantity * $item->varient->price),
                'status' => 'pending', // Default status
                'shipping_status' => 'not_shipped', // Default shipping status
            ]);

            // Transfer cart items to order items
            foreach ($this->cartItems as $cartItem) {
                $order->orderItems()->create([
                    'varient_id' => $cartItem->varient_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->varient->price,
                ]);
            }

            // Update cart status
            $this->update(['status' => 'checked_out']);

            return $order;
        });
    }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function variant()
    {
        return $this->belongsTo(Varient::class);
    }

    // One Cart has many CartItems
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
}
