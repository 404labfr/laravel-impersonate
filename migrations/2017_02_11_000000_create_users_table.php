<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        DB::table('users')->insert([
            [
                'id'         =>  1,
                'name'       => 'Admin',
                'email'      => 'admin@test.rocks',
                'password'   => bcrypt('password'),
                'role'       => 'admin',
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'id'         =>  2,
                'name'       => 'User',
                'email'      => 'user@test.rocks',
                'password'   => bcrypt('password'),
                'role'       => 'user',
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'id'         =>  3,
                'name'       => 'SuperAdmin',
                'email'      => 'superadmin@test.rocks',
                'password'   => bcrypt('password'),
                'role'       => 'superadmin',
                'created_at' => Carbon::now()->toDateTimeString(),
            ],
            [
                'id'         =>  4,
                'name'       => 'Manager',
                'email'      => 'manager@test.rocks',
                'password'   => bcrypt('password'),
                'role'       => 'manager',
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
        Schema::dropIfExists('users');
    }
}
