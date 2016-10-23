<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AssociateBooksWithAuthors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('books', function (Blueprint $table) {

            // Create the author_id column as an unsigned integer
            $table->integer('author_id')->after('id')->unsigned();

            // Create a basic index for the author_id column
            $table->index('author_id');

            // Create a foreign key constraint and cascade on delete.
            $table
                ->foreign('author_id')
                ->references('id')
                ->on('authors')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('books', function (Blueprint $table) {
            // Drop the foreign key first
            $table->dropForeign('books_author_id_foreign');

            // Now drop the basic index
            $table->dropIndex('books_author_id_index');

            // Lastly, now it's safe to drop the column
            $table->dropColumn('author_id');
        });
    }
}
