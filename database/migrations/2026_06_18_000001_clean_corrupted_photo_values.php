<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Clean corrupted photo values in blog_posts (JSON-encoded arrays)
        DB::table('blog_posts')
            ->where('photo', '[]')
            ->orWhere('photo', '[""]')
            ->update(['photo' => null]);

        // Clean corrupted photo values in blog_post_translations
        DB::table('blog_post_translations')
            ->where('photo', '[]')
            ->orWhere('photo', '[""]')
            ->update(['photo' => null]);

        // Fix JSON-encoded single-element arrays by extracting the path
        $fixPath = function ($table) {
            $rows = DB::table($table)
                ->whereNotNull('photo')
                ->where('photo', 'like', '[%')
                ->get(['id', 'photo']);

            foreach ($rows as $row) {
                $decoded = json_decode($row->photo, true);
                if (is_array($decoded) && count($decoded) === 1 && is_string($decoded[0])) {
                    DB::table($table)
                        ->where('id', $row->id)
                        ->update(['photo' => $decoded[0]]);
                } elseif (is_array($decoded) && empty($decoded)) {
                    DB::table($table)
                        ->where('id', $row->id)
                        ->update(['photo' => null]);
                }
            }
        };

        $fixPath('blog_posts');
        $fixPath('blog_post_translations');
    }

    public function down(): void
    {
        // No rollback for data cleanup
    }
};
