<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('poll_id');
            $table->unsignedBigInteger('poll_option_id');

            $table->string('ip_address', 45);

            $table->boolean('released')->default(false);

            $table->timestamps();

            $table->foreign('poll_id')
                ->references('id')->on('polls')
                ->onDelete('cascade');

            $table->foreign('poll_option_id')
                ->references('id')->on('poll_options')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('votes');
    }
};
