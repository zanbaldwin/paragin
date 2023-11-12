<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Exam;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExamsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('/exams', name: 'list_exams', methods: [Request::METHOD_GET])]
    public function listAction(): Response
    {
        $repository = $this->em->getRepository(Exam::class);
        $exams = $repository->findAll();

        return $this->render('exams/list.html.twig', [
            'exams' => $exams,
        ]);
    }

    #[Route('/exam/{exam}', name: 'show_exam', methods: [Request::METHOD_GET])]
    public function showExamAction(Exam $exam): Response
    {
        return $this->render('exams/show.html.twig', [
            'exam' => $exam,
        ]);
    }

    #[Route('/exam/{exam}/students', name: 'student_results', methods: [Request::METHOD_GET])]
    public function showStudentScores(Exam $exam): Response
    {
        $examRepository = $this->em->getRepository(Exam::class);
        return $this->render('exams/students.html.twig', [
            'exam' => $exam,
            'results' => $examRepository->getStudentPercentagesForExam($exam)
        ]);
    }

    #[Route('/exam/{exam}/analytics', name: 'exam_analytics', methods: [Request::METHOD_GET])]
    public function showExamAnalytics(Exam $exam): Response
    {
        $examRepository = $this->em->getRepository(Exam::class);
        return $this->render('exams/analytics.html.twig', [
            'exam' => $exam,
            'questionStats' => $examRepository->getQuestionStatsForExam($exam),
        ]);
    }
}
