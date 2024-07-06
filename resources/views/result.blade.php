<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate Details</title>
</head>
<body>
    <h1>Certificate Details</h1>
    <p><strong>Serial Number:</strong> {{ $serial_number }}</p>
    <p><strong>Issuer:</strong> {{ $issuer }}</p>
    <p><strong>Valid From:</strong> {{ $valid_from }}</p>
    <p><strong>Valid To:</strong> {{ $valid_to }}</p>
    <p><strong>OCSP Status:</strong> {{ $ocsp_status }}</p>
    <p><strong>CRL Status:</strong> {{ $crl_status }}</p>
    <a href="{{ route('upload.form') }}">Upload Another PDF</a>
</body>
</html>
