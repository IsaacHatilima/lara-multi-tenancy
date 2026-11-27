<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A plan's price in minor units (cents), its ISO 4217 currency, how often it bills and its trial length.
     * Existing plans become free monthly euro plans without a trial.
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->unsignedBigInteger('price_minor')->default(0)->after('description');
            $table->char('currency', 3)->default('EUR')->after('price_minor');
            $table->string('interval', 16)->default('month')->after('currency');
            $table->unsignedSmallInteger('trial_days')->default(0)->after('interval');
        });
    }

    /**
     * Rolling back loses every plan's price, currency, interval and trial length.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->dropColumn(['price_minor', 'currency', 'interval', 'trial_days']);
        });
    }
};
