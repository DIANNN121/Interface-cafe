<?php
/*
 * Simplified QR Code Generator for Kafe Ndalem
 * This uses Google Charts API as a fallback solution
 */

class QRcode
{
    public static function png($text, $outfile = false, $level = 'L', $size = 3, $margin = 4)
    {
        // Use Google Charts API to generate QR code
        $url = 'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=' . urlencode($text);

        $qrData = @file_get_contents($url);

        if ($qrData === false) {
            // Fallback: create a simple placeholder image
            $img = imagecreate(300, 300);
            $white = imagecolorallocate($img, 255, 255, 255);
            $black = imagecolorallocate($img, 0, 0, 0);
            imagefilledrectangle($img, 0, 0, 300, 300, $white);
            imagestring($img, 5, 50, 140, 'QR: ' . substr($text, 0, 20), $black);

            if ($outfile) {
                imagepng($img, $outfile);
                imagedestroy($img);
                return;
            } else {
                header('Content-Type: image/png');
                imagepng($img);
                imagedestroy($img);
                exit;
            }
        }

        if ($outfile) {
            file_put_contents($outfile, $qrData);
        } else {
            header('Content-Type: image/png');
            echo $qrData;
            exit;
        }
    }
}
