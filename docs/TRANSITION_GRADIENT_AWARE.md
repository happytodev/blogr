# Gradient-Aware Transition System

## Overview

The transition system now intelligently extracts colors from adjacent blocks based on their gradient directions, ensuring seamless visual blending between sections.

## How It Works

### Color Extraction Logic

The `WaveSeparatorService::extractEdgeColor()` method intelligently maps gradient directions to the colors that appear at specific edges of blocks:

```
For gradient 'to-br' (bottom-right):
  - FROM color (#667eea) appears at top-left
  - TO color (#764ba2) appears at bottom-right

For gradient 'to-r' (right):
  - FROM color (#f093fb) appears at left
  - TO color (#f5576c) appears at right
  - Blend colors at top/bottom (intermediate)

For gradient 'to-b' (bottom):
  - FROM color (#667eea) appears at top
  - TO color (#764ba2) appears at bottom
```

### Transition Positioning

When a transition is placed between two blocks:

1. **Bottom edge of PREVIOUS block** → Extract color at bottom of that block
2. **Top edge of NEXT block** → Extract color at top of that block
3. **Create gradient** between these two intelligent colors

### Example: Hero → Transition → Stats

```
Hero Block (to-br: #667eea → #764ba2)
├─ At bottom: #764ba2 (bottom-right color)
│
Transition Block
├─ From: #764ba2 (from Hero bottom)
├─ To: #f07fb5 (blended from Stats top)
│
Stats Block (to-r: #f093fb → #f5576c)
├─ At top: ~#f07fb5 (blended left-center)
```

Result: Smooth gradient transition that visually "belongs" between the two blocks.

## Gradient Direction Support

### Vertical Gradients
- `to-b`: Gradient goes downward
- `to-t`: Gradient goes upward
- `to-bl`, `to-br`, `to-tl`, `to-tr`: Diagonal combinations

### Horizontal Gradients
- `to-r`: Gradient goes rightward
- `to-l`: Gradient goes leftward

### Radial/Circular
- `circle`: Radial gradient from center
- `radial`: Radial gradient

## Implementation Details

### Service Method

```php
public static function extractEdgeColor(?array $block, string $edge = 'bottom'): ?string
{
    // Parameters:
    // - $block: Block data array (including 'data' key with gradient config)
    // - $edge: 'top' or 'bottom' - which edge of the block to extract from
    
    // Returns: Hex color string (#RRGGBB) or null if not a gradient block
}
```

### Usage in Transition Components

All 4 transition types now use this method:

```blade
$prevColor = WaveSeparatorService::extractEdgeColor($previousBlock, 'bottom');
$nextColor = WaveSeparatorService::extractEdgeColor($nextBlock, 'top');
```

### Color Blending

For horizontal or complex gradients, colors are intelligently blended:

```php
// To-right gradient: blend colors at different ratios
- Bottom edge: blend toward TO (0.7 ratio)
- Top edge: blend toward FROM (0.3 ratio)
```

## Transition Types

All 4 transition types benefit from this system:

1. **transition-diagonal** (135°): Angled gradient with clip-path
2. **transition-clippath**: Multiple clip-path styles (wavy, zigzag, smooth)
3. **transition-margin**: Simple negative margin + gradient
4. **transition-animation**: Animated entrance (fade-slide, scale, rotate)

## Testing

Comprehensive tests ensure correctness:

- 9 new tests in `WaveSeparatorEdgeColorTest.php`
- Tests cover all gradient directions (to-br, to-r, to-b, circle, etc.)
- Tests verify realistic transition scenarios
- Tests check edge cases (null blocks, non-gradient blocks, etc.)

## Performance

- **Zero runtime overhead**: Color extraction happens once during rendering
- **No JavaScript**: Pure PHP + CSS solution
- **Mobile-friendly**: Gradients scale naturally with viewport

## Future Enhancements

- Support for multiple color stops (beyond from/to)
- Conic gradients (CSS angle gradients)
- Mobile-specific responsive color adjustments
- Animation performance optimization

## Common Scenarios

### Hero to Stats (Purple to Pink)
```
Hero (to-br: #667eea → #764ba2)
  ↓ Extract bottom: #764ba2
Transition (135deg): #764ba2 → #f07fb5
  ↓ (intelligently blended)
Stats (to-r: #f093fb → #f5576c)
  ↓ Extract top: ~#f07fb5
```

### Features to CTA (Vertical sections)
```
Features (to-b: #ffffff → #f3f4f6)
  ↓ Extract bottom: #f3f4f6
Transition (gradient): #f3f4f6 → #667eea
  ↓
CTA (to-br: #667eea → #764ba2)
  ↓ Extract top: #667eea
```

### Same Color Sections
When adjacent blocks have similar gradients, transitions blend seamlessly with minimal color difference.
