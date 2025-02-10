<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 80%; margin: auto; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f4f4f4; }
        .summary { margin-top: 20px; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Order Report</h2>

        <table>
            <thead>
                <tr>
                    <th>Total Orders</th>
                    <th>Total Price</th>
                    <th>Total Shipping Fee</th>
                    <th>Total Packing Price</th>
                    <th>Total Cost Price</th>
                    <th>Net Profit</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $total_orders }}</td>
                    <td>${{ number_format($total_price, 2) }}</td>
                    <td>${{ number_format($total_shipping_fee, 2) }}</td>
                    <td>${{ number_format($total_packing_price, 2) }}</td>
                    <td>${{ number_format($total_cost_price, 2) }}</td>
                    <td>${{ number_format($net_profit, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

</body>
</html>
