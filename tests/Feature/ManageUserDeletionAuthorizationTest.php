<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class ManageUserDeletionAuthorizationTest extends TestCase
{
    private string $fixtureMarker;

    private array $fixtureEmails = [];

    private int $fixtureSequence = 0;

    private mixed $originalMainAdminId;

    private bool $safeDatabaseVerified = false;

    private $mongoConnection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalMainAdminId = config('admin.main_admin_user_id');

        $this->assertSame('mongodb', config('database.default'));
        $this->assertSame('mongodb', config('database.connections.mongodb.driver'));
        $this->assertSame('sbci_clearance_test', config('database.connections.mongodb.database'));

        $this->mongoConnection = app('db')->connection('mongodb');

        $this->assertSame('sbci_clearance_test', $this->mongoConnection->getDatabaseName());
        $this->assertNotSame('sbci_clearance', $this->mongoConnection->getDatabaseName());

        $this->safeDatabaseVerified = true;
        $this->fixtureMarker = (string) Str::uuid();
    }

    protected function tearDown(): void
    {
        try {
            if ($this->safeDatabaseVerified && $this->fixtureEmails !== []) {
                $collection = $this->mongoConnection->getCollection((new User())->getTable());
                $fixtureFilter = [
                    '_manage_users_test_marker' => $this->fixtureMarker,
                    'email' => ['$in' => $this->fixtureEmails],
                ];

                $collection->deleteMany($fixtureFilter);

                $this->assertSame(0, $collection->countDocuments($fixtureFilter));
            }
        } finally {
            if (isset($this->originalMainAdminId)) {
                config(['admin.main_admin_user_id' => $this->originalMainAdminId]);
            }

            parent::tearDown();
        }
    }

    public function test_main_admin_can_soft_delete_a_student(): void
    {
        $this->assertDeletionAllowed('admin', true, 'student');
    }

    public function test_main_admin_can_soft_delete_a_staff_member(): void
    {
        $this->assertDeletionAllowed('admin', true, 'staff');
    }

    public function test_main_admin_can_soft_delete_another_admin(): void
    {
        $this->assertDeletionAllowed('admin', true, 'admin');
    }

    public function test_main_admin_cannot_soft_delete_their_own_account(): void
    {
        $actor = $this->createUser('admin');
        config(['admin.main_admin_user_id' => (string) $actor->getKey()]);

        $response = $this->actingAs($actor)
            ->delete(route('admin.users.destroy', ['id' => $actor->getKey()]));

        $response->assertForbidden();
        $this->assertUserRemainsActive($actor);
    }

    public function test_regular_admin_can_soft_delete_a_student(): void
    {
        $this->assertDeletionAllowed('admin', false, 'student');
    }

    public function test_regular_admin_can_soft_delete_a_staff_member(): void
    {
        $this->assertDeletionAllowed('admin', false, 'staff');
    }

    public function test_regular_admin_cannot_soft_delete_another_admin(): void
    {
        $this->assertDeletionDenied('admin', 'admin');
    }

    public function test_regular_admin_cannot_soft_delete_the_main_admin(): void
    {
        $actor = $this->createUser('admin');
        $mainAdmin = $this->createUser('admin');
        config(['admin.main_admin_user_id' => (string) $mainAdmin->getKey()]);

        $response = $this->actingAs($actor)
            ->delete(route('admin.users.destroy', ['id' => $mainAdmin->getKey()]));

        $response->assertForbidden();
        $this->assertUserRemainsActive($mainAdmin);
    }

    public function test_staff_cannot_delete_users_through_a_direct_delete_request(): void
    {
        $this->assertRoleCannotDelete('staff');
    }

    public function test_student_cannot_delete_users_through_a_direct_delete_request(): void
    {
        $this->assertRoleCannotDelete('student');
    }

    public function test_unauthenticated_user_cannot_delete_users(): void
    {
        $target = $this->createUser('student');

        $response = $this->delete(route('admin.users.destroy', ['id' => $target->getKey()]));

        $response->assertRedirect(route('login'));
        $this->assertUserRemainsActive($target);
    }

    private function assertDeletionAllowed(string $actorRole, bool $isMainAdmin, string $targetRole): void
    {
        $actor = $this->createUser($actorRole);
        $target = $this->createUser($targetRole);

        config([
            'admin.main_admin_user_id' => $isMainAdmin
                ? (string) $actor->getKey()
                : $this->fixtureMarker . '-configured-main-admin',
        ]);

        $response = $this->actingAs($actor)
            ->from('/admin/users')
            ->delete(route('admin.users.destroy', ['id' => $target->getKey()]));

        $response->assertRedirect('/admin/users');
        $this->assertNotNull(
            User::withTrashed()->where('email', $target->email)->first()?->deleted_at
        );
        $this->assertNull(User::query()->where('email', $target->email)->first());
    }

    private function assertDeletionDenied(string $actorRole, string $targetRole): void
    {
        $actor = $this->createUser($actorRole);
        $target = $this->createUser($targetRole);

        config(['admin.main_admin_user_id' => $this->fixtureMarker . '-configured-main-admin']);

        $response = $this->actingAs($actor)
            ->delete(route('admin.users.destroy', ['id' => $target->getKey()]));

        $response->assertForbidden();
        $this->assertUserRemainsActive($target);
    }

    private function assertRoleCannotDelete(string $role): void
    {
        $actor = $this->createUser($role);
        $target = $this->createUser('student');

        $response = $this->actingAs($actor)
            ->delete(route('admin.users.destroy', ['id' => $target->getKey()]));

        $response->assertForbidden();
        $this->assertUserRemainsActive($target);
    }

    private function assertUserRemainsActive(User $user): void
    {
        $this->assertNotNull(User::query()->where('email', $user->email)->first());
        $this->assertNull(User::withTrashed()->where('email', $user->email)->first()?->deleted_at);
    }

    private function createUser(string $role): User
    {
        $sequence = ++$this->fixtureSequence;
        $email = sprintf(
            'manage-users-delete-%s-%d@example.test',
            $this->fixtureMarker,
            $sequence
        );
        $this->fixtureEmails[] = $email;

        $user = new User();
        $user->setAttribute('_id', $this->fixtureMarker . '-' . $sequence);
        $user->setAttribute('_manage_users_test_marker', $this->fixtureMarker);
        $user->name = 'Disposable Manage Users test account';
        $user->email = $email;
        $user->password = Str::random(48);
        $user->role = $role;
        $user->save();

        return $user;
    }
}
