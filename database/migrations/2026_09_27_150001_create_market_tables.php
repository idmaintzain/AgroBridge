<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->bigInteger('available_kobo')->default(0);
            $table->bigInteger('payable_kobo')->default(0);
            $table->timestamps();
        });

        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('crop');
            $table->string('unit', 16);
            $table->unsignedInteger('quantity_on_hand');
            $table->unsignedInteger('quantity_reserved')->default(0);
            $table->unsignedBigInteger('price_per_unit_kobo');
            $table->string('state');
            $table->string('lga');
            $table->date('collect_by');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['crop', 'state', 'is_active']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->restrictOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('farmer_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_kobo');
            $table->unsignedBigInteger('item_kobo');
            $table->unsignedBigInteger('buyer_fee_kobo');
            $table->unsignedBigInteger('seller_fee_kobo');
            $table->unsignedBigInteger('total_kobo');
            $table->unsignedBigInteger('payout_kobo');
            $table->unsignedBigInteger('platform_fee_kobo');
            $table->unsignedSmallInteger('buyer_fee_percent');
            $table->unsignedSmallInteger('seller_fee_percent');
            $table->unsignedInteger('buyer_fee_floor_kobo');
            $table->unsignedInteger('seller_fee_floor_kobo');
            $table->unsignedSmallInteger('dispute_window_hours');
            $table->unsignedSmallInteger('collection_window_hours');
            $table->string('status', 32);
            $table->text('collection_code')->nullable();
            $table->unsignedTinyInteger('code_attempts')->default(0);
            $table->timestamp('payment_deadline_at');
            $table->timestamp('collection_deadline_at')->nullable();
            $table->timestamp('dispute_window_ends_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('dispute_opened_at')->nullable();
            $table->text('dispute_reason')->nullable();
            $table->text('resolution_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'payment_deadline_at']);
            $table->index(['buyer_id', 'status']);
            $table->index(['farmer_id', 'status']);
        });

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('account', 32);
            $table->string('direction', 8);
            $table->string('type', 16);
            $table->unsignedBigInteger('amount_kobo');
            $table->string('memo');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['order_id', 'type']);
        });

        Schema::create('platform_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('value');
            $table->timestamps();
        });

        Schema::create('platform_setting_revisions', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('old_value')->nullable();
            $table->string('new_value');
            $table->text('reason');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });

        $now = now();
        $settings = [
            'fees.buyer_percent' => '2',
            'fees.buyer_floor_kobo' => '5000',
            'fees.seller_percent' => '5',
            'fees.seller_floor_kobo' => '10000',
            'windows.dispute_hours' => '24',
            'windows.payment_hours' => '24',
            'windows.collection_hours' => '48',
        ];

        foreach ($settings as $key => $value) {
            DB::table('platform_settings')->insert([
                'key' => $key,
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_setting_revisions');
        Schema::dropIfExists('platform_settings');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('listings');
        Schema::dropIfExists('wallets');
    }
};
