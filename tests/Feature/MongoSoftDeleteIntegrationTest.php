<?php

namespace Tests\Feature;

use App\Models\Clearance;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

class MongoSoftDeleteIntegrationTest extends TestCase
{
    public function test_soft_deleting_user_preserves_clearance_in_isolated_mongodb_database(): void
    {
        $this->assertSame('mongodb', config('database.default'));
        $this->assertSame('mongodb', config('database.connections.mongodb.driver'));
        $this->assertSame('sbci_clearance_test', config('database.connections.mongodb.database'));

        $connection = app('db')->connection('mongodb');

        $this->assertSame('sbci_clearance_test', $connection->getDatabaseName());
        $this->assertNotSame('sbci_clearance', $connection->getDatabaseName());

        $identifier = (string) Str::uuid();
        $email = 'mongo-soft-delete-'.$identifier.'@example.test';
        $user = null;
        $clearance = null;

        try {
            $user = new User;
            $user->name = 'Disposable MongoDB integration account';
            $user->email = $email;
            $user->password = Str::random(48);
            $user->role = 'student';
            $user->setAttribute('integration_test_run_id', $identifier);
            $user->save();

            $clearance = new Clearance;
            $clearance->student_id = $email;
            $clearance->department = 'Integration test '.$identifier;
            $clearance->status = 'Pending';
            $clearance->remarks = 'Disposable integration fixture';
            $clearance->setAttribute('integration_test_run_id', $identifier);
            $clearance->save();

            $this->assertNotNull(User::query()->find($user->getKey()));
            $this->assertNotNull(Clearance::query()->find($clearance->getKey()));

            $user->delete();

            $this->assertNull(User::query()->find($user->getKey()));

            $deletedUser = User::withTrashed()->find($user->getKey());
            $this->assertNotNull($deletedUser);
            $this->assertNotNull($deletedUser->deleted_at);

            $retainedClearance = Clearance::query()->find($clearance->getKey());
            $this->assertNotNull($retainedClearance);
            $this->assertSame($email, $retainedClearance->student_id);
            $this->assertSame('Pending', $retainedClearance->status);
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
}
