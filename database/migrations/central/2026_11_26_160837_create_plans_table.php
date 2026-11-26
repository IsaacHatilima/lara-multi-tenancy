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
        Schema::create('plans', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 16);
            $table->string('deployment', 16);
            $table->boolean('is_public')->default(false);
            $table->boolean('is_default')->default(false);
            $table->string('tenant_id')->nullable()->index();
            $table->unsignedBigInteger('price_minor');
            $table->char('currency', 3);
            $table->string('interval', 16);
            $table->unsignedInteger('trial_days')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->index()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
        });

        // Partial unique indexes, which Blueprint can't express; SQLite and Postgres both accept this syntax.
        DB::statement("CREATE UNIQUE INDEX plans_active_code_unique ON plans (code) WHERE status = 'active'");
        DB::statement("CREATE UNIQUE INDEX plans_hosted_default_unique ON plans (deployment) WHERE is_default AND deployment = 'hosted'");
    }

    /**
     * Reverse the migrations. Dropping the table also drops its partial indexes.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
