<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * database/migrations/xxxx_xx_xx_xxxxxx_create_review_table.php
     *
     * Run the migrations.
     *
     * Purpose:
     * - Creates the review table used for storing approved public/client reviews.
     */
    public function up(): void
    {
        Schema::create('review', function (Blueprint $table) {
            $table->bigIncrements('review_id');

            /*
            |--------------------------------------------------------------------------
            | Review Details
            |--------------------------------------------------------------------------
            */
            $table->string('reviewer_name');
            $table->integer('rating');
            $table->text('review_text');

            /*
            |--------------------------------------------------------------------------
            | Approval Status
            |--------------------------------------------------------------------------
            |
            | Approved reviews can be displayed publicly if needed.
            |
            */
            $table->boolean('is_approved')->default(true);

            /*
            |--------------------------------------------------------------------------
            | System Field
            |--------------------------------------------------------------------------
            |
            | The existing review table only has created_at, so this migration
            | follows the same structure instead of using full timestamps.
            |
            */
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */
            $table->index('rating');
            $table->index('is_approved');
        });

        /*
        |--------------------------------------------------------------------------
        | PostgreSQL CHECK Constraint
        |--------------------------------------------------------------------------
        |
        | Rating must only be from 1 to 5.
        |
        */
        DB::statement("
            ALTER TABLE review
            ADD CONSTRAINT review_rating_check
            CHECK (rating >= 1 AND rating <= 5)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review');
    }
};