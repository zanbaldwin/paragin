<?php declare(strict_types=1);

namespace App\Repository;

use App\DTO\StudentExamResult;
use App\Entity\Exam;
use App\Entity\Student;
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
    public function getStudentPercentagesForExam(Exam $exam): array
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
}
