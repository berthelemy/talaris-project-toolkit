<?php

use App\Libraries\Auth\RbacService;
use App\Models\AuthAuditLogModel;
use App\Models\ProgrammeModel;
use App\Models\ProgrammeProjectModel;
use App\Models\ProjectModel;
use App\Models\UserModel;
use App\Models\UserRoleAssignmentModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class ProgrammeProjectDomainSystemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    public function testCreateUpdateDeleteProgrammeAndProjectWithAuditLogs(): void
    {
        $actor = $this->createUser('phase4owner', 'phase4owner@example.com');
        $this->grantCreationPermissions((int) $actor['id']);

        $programmeCreate = $this->withSession($this->sessionForUser($actor))
            ->withBodyFormat('form')
            ->post('/programmes', [
                'name' => 'Programme Alpha',
                'description' => 'Programme description',
            ]);

        $programmeCreate->assertRedirectTo('/dashboard');

        $programme = (new ProgrammeModel())->where('name', 'Programme Alpha')->first();
        $this->assertNotNull($programme);
        $this->assertSame((int) $actor['id'], (int) $programme['owner_user_id']);

        $projectCreate = $this->withSession($this->sessionForUser($actor))
            ->withBodyFormat('form')
            ->post('/projects', [
                'name' => 'Project One',
                'description' => 'Project description',
            ]);

        $projectCreate->assertRedirectTo('/dashboard');

        $project = (new ProjectModel())->where('name', 'Project One')->first();
        $this->assertNotNull($project);

        $programmeUpdate = $this->withSession($this->sessionForUser($actor))
            ->withBodyFormat('form')
            ->post('/programmes/' . (int) $programme['id'], [
                'name' => 'Programme Alpha Updated',
                'description' => 'Updated programme',
            ]);

        $programmeUpdate->assertRedirectTo('/dashboard');

        $projectDelete = $this->withSession($this->sessionForUser($actor))
            ->withBodyFormat('form')
            ->post('/projects/' . (int) $project['id'] . '/delete');

        $projectDelete->assertRedirectTo('/dashboard');

        $deletedProject = (new ProjectModel())->find((int) $project['id']);
        $this->assertNull($deletedProject);

        $events = (new AuthAuditLogModel())
            ->whereIn('event_type', ['programme_created', 'programme_updated', 'project_created', 'project_deleted'])
            ->findAll();

        $this->assertCount(4, $events);
    }

    public function testProgrammeProjectLinkingAndManagerAssignmentsArePersisted(): void
    {
        $actor = $this->createUser('phase4manager', 'phase4manager@example.com');
        $manager = $this->createUser('phase4assigned', 'phase4assigned@example.com');
        $this->grantCreationPermissions((int) $actor['id']);

        (new ProgrammeModel())->insert([
            'name' => 'Programme Link',
            'description' => null,
            'owner_user_id' => (int) $actor['id'],
        ]);
        (new ProjectModel())->insert([
            'name' => 'Project Link',
            'description' => null,
            'owner_user_id' => (int) $actor['id'],
        ]);

        $programme = (new ProgrammeModel())->where('name', 'Programme Link')->first();
        $project = (new ProjectModel())->where('name', 'Project Link')->first();

        $this->withSession($this->sessionForUser($actor))
            ->withBodyFormat('form')
            ->post('/programmes/' . (int) $programme['id'] . '/projects/' . (int) $project['id'] . '/link')
            ->assertRedirectTo('/dashboard');

        $link = (new ProgrammeProjectModel())
            ->where('programme_id', (int) $programme['id'])
            ->where('project_id', (int) $project['id'])
            ->first();

        $this->assertNotNull($link);

        $this->withSession($this->sessionForUser($actor))
            ->withBodyFormat('form')
            ->post('/programmes/' . (int) $programme['id'] . '/managers', [
                'user_id' => (int) $manager['id'],
            ])
            ->assertRedirectTo('/dashboard');

        $this->withSession($this->sessionForUser($actor))
            ->withBodyFormat('form')
            ->post('/projects/' . (int) $project['id'] . '/managers', [
                'user_id' => (int) $manager['id'],
            ])
            ->assertRedirectTo('/dashboard');

        $assignments = (new UserRoleAssignmentModel())
            ->where('user_id', (int) $manager['id'])
            ->findAll();

        $this->assertCount(2, $assignments);

        $this->withSession($this->sessionForUser($actor))
            ->withBodyFormat('form')
            ->post('/programmes/' . (int) $programme['id'] . '/projects/' . (int) $project['id'] . '/unlink')
            ->assertRedirectTo('/dashboard');

        $removedLink = (new ProgrammeProjectModel())
            ->where('programme_id', (int) $programme['id'])
            ->where('project_id', (int) $project['id'])
            ->first();

        $this->assertNull($removedLink);
    }

    public function testValidationAndAuthorizationFailuresAreHandled(): void
    {
        $actor = $this->createUser('phase4member', 'phase4member@example.com');
        $other = $this->createUser('phase4other', 'phase4other@example.com');

        $invalidCreate = $this->withSession($this->sessionForUser($actor))
            ->withBodyFormat('form')
            ->post('/programmes', [
                'name' => '',
            ]);

        $invalidCreate->assertRedirect();

        (new ProgrammeModel())->insert([
            'name' => 'Protected Programme',
            'description' => null,
            'owner_user_id' => (int) $other['id'],
        ]);

        $protected = (new ProgrammeModel())->where('name', 'Protected Programme')->first();

        $unauthorizedUpdate = $this->withSession($this->sessionForUser($actor))
            ->withBodyFormat('form')
            ->post('/programmes/' . (int) $protected['id'], [
                'name' => 'Try update',
                'description' => '',
            ]);

        $unauthorizedUpdate->assertRedirectTo('/dashboard');

        $fresh = (new ProgrammeModel())->find((int) $protected['id']);
        $this->assertSame('Protected Programme', (string) $fresh['name']);
    }

    private function grantCreationPermissions(int $userId): void
    {
        $rbac = new RbacService();
        $rbac->assignRoleToUser($userId, 'programme_manager', 'system', null, $userId);
        $rbac->assignRoleToUser($userId, 'project_manager', 'system', null, $userId);
    }

    /**
     * @return array<string, mixed>
     */
    private function createUser(string $username, string $email): array
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

    /**
     * @param array<string, mixed> $user
     *
     * @return array<string, mixed>
     */
    private function sessionForUser(array $user): array
    {
        return [
            'user_id' => (int) $user['id'],
            'username' => (string) $user['username'],
            'last_activity_at' => time(),
        ];
    }
}