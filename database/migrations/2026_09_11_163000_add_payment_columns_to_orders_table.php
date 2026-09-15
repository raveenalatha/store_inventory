<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentColumnsToOrdersTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'amount_given')) {
                $table->decimal('amount_given', 12, 2)->nullable();
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'change_due')) {
                $table->decimal('change_due', 12, 2)->nullable();
            }
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'amount_given')) {
                $table->dropColumn('amount_given');
            }

            if (Schema::hasColumn('orders', 'change_due')) {
                $table->dropColumn('change_due');
            }
        });
    }
}
