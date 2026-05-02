<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
        });

        DB::table('customers')
            ->join('users', 'customers.email', '=', 'users.email')
            ->whereNull('customers.user_id')
            ->update(['customers.user_id' => DB::raw('users.id')]);

        $roleId = DB::table('roles')->where('name', 'panel_user')->value('id');

        if (! $roleId) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'panel_user',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('permissions')
            ->whereIn('name', [
                'view_any_order',
                'view_order',
                'view_any_customer',
                'view_customer',
            ])
            ->pluck('id')
            ->each(function (int $permissionId) use ($roleId): void {
                DB::table('role_has_permissions')->updateOrInsert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
