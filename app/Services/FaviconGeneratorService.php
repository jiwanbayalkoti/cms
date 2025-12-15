<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FaviconGeneratorService
{
    /**
     * Convert hex color to RGB
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }

    /**
     * Generate a default favicon from company's first letter
     */
    public function generateDefaultFavicon(string $companyName, int $size = 32): string
    {
        if (!function_exists('imagecreatetruecolor')) {
            throw new \Exception('GD library is not available');
        }

        $firstLetter = strtoupper(substr($companyName, 0, 1));
        
        // Generate a color based on the letter (for consistency)
        $colors = [
            'A' => '#3B82F6', 'B' => '#8B5CF6', 'C' => '#EC4899', 'D' => '#F59E0B',
            'E' => '#10B981', 'F' => '#EF4444', 'G' => '#06B6D4', 'H' => '#6366F1',
            'I' => '#F97316', 'J' => '#84CC16', 'K' => '#14B8A6', 'L' => '#A855F7',
            'M' => '#3B82F6', 'N' => '#8B5CF6', 'O' => '#EC4899', 'P' => '#F59E0B',
            'Q' => '#10B981', 'R' => '#EF4444', 'S' => '#06B6D4', 'T' => '#6366F1',
            'U' => '#F97316', 'V' => '#84CC16', 'W' => '#14B8A6', 'X' => '#A855F7',
            'Y' => '#3B82F6', 'Z' => '#8B5CF6'
        ];
        
        $bgColorHex = $colors[$firstLetter] ?? '#3B82F6';
        $bgColor = $this->hexToRgb($bgColorHex);
        
        // Create image
        $image = imagecreatetruecolor($size, $size);
        
        // Enable alpha blending
        imagealphablending($image, false);
        imagesavealpha($image, true);
        
        // Create transparent background
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $transparent);
        
        // Fill with background color
        $bgColorResource = imagecolorallocate($image, $bgColor[0], $bgColor[1], $bgColor[2]);
        imagefilledrectangle($image, 0, 0, $size - 1, $size - 1, $bgColorResource);
        
        // Add text (letter) - using built-in font with better positioning
        $white = imagecolorallocate($image, 255, 255, 255);
        
        // Use larger built-in font (5 is the largest)
        $fontSize = 5;
        $textWidth = imagefontwidth($fontSize) * strlen($firstLetter);
        $textHeight = imagefontheight($fontSize);
        
        // Center the text
        $x = (int)(($size - $textWidth) / 2);
        $y = (int)(($size - $textHeight) / 2) - 2; // Slight adjustment for better centering
        
        imagestring($image, $fontSize, $x, $y, $firstLetter, $white);
        
        // Save to storage
        $filename = 'companies/favicons/' . md5($companyName) . '.png';
        $path = storage_path('app/public/' . $filename);
        
        // Ensure directory exists
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Save image
        imagepng($image, $path);
        imagedestroy($image);
        
        return $filename;
    }

    /**
     * Generate favicon from uploaded file
     */
    public function generateFromFile($file, string $companyName): string
    {
        if (!function_exists('imagecreatefromstring')) {
            throw new \Exception('GD library is not available');
        }

        $sourceImage = imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (!$sourceImage) {
            throw new \Exception('Could not create image from file');
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        $size = 32;
        
        // Create new image with desired size
        $image = imagecreatetruecolor($size, $size);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        
        // Resize
        imagecopyresampled($image, $sourceImage, 0, 0, 0, 0, $size, $size, $sourceWidth, $sourceHeight);
        imagedestroy($sourceImage);
        
        // Save to storage
        $filename = 'companies/favicons/' . md5($companyName . time()) . '.png';
        $path = storage_path('app/public/' . $filename);
        
        // Ensure directory exists
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        // Save image
        imagepng($image, $path);
        imagedestroy($image);
        
        return $filename;
    }

    /**
     * Get favicon URL for company
     */
    public function getFaviconUrl($company): string
    {
        if ($company->favicon && Storage::disk('public')->exists($company->favicon)) {
            return asset('storage/' . $company->favicon);
        }
        
        // Generate default if not exists
        if ($company->name) {
            try {
                $faviconPath = $this->generateDefaultFavicon($company->name);
                $company->update(['favicon' => $faviconPath]);
                return asset('storage/' . $faviconPath);
            } catch (\Exception $e) {
                // Fallback if generation fails
                return asset('favicon.ico');
            }
        }
        
        // Fallback
        return asset('favicon.ico');
    }
}

