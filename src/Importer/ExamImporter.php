<?php declare(strict_types=1);

namespace App\Importer;

use App\Entity\Answer;
use App\Entity\Exam;
use App\Entity\Question;
use App\Entity\Student;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Row;

class ExamImporter
{
    private const MAX_OBJECTS_IN_UNIT_OF_WORK = 250;

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    public function import(string $filename): void
    {
        $spreadsheet = IOFactory::load($filename, readers: [
            IOFactory::READER_XLS,
            IOFactory::READER_XLSX,
        ]);
        // Assume that exam results are always stored in the first worksheet.
        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->getRowIterator();
        $rows->rewind();

        $exam = new Exam(basename($filename));
        $this->em->persist($exam);

        // First, figure out the questions on the exam.
        $questionEntities = $this->generateQuestions($exam, $rows->current());
        $rows->next();
        $this->setMaxPointsForQuestions($questionEntities, $rows->current());

        // Don't persist the exam and questions to the database until we know there's at least one valid set of student answers.

        $unpersistedObjectCount = 0;
        while (true) {
            $rows->next();
            try {
                $unpersistedObjectCount += $this->processStudentRow($questionEntities, $rows->current());
            } catch (\OutOfBoundsException) {
                break;
            }

            if ($unpersistedObjectCount > self::MAX_OBJECTS_IN_UNIT_OF_WORK) {
                $this->em->flush();
                $unpersistedObjectCount = 0;
            }
        }

        $this->em->flush();
    }

    /** @return Question[] */
    private function generateQuestions(Exam $exam, Row $questionsRow): array
    {
        $questionsCellIterator = $questionsRow->getCellIterator();
        $questionsCellIterator->setIterateOnlyExistingCells(true);
        $questionCells = iterator_to_array($questionsCellIterator);
        // Remove the first element from the question names (ID).
        array_shift($questionCells);
        $questionCells = array_values($questionCells);
        return array_map(function (Cell $questionCell) use ($exam): Question {
            $question = new Question($exam, $questionCell->getValue());
            $this->em->persist($question);
            return $question;
        }, $questionCells);
    }

    /** @param Question[] $questions */
    private function setMaxPointsForQuestions(array $questions, Row $pointsRow): void
    {
        $pointsCellIterator = $pointsRow->getCellIterator();
        $pointsCellIterator->setIterateOnlyExistingCells(true);
        // Get the max points cells, skipping the first one (Max question score:) and limiting to the number of questions.
        // Array keys are the column names, reset to zero-based index.
        $points = array_values(array_map(
            fn (Cell $cell): mixed => $cell->getValue(),
            array_slice(iterator_to_array($pointsCellIterator), 1, count($questions), false)
        ));
        foreach ($points as $index => $maxPointsForQuestion) {
            if (is_numeric($maxPointsForQuestion)) {
                $questions[$index]->setMaxPoints((int) $maxPointsForQuestion);
            }
        }
    }

    private function processStudentRow(array $questions, Row $studentRow): int
    {
        $unpersistedObjectCount = 0;

        $studentCellIterator = $studentRow->getCellIterator();
        $studentCellIterator->setIterateOnlyExistingCells(true);
        $studentResults = array_values(array_map(fn (Cell $cell): mixed => $cell->getValue(), iterator_to_array($studentCellIterator)));

        if (empty($studentResults[0])) {
            throw new \OutOfBoundsException;
        }

        $student = new Student($studentResults[0]);
        $this->em->persist($student);
        $unpersistedObjectCount++;

        array_shift($studentResults);
        $studentResults = array_values(array_slice($studentResults, 0, count($questions)));
        foreach ($studentResults as $index => $pointsForStudent) {
            if (is_numeric($pointsForStudent)) {
                $answer = new Answer($questions[$index], $student, (int) $pointsForStudent);
                $this->em->persist($answer);
                $unpersistedObjectCount++;
            }
        }

        return $unpersistedObjectCount;
    }
}
