<?php

declare(strict_types=1);

namespace Asaas\Sdk\Generator;

use Asaas\Sdk\AsaasSdk;

final class ParityVerifier
{
    /**
     * @return string[]
     */
    public function expectedServices(): array
    {
        return [
            'payment',
            'sandboxActions',
            'paymentWithSummaryData',
            'creditCard',
            'paymentRefund',
            'paymentSplit',
            'escrowAccount',
            'paymentDocument',
            'customer',
            'notification',
            'installment',
            'subscription',
            'pix',
            'pixTransaction',
            'anticipation',
            'recurringPix',
            'paymentLink',
            'checkout',
            'transfer',
            'paymentDunning',
            'bill',
            'mobilePhoneRecharge',
            'creditBureauReport',
            'financialTransaction',
            'finance',
            'accountInfo',
            'invoice',
            'fiscalInfo',
            'webhook',
            'subaccount',
            'accountDocument',
            'chargeback',
        ];
    }

    /**
     * @return string[]
     */
    public function currentServices(): array
    {
        $reflection = new \ReflectionClass(AsaasSdk::class);
        $properties = array_filter(
            $reflection->getProperties(\ReflectionProperty::IS_PUBLIC),
            static fn(\ReflectionProperty $prop) => !$prop->isStatic()
        );

        $names = array_map(static fn(\ReflectionProperty $prop) => $prop->getName(), $properties);
        sort($names);

        return $names;
    }
}
