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
        Schema::create('compra_produto', function (Blueprint $table) {            
            $table->foreignId('compra_id')->constrained('compras')->onDelete('cascade')->notNullable();
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade')->notNullable();
            $table->integer('quantidade')->notNullable();
            $table->decimal('preco_unitario', 10, 2)->notNullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->primary(['compra_id', 'produto_id']);
            
            $table->index('compra_id');
            $table->index('produto_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras_produto');
    }
};
