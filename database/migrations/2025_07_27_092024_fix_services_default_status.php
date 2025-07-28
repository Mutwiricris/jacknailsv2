<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set all services to active by default
        DB::table('services')
            ->whereNull('status')
            ->orWhere('status', '')
            ->update(['status' => 'active']);
            
        // Also set any services that might have 'inactive' to 'active' for testing
        DB::table('services')
            ->where('status', 'inactive')
            ->update(['status' => 'active']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this data fix
    }
};