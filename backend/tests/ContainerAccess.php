<?php

declare(strict_types=1);

namespace App\Tests;

/**
 * Accès typé aux services du conteneur de test (à utiliser dans un KernelTestCase).
 */
trait ContainerAccess
{
    /**
     * @template T of object
     *
     * @param class-string<T> $id
     *
     * @return T
     */
    private static function service(string $id): object
    {
        $service = self::getContainer()->get($id);
        self::assertInstanceOf($id, $service);

        return $service;
    }
}
