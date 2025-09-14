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
    Schema::table('compras', function (Blueprint $table) {
      $table->dropColumn(['estornado', 'status']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('compras', function (Blueprint $table) {
      $table->boolean('estornado')->default(false)->after('total');
      $table->string('status')->default('pendente')->after('estornado');
    });
  }
};
