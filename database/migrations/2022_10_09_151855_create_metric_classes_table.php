<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetricClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('metric_classes', function (Blueprint $table) {
            $table->id();
            $table->string('class_id')->nullable();
            $table->string('module_id')->nullable();
            $table->string('name_module')->nullable();
            $table->string('name_aula')->nullable();
            $table->string('users_access')->nullable();
            $table->string('package_id')->nullable();
            $table->string('tenant_id')->nullable();
            $table->string('course_id')->nullable();
            $table->string('time_total')->nullable()->default("00:00:00");
            $table->string('time_consumed')->nullable()->default("00:00:00");
            $table->string('percent_users_watched')->nullable()->default(0);
            $table->string('users_finished')->nullable()->default(0);
            $table->string('users_finished_percented')->nullable()->default(0);
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
        Schema::dropIfExists('metric_classes');
    }
}