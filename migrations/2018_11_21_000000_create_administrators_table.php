<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateAdministratorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('administrators', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_admin')->default(1)->index();
            $table->boolean('can_be_impersonated')->default(1)->index();
            $table->rememberToken();
            $table->timestamps();
        });

        DB::table('administrators')->insert([
            [
                'name'       => 'Administrator',
                'email'      => 'admin@test.rocks',
                'password'   => bcrypt('password'),
                'is_admin'   => 1,
                'can_be_impersonated' => 0,
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'name'       => 'SuperAdministrator',
                'email'      => 'superadmin@test.rocks',
                'password'   => bcrypt('password'),
                'is_admin'   => 1,
                'can_be_impersonated' => 1,
                'created_at' => Carbon::now()->toDateTimeString(),
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
        Schema::dropIfExists('administrators');
    }
}
