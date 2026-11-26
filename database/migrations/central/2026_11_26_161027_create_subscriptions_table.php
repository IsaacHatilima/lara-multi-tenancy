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
        Schema::create('subscriptions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('tenant_id')->index();
            $table->foreignUuid('plan_id')->index()->constrained()->restrictOnDelete();
            $table->string('status', 16);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable();
            $table->timestamp('grace_ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->restrictOnDelete();
        });

        // A partial unique index, which Blueprint can't express: canceled and ended subscriptions are history.
        DB::statement(
            "CREATE UNIQUE INDEX subscriptions_current_tenant_unique ON subscriptions (tenant_id) WHERE status IN ('trialing', 'active', 'past_due', 'read_only')",
        );
    }

    /**
     * Reverse the migrations. Dropping the table also drops its partial index.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
