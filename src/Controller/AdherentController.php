<?php

declare(strict_types=1);

// src/Controller/AdherentController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdherentController extends AbstractController
{
    #[Route('/espace-adherent', name: 'app_adherent')]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('adherent/index.html.twig', [
            'user' => $user,
        ]);
    }
}
