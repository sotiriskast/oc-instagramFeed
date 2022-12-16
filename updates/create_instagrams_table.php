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
            $table->string('access_token',255)->nullable();
            $table->string('num_images',4)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ideaseven_instagram_instagrams');
    }
}
