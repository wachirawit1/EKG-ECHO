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
        Schema::create('appointment', function (Blueprint $table) {
            $table->increments('a_id');
            $table->string('hn',7);
            $table->string('tel',50);
            $table->string('ward',30);
            $table->string('doc_id',6);
            $table->date('a_date');
            $table->string('a_time',20);
            $table->text('note');
            $table->dateTime('create_at');
            $table->string('create_by',50);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointment');
        
    }
};
