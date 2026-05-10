<?php

use Tests\Support\Modules\ModuleUnitTestCase;

/**
 * Reference template test for module authors.
 *
 * Copy this file, rename the class, and replace the sample metadata with
 * module-specific values and assertions.
 *
 * @internal
 */
final class ModuleUnitTemplateExampleTest extends ModuleUnitTestCase
{
    public function testTemplateMetadataPassesBaselineChecks(): void
    {
        $metadata = $this->makeModuleMetadata([
            'slug' => 'hello_world_project',
            'name' => 'Hello World (Project)',
            'scope_type' => 'project',
        ]);

        $this->assertValidModuleMetadata($metadata);
        $this->assertValidScopeId(1);
    }
}
