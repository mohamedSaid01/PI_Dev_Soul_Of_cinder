<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Notification;
use App\Form\EventType;
use App\Service\EventService;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;


#[Route('/event')]
class EventController extends AbstractController
{
    // FRONT-OFFICE: Liste des événements pour les médecins
    #[Route('/medecin', name: 'front_medecin_event_index', methods: ['GET'])]
    public function medecinIndex(EventRepository $eventRepository): Response
    {
        return $this->render('front/event/medecin_index.html.twig', [
            'events_active' => $eventRepository->findBy(['isArchived' => false]),
            'events_archived' => $eventRepository->findBy(['isArchived' => true]),
        ]);
    }

    #[Route('/events/all', name: 'front_event_all')]
    public function allEvents(EventRepository $eventRepository): Response
    {
        // Récupère tous les événements (actifs et archivés)
        $events = $eventRepository->findAll();

        return $this->render('front/event/all.html.twig', [
            'events' => $events,
        ]);
    }
    // FRONT-OFFICE: Liste des événements pour les patients
    #[Route('/patient', name: 'front_patient_event_index', methods: ['GET'])]
    public function patientIndex(EventRepository $eventRepository): Response
    {
        return $this->render('front/event/patient_index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }

    // FRONT-OFFICE: Afficher un événement pour les médecins
    #[Route('/medecin/{id}', name: 'front_medecin_event_show', methods: ['GET'])]
    public function medecinShow(Event $event): Response
    {
        return $this->render('front/event/medecin_show.html.twig', [
            'event' => $event,
        ]);
    }

    // FRONT-OFFICE: Afficher un événement pour les patients
    #[Route('/patient/{id}', name: 'front_patient_event_show', methods: ['GET'])]
    public function patientShow(Event $event): Response
    {
        return $this->render('front/event/patient_show.html.twig', [
            'event' => $event,
        ]);
    }

    // FRONT-OFFICE: Créer un nouvel événement
    #[Route('/new', name: 'front_event_new', methods: ['GET', 'POST'])]
    public function frontNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setAfficheFile($form->get('afficheFile')->getData());
            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Événement créé avec succès.');
            return $this->redirectToRoute('front_medecin_event_index');
        }

        return $this->render('front/event/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // FRONT-OFFICE: Éditer un événement
    #[Route('/{id}/edit', name: 'front_event_edit', methods: ['GET', 'POST'])]
    public function frontEdit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $afficheFile = $form->get('afficheFile')->getData();
            if ($afficheFile) {
                $newFilename = uniqid() . '.' . $afficheFile->guessExtension();
                $afficheFile->move($this->getParameter('affiches_directory'), $newFilename);

                // Remove old file if it exists
                if ($event->getAffiche()) {
                    $oldFilePath = $this->getParameter('affiches_directory') . '/' . $event->getAffiche();
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }

                $event->setAffiche($newFilename);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Événement mis à jour avec succès.');
            return $this->redirectToRoute('front_medecin_event_show', ['id' => $event->getId()]);
        }

        return $this->render('front/event/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    

    // BACK-OFFICE: Liste des événements
    #[Route('/back', name: 'back_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        // Récupérer les événements actifs et archivés
        $eventsActive = $eventRepository->findBy(['isArchived' => false]);
        $eventsArchived = $eventRepository->findBy(['isArchived' => true]);

        return $this->render('back/event/index.html.twig', [
            'events_active' => $eventsActive,
            'events_archived' => $eventsArchived,
        ]);
    }

    // BACK-OFFICE: Créer un nouvel événement
    // src/Controller/EventController.php

    #[Route('/back/new', name: 'back_event_new', methods: ['GET', 'POST'])]
    public function backNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // Gestion de l'upload de l'affiche
                $afficheFile = $form->get('afficheFile')->getData();
                if ($afficheFile) {
                    $newFilename = uniqid() . '.' . $afficheFile->guessExtension();
                    $afficheFile->move($this->getParameter('affiches_directory'), $newFilename);
                    $event->setAffiche($newFilename);
                }

                $entityManager->persist($event);
                $entityManager->flush();

                // Ajout du message de succès
                $this->addFlash('success', 'Événement ajouté avec succès.');

                return $this->redirectToRoute('back_event_index');
            } else {
                // 🚨 Gérer les erreurs en JSON pour SweetAlert
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }

                return $this->render('back/event/new.html.twig', [
                    'form' => $form->createView(),
                    'errors' => $errors, // Passer les erreurs à Twig pour SweetAlert
                ]);
            }
        }

        return $this->render('back/event/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }





    // BACK-OFFICE: Afficher un événement
    #[Route('/back/{id}', name: 'back_event_show', methods: ['GET'])]
    public function backShow(Event $event): Response
    {
        return $this->render('back/event/show.html.twig', [
            'event' => $event,
        ]);
    }

   // BACK-OFFICE: Éditer un événement
   #[Route('/back/{id}/edit', name: 'back_event_edit', methods: ['GET', 'POST'])]
public function backEdit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(EventType::class, $event);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Gestion du fichier image
        $afficheFile = $form->get('afficheFile')->getData();
        if ($afficheFile) {
            // Générer un nom unique pour l’image
            $newFilename = uniqid() . '.' . $afficheFile->guessExtension();
            
            // Déplacer l’image dans le répertoire des affiches
            $afficheFile->move($this->getParameter('affiches_directory'), $newFilename);

            // Supprimer l’ancienne image si elle existe
            if ($event->getAffiche()) {
                $oldFilePath = $this->getParameter('affiches_directory') . '/' . $event->getAffiche();
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            // Mettre à jour l’entité avec la nouvelle image
            $event->setAffiche($newFilename);
        }

        $entityManager->flush();

        $this->addFlash('success', 'Événement mis à jour avec succès.');
        return $this->redirectToRoute('back_event_index');
    }

    return $this->render('back/event/edit.html.twig', [
        'form' => $form->createView(),
        'event' => $event,
    ]);
}

   

    #[Route('/back/delete/{id}', name: 'back_event_delete', methods: ['POST'])]
    public function backDelete(Request $request, Event $event, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide.'], 403);
        }

        $entityManager->remove($event);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Événement supprimé avec succès.']);
    }
    #[Route('/event/archive/{id}', name: 'event_archive', methods: ['POST'])]
    public function archive(Event $event, EntityManagerInterface $entityManager): JsonResponse
    {
        $event->setIsArchived(true); // Marque l'événement comme archivé
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Événement archivé avec succès.']);
    }

    #[Route('/event/unarchive/{id}', name: 'event_unarchive', methods: ['POST'])]
    public function unarchive(Request $request, Event $event, EntityManagerInterface $em): JsonResponse
    {
        // Vérifiez si l'événement est expiré
        if ($event->isExpired()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Les événements expirés ne peuvent pas être désarchivés.'
            ], 400); // Retourne une erreur HTTP 400 (Bad Request)
        }

        // Si l'événement n'est pas expiré, procédez à la désarchivation
        $event->setIsArchived(false);
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'L\'événement a été désarchivé avec succès.']);
    }


    private EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    #[Route('/archive-expired-events', name: 'archive_expired_events')]
    public function archiveExpiredEvents(): Response
    {
        $this->eventService->archiveExpiredEvents();

        return new Response('Les événements expirés ont été archivés.');
    }
    

}