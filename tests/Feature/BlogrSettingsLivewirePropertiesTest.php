<?php

namespace Happytodev\Blogr\Tests\Feature;

use Happytodev\Blogr\Filament\Pages\BlogrSettings;       

use Happytodev\Blogr\Tests\TestCase;    

use ReflectionClass;

use ReflectionProperty;


class BlogrSettingsLivewirePropertiesTest extends TestCase
{
    /** @test */
    public function it_ensures_all_public_properties_have_livewire_supported_types()
    {
        $reflection = new ReflectionClass(BlogrSettings::class);

        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $propertyName = $property->getName();
            $propertyType = $property->getType();

            // Skip properties without type declarations (they are supported)
            if (!$propertyType) {
                continue;
            }

            $typeName = $propertyType->getName();

            // Check if it's a union type with null
            if ($propertyType->allowsNull() && $typeName !== 'mixed') {
                // Union types with null can be problematic in Livewire
                // The property should either not allow null or have a proper default value
                $defaultValue = $property->getDefaultValue();

                // If it allows null but has no default value, it might cause issues
                if ($defaultValue === null) {
                    $this->fail("Property '{$propertyName}' allows null but has no default value. This can cause Livewire type errors.");
                }
            }

            // Ensure the type is supported by Livewire
            $supportedTypes = [
                'string', 'int', 'float', 'bool', 'array',
                'null', // null is supported when it's the only type
            ];

            if (!in_array($typeName, $supportedTypes) && !$propertyType->isBuiltin()) {
                // For non-builtin types, we need to check if they're supported
                // For now, we'll allow common types but flag suspicious ones
                if (!preg_match('/^\\\\?/', $typeName)) {
                    $this->fail("Property '{$propertyName}' has unsupported type '{$typeName}' for Livewire.");
                }
            }
        }

        // Specifically test that import_file property exists and has correct type
        $this->assertTrue($reflection->hasProperty('import_file'), 'import_file property should exist');

        $importFileProperty = $reflection->getProperty('import_file');
        $this->assertEquals('array', $importFileProperty->getType()->getName(), 'import_file should be typed as array');
        $this->assertFalse($importFileProperty->getType()->allowsNull(), 'import_file should not allow null');
        $this->assertEquals([], $importFileProperty->getDefaultValue(), 'import_file should default to empty array');
    }

    /** @test */
    public function it_can_instantiate_blogr_settings_without_livewire_errors()
    {
        // This test ensures that the BlogrSettings class can be instantiated
        // without triggering Livewire property type errors

        $instance = app(BlogrSettings::class);

        $this->assertInstanceOf(BlogrSettings::class, $instance);

        // Verify that the import_file property is properly initialized
        $this->assertEquals([], $instance->import_file);
    }

    /** @test */
    public function it_validates_file_upload_properties_have_correct_defaults()
    {
        $reflection = new ReflectionClass(BlogrSettings::class);

        // Check all array properties that might be used for file uploads
        $arrayProperties = ['import_file', 'series_default_image', 'posts_default_image'];

        foreach ($arrayProperties as $propertyName) {
            if ($reflection->hasProperty($propertyName)) {
                $property = $reflection->getProperty($propertyName);
                $type = $property->getType();

                if ($type && $type->getName() === 'array') {
                    $defaultValue = $property->getDefaultValue();

                    // File upload properties should default to empty arrays, not null
                    $this->assertIsArray($defaultValue, "Property '{$propertyName}' should default to an array");
                    $this->assertEmpty($defaultValue, "Property '{$propertyName}' should default to an empty array");
                }
            }
        }
    }
}