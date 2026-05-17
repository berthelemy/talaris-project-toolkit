<?php

/**
 * Shared module system-test support utilities.
 */

namespace App\Modules\TestSupport\Testing;

use App\Models\ProjectModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Shared base for module-level system tests.
 *
 * @internal
 */
abstract class ModuleSystemTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    /**
     * @return array<string, mixed>
     */
    protected function createUser(string $username, string $email): array
    {
        $model = new UserModel();
        $model->insert([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash('StrongPass!123', PASSWORD_DEFAULT),
            'is_active' => 1,
        ]);

        return (array) $model->where('username', $username)->first();
    }

    protected function createProject(int $ownerId, string $name): int
    {
        $projectId = (new ProjectModel())->insert([
            'name' => $name,
            'description' => null,
            'owner_user_id' => $ownerId,
        ], true);

        $this->assertIsInt($projectId);

        return $projectId;
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    protected function authSession(array $user): array
    {
        return [
            'user_id' => (int) $user['id'],
            'username' => (string) $user['username'],
            'last_activity_at' => time(),
        ];
    }
}
