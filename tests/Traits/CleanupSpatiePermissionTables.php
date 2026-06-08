<?php

namespace Happytodev\Blogr\Tests\Traits;

use Illuminate\Support\Facades\DB;

trait CleanupSpatiePermissionTables
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->afterApplicationCreated(function () {
            // After each test, truncate Spatie permission tables
            DB::statement('PRAGMA foreign_keys=OFF;');
            DB::table('role_has_permissions')->truncate();
            DB::table('model_has_roles')->truncate();
            DB::table('model_has_permissions')->truncate();
            DB::table('roles')->truncate();
            DB::table('permissions')->truncate();
            DB::statement('PRAGMA foreign_keys=ON;');
        });
    }
}
