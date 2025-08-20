<?php

declare(strict_types=1);

use App\Enum\Location;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            
            $table->string('name');
            $table->text('type');

            $table->enum('category', Location\Category::values());

            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            
            $table->string('authors')->nullable();
            
            $table->timestamp('published_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['category']);
            $table->index(['published_at']);
            $table->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
