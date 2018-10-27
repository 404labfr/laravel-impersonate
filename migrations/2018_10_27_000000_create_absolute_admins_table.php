<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateAbsoluteAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('absolute_admins', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('username')->unique();
            $table->string('password');
            $table->timestamps();
        });

        DB::table('absolute_admins')->insert([
            [
                'username'       => 'absolute_admin',
                'password'   => bcrypt('password'),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('absolute_admins');
    }
}
