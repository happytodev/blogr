<?php

namespace Happytodev\Blogr\Services;

/**
 * WaveSeparatorService - Intelligent wave configuration based on adjacent blocks
 * 
 * Generates clean SVG waves with auto-calculated colors based on the gradient colors
 * of blocks above and below the wave separator.
 */
class WaveSeparatorService
{
    /**
     * Calculate wave configuration based on adjacent block colors
     * 
     * @param array|null $previousBlock Block data before wave separator (or null if first)
     * @param array|null $nextBlock Block data after wave separator (or null if last)
     * @param string $mode 'auto' or 'manual'
     * @param array $manualConfig Manual configuration (used only in manual mode)
     * 
     * @return array Configuration with wave colors and layers
     */
    public static function calculateWaveConfig(
        ?array $previousBlock = null,
        ?array $nextBlock = null,
        string $mode = 'auto',
        array $manualConfig = []
    ): array {
        if ($mode === 'manual' && !empty($manualConfig)) {
            return self::buildManualConfig($manualConfig);
        }

        // Auto mode: calculate based on adjacent blocks
        $prevColor = self::extractGradientColor($previousBlock, 'start');
        $nextColor = self::extractGradientColor($nextBlock, 'end');

        // Fallback to defaults if colors not found
        $prevColor = $prevColor ?? '#667eea'; // Default: purple
        $nextColor = $nextColor ?? '#f093fb'; // Default: pink

        return self::buildAutoConfig($prevColor, $nextColor);
    }

    /**
     * Extract gradient color from block data
     * 
     * @param array|null $block Block data
     * @param string $position 'start' (from) or 'end' (to)
     * 
     * @return string|null Color hex value or null
     */
    public static function extractGradientColor(?array $block, string $position = 'start'): ?string
    {
        if (empty($block) || !is_array($block)) {
            return null;
        }

        $data = $block['data'] ?? [];

        // Check if block has gradient background
        if (($data['background_type'] ?? null) !== 'gradient') {
            return null;
        }

        if ($position === 'start') {
            return $data['gradient_from'] ?? null;
        }

        return $data['gradient_to'] ?? null;
    }

    /**
     * Extract the color at the edge of a block based on gradient direction or solid color
     * This intelligently selects the color that appears at the "touching point" of adjacent blocks
     * 
     * For a transition AFTER a block, we want the color that appears at the bottom of that block
     * For a transition BEFORE a block, we want the color that appears at the top of that block
     * 
     * @param array|null $block Block data
     * @param string $edge 'top' or 'bottom' - which edge of the block we're transitioning from
     * 
     * @return string|null Color hex value or null
     */
    public static function extractEdgeColor(?array $block, string $edge = 'bottom'): ?string
    {
        if (empty($block) || !is_array($block)) {
            return null;
        }

        $data = $block['data'] ?? [];
        $backgroundType = $data['background_type'] ?? 'none';

        // Handle solid color backgrounds
        if ($backgroundType === 'color') {
            return $data['background_color'] ?? null;
        }

        // Handle gradient backgrounds
        if ($backgroundType !== 'gradient') {
            return null;
        }

        $gradientDir = $data['gradient_direction'] ?? 'to-br';
        $gradientFrom = $data['gradient_from'] ?? '#667eea';
        $gradientTo = $data['gradient_to'] ?? '#764ba2';

        // Map gradient directions to determine which color appears at which edge
        // Note: CSS gradient directions indicate where the end color appears
        // 'to-b' = gradient goes downward, so TO color is at bottom
        // 'to-br' = TO color is at bottom-right
        // 'to-r' = TO color is at right side
        // 'circle' (radial) = treat as if to-br for simplicity

        if ($edge === 'bottom') {
            // Bottom edge: we want the color that appears at the bottom
            return match ($gradientDir) {
                'to-b', 'to-br', 'to-bl' => $gradientTo,      // Bottom colors: use TO
                'to-t', 'to-tr', 'to-tl' => $gradientFrom,    // Top colors: use FROM
                'to-r', 'to-l' => self::blendColors($gradientFrom, $gradientTo, 0.7), // Side colors: blend toward TO
                'circle', 'radial' => $gradientTo,            // Radial: center-ish, use TO
                default => $gradientTo,                       // Default to TO for bottom
            };
        } else { // top edge
            // Top edge: we want the color that appears at the top
            return match ($gradientDir) {
                'to-t', 'to-tr', 'to-tl' => $gradientTo,      // Top colors: use TO
                'to-b', 'to-br', 'to-bl' => $gradientFrom,    // Bottom colors: use FROM
                'to-r', 'to-l' => self::blendColors($gradientFrom, $gradientTo, 0.3), // Side colors: blend toward FROM
                'circle', 'radial' => $gradientFrom,          // Radial: edge, use FROM
                default => $gradientFrom,                     // Default to FROM for top
            };
        }
    }

