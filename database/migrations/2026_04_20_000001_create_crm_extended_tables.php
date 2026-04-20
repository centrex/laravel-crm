<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        $prefix = config('crm.table_prefix', 'crm_');
        $connection = config('crm.drivers.database.connection', config('database.default'));

        Schema::connection($connection)->create($prefix . 'tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 7)->default('#6366f1');
            $table->timestamps();

            $table->index('slug');
        });

        Schema::connection($connection)->create($prefix . 'taggables', function (Blueprint $table) use ($prefix): void {
            $table->foreignId('tag_id')->constrained($prefix . 'tags')->cascadeOnDelete();
            $table->morphs('taggable');

            $table->unique(['tag_id', 'taggable_type', 'taggable_id']);
        });

        Schema::connection($connection)->create($prefix . 'clv_snapshots', function (Blueprint $table) use ($prefix): void {
            $table->id();
            $table->foreignId('contact_id')->constrained($prefix . 'contacts')->cascadeOnDelete();
            $table->unsignedSmallInteger('horizon_months')->default(12);
            $table->decimal('clv_value', 18, 2)->default(0);
            $table->decimal('expected_monthly_value', 18, 2)->default(0);
            $table->decimal('avg_deal_value', 18, 2)->default(0);
            $table->decimal('total_revenue', 18, 2)->default(0);
            $table->unsignedInteger('frequency')->default(0);
            $table->decimal('recency_days', 10, 2)->default(0);
            $table->decimal('age_days', 10, 2)->default(0);
            $table->decimal('p_alive', 5, 4)->default(0);
            $table->decimal('expected_transactions', 10, 2)->default(0);
            $table->timestamp('calculated_at');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('contact_id');
            $table->index('calculated_at');
        });

        Schema::connection($connection)->table($prefix . 'leads', function (Blueprint $table): void {
            $table->unsignedSmallInteger('score')->default(0)->after('probability');
        });

        Schema::connection($connection)->table($prefix . 'activities', function (Blueprint $table): void {
            $table->string('priority')->default('normal')->after('type');
        });
    }

    public function down(): void
    {
        $prefix = config('crm.table_prefix', 'crm_');
        $connection = config('crm.drivers.database.connection', config('database.default'));

        Schema::connection($connection)->table($prefix . 'activities', function (Blueprint $table): void {
            $table->dropColumn('priority');
        });

        Schema::connection($connection)->table($prefix . 'leads', function (Blueprint $table): void {
            $table->dropColumn('score');
        });

        Schema::connection($connection)->dropIfExists($prefix . 'clv_snapshots');
        Schema::connection($connection)->dropIfExists($prefix . 'taggables');
        Schema::connection($connection)->dropIfExists($prefix . 'tags');
    }
};
