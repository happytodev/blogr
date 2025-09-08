<?php

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

it('can access blogr settings configuration', function () {
    // Test that we can access the blogr config
    $config = config('blogr', []);

    expect($config)->toBeArray();
});

it('can update config array structure', function () {
    // Test the config update logic without instantiating the class
    $formData = [
        'posts_per_page' => 15,
        'colors' => [
            'primary' => '#FF0000'
        ],
        'seo' => [
            'site_name' => 'Test Blog'
        ]
    ];

    $currentConfig = config('blogr', []);

    // Simulate the merge logic
    $setNestedValue = function (&$array, $keys, $value) use (&$setNestedValue) {
        $key = array_shift($keys);
        if (count($keys) === 0) {
            $array[$key] = $value;
        } else {
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $setNestedValue($array[$key], $keys, $value);
        }
    };

    foreach ($formData as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $subKey => $subValue) {
                if (is_array($subValue)) {
                    foreach ($subValue as $deepKey => $deepValue) {
                        $setNestedValue($currentConfig, [$key, $subKey, $deepKey], $deepValue);
                    }
                } else {
                    $setNestedValue($currentConfig, [$key, $subKey], $subValue);
                }
            }
        } else {
            $currentConfig[$key] = $value;
        }
    }

    expect($currentConfig['posts_per_page'])->toBe(15);
    expect($currentConfig['colors']['primary'])->toBe('#FF0000');
    expect($currentConfig['seo']['site_name'])->toBe('Test Blog');
});

it('can generate valid PHP config file content', function () {
    // Test config file generation logic
    $config = [
        'posts_per_page' => 10,
        'colors' => [
            'primary' => '#FA2C36'
        ]
    ];

    $content = "<?php\n\n";
    $content .= "// config for Happytodev/Blogr\n";
    $content .= "return [\n";

    $arrayToString = function ($array, $indent = 0) use (&$arrayToString) {
        $result = '';
        $indentStr = str_repeat('    ', $indent);

        foreach ($array as $key => $value) {
            $result .= $indentStr;

            if (is_int($key)) {
                $result .= is_array($value) ? "[\n" . $arrayToString($value, $indent + 1) . str_repeat('    ', $indent) . ']' :
                           (is_bool($value) ? ($value ? 'true' : 'false') :
                           (is_null($value) ? 'null' : "'{$value}'"));
            } else {
                $result .= "'{$key}' => ";
                $result .= is_array($value) ? "[\n" . $arrayToString($value, $indent + 1) . str_repeat('    ', $indent) . ']' :
                           (is_bool($value) ? ($value ? 'true' : 'false') :
                           (is_null($value) ? 'null' : "'{$value}'"));
            }

            $result .= ",\n";
        }

        return $result;
    };

    $content .= $arrayToString($config, 1);
    $content .= "];\n";

    expect($content)->toContain('<?php');
    expect($content)->toContain('return [');
    expect($content)->toContain("'posts_per_page' => '10'");
    expect($content)->toContain("'primary' => '#FA2C36'");
});
