<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHrmProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Authentication uses the existing hrm_profile table. This migration
     * is skipped when the table is already present.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('hrm_profile')) {
            return;
        }

        Schema::create('hrm_profile', function (Blueprint $table) {
            $table->id();
            $table->string('username', 50);
            $table->string('name', 50);
            $table->string('email', 100);
            $table->string('password');
            $table->enum('role', ['admin', 'manager', 'staff'])->default('staff');
            $table->boolean('active')->nullable()->default(true);
            $table->dateTime('last_login')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hrm_profile');
    }
}
