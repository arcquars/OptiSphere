<?php

namespace App\DTOs;

class PaymentQr
{
    public function __construct(
        public ?string $qrId = null,
        public ?string $transactionId = null,
        public ?string $qrImage = null,
        public ?string $expirationDate = null,
        public ?float $amount = null,
        public ?string $currency = 'BOB',
        public ?string $gloss = null,
        public ?string $singleUse = 'true', // true/false como string para la API
        public ?array $additionalData = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            qrId: $data['qrId'] ?? null,
            transactionId: $data['transactionId'] ?? null,
            qrImage: $data['qrImage'] ?? $data['qrBase64'] ?? null,
            expirationDate: $data['expirationDate'] ?? null,
            amount: isset($data['amount']) ? (float)$data['amount'] : null,
            currency: $data['currency'] ?? 'BOB',
            gloss: $data['description'] ?? null,
            singleUse: isset($data['singleUse']) ? (string)$data['singleUse'] : 'true',
            additionalData: $data
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'qrId' => $this->qrId,
            'transactionId' => $this->transactionId,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->gloss,
            'singleUse' => $this->singleUse,
        ], fn($value) => !is_null($value));
    }
}