<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserListController extends AbstractController
{
    protected EntityManagerInterface $em;
    protected UserRepository $repo;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repo = $entityManager->getRepository(User::class);
        $this->em = $entityManager;
    }

    #[Route('/users')]
    public function index(): Response
    {
        $users = $this->repo->findAll();
        return $this->render('user_list.html.twig', ['users' => $users]);
    }
}