    /**
     * Build wave configuration for auto mode
     * 
     * Creates a smooth transition between two colors
     * 
     * @param string $fromColor Start color (from previous block)
     * @param string $toColor End color (from next block)
     * 
     * @return array Configuration
     */
    private static function buildAutoConfig(string $fromColor, string $toColor): array
    {
        // Calculate a smooth blend between the two colors at 50%
        $blendedColor = self::blendColors($fromColor, $toColor, 0.5);

        return [
            'mode' => 'auto',
            'layers' => [
                [
                    'colorLight' => $fromColor,
                    'colorDark' => $toColor,
                    'opacity' => 1.0,
                ],
            ],
            'waveStyle' => 'wave-3',
            'waveAmplitude' => 'medium',
        ];
    }

    /**
     * Build wave configuration for manual mode
     * 
     * @param array $config Manual configuration
     * 
     * @return array Configuration
     */
    private static function buildManualConfig(array $config): array
    {
        return [
            'mode' => 'manual',
            'layers' => [
                [
                    'colorLight' => $config['wave_color_light'] ?? '#d946ef',
                    'colorDark' => $config['wave_color_dark'] ?? '#ec4899',
                    'opacity' => 1.0,
                ],
            ],
            'waveStyle' => $config['wave_style'] ?? 'wave-3',
            'waveAmplitude' => $config['wave_amplitude'] ?? 'medium',
        ];
    }

    /**
     * Blend two colors together
     * 
     * @param string $color1 First color hex (e.g., '#667eea')
     * @param string $color2 Second color hex (e.g., '#f093fb')
     * @param float $ratio Blend ratio 0-1 (0 = color1, 1 = color2)
     * 
     * @return string Blended color hex
     */
    public static function blendColors(string $color1, string $color2, float $ratio = 0.5): string
    {
        // Normalize ratio
        $ratio = max(0, min(1, $ratio));

        // Convert hex to RGB
        $rgb1 = self::hexToRgb($color1);
        $rgb2 = self::hexToRgb($color2);

        if (!$rgb1 || !$rgb2) {
            return $color1; // Fallback to first color if conversion fails
        }

        // Blend each channel
        $r = (int)(($rgb1['r'] * (1 - $ratio)) + ($rgb2['r'] * $ratio));
        $g = (int)(($rgb1['g'] * (1 - $ratio)) + ($rgb2['g'] * $ratio));
        $b = (int)(($rgb1['b'] * (1 - $ratio)) + ($rgb2['b'] * $ratio));

        return self::rgbToHex($r, $g, $b);
    }

    /**
     * Convert hex color to RGB array
     * 
     * @param string $hex Color hex (e.g., '#667eea')
     * 
     * @return array|null RGB array or null on failure
     */
    private static function hexToRgb(string $hex): ?array
    {
        // Remove '#' if present
        $hex = ltrim($hex, '#');

        if (strlen($hex) !== 6) {
            return null;
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Convert RGB to hex color
     * 
     * @param int $r Red channel 0-255
     * @param int $g Green channel 0-255
     * @param int $b Blue channel 0-255
     * 
     * @return string Hex color
     */
    private static function rgbToHex(int $r, int $g, int $b): string
    {
        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT)
               . str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
               . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get wave path definitions
     * 
     * @return array Wave paths indexed by style
     */
    public static function getWavePaths(): array
    {
        return [
            'wave' => 'M0,50 Q360,20 720,50 T1440,50 L1440,120 L0,120 Z',
            'wave-2' => 'M0,60 Q180,30 360,60 Q540,30 720,60 Q900,30 1080,60 Q1260,30 1440,60 L1440,120 L0,120 Z',
            'wave-3' => 'M0,70 Q120,40 240,70 Q360,40 480,70 Q600,40 720,70 Q840,40 960,70 Q1080,40 1200,70 Q1320,40 1440,70 L1440,120 L0,120 Z',
            'curve' => 'M0,60 Q720,30 1440,60 L1440,120 L0,120 Z',
        ];
    }

    /**
     * Adjust wave path based on amplitude
     * 
     * @param string $wavePath Original wave path
     * @param string $amplitude 'high', 'medium', or 'low'
     * 
     * @return string Adjusted wave path
     */
    public static function adjustWaveAmplitude(string $wavePath, string $amplitude = 'medium'): string
    {
        if ($amplitude === 'high') {
            // More dramatic wave
            $wavePath = str_replace(',70 Q', ',50 Q', $wavePath);
            $wavePath = str_replace(',40', ',20', $wavePath);
        } elseif ($amplitude === 'low') {
            // Subtle wave
            $wavePath = str_replace(',70 Q', ',85 Q', $wavePath);
            $wavePath = str_replace(',40', ',55', $wavePath);
        }
        // 'medium' stays as is

        return $wavePath;
    }
}
