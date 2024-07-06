<?php

namespace App\Services;

use Smalot\PdfParser\Parser;
use phpseclib3\File\X509;

class CertificateService
{
    public function extractCertificateAttributes($pdfPath)
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($pdfPath);
        $details = $pdf->getDetails();
dd($details);
        // Assuming the details contain the digital signature info
        // This might need adjustment based on the structure of your PDF
        if (!isset($details['EmbeddedFiles'])) {
            return null;
        }

        $embeddedFiles = $details['EmbeddedFiles'];
        foreach ($embeddedFiles as $file) {
            if (isset($file['/Type']) && $file['/Type'] == '/Sig') {
                $signature = $file['/Contents'];
                $cert = base64_decode($signature);
                $x509 = new X509();
                $certInfo = $x509->loadX509($cert);

                if ($certInfo) {
                    $serialNumber = $certInfo['tbsCertificate']['serialNumber']->toString();
                    $validFrom = $certInfo['tbsCertificate']['validity']['notBefore']['utcTime'];
                    $validTo = $certInfo['tbsCertificate']['validity']['notAfter']['utcTime'];
                    $ocspUrl = null;
                    $crlUrl = null;

                    if (isset($certInfo['tbsCertificate']['extensions'])) {
                        foreach ($certInfo['tbsCertificate']['extensions'] as $extension) {
                            if ($extension['extnId'] === 'id-pe-authorityInfoAccess') {
                                foreach ($extension['extnValue'] as $accessDescription) {
                                    if ($accessDescription['accessMethod'] === 'id-ad-ocsp') {
                                        $ocspUrl = $accessDescription['accessLocation']['uniformResourceIdentifier'];
                                    }
                                }
                            }

                            if ($extension['extnId'] === 'id-ce-cRLDistributionPoints') {
                                foreach ($extension['extnValue'] as $distributionPoint) {
                                    if (isset($distributionPoint['distributionPoint']['fullName'])) {
                                        foreach ($distributionPoint['distributionPoint']['fullName'] as $fullName) {
                                            if ($fullName['uniformResourceIdentifier']) {
                                                $crlUrl = $fullName['uniformResourceIdentifier'];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    return [
                        'serial_number' => $serialNumber,
                        'valid_from' => $validFrom,
                        'valid_to' => $validTo,
                        'ocsp_url' => $ocspUrl,
                        'crl_url' => $crlUrl,
                    ];
                }
            }
        }

        return null;
    }
}
