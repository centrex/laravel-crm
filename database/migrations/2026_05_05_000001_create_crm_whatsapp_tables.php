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

        Schema::connection($connection)->create($prefix . 'whatsapp_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type')->default('custom'); // product_update | offer | follow_up | custom
            $table->text('message_body');              // supports {{name}}, {{company}}, {{product}}, {{offer}}, {{price}}
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('type');
            $table->index('is_active');
        });

        Schema::connection($connection)->create($prefix . 'whatsapp_messages', function (Blueprint $table) use ($prefix, $withUserForeignKeys): void {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained($prefix . 'whatsapp_templates')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained($prefix . 'contacts')->nullOnDelete();
            $table->string('phone');
            $table->string('type')->default('custom');
            $table->text('message_body');
            $table->text('wa_url');
            $table->string('status')->default('pending'); // pending | opened
            $table->unsignedBigInteger('sent_by')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamps();

            $table->index(['contact_id', 'status']);
            $table->index('type');
            $table->index('sent_by');

            if ($withUserForeignKeys) {
                $table->foreign('sent_by')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        $prefix = config('crm.table_prefix', 'crm_');
        $connection = config('crm.drivers.database.connection', config('database.default'));

        Schema::connection($connection)->dropIfExists($prefix . 'whatsapp_messages');
        Schema::connection($connection)->dropIfExists($prefix . 'whatsapp_templates');
    }
};
