<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachmentsTable extends Migration {

	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		Schema::create('attachments', function($table)
		{
			$table->increments('id');
			$table->integer('attachable_id');
			$table->string('attachable_type');
			$table->string('filename');
			$table->string('extension');
			$table->string('file_type');
			$table->bigInteger('size');

			$table->timestamp('created_at')->nullable();
			$table->timestamp('updated_at')->nullable();
		});
	}

	/**
	* Reverse the migrations.
	*
	* @return void
	*/
	public function down()
	{
		Schema::drop('attachments');
	}

}
