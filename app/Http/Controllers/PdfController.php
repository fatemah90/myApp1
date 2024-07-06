<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
    public function extractDetails(Request $request)
    {
        $request->validate([
            'pdf' => 'required|mimes:pdf|max:10000',
        ]);

        // Store the uploaded PDF
        $pdfPath = $request->file('pdf')->store('uploads');
        $pdfFullPath = storage_path('app/' . $pdfPath);

        // Parse the PDF to extract embedded objects
        $parser = new Parser();
        $pdf = $parser->parseFile($pdfFullPath);

        // Attempt to extract embedded certificate
        $certificateData = $this->extractEmbeddedCertificate($pdfFullPath);

        if ($certificateData) {
            // Save the certificate data to a temporary file
            $certPath = storage_path('app/uploads/certificate.crt');
            file_put_contents($certPath, $certificateData);

            // Use OpenSSL to parse the certificate
            $certDetails = $this->parseCertificate($certPath);

            return response()->json($certDetails);
        } else {
            return response()->json(['error' => 'Certificate not found in PDF'], 404);
        }
    }

    private function extractEmbeddedCertificate($pdfFullPath)
    {
        $fpdi = new Fpdi();
        $pageCount = $fpdi->setSourceFile($pdfFullPath);

        for ($i = 1; $i <= $pageCount; $i++) {
            $pageId = $fpdi->importPage($i);
            $fpdi->addPage();
            $fpdi->useTemplate($pageId);

            $pdfContent = $fpdi->getImportedPages();
            foreach ($pdfContent as $content) {
                if (strpos($content, '-----BEGIN CERTIFICATE-----') !== false) {
                    $start = strpos($content, '-----BEGIN CERTIFICATE-----');
                    $end = strpos($content, '-----END CERTIFICATE-----') + strlen('-----END CERTIFICATE-----');
                    return substr($content, $start, $end - $start);
                }
            }
        }

        return null;
    }

    private function parseCertificate($certPath)
    {
        $output = [];
        $returnVar = 0;
        // Run the OpenSSL command to parse the certificate
        exec("openssl x509 -in \"$certPath\" -noout -text", $output, $returnVar);

        if ($returnVar === 0) {
            return $this->extractCertificateDetails(implode("\n", $output));
        } else {
            return ['error' => 'Failed to parse certificate'];
        }
    }

    private function extractCertificateDetails($certText)
    {
        // Extract details from the certificate text using regular expressions
        $details = [];

        // Example regex patterns (these may need adjustments)
        if (preg_match('/Serial Number:\s*([^\n]+)/', $certText, $matches)) {
            $details['serial_number'] = trim($matches[1]);
        }
        if (preg_match('/Issuer:\s*([^\n]+)/', $certText, $matches)) {
            $details['issuer'] = trim($matches[1]);
        }
        if (preg_match('/Validity\n\s*Not Before:\s*([^\n]+)\n\s*Not After :\s*([^\n]+)/', $certText, $matches)) {
            $details['valid_from'] = trim($matches[1]);
            $details['valid_to'] = trim($matches[2]);
        }
        if (preg_match('/Subject:\s*([^\n]+)/', $certText, $matches)) {
            $details['subject'] = trim($matches[1]);
        }
        if (preg_match('/X509v3 CRL Distribution Points:\n\s*URI:(\S+)/', $certText, $matches)) {
            $details['crl_distribution'] = trim($matches[1]);
        }
        if (preg_match('/Authority Information Access:\n\s*CA Issuers - URI:(\S+)/', $certText, $matches)) {
            $details['authority_info'] = trim($matches[1]);
        }

        return $details;
    }
}
