<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datasets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sha256', 64);
            $table->text('manifest');
            $table->timestamp('imported_at');
        });
        Schema::create('territories', function (Blueprint $table) {
            $table->string('code', 7)->primary();
            $table->string('name');
            $table->string('uf', 2)->index();
            $table->string('region')->index();
        });
        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained();
            $table->string('territory_code', 7);
            $table->foreign('territory_code')->references('code')->on('territories');
            $table->unsignedSmallInteger('year')->index();
            foreach (['idhm', 'idhm_educacao', 'idhm_renda', 'idhm_longevidade', 'renda_per_capita'] as $metric) {
                $table->double($metric)->nullable();
            }
            $table->unique(['territory_code', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observations');
        Schema::dropIfExists('territories');
        Schema::dropIfExists('datasets');
    }
};
