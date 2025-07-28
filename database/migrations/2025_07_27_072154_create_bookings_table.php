<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference', 20)->unique();
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('client_name');
            $table->string('client_email');
            $table->string('client_phone');
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('pending');
            $table->text('notes')->nullable();
            $table->decimal('total_amount', 8, 2);
            $table->enum('payment_status', ['pending', 'completed', 'partial', 'refunded'])->default('pending');
            $table->enum('payment_method', ['cash', 'mpesa', 'card', 'bank_transfer'])->default('cash');
            $table->string('mpesa_transaction_id', 50)->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('booking_reference');
            $table->index('appointment_date');
            $table->index(['client_email', 'status']);
            $table->index(['appointment_date', 'start_time']);
            $table->index('status');
            $table->index('payment_status');
            
            // Unique constraint to prevent double booking for same time slot
            $table->unique(['appointment_date', 'start_time'], 'unique_appointment_slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};