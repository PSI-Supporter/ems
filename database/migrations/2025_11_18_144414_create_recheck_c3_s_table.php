<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recheck_c3_s', function (Blueprint $table) {
            $table->id();
            $table->string('code', 21);
            $table->string('compare_poin', 7);
            $table->string('compare_value_1', 31);
            $table->string('compare_value_2', 31);
            $table->char('compare_status', 1);
            $table->string('client_ip', 17);
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();
            $table->string('created_by', 9);
            $table->string('updated_by', 9)->nullable();
            $table->string('deleted_by', 9)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recheck_c3_s');
    }
};
