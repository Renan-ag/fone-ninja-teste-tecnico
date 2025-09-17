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
    Schema::table('compra_produto', function (Blueprint $table) {
      $table->dropPrimary();

      if (Schema::hasColumn('compra_produto', 'id')) {
        $table->dropColumn('id');
      }

      $table->primary(['compra_id', 'produto_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('compra_produto', function (Blueprint $table) {
      $table->dropPrimary(['compra_id', 'produto_id']);

      $table->id()->first();
    });
  }
};
