<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;

class StudentListOrganizer
{
    private const NOT_SET_FILTER = 'missing';

    private const YEAR_LEVEL_ORDER = [
        '1st Year' => 0,
        '2nd Year' => 1,
        '3rd Year' => 2,
        '4th Year' => 3,
    ];

    /**
     * @return array{
     *     groups: array<int, array{program: string, years: array<int, array{level: string, students: EloquentCollection}>}>,
     *     programOptions: array<int, array{value: string, label: string}>,
     *     yearLevelOptions: array<int, array{value: string, label: string}>,
     *     studentCount: int
     * }
     */
    public function organize(
        EloquentCollection $students,
        ?string $search,
        ?string $programFilter,
        ?string $yearLevelFilter
    ): array {
        $programValues = [];
        $yearLevelValues = [];
        $hasMissingProgram = false;
        $hasMissingYearLevel = false;

        foreach ($students as $student) {
            $program = $this->fieldValue($student->program);
            $yearLevel = $this->fieldValue($student->year_level);

            if ($program === null) {
                $hasMissingProgram = true;
            } else {
                $programValues[$this->filterValue($program)] = $program;
            }

            if ($yearLevel === null) {
                $hasMissingYearLevel = true;
            } else {
                $yearLevelValues[$this->filterValue($yearLevel)] = $yearLevel;
            }
        }

        $programOptions = $this->options($programValues, $hasMissingProgram);
        $yearLevelOptions = $this->options($yearLevelValues, $hasMissingYearLevel, true);
        $search = Str::lower(trim((string) $search));
        $grouped = [];
        $studentCount = 0;

        foreach ($students as $student) {
            $program = $this->fieldValue($student->program);
            $yearLevel = $this->fieldValue($student->year_level);
            $programFilterValue = $program === null
                ? self::NOT_SET_FILTER
                : $this->filterValue($program);
            $yearFilterValue = $yearLevel === null
                ? self::NOT_SET_FILTER
                : $this->filterValue($yearLevel);
            $programLabel = $program ?? 'Not Set';
            $yearLabel = $yearLevel ?? 'Not Set';
            $name = $this->fieldValue($student->name) ?? '';

            if (
                !$this->matchesFilter($programFilter, $programFilterValue)
                || !$this->matchesFilter($yearLevelFilter, $yearFilterValue)
            ) {
                continue;
            }

            if (
                $search !== ''
                && !str_contains(Str::lower($name), $search)
                && !str_contains(Str::lower($programLabel), $search)
                && !str_contains(Str::lower($yearLabel), $search)
            ) {
                continue;
            }

            $programKey = $program === null ? self::NOT_SET_FILTER : $this->filterValue($program);
            $yearKey = $yearLevel === null ? self::NOT_SET_FILTER : $this->filterValue($yearLevel);

            $grouped[$programKey]['program'] = $programLabel;
            $grouped[$programKey]['years'][$yearKey]['level'] = $yearLabel;
            $grouped[$programKey]['years'][$yearKey]['students'][] = $student;
            $studentCount++;
        }

        $groups = array_values($grouped);

        foreach ($groups as &$group) {
            $yearGroups = array_values($group['years']);

            foreach ($yearGroups as &$yearGroup) {
                $yearGroup['students'] = new EloquentCollection(
                    collect($yearGroup['students'])
                        ->sort(function ($left, $right) {
                            $byName = strnatcasecmp(
                                $this->fieldValue($left->name) ?? '',
                                $this->fieldValue($right->name) ?? ''
                            );

                            return $byName !== 0
                                ? $byName
                                : strcmp((string) $left->getKey(), (string) $right->getKey());
                        })
                        ->values()
                        ->all()
                );
            }

            unset($yearGroup);
            usort($yearGroups, fn (array $left, array $right): int => $this->compareYearLevels(
                $left['level'],
                $right['level']
            ));
            $group['years'] = $yearGroups;
        }

        unset($group);
        usort($groups, static function (array $left, array $right): int {
            if ($left['program'] === 'Not Set' && $right['program'] !== 'Not Set') {
                return 1;
            }

            if ($right['program'] === 'Not Set' && $left['program'] !== 'Not Set') {
                return -1;
            }

            return strnatcasecmp($left['program'], $right['program']);
        });

        return [
            'groups' => $groups,
            'programOptions' => $programOptions,
            'yearLevelOptions' => $yearLevelOptions,
            'studentCount' => $studentCount,
        ];
    }

    private function fieldValue(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $value = (string) $value;

        return trim($value) === '' ? null : $value;
    }

    private function filterValue(string $value): string
    {
        return 'value:' . base64_encode($value);
    }

    /**
     * @param array<string, string> $values
     * @return array<int, array{value: string, label: string}>
     */
    private function options(array $values, bool $hasMissing, bool $orderYears = false): array
    {
        $options = [];

        foreach ($values as $filterValue => $label) {
            $options[] = ['value' => $filterValue, 'label' => $label];
        }

        usort($options, function (array $left, array $right) use ($orderYears): int {
            if ($orderYears) {
                return $this->compareYearLevels($left['label'], $right['label']);
            }

            return strnatcasecmp($left['label'], $right['label']);
        });

        if ($hasMissing) {
            $options[] = ['value' => self::NOT_SET_FILTER, 'label' => 'Not Set'];
        }

        return $options;
    }

    private function matchesFilter(?string $filter, string $value): bool
    {
        return $filter === null || $filter === '' || $filter === $value;
    }

    private function compareYearLevels(string $left, string $right): int
    {
        $leftRank = self::YEAR_LEVEL_ORDER[$left] ?? ($left === 'Not Set' ? 5 : 4);
        $rightRank = self::YEAR_LEVEL_ORDER[$right] ?? ($right === 'Not Set' ? 5 : 4);

        return $leftRank === $rightRank
            ? strnatcasecmp($left, $right)
            : $leftRank <=> $rightRank;
    }
}
