<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surgeries', function (Blueprint $table) {
            $table->renameColumn('room_number', 'room');
            $table->renameColumn('start_time', 'starts_at');
            $table->renameColumn('expected_duration', 'duration_min');
            $table->dropForeign(['doctor_id']);
            $table->dropColumn(['doctor_id', 'end_time']);
        });

        Schema::table('surgeries', function (Blueprint $table) {
            $table->unsignedTinyInteger('room')->change();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('confirmed_by')->nullable()->constrained('users');
            $table->boolean('is_conflict')->default(false);
            if (!Schema::hasColumns('surgeries', ['created_at', 'updated_at'])) {
                $table->timestamps();
            }
        });

        DB::statement('ALTER TABLE surgeries ADD CONSTRAINT chk_room CHECK (room BETWEEN 1 AND 9)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE surgeries DROP CONSTRAINT chk_room');

        if (Schema::hasColumns('surgeries', ['created_at', 'updated_at'])) {
            Schema::table('surgeries', fn (Blueprint $table) => $table->dropTimestamps());
        }

        Schema::table('surgeries', function (Blueprint $table) {
            $table->dropColumn('is_conflict');
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn('confirmed_by');
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
            $table->unsignedInteger('room')->change();
            $table->renameColumn('room', 'room_number');
            $table->renameColumn('starts_at', 'start_time');
            $table->renameColumn('duration_min', 'expected_duration');
            $table->foreignId('doctor_id')->constrained('users');
            $table->timestamp('end_time');
        });
    }
};
