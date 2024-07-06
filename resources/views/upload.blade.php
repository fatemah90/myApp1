<!DOCTYPE html>
<html>
<head>
    <title>Extract Digital Signature</title>
</head>
<body>
    <h1>Upload PDF to Extract Digital Signature</h1>
    <form action="{{ route('upload.pdf') }}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="pdf" accept="application/pdf">
        <button type="submit">Upload</button>
    </form>
</body>
</html>
