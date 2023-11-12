<?php declare(strict_types=1);

namespace App\DTO;

use App\Entity\Exam;
use App\Entity\Question;

class QuestionStats
{
    public function __construct(
        public readonly Exam $exam,
        public readonly Question $question,
        public readonly float $averageScore,
    ) {}

    public function getPDash(): float
    {
        return $this->averageScore / $this->question->getMaxPoints();
    }
}
