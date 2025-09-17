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
    Schema::table('venda_produto', function (Blueprint $table) {
      $table->dropPrimary();

      if (Schema::hasColumn('venda_produto', 'id')) {
        $table->dropColumn('id');
      }

      $table->primary(['venda_id', 'produto_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('venda_produto', function (Blueprint $table) {
      $table->dropPrimary(['venda_id', 'produto_id']);

      $table->id()->first();
    });
  }
};
