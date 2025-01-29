<!-- resources/views/qrcode/show.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order QR Code</title>
</head>
<body>
    <h1>Scan this QR Code to view the order details</h1>
    <div>
        <!-- Display the QR code -->
        {!! $qrCode !!}
    </div>
</body>
</html>