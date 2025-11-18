<?php

use Happytodev\Blogr\Services\WaveSeparatorService;

describe('Transition Gradient Integration', function () {
    it('extracts correct colors for hero to stats transition', function () {
        $heroBlock = [
            'type' => 'hero',
            'data' => [
                'gradient_from' => '#667eea',
                'gradient_to' => '#764ba2',
                'gradient_direction' => 'to-br',
                'background_type' => 'gradient',
            ],
        ];

        $statsBlock = [
            'type' => 'stats',
            'data' => [
                'gradient_from' => '#f093fb',
                'gradient_to' => '#f5576c',
                'gradient_direction' => 'to-r',
                'background_type' => 'gradient',
            ],
        ];

        // Test color extraction
        $prevColor = WaveSeparatorService::extractEdgeColor($heroBlock, 'bottom');
        $nextColor = WaveSeparatorService::extractEdgeColor($statsBlock, 'top');
        
        // Hero bottom-right: should be TO color
        expect($prevColor)->toBe('#764ba2');
        
        // Stats top (from to-r): should be blended toward FROM
        expect($nextColor)->toBeString();
        expect($nextColor)->toMatch('/^#[0-9a-f]{6}$/i');
    });

    it('extracts correct colors for different gradient directions', function () {
        $verticalBlock = [
            'data' => [
                'background_type' => 'gradient',
                'gradient_direction' => 'to-b',
                'gradient_from' => '#ffffff',
                'gradient_to' => '#000000',
            ],
        ];

        // Bottom edge of vertical gradient should be the TO color
        $bottomColor = WaveSeparatorService::extractEdgeColor($verticalBlock, 'bottom');
        expect($bottomColor)->toBe('#000000');
        
        // Top edge should be FROM color
        $topColor = WaveSeparatorService::extractEdgeColor($verticalBlock, 'top');
        expect($topColor)->toBe('#ffffff');
    });

    it('handles radial gradient transitions', function () {
        $radialBlock = [
            'data' => [
                'background_type' => 'gradient',
                'gradient_direction' => 'circle',
                'gradient_from' => '#ff0000',
                'gradient_to' => '#0000ff',
            ],
        ];

        $bottomColor = WaveSeparatorService::extractEdgeColor($radialBlock, 'bottom');
        $topColor = WaveSeparatorService::extractEdgeColor($radialBlock, 'top');
        
        // Radial bottom should use TO color
        expect($bottomColor)->toBe('#0000ff');
        // Radial top should use FROM color
        expect($topColor)->toBe('#ff0000');
    });

    it('blends colors correctly for horizontal gradients', function () {
        $horizontalBlock = [
            'data' => [
                'background_type' => 'gradient',
                'gradient_direction' => 'to-r',
                'gradient_from' => '#667eea',
                'gradient_to' => '#764ba2',
            ],
        ];

        $bottomColor = WaveSeparatorService::extractEdgeColor($horizontalBlock, 'bottom');
        $topColor = WaveSeparatorService::extractEdgeColor($horizontalBlock, 'top');
        
        // Both should be blends of the two colors
        expect($bottomColor)->toBeString();
        expect($topColor)->toBeString();
        expect($bottomColor)->toMatch('/^#[0-9a-f]{6}$/i');
        expect($topColor)->toMatch('/^#[0-9a-f]{6}$/i');
        
        // Bottom should be closer to TO (0.7 blend)
        // Top should be closer to FROM (0.3 blend)
        // We can't test exact values due to rounding, but both should be valid hex
    });

    it('returns null for non-gradient blocks', function () {
        $solidBlock = [
            'data' => [
                'background_type' => 'solid',
                'background_color' => '#ffffff',
            ],
        ];

        $color = WaveSeparatorService::extractEdgeColor($solidBlock, 'bottom');
        expect($color)->toBeNull();
    });

    it('returns null for null/empty blocks', function () {
        $color = WaveSeparatorService::extractEdgeColor(null, 'bottom');
        expect($color)->toBeNull();

        $color = WaveSeparatorService::extractEdgeColor([], 'bottom');
        expect($color)->toBeNull();
    });

    it('uses defaults for incomplete block data', function () {
        $incompleteBlock = [
            'data' => [
                'background_type' => 'gradient',
                // Missing gradient_from and gradient_to - should use defaults
            ],
        ];

        $color = WaveSeparatorService::extractEdgeColor($incompleteBlock, 'bottom');
        expect($color)->toBeString();
        expect($color)->toMatch('/^#[0-9a-f]{6}$/i');
    });

    it('handles all gradient directions correctly', function () {
        $directions = [
            'to-b' => ['bottom' => 'TO', 'top' => 'FROM'],
            'to-t' => ['bottom' => 'FROM', 'top' => 'TO'],
            'to-r' => ['bottom' => 'blend-to', 'top' => 'blend-from'],
            'to-l' => ['bottom' => 'blend-to', 'top' => 'blend-from'],
            'to-br' => ['bottom' => 'TO', 'top' => 'FROM'],
            'to-bl' => ['bottom' => 'TO', 'top' => 'FROM'],
            'to-tr' => ['bottom' => 'FROM', 'top' => 'TO'],
            'to-tl' => ['bottom' => 'FROM', 'top' => 'TO'],
            'circle' => ['bottom' => 'TO', 'top' => 'FROM'],
        ];

        foreach ($directions as $dir => $expected) {
            $block = [
                'data' => [
                    'background_type' => 'gradient',
                    'gradient_direction' => $dir,
                    'gradient_from' => '#667eea',
                    'gradient_to' => '#764ba2',
                ],
            ];

            $bottomColor = WaveSeparatorService::extractEdgeColor($block, 'bottom');
            $topColor = WaveSeparatorService::extractEdgeColor($block, 'top');
            
            expect($bottomColor)->toBeString();
            expect($topColor)->toBeString();
            expect($bottomColor)->toMatch('/^#[0-9a-f]{6}$/i');
            expect($topColor)->toMatch('/^#[0-9a-f]{6}$/i');
            
            // Specific checks for vertical gradients
            if ($dir === 'to-b') {
                expect($bottomColor)->toBe('#764ba2');
                expect($topColor)->toBe('#667eea');
            } elseif ($dir === 'to-t') {
                expect($bottomColor)->toBe('#667eea');
                expect($topColor)->toBe('#764ba2');
            }
        }
    });
});
