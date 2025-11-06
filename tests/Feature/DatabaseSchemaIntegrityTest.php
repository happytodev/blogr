<?php



namespace Happytodev\Blogr\Tests\Feature;


uses(\Happytodev\Blogr\Tests\TestCase::class);
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Happytodev\Blogr\Tests\TestCase;

class DatabaseSchemaIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_blog_series_table_has_correct_schema(): void
    {
        $this->assertTrue(
            Schema::hasTable('blog_series'),
            "La table 'blog_series' devrait exister"
        );

        $expectedColumns = ['id', 'slug', 'photo', 'position', 'is_featured', 'published_at', 'created_at', 'updated_at'];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('blog_series', $column),
                "La colonne '{$column}' devrait exister dans la table 'blog_series'"
            );
        }
    }

    public function test_blog_series_photo_column_is_nullable(): void
    {
        $columns = DB::select('PRAGMA table_info(blog_series)');
        $photoColumn = collect($columns)->firstWhere('name', 'photo');

        $this->assertNotNull($photoColumn, "La colonne 'photo' devrait exister");
        $this->assertEquals(0, $photoColumn->notnull, "La colonne 'photo' devrait être nullable");
    }

    public function test_blog_series_slug_column_is_unique(): void
    {
        $indexes = DB::select('PRAGMA index_list(blog_series)');
        
        $hasUniqueSlug = false;
        foreach ($indexes as $index) {
            if ($index->unique == 1) {
                $indexInfo = DB::select("PRAGMA index_info({$index->name})");
                foreach ($indexInfo as $column) {
                    if ($column->name === 'slug') {
                        $hasUniqueSlug = true;
                        break 2;
                    }
                }
            }
        }

        $this->assertTrue(
            $hasUniqueSlug,
            "La colonne 'slug' dans 'blog_series' devrait avoir une contrainte unique"
        );
    }

    public function test_blog_series_can_be_created_with_all_columns(): void
    {
        $series = \Happytodev\Blogr\Models\BlogSeries::create([
            'slug' => 'complete-test',
            'photo' => 'photo.jpg',
            'position' => 1,
            'is_featured' => true,
            'published_at' => now(),
        ]);

        $this->assertNotNull($series->id);
        $this->assertEquals('complete-test', $series->slug);
        $this->assertEquals('photo.jpg', $series->photo);
        $this->assertEquals(1, $series->position);
        $this->assertTrue($series->is_featured);
        $this->assertNotNull($series->published_at);
    }

    public function test_blog_series_defaults_are_correct(): void
    {
        $series = \Happytodev\Blogr\Models\BlogSeries::create([
            'slug' => 'defaults-test',
        ]);

        $this->assertEquals(0, $series->position);
        
        $this->assertEquals(false, $series->is_featured, "is_featured devrait être false par défaut");
        $this->assertNull($series->photo);
        $this->assertNull($series->published_at);
    }

    public function test_all_required_blog_tables_exist(): void
    {
        $requiredTables = [
            'blog_posts',
            'blog_post_translations',
            'blog_series',
            'blog_series_translations',
            'categories',
            'category_translations',
            'tags',
            'tag_translations',
        ];

        foreach ($requiredTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "La table '{$table}' devrait exister après les migrations"
            );
        }
    }

    public function test_migrations_can_be_run_multiple_times_safely(): void
    {
        $this->artisan('migrate')->assertSuccessful();

        $this->assertTrue(Schema::hasTable('blog_series'));
        $this->assertTrue(Schema::hasColumn('blog_series', 'photo'));
    }
}
