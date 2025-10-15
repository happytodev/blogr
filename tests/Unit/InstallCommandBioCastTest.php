<?php

namespace Happytodev\Blogr\Tests\Unit;

use PHPUnit\Framework\TestCase;

class InstallCommandBioCastTest extends TestCase
{
    public function test_it_adds_bio_cast_to_laravel_11_style_casts_method()
    {
        $content = '<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
        ];
    }
}';

        // Simulate the logic from BlogrInstallCommand
        if (preg_match('/(protected function casts\(\)\s*:\s*array\s*\{[^}]*return\s*\[)([^\]]*?)(\];)/s', $content, $matches)) {
            $before = $matches[1];
            $castsContent = $matches[2];
            $after = $matches[3];
            
            if (trim($castsContent) !== '') {
                // Remove trailing commas and whitespace, then add bio cast
                $cleanContent = rtrim($castsContent);
                // Remove trailing comma if present
                $cleanContent = rtrim($cleanContent, ',');
                $newCastsContent = $cleanContent . ",\n            'bio' => 'array',\n        ";
            } else {
                $newCastsContent = "\n            'bio' => 'array',\n        ";
            }
            
            $content = str_replace(
                $matches[0],
                $before . $newCastsContent . $after,
                $content
            );
        }

        $this->assertStringContainsString("'bio' => 'array'", $content);
        // Verify no double commas
        $this->assertStringNotContainsString(',,', $content);
    }

    public function test_it_handles_trailing_comma_without_creating_double_comma()
    {
        $content = '<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected function casts(): array
    {
        return [
            "email_verified_at" => "datetime",
            "password" => "hashed",
        ];
    }
}';

        // Simulate the logic from BlogrInstallCommand
        if (preg_match('/(protected function casts\(\)\s*:\s*array\s*\{[^}]*return\s*\[)([^\]]*?)(\];)/s', $content, $matches)) {
            $before = $matches[1];
            $castsContent = $matches[2];
            $after = $matches[3];
            
            if (trim($castsContent) !== '') {
                // Remove trailing commas and whitespace
                $cleanContent = rtrim($castsContent);
                $cleanContent = rtrim($cleanContent, ',');
                $newCastsContent = $cleanContent . ",\n            'bio' => 'array',\n        ";
            } else {
                $newCastsContent = "\n            'bio' => 'array',\n        ";
            }
            
            $content = str_replace(
                $matches[0],
                $before . $newCastsContent . $after,
                $content
            );
        }

        // Critical: Verify no double commas are created
        $this->assertStringNotContainsString(',,', $content);
        $this->assertStringContainsString("'bio' => 'array'", $content);
        
        // Verify proper structure
        $this->assertMatchesRegularExpression('/"hashed",\s*\'bio\' => \'array\'/', $content);
    }
}
