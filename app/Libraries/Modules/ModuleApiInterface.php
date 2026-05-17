<?php

/**
 * Contract for internal module APIs exposed to other modules.
 */

namespace App\Libraries\Modules;

interface ModuleApiInterface
{
    /**
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    public function read(string $moduleSlug, string $resource, array $query, int $requesterId): array;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function create(string $moduleSlug, string $resource, array $data, int $requesterId): array;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function update(string $moduleSlug, string $resource, int $id, array $data, int $requesterId): array;
}
