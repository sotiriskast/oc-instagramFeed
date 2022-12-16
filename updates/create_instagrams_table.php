<?php namespace Ideaseven\Instagram\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateInstagramsTable Migration
 */
class CreateInstagramsTable extends Migration
{
    public function up()
    {
        Schema::create('ideaseven_instagram_instagrams', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ideaseven_instagram_instagrams');
    }
}
