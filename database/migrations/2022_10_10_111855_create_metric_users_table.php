<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetricUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('metric_users', function (Blueprint $table) {
            $table->id();
            $table->string('course_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('name_user')->nullable()->default("00:00:00");
            $table->string('time_consumed')->nullable()->default("00:00:00");
            $table->string('package_id')->nullable();
            $table->string('finished')->nullable();
            $table->string('tenant_id')->nullable();
            $table->string('percent_watched')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('metric_users');
    }
}