<?php

use Happytodev\Blogr\Services\WaveSeparatorService;

describe('WaveSeparatorService::extractEdgeColor', function () {
    it('extracts bottom color from block with to-br gradient', function () {
        $block = [
            'data' => [
                'background_type' => 'gradient',
                'gradient_direction' => 'to-br',
                'gradient_from' => '#667eea',
                'gradient_to' => '#764ba2',
            ],
        ];

        $color = WaveSeparatorService::extractEdgeColor($block, 'bottom');
        expect($color)->toBe('#764ba2');
    });

    it('extracts top color from block with to-br gradient', function () {
        $block = [
            'data' => [
                'background_type' => 'gradient',
                'gradient_direction' => 'to-br',
                'gradient_from' => '#667eea',
                'gradient_to' => '#764ba2',
            ],
        ];

        $color = WaveSeparatorService::extractEdgeColor($block, 'top');
        expect($color)->toBe('#667eea');
    });

    it('extracts bottom color from block with to-r gradient', function () {
        $block = [
            'data' => [
                'background_type' => 'gradient',
                'gradient_direction' => 'to-r',
                'gradient_from' => '#f093fb',
                'gradient_to' => '#f5576c',
            ],
        ];

        $color = WaveSeparatorService::extractEdgeColor($block, 'bottom');
        expect($color)->toBeString();
        expect($color)->toMatch('/^#[0-9a-f]{6}$/i');
    });

    it('extracts top color from block with to-r gradient', function () {
        $block = [
            'data' => [
                'background_type' => 'gradient',
                'gradient_direction' => 'to-r',
                'gradient_from' => '#f093fb',
                'gradient_to' => '#f5576c',
            ],
        ];

        $color = WaveSeparatorService::extractEdgeColor($block, 'top');
        expect($color)->toBeString();
        expect($color)->toMatch('/^#[0-9a-f]{6}$/i');
    });

    it('extracts bottom color from block with to-b gradient', function () {
        $block = [
            'data' => [
                'background_type' => 'gradient',
                'gradient_direction' => 'to-b',
                'gradient_from' => '#667eea',
                'gradient_to' => '#764ba2',
            ],
        ];

        $color = WaveSeparatorService::extractEdgeColor($block, 'bottom');
        expect($color)->toBe('#764ba2');
    });

    it('extracts top color from block with to-b gradient', function () {
        $block = [
            'data' => [
                'background_type' => 'gradient',
                'gradient_direction' => 'to-b',
                'gradient_from' => '#667eea',
                'gradient_to' => '#764ba2',
            ],
        ];

        $color = WaveSeparatorService::extractEdgeColor($block, 'top');
        expect($color)->toBe('#667eea');
    });

    it('returns null for non-gradient block', function () {
        $block = [
            'data' => [
                'background_type' => 'solid',
                'background_color' => '#ffffff',
            ],
        ];

        $color = WaveSeparatorService::extractEdgeColor($block, 'bottom');
        expect($color)->toBeNull();
    });

    it('returns null for empty block', function () {
        $color = WaveSeparatorService::extractEdgeColor(null, 'bottom');
        expect($color)->toBeNull();
    });

    it('handles realistic transition scenario', function () {
        $previousBlock = [
            'data' => [
                'background_type' => 'gradient',
                'gradient_direction' => 'to-br',
                'gradient_from' => '#667eea',
                'gradient_to' => '#764ba2',
            ],
        ];

        $nextBlock = [
            'data' => [
                'background_type' => 'gradient',
                'gradient_direction' => 'to-r',
                'gradient_from' => '#f093fb',
                'gradient_to' => '#f5576c',
            ],
        ];

        $prevColor = WaveSeparatorService::extractEdgeColor($previousBlock, 'bottom');
        $nextColor = WaveSeparatorService::extractEdgeColor($nextBlock, 'top');

        expect($prevColor)->toBe('#764ba2');
        expect($nextColor)->toBeString();
        expect($nextColor)->toMatch('/^#[0-9a-f]{6}$/i');
    });
});
