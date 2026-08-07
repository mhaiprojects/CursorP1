<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('cron_expression')->nullable()->after('scheduled_at');
            $table->timestamp('last_run_at')->nullable()->after('cron_expression');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['cron_expression', 'last_run_at']);
        });
    }
};
