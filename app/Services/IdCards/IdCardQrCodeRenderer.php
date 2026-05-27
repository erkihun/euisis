<?php

declare(strict_types=1);

namespace App\Services\IdCards;

use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * Generates a QR code for a given verification URL and returns it as a
 * base64-encoded PNG data URI (via GD) or an SVG string for embedding.
 *
 * The QR payload must be a safe, tokenized reference URL — never the raw
 * token hash or any PII.
 */
final class IdCardQrCodeRenderer
{
    /**
     * Returns a base64 data URI (SVG) suitable for use in an SVG <image> element.
     * Falls back to an empty string on error.
     *
     * @param  int  $pixelSize  Desired pixel side length of the QR image
     */
    public function asSvgDataUri(string $url, int $pixelSize = 96): string
    {
        try {
            $options = new QROptions;
            $options->outputInterface = QRMarkupSVG::class;
            $options->eccLevel = 'M';
            $options->svgAddXmlHeader = false;
            $options->drawLightModules = true;
            $options->connectPaths = true;

            $svg = (new QRCode($options))->render($url);

            // Wrap in a sized <svg> so the <image> element scales it correctly
            $wrapped = sprintf(
                '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" '
                .'width="%d" height="%d" viewBox="0 0 100 100">%s</svg>',
                $pixelSize,
                $pixelSize,
                $svg,
            );

            return 'data:image/svg+xml;base64,'.base64_encode($wrapped);
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Returns the raw SVG string for inline embedding inside another SVG.
     * The returned string is the inner content (no <?xml?> header).
     */
    public function asInlineSvgContent(string $url): string
    {
        try {
            $options = new QROptions;
            $options->outputInterface = QRMarkupSVG::class;
            $options->eccLevel = 'M';
            $options->svgAddXmlHeader = false;
            $options->drawLightModules = true;
            $options->connectPaths = true;

            return (new QRCode($options))->render($url);
        } catch (\Throwable) {
            return '';
        }
    }
}
