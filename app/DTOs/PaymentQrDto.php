<?php

namespace App\DTOs;

class PaymentQrDto
{
    public function __construct(
        public ?string $qrId = null,
        public ?string $transactionId = null,
        public ?string $paymentDate = null,
        public ?string $paymentTime = null,
        public ?float $amount = null,
        public ?string $currency = 'BOB',
        public ?string $senderBankCode = null,
        public ?string $senderName = null,
        public ?string $senderDocumentId = null,
        public ?string $senderAccount = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            qrId: $data['qrId'] ?? null,
            transactionId: $data['transactionId'] ?? null,
            paymentDate: $data['paymentDate'],
            paymentTime: $data['paymentTime'],
            amount: isset($data['amount']) ? (float)$data['amount'] : null,
            currency: $data['currency'] ?? 'BOB',
            senderBankCode: $data['senderBankCode'],
            senderName: $data['senderName'],
            senderDocumentId: $data['senderDocumentId'],
            senderAccount: $data['senderAccount'],
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'qrId' => $this->qrId,
            'transactionId' => $this->transactionId,
            'paymentDate' => $this->paymentDate,
            'paymentTime' => $this->paymentTime,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'senderBankCode' => $this->senderBankCode,
            'senderName' => $this->senderName,
            'senderDocumentId' => $this->senderDocumentId,
            'senderAccount' => $this->senderAccount
            
        ], fn($value) => !is_null($value));
    }
}