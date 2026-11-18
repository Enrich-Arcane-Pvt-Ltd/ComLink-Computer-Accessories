<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_returns', function (Blueprint $table) {
            if (! Schema::hasColumn('customer_returns', 'payment_receipt_path')) {
                $table->string('payment_receipt_path')->nullable()->after('admin_notes');
            }
            if (! Schema::hasColumn('customer_returns', 'evidence_image_path')) {
                $table->string('evidence_image_path')->nullable()->after('payment_receipt_path');
            }
            if (! Schema::hasColumn('customer_returns', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('evidence_image_path');
            }
            if (! Schema::hasColumn('customer_returns', 'bank_branch')) {
                $table->string('bank_branch')->nullable()->after('bank_name');
            }
            if (! Schema::hasColumn('customer_returns', 'account_number')) {
                $table->string('account_number')->nullable()->after('bank_branch');
            }
            if (! Schema::hasColumn('customer_returns', 'account_name')) {
                $table->string('account_name')->nullable()->after('account_number');
            }
            if (! Schema::hasColumn('customer_returns', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('account_name');
            }
            if (! Schema::hasColumn('customer_returns', 'requested_by_customer')) {
                $table->boolean('requested_by_customer')->default(false)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customer_returns', function (Blueprint $table) {
            $cols = [
                'payment_receipt_path',
                'evidence_image_path',
                'bank_name',
                'bank_branch',
                'account_number',
                'account_name',
                'rejection_reason',
                'requested_by_customer',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('customer_returns', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
