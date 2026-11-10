<?php

namespace Tests\Unit;

use App\Models\User;
use App\Policies\UserManagementPolicy;
use Tests\TestCase;

class UserManagementPolicyTest extends TestCase
{
    private UserManagementPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        config(['admin.main_admin_user_id' => 'main-admin-id']);
        $this->policy = new UserManagementPolicy();
    }

    public function test_deletion_permission_matrix(): void
    {
        $mainAdmin = $this->user('main-admin-id', 'admin');
        $regularAdmin = $this->user('regular-admin-id', 'admin');
        $student = $this->user('student-id', 'student');
        $staff = $this->user('staff-id', 'staff');
        $otherAdmin = $this->user('other-admin-id', 'admin');

        $this->assertTrue($this->policy->canDelete($mainAdmin, $student));
        $this->assertTrue($this->policy->canDelete($mainAdmin, $staff));
        $this->assertTrue($this->policy->canDelete($mainAdmin, $otherAdmin));
        $this->assertFalse($this->policy->canDelete($mainAdmin, $mainAdmin));

        $this->assertTrue($this->policy->canDelete($regularAdmin, $student));
        $this->assertTrue($this->policy->canDelete($regularAdmin, $staff));
        $this->assertFalse($this->policy->canDelete($regularAdmin, $otherAdmin));
        $this->assertFalse($this->policy->canDelete($regularAdmin, $mainAdmin));
        $this->assertFalse($this->policy->canDelete($regularAdmin, $regularAdmin));

        $this->assertFalse($this->policy->canDelete($staff, $student));
        $this->assertFalse($this->policy->canDelete($student, $staff));
    }

    public function test_role_changes_are_limited_to_main_admin_when_admin_role_is_involved(): void
    {
        $mainAdmin = $this->user('main-admin-id', 'admin');
        $regularAdmin = $this->user('regular-admin-id', 'admin');
        $student = $this->user('student-id', 'student');
        $otherAdmin = $this->user('other-admin-id', 'admin');

        $this->assertTrue($this->policy->canChangeRole($regularAdmin, $student, 'staff'));
        $this->assertFalse($this->policy->canChangeRole($regularAdmin, $student, 'admin'));
        $this->assertFalse($this->policy->canChangeRole($regularAdmin, $otherAdmin, 'staff'));

        $this->assertTrue($this->policy->canChangeRole($mainAdmin, $student, 'admin'));
        $this->assertTrue($this->policy->canChangeRole($mainAdmin, $otherAdmin, 'staff'));
        $this->assertFalse($this->policy->canChangeRole($mainAdmin, $mainAdmin, 'staff'));
        $this->assertFalse($this->policy->canChangeRole($regularAdmin, $mainAdmin, 'staff'));
    }

    private function user(string $id, string $role): User
    {
        $user = new User();
        $user->setAttribute('_id', $id);
        $user->role = $role;

        return $user;
    }
}
