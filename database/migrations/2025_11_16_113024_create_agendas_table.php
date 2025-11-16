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
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Siapa yang buat
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('location_text')->nullable(); // Untuk lokasi non-booking (misal: "Dinas Kominfo")

            // Untuk fitur canggih (Recurring)
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_rule')->nullable(); // Menyimpan rule (misal: "FREQ=WEEKLY;BYDAY=MO")

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};
