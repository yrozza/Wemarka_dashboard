<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order PDF</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .order-details {
            margin-bottom: 20px;
        }
        .products {
            margin-top: 20px;
        }
        .products table {
            width: 100%;
            border-collapse: collapse;
        }
        .products th, .products td {
            padding: 8px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <h1>Order #{{ $orderNumber }}</h1>
    <div class="order-details">
        <p><strong>Client Name:</strong> {{ $clientName }}</p>
        <p><strong>Client Phone:</strong> {{ $clientPhone }}</p>
        <p><strong>Shipping Address:</strong> {{ $shippingAddress }}</p>
        <p><strong>Total Items:</strong> {{ $totalItems }}</p>
        <p><strong>Total Price:</strong> {{ $totalPrice }}</p>
        <p><strong>Shipping Fee:</strong> {{ $shippingFee }}</p>
        <p><strong>Discount:</strong> {{ $discountInfo }}</p>
        <p><strong>Final Total:</strong> {{ $finalTotal }}</p>
        <p><strong>Client Notes:</strong> {{ $clientNotes }}</p>
        <p><strong>Order Time:</strong> {{ $orderTime }}</p>
        <p><strong>Order Number:</strong> {{ $orderNumber }}</p>
    </div>

    <div class="products">
        <h2>Products</h2>
        <table>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
            </tr>
            @foreach($productDetails as $item)
                <tr>
                    <td>{{ $item['Product'] }}</td>
                    <td>{{ $item['Quantity'] }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</body>
</html>
