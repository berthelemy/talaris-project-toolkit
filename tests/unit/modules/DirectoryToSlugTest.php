<?php

namespace Tests\Unit\Modules;

use App\Libraries\Modules\ModuleWidgetService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class DirectoryToSlugTest extends CIUnitTestCase
{
    public function testConvertsCommonNamingPatterns(): void
    {
        $service = new ModuleWidgetService();

        $this->assertSame('hello_world_project', $service->directoryToSlug('HelloWorldProject'));
        $this->assertSame('xml_parser', $service->directoryToSlug('XMLParser'));
        $this->assertSame('https_connection', $service->directoryToSlug('HTTPSConnection'));
        $this->assertSame('module_2', $service->directoryToSlug('Module2'));
        $this->assertSame('risk_2_analysis', $service->directoryToSlug('Risk2Analysis'));
        $this->assertSame('already_slugged', $service->directoryToSlug('Already_Slugged'));
    }
}
