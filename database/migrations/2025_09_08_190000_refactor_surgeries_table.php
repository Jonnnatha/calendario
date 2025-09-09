<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Renomes e remoções condicionais
        Schema::table('surgeries', function (Blueprint $table) {
            if (Schema::hasColumn('surgeries', 'room_number') && !Schema::hasColumn('surgeries', 'room')) {
                $table->renameColumn('room_number', 'room');
            }
            if (Schema::hasColumn('surgeries', 'start_time') && !Schema::hasColumn('surgeries', 'starts_at')) {
                $table->renameColumn('start_time', 'starts_at');
            }
            if (Schema::hasColumn('surgeries', 'expected_duration') && !Schema::hasColumn('surgeries', 'duration_min')) {
                $table->renameColumn('expected_duration', 'duration_min');
            }

            // Remover FK/coluna doctor_id se existir
            if (Schema::hasColumn('surgeries', 'doctor_id')) {
                // Se a FK tiver sido criada com convenção padrão, isso já remove a FK + índice:
                $table->dropConstrainedForeignId('doctor_id');
            }

            // Remover end_time se existir
            if (Schema::hasColumn('surgeries', 'end_time')) {
                $table->dropColumn('end_time');
            }
        });

        // 2) Ajustes de tipo e novas colunas (apenas se faltarem)
        Schema::table('surgeries', function (Blueprint $table) {
            if (Schema::hasColumn('surgeries', 'room')) {
                // requer doctrine/dbal para ->change()
                $table->unsignedTinyInteger('room')->change();
            }

            if (!Schema::hasColumn('surgeries', 'created_by')) {
                $table->foreignId('created_by')->constrained('users');
            }
            if (!Schema::hasColumn('surgeries', 'confirmed_by')) {
                $table->foreignId('confirmed_by')->nullable()->constrained('users');
            }
            if (!Schema::hasColumn('surgeries', 'is_conflict')) {
                $table->boolean('is_conflict')->default(false);
            }

            if (!Schema::hasColumns('surgeries', ['created_at', 'updated_at'])) {
                $table->timestamps();
            }
        });

        // 3) CHECK (só se ainda não existir e se seu MySQL/MariaDB suportar)
        // Em MySQL 8: ADD CONSTRAINT ... CHECK funciona; em MariaDB antigo o CHECK é ignorado.
        // Tente criar apenas se ainda não houver a constraint.
        try {
            DB::statement('ALTER TABLE surgeries ADD CONSTRAINT chk_room CHECK (room BETWEEN 1 AND 9)');
        } catch (\Throwable $e) {
            // ignora se já existe ou se o SGBD não suportar
        }
    }

    public function down(): void
    {
        // Remover CHECK com sintaxe compatível
        try {
            // MySQL 8
            DB::statement('ALTER TABLE surgeries DROP CHECK chk_room');
        } catch (\Throwable $e) {
            try {
                // MariaDB
                DB::statement('ALTER TABLE surgeries DROP CONSTRAINT chk_room');
            } catch (\Throwable $e2) {
                // ignora
            }
        }

        if (Schema::hasColumns('surgeries', ['created_at', 'updated_at'])) {
            Schema::table('surgeries', fn (Blueprint $table) => $table->dropTimestamps());
        }

        Schema::table('surgeries', function (Blueprint $table) {
            if (Schema::hasColumn('surgeries', 'is_conflict')) {
                $table->dropColumn('is_conflict');
            }

            if (Schema::hasColumn('surgeries', 'confirmed_by')) {
                $table->dropConstrainedForeignId('confirmed_by');
            }

            if (Schema::hasColumn('surgeries', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }

            if (Schema::hasColumn('surgeries', 'room')) {
                // voltar o tipo
                $table->unsignedInteger('room')->change();
            }

            if (Schema::hasColumn('surgeries', 'room') && !Schema::hasColumn('surgeries', 'room_number')) {
                $table->renameColumn('room', 'room_number');
            }
            if (Schema::hasColumn('surgeries', 'starts_at') && !Schema::hasColumn('surgeries', 'start_time')) {
                $table->renameColumn('starts_at', 'start_time');
            }
            if (Schema::hasColumn('surgeries', 'duration_min') && !Schema::hasColumn('surgeries', 'expected_duration')) {
                $table->renameColumn('duration_min', 'expected_duration');
            }

            if (!Schema::hasColumn('surgeries', 'doctor_id')) {
                $table->foreignId('doctor_id')->constrained('users');
            }
            if (!Schema::hasColumn('surgeries', 'end_time')) {
                $table->timestamp('end_time');
            }
        });
    }
};
