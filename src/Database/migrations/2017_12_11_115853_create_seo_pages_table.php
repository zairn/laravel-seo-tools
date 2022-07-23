<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSeoPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seo_pages', function (Blueprint $table) {
            $table->increments('id');

            $table->text('path');
            $table->index([DB::raw('path(220)')]);
            $table->unique([DB::raw('path(220)')]);


            $table->string('object', 80)->nullable()->index();
            $table->string('object_id', 80)->nullable()->index();
            $table->string('robot_index', 50)->default('noindex')->nullable();
            $table->string('robot_follow', 50)->default('nofollow')->nullable();
            
            $table->text('canonical_url')->nullable();


            $table->string('title', 180)->nullable()->index();
            $table->string('title_source', 180)->nullable()->index();
            $table->string('description', 180)->nullable();
            $table->string('description_source', 180)->nullable();


            $table->string('change_frequency', 20)->default('monthly');
            $table->double('priority', 4)->default(0.5);
            $table->longText('schema')->nullable();
            $table->string('focus_keyword')->nullable();
            $table->text('tags')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seo_pages');
    }
}
