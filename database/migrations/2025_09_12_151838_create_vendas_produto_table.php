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
        Schema::create('venda_produto', function (Blueprint $table) {
            $table->foreignId('venda_id')->constrained('vendas')->onDelete('cascade')->notNullable();
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('cascade')->notNullable();
            $table->integer('quantidade')->notNullable()->check('quantidade > 0');
            $table->decimal('preco_unitario', 10, 2)->notNullable()->check('preco_unitario > 0');
            $table->timestamps();
            $table->softDeletes();
            
            $table->primary(['venda_id', 'produto_id']);
            
            $table->index('venda_id');
            $table->index('produto_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendas_produto');
    }
};
