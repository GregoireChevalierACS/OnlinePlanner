<?php

declare(strict_types=1);

namespace App\Identity\Domain;

interface AccountRepository
{
    /**
     * @throws EmailAlreadyUsed si l'adresse est déjà prise (garanti aussi par le stockage)
     */
    public function save(Account $account): void;

    public function findByEmail(string $normalizedEmail): ?Account;

    public function find(AccountId $id): ?Account;
}
