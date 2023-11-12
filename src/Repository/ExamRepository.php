<?php declare(strict_types=1);

namespace App\Repository;

use App\DTO\QuestionStats;
use App\DTO\StudentExamResult;
use App\Entity\Exam;
use App\Entity\Question;
use App\Entity\Student;
use App\Util;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Exam>
 *
 * @method Exam|null find($id, $lockMode = null, $lockVersion = null)
 * @method Exam|null findOneBy(array $criteria, array $orderBy = null)
 * @method Exam[]    findAll()
 * @method Exam[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exam::class);
    }

    /** @return StudentExamResult[] */
    public function getStudentResultsForExam(Exam $exam): array
    {
        $rsm = new ResultSetMappingBuilder(
            $this->getEntityManager(),
            ResultSetMappingBuilder::COLUMN_RENAMING_INCREMENT,
        );
        $rsm->addRootEntityFromClassMetadata(Student::class, 'student');
        $rsm->addScalarResult('_student_points', 'studentResult', 'integer');
        $rsm->addScalarResult('_max_points', 'maxPoints', 'integer');

        $sql = 'SELECT ' . ((string) $rsm) . ',
                SUM(question.max_points) AS _max_points,
                SUM(answer.points) AS _student_points
            FROM answer
            LEFT JOIN student ON answer.student_id = student.id
            LEFT JOIN question ON answer.question_id = question.id
            WHERE question.exam_id = :examId
            GROUP BY answer.student_id
            ORDER BY student.id ASC';

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->execute(['examId' => $exam->getId()]);
        return array_map(
            fn (array $result): StudentExamResult => new StudentExamResult($exam, $result['maxPoints'], $result[0], $result['studentResult']),
            $query->getResult(),
        );
    }

    public function getQuestionStatsForExam(Exam $exam): array
    {
        $rsm = new ResultSetMappingBuilder(
            $this->getEntityManager(),
            ResultSetMappingBuilder::COLUMN_RENAMING_INCREMENT,
        );
        $rsm->addRootEntityFromClassMetadata(Question::class, 'question');
        $rsm->addScalarResult('_question_average', 'averageScore', 'float');

        $sql = 'SELECT ' . ((string) $rsm) . ',
                AVG(answer.points) AS _question_average
            FROM answer
            LEFT JOIN question ON answer.question_id = question.id
            WHERE question.exam_id = :examId
            GROUP BY question.id
            ORDER BY question.id ASC';

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $query->execute(['examId' => $exam->getId()]);
        return array_map(
            fn (array $result): QuestionStats => new QuestionStats($exam, $result[0], $result['averageScore']),
            $query->getResult(),
        );
    }

    /** @return array<int, float> (questionID => correlationResult) */
    public function getPitValuesForExamQuestions(Exam $exam): array
    {
        $studentResultsArray = array_map(function (StudentExamResult $result): int {
            return $result->points;
        }, $this->getStudentResultsForExam($exam));
        $sql = 'SELECT question.id AS question_id, student.id AS student_id, answer.points AS student_points
                FROM answer
                LEFT JOIN student ON answer.student_id = student.id
                LEFT JOIN question ON answer.question_id = question.id
                WHERE question.exam_id = :examId
                ORDER BY question.id ASC, student.id ASC';
        $statement = $this->getEntityManager()->getConnection()->prepare($sql);
        $flatResult = $statement->executeQuery(['examId' => $exam->getId()]);
        $nestedResult = [];

        foreach ($flatResult->iterateAssociative() as $row) {
            $nestedResult[$row['question_id']] ??= [];
            $nestedResult[$row['question_id']][] = $row['student_points'];
        }

        $questionCorrelationResults = [];
        foreach ($nestedResult as $questionId => $studentPointsArray) {
            $questionCorrelationResults[$questionId] = Util::correlation($studentPointsArray, $studentResultsArray);
        }

        return $questionCorrelationResults;
    }
}
