<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surgeries', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamp('scheduled_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('confirmed_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surgeries');
    }
};
