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

        Schema::connection($connection)->create($prefix . 'email_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $prefix = config('crm.table_prefix', 'crm_');
        $connection = config('crm.drivers.database.connection', config('database.default'));

        Schema::connection($connection)->dropIfExists($prefix . 'email_settings');
    }
};
