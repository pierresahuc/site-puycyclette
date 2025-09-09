<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RestHelperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @RouteResource("event")
 */
class EventController extends AbstractController implements ClassResourceInterface
{
    public function __construct(
        private ViewHandlerInterface $viewHandler,
        private FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        private DoctrineListBuilderFactoryInterface $listBuilderFactory,
        private RestHelperInterface $restHelper,
        private EntityManagerInterface $em
    ) {
    }

    public function cgetAction(): Response
    {
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors(Event::RESOURCE_KEY);
        $listBuilder = $this->listBuilderFactory->create(Event::class);
        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $listRepresentation = new PaginatedRepresentation(
            $listBuilder->execute(),
            Event::RESOURCE_KEY,
            $listBuilder->getCurrentPage(),
            $listBuilder->getLimit(),
            $listBuilder->count()
        );

        return $this->viewHandler->handle(View::create($listRepresentation));
    }

    public function getAction(int $id): Response
    {
        $event = $this->em->getRepository(Event::class)->find($id);

        if (!$event) {
            return $this->viewHandler->handle(View::create(['message' => 'Event not found'], 404));
        }

        return $this->viewHandler->handle(View::create($event));
    }

    public function postAction(Request $request): Response
    {
        $data = $request->request->all();

        $event = new Event();
        $event->setTitle($data['title'] ?? '');
        $event->setDescription($data['description'] ?? null);
        $event->setLocation($data['location'] ?? null);
        $event->setStartDate(new \DateTime($data['startDate'] ?? 'now'));

        $this->em->persist($event);
        $this->em->flush();

        return $this->viewHandler->handle(View::create($event, 201));
    }

    public function putAction(int $id, Request $request): Response
    {
        $event = $this->em->getRepository(Event::class)->find($id);

        if (!$event) {
            return $this->viewHandler->handle(View::create(['message' => 'Event not found'], 404));
        }

        $data = $request->request->all();

        $event->setTitle($data['title'] ?? $event->getTitle());
        $event->setDescription($data['description'] ?? $event->getDescription());
        $event->setLocation($data['location'] ?? $event->getLocation());

        if (isset($data['startDate'])) {
            $event->setStartDate(new \DateTime($data['startDate']));
        }

        $this->em->flush();

        return $this->viewHandler->handle(View::create($event));
    }

    public function deleteAction(int $id): Response
    {
        $event = $this->em->getRepository(Event::class)->find($id);

        if (!$event) {
            return $this->viewHandler->handle(View::create(['message' => 'Event not found'], 404));
        }

        $this->em->remove($event);
        $this->em->flush();

        return $this->viewHandler->handle(View::create(null, 204));
    }
}
