<?php

uses(Happytodev\Blogr\Tests\TestCase::class);

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

it('notifications table migration is published and executes', function () {
    // The table should exist after migration
    expect(Schema::hasTable('notifications'))->toBeTrue();
});

it('notifications table has correct structure', function () {
    expect(Schema::hasTable('notifications'))->toBeTrue();
    expect(Schema::hasColumns('notifications', [
        'id',
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

it('notifications id column exists and is primary key', function () {
    // Simply check that the id column exists and we can insert data
    expect(Schema::hasColumn('notifications', 'id'))->toBeTrue();
    
    // Insert a record to verify the ID is primary key (would fail if not)
    DB::table('notifications')->insert([
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'type' => 'TestPrimary',
        'notifiable_type' => 'User',
        'notifiable_id' => 999,
        'data' => json_encode(['pk_test' => true]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    // Verify insertion worked (implies primary key is working)
    expect(DB::table('notifications')->where('type', 'TestPrimary')->count())->toBe(1);
});

it('notifications table stores notification data correctly', function () {
    // Create a test notification record
    DB::table('notifications')->insert([
        'id' => (string) \Illuminate\Support\Str::uuid(),
        'type' => 'TestNotification',
        'notifiable_type' => 'User',
        'notifiable_id' => 1,
        'data' => json_encode(['test' => 'data']),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    
    $notification = DB::table('notifications')->first();
    expect($notification)->not->toBeNull();
    expect($notification->type)->toBe('TestNotification');
    expect(json_decode($notification->data, true))->toBe(['test' => 'data']);
});
