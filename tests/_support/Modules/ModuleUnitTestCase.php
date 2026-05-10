<?php

namespace Tests\Support\Modules;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Reusable base test case for new module unit tests.
 *
 * Module teams can extend this class to keep metadata and scope checks
 * consistent across all module implementations.
 *
 * @internal
 */
abstract class ModuleUnitTestCase extends CIUnitTestCase
{
    /**
     * @param array<string, mixed> $metadata
     */
    protected function assertValidModuleMetadata(array $metadata): void
    {
        $this->assertArrayHasKey('slug', $metadata);
        $this->assertArrayHasKey('name', $metadata);
        $this->assertArrayHasKey('scope_type', $metadata);

        $this->assertNotSame('', trim((string) ($metadata['slug'] ?? '')));
        $this->assertNotSame('', trim((string) ($metadata['name'] ?? '')));
        $this->assertContains((string) ($metadata['scope_type'] ?? ''), ['programme', 'project']);
    }

    protected function assertValidScopeId(int $scopeId): void
    {
        $this->assertGreaterThan(0, $scopeId);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    protected function makeModuleMetadata(array $overrides = []): array
    {
        return array_merge([
            'slug' => 'sample_module',
            'name' => 'Sample Module',
            'scope_type' => 'project',
            'description' => 'Template metadata for a module test.',
        ], $overrides);
    }
}
