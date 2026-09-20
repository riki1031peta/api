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
        Schema::create('dopa_experience_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dopa_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->integer('amount');
            $table->string('action');
            $table->nullableMorphs('source');
            $table->timestamps();
            $table->index(['dopa_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dopa_experience_logs');
    }
};
