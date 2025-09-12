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
        Schema::create('fornecedores', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 200)->notNullable()->unique();
            $table->string('email', 200)->nullable()->unique()->email();
            $table->string('telefone', 20)->nullable();
            $table->string('descricao', 200)->nullable();
            $table->string('endereco', 500)->nullable(); 
            $table->boolean('ativo')->default(true);             
            $table->timestamps();
            $table->softDeletes();

            $table->index('nome');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('compras', function (Blueprint $table) {
            $table->dropForeign(['fornecedor_id']);
            $table->dropColumn('fornecedor_id');
        });

        Schema::dropIfExists('fornecedores');
    }
};
