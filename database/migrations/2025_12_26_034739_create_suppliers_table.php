<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('supplier')) {
        Schema::create('supplier', function (Blueprint $table) {
            $table->id('supplier_id');
            
            // Company Information
            $table->string('supplier_name', 200);
            $table->string('supplier_code', 50)->unique()->nullable();
            
            // Contact Information
            $table->string('contact_person', 200)->nullable();
            $table->string('contact_position', 100)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 11)->nullable();
            $table->string('website', 255)->nullable();
            
            // Address
            $table->string('address_line1', 255)->nullable();
            $table->string('address_line2', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->default('Philippines');
            
            // Business Details
            $table->text('products_supplied')->nullable();
            $table->text('product_categories')->nullable();
            $table->string('payment_terms', 200)->nullable();
            $table->string('delivery_terms', 200)->nullable();
            $table->decimal('minimum_order_amount', 10, 2)->nullable();
            
            // Performance
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('total_orders')->default(0);
            $table->date('last_order_date')->nullable();
            
            // System Fields
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('supplier_name');
            $table->index('supplier_code');
            $table->index('is_active');
        });
    }
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier');
    }
};