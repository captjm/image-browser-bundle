<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SandboxController extends AbstractController
{
    #[Route('/', name: 'sandbox_home')]
    public function index(): Response
    {
        return $this->render('homepage.html.twig');
    }
}
