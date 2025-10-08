<?php

namespace Happytodev\Blogr\Tests\Feature;

use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\BlogSeriesTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Happytodev\Blogr\Tests\TestCase;

class BlogSeriesModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_it_has_all_required_database_columns(): void
    {
        $requiredColumns = [
            'id',
            'slug',
            'photo',
            'position',
            'is_featured',
            'published_at',
            'created_at',
            'updated_at',
        ];

        $tableColumns = \Schema::getColumnListing('blog_series');

        foreach ($requiredColumns as $column) {
            $this->assertContains(
                $column,
                $tableColumns,
                "La colonne '{$column}' est manquante dans la table blog_series"
            );
        }
    }

    public function test_it_can_create_a_blog_series_with_all_fillable_fields(): void
    {
        $series = BlogSeries::create([
            'slug' => 'test-series',
            'photo' => 'test-photo.jpg',
            'position' => 1,
            'is_featured' => true,
            'published_at' => now(),
        ]);

        $this->assertDatabaseHas('blog_series', [
            'slug' => 'test-series',
            'photo' => 'test-photo.jpg',
            'position' => 1,
            'is_featured' => true,
        ]);

        $this->assertEquals('test-series', $series->slug);
        $this->assertEquals('test-photo.jpg', $series->photo);
        $this->assertEquals(1, $series->position);
        $this->assertTrue($series->is_featured);
    }

    public function test_it_can_update_is_featured_without_affecting_other_fields(): void
    {
        $series = BlogSeries::create([
            'slug' => 'test-series',
            'photo' => 'original-photo.jpg',
            'position' => 1,
            'is_featured' => true,
            'published_at' => now(),
        ]);

        $series->update(['is_featured' => false]);

        $this->assertDatabaseHas('blog_series', [
            'id' => $series->id,
            'slug' => 'test-series',
            'photo' => 'original-photo.jpg',
            'position' => 1,
            'is_featured' => false,
        ]);
    }

    public function test_it_can_toggle_is_featured_without_database_errors(): void
    {
        $series = BlogSeries::create([
            'slug' => 'toggle-test',
            'photo' => 'test.jpg',
            'position' => 1,
            'is_featured' => true,
        ]);

        $series->update(['is_featured' => false]);
        $this->assertFalse($series->fresh()->is_featured);

        $series->update(['is_featured' => true]);
        $this->assertTrue($series->fresh()->is_featured);

        $this->assertEquals('test.jpg', $series->fresh()->photo);
    }

    public function test_fillable_array_includes_photo(): void
    {
        $series = new BlogSeries();
        $fillable = $series->getFillable();

        $this->assertContains(
            'photo',
            $fillable,
            "Le champ 'photo' devrait être dans le tableau fillable"
        );
    }
}
