<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case CashWallet = 'cash_wallet';
    case Instapay = 'instapay';
    case BankCard = 'bank_card';

    public function label(): string
    {
        return __('messages.payment_methods.'.$this->value);
    }

    /**
     * @return array<string, string> value => Arabic label
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $method) => [$method->value => $method->label()])
            ->all();
    }
}
