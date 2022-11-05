<?php

use App\Models\Dojo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCategoryIdFromDojosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $dojos = Dojo::all();
        foreach($dojos as $dojo) {
            $dojo->categories()->attach($dojo->category_id);
        }

        Schema::table('dojos', function (Blueprint $table) {
            $table->dropColumn('category_id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dojos', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable();
        });
    }
}
