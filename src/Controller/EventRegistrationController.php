<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventRegistration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventRegistrationController extends AbstractController
{
    #[Route('/event/{id}/register', name: 'event_register')]
    public function register(Event $event, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $existing = $em->getRepository(EventRegistration::class)->findOneBy([
            'user' => $user,
            'event' => $event,
        ]);

        if ($existing) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cet événement.');

            return $this->redirectToRoute('front_event_show', ['id' => $event->getId()]);
        }

        $registration = new EventRegistration();
        $registration->setUser($user);
        $registration->setEvent($event);

        $em->persist($registration);
        $em->flush();

        $this->addFlash('success', 'Inscription enregistrée !');

        return $this->redirectToRoute('front_event_show', ['id' => $event->getId()]);
    }

    #[Route('/event/{id}/unregister', name: 'event_unregister')]
    public function unregister(Event $event, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $registration = $em->getRepository(EventRegistration::class)->findOneBy([
            'user' => $user,
            'event' => $event,
        ]);

        if (!$registration) {
            $this->addFlash('warning', 'Vous n’êtes pas inscrit à cet événement.');

            return $this->redirectToRoute('front_event_show', ['id' => $event->getId()]);
        }

        $em->remove($registration);
        $em->flush();

        $this->addFlash('success', 'Vous êtes désinscrit de cet événement.');

        return $this->redirectToRoute('front_event_show', ['id' => $event->getId()]);
    }
}
