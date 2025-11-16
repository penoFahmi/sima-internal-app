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
        Schema::create('guest_logs', function (Blueprint $table) {
            $table->id();
            $table->string('guest_name');
            $table->string('guest_from'); // Misal: "Dinas Kominfo"
            $table->text('purpose'); // Keperluan
            $table->foreignId('host_user_id')->constrained('users'); // Bertemu dengan siapa
            $table->timestamp('check_in')->useCurrent();
            $table->timestamp('check_out')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_logs');
    }
};
