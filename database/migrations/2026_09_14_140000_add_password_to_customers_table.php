<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddPasswordToCustomersTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'password')) {
                $table->string('password')->nullable()->after('email');
            }
        });

        $this->ensureEmailIsUnique();
    }

    /**
     * @return void
     */
    public function down()
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'password')) {
                $table->dropColumn('password');
            }
        });
    }

    /**
     * @return void
     */
    protected function ensureEmailIsUnique()
    {
        $connection = Schema::getConnection()->getDriverName();

        if ($connection === 'sqlite') {
            return;
        }

        $indexes = DB::select('SHOW INDEX FROM customers WHERE Column_name = ? AND Non_unique = 0', ['email']);

        if (! empty($indexes)) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->unique('email');
        });
    }
}
