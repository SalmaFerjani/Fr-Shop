<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SwaggerController extends AbstractController
{
    #[Route('/api/doc', name: 'api_doc')]
    public function index(): Response
    {
        return $this->render('swagger/index.html.twig');
    }
}
