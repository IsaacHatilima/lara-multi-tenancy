<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Subjects and causers include tenants and users, whose keys are strings, as well as integer-keyed models such as domains.
     */
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table): void {
            $table->string('subject_id')->nullable()->change();
            $table->string('causer_id')->nullable()->change();
        });
    }

    /**
     * Rolling back deletes activity rows whose subject or causer has a non-numeric key, such as a tenant or user,
     * because integer columns cannot hold them.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::table('activity_log')
                ->whereRaw("subject_id !~ '^[0-9]+$'")
                ->orWhereRaw("causer_id !~ '^[0-9]+$'")
                ->delete();

            DB::statement('ALTER TABLE activity_log ALTER COLUMN subject_id TYPE bigint USING subject_id::bigint');
            DB::statement('ALTER TABLE activity_log ALTER COLUMN causer_id TYPE bigint USING causer_id::bigint');

            return;
        }

        Schema::table('activity_log', function (Blueprint $table): void {
            $table->unsignedBigInteger('subject_id')->nullable()->change();
            $table->unsignedBigInteger('causer_id')->nullable()->change();
        });
    }
};
