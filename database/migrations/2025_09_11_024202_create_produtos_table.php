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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 255)->notNullable()->unique()->index(); 
            $table->foreignId('categoria_id')->notNullable()->constrained('categorias');
            $table->string('descricao', 255)->nullable();
            $table->integer('estoque')->check('estoque >= 0')->default(0); 
            $table->decimal('preco_venda', 10, 2)->check('preco_venda >= 0')->default(0.00); 
            $table->decimal('custo_medio', 10, 2)->check('custo_medio >= 0')->default(0.00); 
            $table->boolean('ativo')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
