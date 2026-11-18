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
        Schema::table('generators', function (Blueprint $table) {
            $table->string('status', 50)->default('purchase_order')->change();
        });

        // Normalizar cualquier estado previo hacia el nuevo ciclo de 7 estados logísticos
        $mapping = [
            'warehouse' => 'baifa_warehouse',
            'in_transit' => 'maritime_transit',
            'checkpoint' => 'destination_customs',
            'installed' => 'delivered',
        ];

        foreach ($mapping as $old => $new) {
            DB::table('generators')->where('status', $old)->update(['status' => $new]);
            DB::table('checkpoints')->where('status', $old)->update(['status' => $new]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generators', function (Blueprint $table) {
            $table->string('status', 50)->default('warehouse')->change();
        });
    }
};
