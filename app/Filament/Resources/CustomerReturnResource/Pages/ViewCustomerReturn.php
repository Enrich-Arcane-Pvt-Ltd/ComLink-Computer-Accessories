<?php

namespace App\Filament\Resources\CustomerReturnResource\Pages;

use App\Filament\Resources\CustomerReturnResource\CustomerReturnResource;
use App\Models\CustomerReturn;
use App\Services\ReturnRefundService;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerReturn extends ViewRecord
{
    protected static string $resource = CustomerReturnResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve Refund')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (CustomerReturn $record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Critical Action: Confirm Refund Approval')
                ->modalDescription('Are you sure you want to APPROVE this customer refund? Once approved, the refund amount will be deducted from Total Sales/Income and the returned items will enter Damaged Stock for Supplier Return.')
                ->modalSubmitActionLabel('Yes, Approve Refund')
                ->form([
                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Approval Notes (Optional)')
                        ->rows(2),
                ])
                ->action(function (CustomerReturn $record, array $data) {
                    app(ReturnRefundService::class)->approveCustomerReturn($record, $data['admin_notes'] ?? null);

                    Notification::make()
                        ->title('Refund Request Approved')
                        ->body("Return {$record->return_number} approved. Sales & Damaged Stock updated.")
                        ->success()
                        ->send();

                    $this->refreshFormData(['status', 'refunded_at', 'admin_notes']);
                }),

            Actions\Action::make('reject')
                ->label('Reject Refund')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (CustomerReturn $record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->modalHeading('Critical Action: Confirm Refund Rejection')
                ->modalDescription('Are you sure you want to REJECT this customer refund request? The customer will see "Rejected" on their Orders page with instructions to call ComLink for more information.')
                ->modalSubmitActionLabel('Yes, Reject Refund')
                ->form([
                    Forms\Components\Textarea::make('rejection_reason')
                        ->label('Rejection Reason (Optional)')
                        ->rows(2),
                ])
                ->action(function (CustomerReturn $record, array $data) {
                    app(ReturnRefundService::class)->rejectCustomerReturn($record, $data['rejection_reason'] ?? null);

                    Notification::make()
                        ->title('Refund Request Rejected')
                        ->body("Return {$record->return_number} has been rejected.")
                        ->warning()
                        ->send();

                    $this->refreshFormData(['status', 'rejection_reason']);
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Return & Refund Summary')
                    ->schema([
                        Infolists\Components\TextEntry::make('return_number')->label('Return No')->weight('bold'),
                        Infolists\Components\TextEntry::make('order.order_number')->label('Order No'),
                        Infolists\Components\TextEntry::make('order.customer_name')->label('Customer'),
                        Infolists\Components\TextEntry::make('refund_amount')->label('Refund Amount')->money('LKR')->color('danger')->weight('bold'),
                        Infolists\Components\TextEntry::make('refund_method')->label('Method')->badge()->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title()),
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                'approved' => 'success',
                                'pending' => 'warning',
                                'rejected' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state) => ucfirst($state)),
                        Infolists\Components\TextEntry::make('reason')->label('Reason'),
                        Infolists\Components\TextEntry::make('refunded_at')->label('Refunded At')->dateTime()->placeholder('Pending Approval'),
                        Infolists\Components\TextEntry::make('admin_notes')->label('Admin Notes')->placeholder('None')->columnSpanFull(),
                        Infolists\Components\TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->color('danger')
                            ->visible(fn (CustomerReturn $record) => $record->status === 'rejected')
                            ->columnSpanFull(),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Customer Refund Bank Details')
                    ->visible(fn (CustomerReturn $record) => ! empty($record->bank_name) || ! empty($record->account_number))
                    ->schema([
                        Infolists\Components\TextEntry::make('bank_name')->label('Bank Name')->weight('bold'),
                        Infolists\Components\TextEntry::make('bank_branch')->label('Branch'),
                        Infolists\Components\TextEntry::make('account_number')->label('Account Number')->copyable()->weight('bold'),
                        Infolists\Components\TextEntry::make('account_name')->label('Account Holder Name'),
                    ])
                    ->columns(4),

                Infolists\Components\Section::make('Uploaded Proof & Attachments')
                    ->visible(fn (CustomerReturn $record) => ! empty($record->payment_receipt_path) || ! empty($record->evidence_image_path))
                    ->schema([
                        Infolists\Components\ImageEntry::make('payment_receipt_path')
                            ->label('Payment Receipt')
                            ->disk('public')
                            ->height(220)
                            ->url(fn (CustomerReturn $record) => $record->payment_receipt_url, shouldOpenInNewTab: true),

                        Infolists\Components\ImageEntry::make('evidence_image_path')
                            ->label('Evidence Image for Return')
                            ->disk('public')
                            ->height(220)
                            ->url(fn (CustomerReturn $record) => $record->evidence_image_url, shouldOpenInNewTab: true),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Returned Items')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('product_name')->label('Product')->weight('bold'),
                                Infolists\Components\TextEntry::make('quantity')->label('Qty'),
                                Infolists\Components\TextEntry::make('unit_price')->label('Unit Price')->money('LKR'),
                                Infolists\Components\TextEntry::make('refund_subtotal')->label('Refund Subtotal')->money('LKR'),
                                Infolists\Components\TextEntry::make('unit_cost')->label('GRN Unit Cost')->money('LKR'),
                                Infolists\Components\TextEntry::make('condition')
                                    ->label('Condition')
                                    ->badge()
                                    ->color(fn (string $state) => $state === 'damaged' ? 'danger' : 'success'),
                                Infolists\Components\TextEntry::make('resolution_status')
                                    ->label('Resolution Status')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title()),
                            ])
                            ->columns(7),
                    ]),
            ]);
    }
}
