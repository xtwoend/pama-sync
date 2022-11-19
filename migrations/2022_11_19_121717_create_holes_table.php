<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateHolesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('site')->nullable();
            $table->string('pit')->nullable();
            $table->string('ewacs_location')->nullable();
            $table->integer('burden')->default(0);
            $table->integer('spacing')->default(0);
            $table->timestamps();
        });

        Schema::create('holes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('site_id');
            $table->unsignedBigInteger('truck_id');

            $table->string('hole_code');
            $table->float('deep', 5, 2)->default(0);
            $table->float('volume', 5, 2)->default(0);
            $table->string('condition')->nullable();
            
            $table->datetime('charge_started_at')->nullable();
            $table->datetime('charge_finished_at')->nullable();
            $table->integer('charge_duration')->default(0); // in seconds
            $table->float('an_weight')->default('0.00');
            $table->float('fo_weight')->default('0.00');
            $table->float('plan_weight')->default('0.00');
            $table->float('actual_weight')->default('0.00');
            $table->float('stemming_height')->default('0.00');
            $table->float('an_leftover')->default('0.00');
            $table->float('fo_leftover')->default('0.00');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
        Schema::dropIfExists('holes');
    }
}
