<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['users', 'categories', 'products', 'sales', 'sale_items', 'stock_movements'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->string('tenant_id')->nullable()->after('id')->index();
            });
        }

        $tenantId = DB::table('tenants')->value('id');
        if ($tenantId) {
            foreach (['users', 'categories', 'products', 'sales', 'sale_items', 'stock_movements'] as $name) {
                DB::table($name)->whereNull('tenant_id')->update(['tenant_id' => $tenantId]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->unique(['tenant_id', 'email']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->unique(['tenant_id', 'slug']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->unique(['tenant_id', 'sku']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique(['invoice_number']);
            $table->unique(['tenant_id', 'invoice_number']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropUnique(['tenant_id', 'email']);
            $table->unique('email');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropUnique(['tenant_id', 'slug']);
            $table->unique('slug');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropUnique(['tenant_id', 'sku']);
            $table->unique('sku');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropUnique(['tenant_id', 'invoice_number']);
            $table->unique('invoice_number');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });

        foreach (['users', 'categories', 'products', 'sales', 'sale_items', 'stock_movements'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }
    }
};
