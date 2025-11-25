<?php<?php

























}    }        });            DB::statement('PRAGMA foreign_keys=ON;');            DB::table('permissions')->truncate();            DB::table('roles')->truncate();            DB::table('model_has_permissions')->truncate();            DB::table('model_has_roles')->truncate();            DB::table('role_has_permissions')->truncate();            DB::statement('PRAGMA foreign_keys=OFF;');        $this->afterApplicationCreated(function () {        // After each test, truncate Spatie permission tables                parent::setUp();    {    protected function setUp(): void{trait CleanupSpatiePermissionTablesuse Illuminate\Database\Eloquent\Model;use Illuminate\Support\Facades\DB;namespace Happytodev\Blogr\Tests;
namespace Happytodev\Blogr\Tests\Traits;

trait CleanupSpatiePermissionTables
{
}
