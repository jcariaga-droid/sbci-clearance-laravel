<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\StudentListOrganizer;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class StudentListOrganizerTest extends TestCase
{
    private StudentListOrganizer $organizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organizer = new StudentListOrganizer();
    }

    public function test_students_are_grouped_and_ordered_without_rewriting_missing_values(): void
    {
        $students = new Collection([
            $this->student('1', 'Zoe', 'BSIT', '2nd Year'),
            $this->student('2', 'Amy', 'BSIT', '1st Year'),
            $this->student('3', 'Ben', 'BSIT', '1st Year'),
            $this->student('4', 'Uma', 'BSBA', '4th Year'),
            $this->student('5', 'Lee', null, null),
            $this->student('6', 'Jay', 'Other Course', 'Preparatory'),
        ]);

        $result = $this->organizer->organize($students, null, null, null);

        $this->assertSame(
            ['BSBA', 'BSIT', 'Other Course', 'Not Set'],
            array_column($result['groups'], 'program')
        );
        $this->assertSame(
            ['1st Year', '2nd Year'],
            array_column($result['groups'][1]['years'], 'level')
        );
        $this->assertSame(
            ['Amy', 'Ben'],
            $result['groups'][1]['years'][0]['students']->pluck('name')->all()
        );
        $this->assertSame('Not Set', $result['groups'][3]['years'][0]['level']);
        $this->assertNull($students[4]->program);
        $this->assertNull($students[4]->year_level);
    }

    public function test_search_and_program_and_year_filters_compose(): void
    {
        $students = new Collection([
            $this->student('1', 'Alice Smith', 'BSIT', '2nd Year'),
            $this->student('2', 'Alicia Jones', 'BSIT', '1st Year'),
            $this->student('3', 'Alice Smith', 'BSBA', '2nd Year'),
            $this->student('4', 'Bobby Jones', 'BSIT', '2nd Year'),
        ]);

        $result = $this->organizer->organize(
            $students,
            'alice',
            'value:' . base64_encode('BSIT'),
            'value:' . base64_encode('2nd Year')
        );

        $this->assertSame(1, $result['studentCount']);
        $this->assertSame(
            ['Alice Smith'],
            $result['groups'][0]['years'][0]['students']->pluck('name')->all()
        );
        $this->assertSame(
            ['BSBA', 'BSIT'],
            array_column($result['programOptions'], 'label')
        );
    }

    public function test_search_matches_program_and_year_group_labels_and_unknown_values_are_preserved(): void
    {
        $students = new Collection([
            $this->student('1', 'Name One', 'Unique Program', 'Fifth Year'),
            $this->student('2', 'Name Two', 'Other Program', '3rd Year'),
        ]);

        $programSearch = $this->organizer->organize($students, 'unique program', null, null);
        $yearSearch = $this->organizer->organize($students, 'fifth year', null, null);

        $this->assertSame(['Unique Program'], array_column($programSearch['groups'], 'program'));
        $this->assertSame('Fifth Year', $programSearch['groups'][0]['years'][0]['level']);
        $this->assertSame(['Unique Program'], array_column($yearSearch['groups'], 'program'));
    }

    private function student(string $id, string $name, ?string $program, ?string $yearLevel): User
    {
        $user = new User();
        $user->setAttribute('_id', $id);
        $user->name = $name;
        $user->role = 'student';
        $user->program = $program;
        $user->year_level = $yearLevel;

        return $user;
    }
}
