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
        if (DB::table('domains')->select('tenant_id')->groupBy('tenant_id')->havingRaw('COUNT(*) > 2')->exists()) {
            throw new RuntimeException('A tenant has more than two domains. Resolve its extra domains before migrating.');
        }

        Schema::table('domains', function (Blueprint $table): void {
            $table->enum('slot', ['primary', 'secondary'])->default('primary');
        });

        foreach (DB::table('domains')->select('tenant_id')->distinct()->pluck('tenant_id') as $tenantId) {
            foreach (DB::table('domains')->where('tenant_id', $tenantId)->orderBy('id')->pluck('id') as $index => $domainId) {
                DB::table('domains')->where('id', $domainId)->update([
                    'slot' => $index === 0 ? 'primary' : 'secondary',
                ]);
            }
        }

        Schema::table('domains', function (Blueprint $table): void {
            $table->unique(['tenant_id', 'slot']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table): void {
            $table->dropUnique(['tenant_id', 'slot']);
            $table->dropColumn('slot');
        });
    }
};
