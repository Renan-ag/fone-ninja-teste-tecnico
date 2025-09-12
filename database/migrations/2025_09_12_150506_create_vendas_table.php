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
        Schema::create('vendas', function (Blueprint $table) {
            $table->id();
            $table->string('cliente')->notNullable();
            $table->decimal('total', 10, 2)->nullable()->check('total >= 0');
            $table->decimal('lucro', 10, 2)->nullable()->check('lucro >= 0');
            $table->enum('status', ['pending', 'confirmed', 'canceled'])->default('confirmed');
            $table->timestamps();
            $table->softDeletes();

            $table->index('cliente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendas');
    }
};
