<?php

namespace App\Models;

/**
 * Entity DataCorrection
 * Represents a record from the `data_corrections` table.
 */
class DataCorrection
{
    public function __construct(
        private int $requestId,
        private string $fieldCorrected,
        private ?string $oldValue,
        private ?string $newValue,
        private ?int $id = null,
        private ?string $correctedAt = null
    ) {}

    // --- Getters ---
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequestId(): int
    {
        return $this->requestId;
    }

    public function getFieldCorrected(): string
    {
        return $this->fieldCorrected;
    }

    public function getOldValue(): ?string
    {
        return $this->oldValue;
    }

    public function getNewValue(): ?string
    {
        return $this->newValue;
    }

    public function getCorrectedAt(): ?string
    {
        return $this->correctedAt;
    }
}