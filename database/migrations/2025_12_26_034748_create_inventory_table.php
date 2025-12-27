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
        if (!Schema::hasTable('inventory')) {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id('item_id');
            
            // Item Information
            $table->string('item_code', 50)->unique();
            $table->string('item_name', 200);
            $table->string('item_type', 100);
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            
            // Stock Information
            $table->integer('quantity')->default(0);
            $table->string('unit_of_measure', 50)->default('piece');
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('retail_price', 10, 2)->nullable();
            $table->integer('low_stock_threshold')->default(5);
            $table->integer('reorder_quantity')->nullable();
            
            // Supplier Information
            $table->foreignId('supplier_id')->nullable()->constrained('supplier', 'supplier_id')->onDelete('set null');
            $table->string('supplier_product_code', 100)->nullable();
            
            // Location
            $table->string('location', 100)->nullable();
            
            // Item Details
            $table->string('warranty_period', 100)->nullable();
            
            // Tracking
            $table->date('last_restocked_date')->nullable();
            $table->date('last_ordered_date')->nullable();
            
            // System Fields
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('item_code');
            $table->index('item_name');
            $table->index('item_type');
            $table->index('supplier_id');
        });
    }
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};