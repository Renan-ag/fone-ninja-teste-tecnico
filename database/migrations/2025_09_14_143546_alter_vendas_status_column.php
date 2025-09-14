<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up()
  {
    Schema::table('vendas', function (Blueprint $table) {
      $table->enum('status', ['pendente', 'concluída', 'cancelada'])->default('concluída')->change();
    });
  }

  public function down()
  {
    Schema::table('vendas', function (Blueprint $table) {
      $table->enum('status', ['pending', 'confirmed', 'canceled'])->default('confirmed')->change();
    });
  }
};
