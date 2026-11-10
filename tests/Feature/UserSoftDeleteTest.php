<?php

namespace Tests\Feature;

use App\Http\Controllers\PasswordController;
use App\Models\Clearance;
use App\Models\User;
use App\Support\StudentListOrganizer;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\ViewErrorBag;
use Mockery;
use Tests\TestCase;

class UserSoftDeleteTest extends TestCase
{
    public function test_user_model_uses_laravels_soft_deletes_trait(): void
    {
        $this->assertContains(SoftDeletes::class, class_uses_recursive(User::class));
    }

    public function test_soft_deleting_an_account_keeps_clearance_history_and_hides_account_from_normal_queries(): void
    {
        $this->assertSame('mongodb', config('database.default'));
        $this->assertSame('mongodb', config('database.connections.mongodb.driver'));
        $this->assertSame('sbci_clearance_test', config('database.connections.mongodb.database'));

        $connection = app('db')->connection('mongodb');

        $this->assertSame('sbci_clearance_test', $connection->getDatabaseName());
        $this->assertNotSame('sbci_clearance', $connection->getDatabaseName());

        $identifier = (string) Str::uuid();
        $email = 'soft-delete-'.$identifier.'@example.test';
        $studentId = 'soft-delete-'.$identifier;
        $user = null;
        $clearance = null;

        try {
            $user = new User;
            $user->name = 'Disposable soft-delete test account';
            $user->email = $email;
            $user->password = Str::random(48);
            $user->role = 'student';
            $user->student_id = $studentId;
            $user->setAttribute('integration_test_run_id', $identifier);
            $user->save();

            $clearance = new Clearance;
            $clearance->student_id = $studentId;
            $clearance->department = 'Soft-delete test '.$identifier;
            $clearance->status = 'Approved';
            $clearance->remarks = 'Disposable soft-delete test clearance';
            $clearance->setAttribute('integration_test_run_id', $identifier);
            $clearance->save();

            $provider = new EloquentUserProvider(app('hash'), User::class);
            $this->assertNotNull($provider->retrieveByCredentials(['email' => $email]));

            $user->delete();

            $this->assertNull(User::query()->find($user->getKey()));
            $deletedUser = User::withTrashed()->find($user->getKey());
            $this->assertNotNull($deletedUser);
            $this->assertNotNull($deletedUser->deleted_at);
            $this->assertNull($provider->retrieveByCredentials(['email' => $email]));

            $retainedClearance = Clearance::query()->find($clearance->getKey());
            $this->assertNotNull($retainedClearance);
            $this->assertSame($studentId, $retainedClearance->student_id);
            $this->assertSame('Approved', $retainedClearance->status);
        } finally {
            try {
                $clearanceCollection = $connection->getCollection((new Clearance)->getTable());
                $clearanceCollection->deleteMany(['integration_test_run_id' => $identifier]);
                $this->assertSame(
                    0,
                    $clearanceCollection->countDocuments(['integration_test_run_id' => $identifier])
                );
            } finally {
                $userCollection = $connection->getCollection((new User)->getTable());
                $userCollection->deleteMany(['integration_test_run_id' => $identifier]);
                $this->assertSame(
                    0,
                    $userCollection->countDocuments(['integration_test_run_id' => $identifier])
                );
            }
        }
    }

    public function test_manage_users_blade_renders_student_filters_and_separate_sections(): void
    {
        $admin = new User();
        $admin->setAttribute('_id', 'fixture-admin');
        $admin->role = 'admin';
        Auth::guard()->setUser($admin);
        $student = $this->user('fixture-student', 'student');
        $student->name = 'Student Example';
        $student->email = 'student@example.test';
        $student->student_id = 'S-100';
        $student->program = 'BSCRIM';
        $student->year_level = '1st Year';
        $staff = $this->user('fixture-staff', 'staff');
        $staff->name = 'Staff Example';
        $staff->email = 'staff@example.test';
        $otherAdmin = $this->user('fixture-other-admin', 'admin');
        $otherAdmin->name = 'Admin Example';
        $otherAdmin->email = 'admin@example.test';
        $studentList = app(StudentListOrganizer::class)->organize(
            new Collection([$student]),
            null,
            null,
            null
        );

        $html = view('admin.users', [
            'studentsByProgram' => $studentList['groups'],
            'programOptions' => $studentList['programOptions'],
            'yearLevelOptions' => $studentList['yearLevelOptions'],
            'studentCount' => $studentList['studentCount'],
            'search' => '',
            'selectedProgram' => '',
            'selectedYearLevel' => '',
            'errors' => new ViewErrorBag(),
            'staffUsers' => new Collection([$staff]),
            'adminUsers' => new Collection([$otherAdmin]),
            'canDeleteUserIds' => [
                'fixture-student' => true,
                'fixture-staff' => true,
                'fixture-other-admin' => true,
            ],
            'isMainAdmin' => true,
        ])->render();

        $this->assertStringContainsString('Search name or program', $html);
        $this->assertStringContainsString('Course / Program', $html);
        $this->assertStringContainsString('Year Level', $html);
        $this->assertStringContainsString('Student Example', $html);
        $this->assertStringContainsString('<select name="program" required>', $html);
        $this->assertStringContainsString('BEEd – GenEd', $html);
        $this->assertStringContainsString('BSEd – Math', $html);
        $this->assertStringContainsString('BSHM', $html);
        $this->assertStringContainsString('BSCA', $html);
        $this->assertStringContainsString('BSIT', $html);
        $this->assertStringContainsString('BSTM', $html);
        $this->assertStringContainsString('BSEntrep', $html);
        $this->assertStringContainsString('BSCrim', $html);
        $this->assertStringContainsString('<option value="BSCRIM" selected>', $html);
        $this->assertStringContainsString('BSCRIM (existing value)', $html);
        $this->assertStringContainsString('Staff Accounts', $html);
        $this->assertStringContainsString('Staff Example', $html);
        $this->assertStringContainsString('Admin Accounts', $html);
        $this->assertStringContainsString('Admin Example', $html);
        $this->assertStringContainsString('Deactivate this student account?', $html);
        $this->assertStringContainsString('Deactivate this admin account?', $html);
    }

    public function test_password_update_only_targets_an_active_user_document(): void
    {
        $user = $this->user('fixture-user-id', 'student');
        $user->password = Hash::make('current-password');
        Auth::guard()->setUser($user);

        $result = Mockery::mock();
        $result->shouldReceive('getMatchedCount')->once()->andReturn(0);

        $collection = Mockery::mock();
        $collection->shouldReceive('updateOne')
            ->once()
            ->withArgs(function (array $filter): bool {
                return $filter['_id'] === 'fixture-user-id'
                    && array_key_exists('deleted_at', $filter)
                    && $filter['deleted_at'] === null;
            })
            ->andReturn($result);

        $database = Mockery::mock();
        $database->shouldReceive('selectCollection')->with('users')->andReturn($collection);

        $client = Mockery::mock();
        $client->shouldReceive('selectDatabase')->with('sbci_clearance')->andReturn($database);

        $connection = Mockery::mock();
        $connection->shouldReceive('getClient')->andReturn($client);

        DB::shouldReceive('connection')->with('mongodb')->andReturn($connection);

        $request = Request::create('/change-password', 'PUT', [
            'current_password' => 'current-password',
            'password' => 'replacement-password',
            'password_confirmation' => 'replacement-password',
        ]);

        (new PasswordController())->update($request);
    }

    private function user(string $id, string $role): User
    {
        $user = new User();
        $user->setAttribute('_id', $id);
        $user->role = $role;

        return $user;
    }
}
