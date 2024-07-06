<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use Spatie\PdfToText\Pdf;

class CertificateController extends Controller
{
    public function extractDetails(Request $request)
    {
        // dd($request);
        $request->validate([
            'pdf' => 'required|mimes:pdf|max:10000',
        ]);

        $pdfPath = $request->file('pdf')->getPathName();
        $path = 'E:/Program Files/Git/mingw64/bin/pdftotext';
// echo Pdf::getText( storage_path('app/uploads/testSigned.pdf'), $path);
// die()  ;      
// $pdfPath = storage_path('app/uploads/test.pdf');;
// dd($pdfPath);
        // Extract text from PDF using spatie/pdf-to-text
        // $text = (new Pdf())->setPdf($pdfPath,$path)->text();
        $text =Pdf::getText( storage_path('app/uploads/testSigned.pdf'), $path);

        // Use regex or parsing logic to extract certificate details
        $serialNumber = $this->extractSerialNumber($text);
        $issuer = $this->extractIssuer($text);
        $validFrom = $this->extractValidFrom($text);
        $validTo = $this->extractValidTo($text);

        // Return extracted details (for demonstration purposes)
        return response()->json([
            'serial_number' => $serialNumber,
            'issuer' => $issuer,
            'valid_from' => $validFrom,
            'valid_to' => $validTo,
        ]);
    }

    private function extractSerialNumber($text)
    {
        // Implement your regex or parsing logic here
        preg_match('/Serial Number:\s*(\S+)/i', $text, $matches);
        return $matches[1] ?? 'N/A';
    }

    private function extractIssuer($text)
    {
        // Implement your regex or parsing logic here
        preg_match('/Issuer:\s*(.+)/i', $text, $matches);
        return $matches[1] ?? 'N/A';
    }

    private function extractValidFrom($text)
    {
        // Implement your regex or parsing logic here
        preg_match('/Valid From:\s*(.+)/i', $text, $matches);
        return $matches[1] ?? 'N/A';
    }

    private function extractValidTo($text)
    {
        // Implement your regex or parsing logic here
        preg_match('/Valid To:\s*(.+)/i', $text, $matches);
        return $matches[1] ?? 'N/A';
    }
}
