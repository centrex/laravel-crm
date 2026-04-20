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
        $withUserForeignKeys = (bool) config('crm.user_foreign_keys', false);

        Schema::connection($connection)->create($prefix . 'companies', function (Blueprint $table) use ($withUserForeignKeys) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('owner_id');
            $table->index('is_active');

            if ($withUserForeignKeys) {
                $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        Schema::connection($connection)->create($prefix . 'contacts', function (Blueprint $table) use ($prefix, $withUserForeignKeys) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained($prefix . 'companies')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('job_title')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_primary']);
            $table->index('email');
            $table->index('owner_id');

            if ($withUserForeignKeys) {
                $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        Schema::connection($connection)->create($prefix . 'leads', function (Blueprint $table) use ($prefix, $withUserForeignKeys) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('company_id')->nullable()->constrained($prefix . 'companies')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained($prefix . 'contacts')->nullOnDelete();
            $table->string('title');
            $table->string('source')->nullable();
            $table->decimal('value', 18, 2)->default(0);
            $table->string('currency', 3)->default('BDT');
            $table->string('status')->default('open');
            $table->unsignedSmallInteger('probability')->default(10);
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->timestamp('qualified_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'owner_id']);
            $table->index('company_id');
            $table->index('contact_id');

            if ($withUserForeignKeys) {
                $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        Schema::connection($connection)->create($prefix . 'deals', function (Blueprint $table) use ($prefix, $withUserForeignKeys) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('lead_id')->nullable()->constrained($prefix . 'leads')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained($prefix . 'companies')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained($prefix . 'contacts')->nullOnDelete();
            $table->string('name');
            $table->string('stage')->default('qualified');
            $table->decimal('amount', 18, 2)->default(0);
            $table->string('currency', 3)->default('BDT');
            $table->unsignedSmallInteger('probability')->default(20);
            $table->date('expected_close_date')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['stage', 'owner_id']);
            $table->index('company_id');
            $table->index('contact_id');
            $table->index('lead_id');

            if ($withUserForeignKeys) {
                $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
            }
        });

        Schema::connection($connection)->create($prefix . 'activities', function (Blueprint $table) use ($withUserForeignKeys) {
            $table->id();
            $table->nullableMorphs('subject');
            $table->string('type');
            $table->string('summary');
            $table->text('description')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('due_at');
            $table->index('completed_at');
            $table->index('owner_id');

            if ($withUserForeignKeys) {
                $table->foreign('owner_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        $prefix = config('crm.table_prefix', 'crm_');
        $connection = config('crm.drivers.database.connection', config('database.default'));

        Schema::connection($connection)->dropIfExists($prefix . 'activities');
        Schema::connection($connection)->dropIfExists($prefix . 'deals');
        Schema::connection($connection)->dropIfExists($prefix . 'leads');
        Schema::connection($connection)->dropIfExists($prefix . 'contacts');
        Schema::connection($connection)->dropIfExists($prefix . 'companies');
    }
};
