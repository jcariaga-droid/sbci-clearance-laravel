<?php

namespace Tests\Feature;

use App\Models\User;
use App\Http\Controllers\PasswordController;
use App\Support\StudentListOrganizer;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ViewErrorBag;
use Mockery;
use Tests\TestCase;

class UserSoftDeleteFixture extends Authenticatable
{
    use SoftDeletes;

    protected $table = 'soft_delete_users';

    protected $guarded = [];
}

class UserSoftDeleteTest extends TestCase
{
    public function test_user_model_uses_laravels_soft_deletes_trait(): void
    {
        $this->assertContains(SoftDeletes::class, class_uses_recursive(User::class));
    }

    public function test_soft_deleting_an_account_keeps_clearance_history_and_hides_account_from_normal_queries(): void
    {
        Schema::create('soft_delete_users', function ($table): void {
            $table->increments('id');
            $table->string('email');
            $table->string('password')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('soft_delete_clearances', function ($table): void {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('status');
        });

        $user = UserSoftDeleteFixture::create(['email' => 'fixture@example.test']);
        $clearanceId = Schema::getConnection()->table('soft_delete_clearances')->insertGetId([
            'user_id' => $user->id,
            'status' => 'Approved',
        ]);
        $provider = new EloquentUserProvider(app('hash'), UserSoftDeleteFixture::class);

        $this->assertNotNull($provider->retrieveByCredentials(['email' => $user->email]));

        $user->delete();

        $this->assertNull(UserSoftDeleteFixture::find($user->id));
        $this->assertNotNull(UserSoftDeleteFixture::withTrashed()->find($user->id)->deleted_at);
        $this->assertNull($provider->retrieveByCredentials(['email' => $user->email]));
        $this->assertDatabaseHas('soft_delete_clearances', [
            'id' => $clearanceId,
            'user_id' => $user->id,
            'status' => 'Approved',
        ]);
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
        $student->program = 'BSIT';
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
