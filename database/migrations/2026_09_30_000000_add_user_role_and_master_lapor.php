<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('email');
            $table->foreignId('master_lapors_id')->nullable()->unique()->after('role')->constrained('master_lapors')->nullOnDelete();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->index('nik');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('master_lapors_id');
            $table->dropColumn('role');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['nik']);
        });
    }
};
