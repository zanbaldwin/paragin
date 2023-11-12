<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\Exam;
use App\Entity\Student;

class StudentExamResult
{
    public const PASS_PERCENT = 70.0;

    public function __construct(
        public readonly Exam    $exam,
        public readonly int     $maxPoints,
        public readonly Student $student,
        public readonly int     $points,
    ) {}

    public function getPercentage(): float
    {
        return $this->maxPoints > 0
            ? (100 * $this->points) / $this->maxPoints
            : 100.0;
    }

    public function isPass(): bool
    {
        return $this->getPercentage() >= self::PASS_PERCENT;
    }

    /** A absolute guess on how to calculate 'caesura'. I think this is what the specs want, but not completely sure. */
    public function getGrade(): string
    {
        if ($this->points === $this->maxPoints) {
            return '10.0';
        }

        $percentage = $this->getPercentage();
        if ($percentage <= 20.0) {
            return '1.0';
        }

        if ($percentage < 70.0) {
            $gradient = ($percentage - 20.0) / 50.0;
            $result = 1.0 + ((5.5 - 1.0) * $gradient);
            return sprintf('%.1f', $result);
        }

        $gradient = ($percentage - 70.0) / 30.0;
        $result = 5.5 + ((10.0 - 5.5) * $gradient);
        return sprintf('%.1f', $result);
    }
}
