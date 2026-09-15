<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MoveAuthenticationToUsersTable extends Migration
{
    /**
     * Use `users` for authentication, without a username column.
     * Existing `hrm_profile` rows are copied into `users`.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('role', 20)->default('staff');
                $table->boolean('active')->default(true);
                $table->dateTime('last_login')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        } else {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'role')) {
                    $table->string('role', 20)->default('staff');
                }

                if (! Schema::hasColumn('users', 'active')) {
                    $table->boolean('active')->default(true);
                }

                if (! Schema::hasColumn('users', 'last_login')) {
                    $table->dateTime('last_login')->nullable();
                }
            });

            if (Schema::hasColumn('users', 'username')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('username');
                });
            }
        }

        $this->copyProfilesIntoUsers();
    }

    /**
     * @return void
     */
    protected function copyProfilesIntoUsers()
    {
        if (! Schema::hasTable('hrm_profile') || ! Schema::hasTable('users')) {
            return;
        }

        $profiles = DB::table('hrm_profile')->get();

        foreach ($profiles as $profile) {
            $exists = DB::table('users')->where('email', $profile->email)->exists();

            if ($exists) {
                continue;
            }

            DB::table('users')->insert([
                'name' => $profile->name,
                'email' => $profile->email,
                'password' => $profile->password,
                'role' => $profile->role ?: 'staff',
                'active' => $profile->active === null ? 1 : $profile->active,
                'last_login' => $profile->last_login,
                'created_at' => $profile->created_at,
                'updated_at' => $profile->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Keep the users table. Authentication depends on it.
    }
}
