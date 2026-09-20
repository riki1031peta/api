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
        Schema::create('dopas', function (Blueprint $table) {
            $table->id();
    
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
    
            $table->unsignedInteger('experience')->default(0);
            $table->unsignedInteger('level')->default(1);
    
            $table->string('stage')->default('egg');
            $table->string('type')->default('normal');
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dopas');
    }
};
