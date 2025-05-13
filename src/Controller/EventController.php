<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Rating;
use App\Form\EventType;
use App\Service\DalleService;
use App\Service\EventService;
use App\Service\GeminiService;
use App\Service\DeepAiImageService;
use App\Repository\EventRepository;
use App\Repository\RatingRepository;
use App\Repository\CategorieEventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Inscription;
use App\Repository\InscriptionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use App\Service\StabilityAIService;

#[Route('/event')]
class EventController extends AbstractController
{
    // FRONT-OFFICE: Liste des événements pour les médecins
    #[Route('/medecin', name: 'front_medecin_event_index', methods: ['GET'])]
    public function medecinIndex(EventRepository $eventRepository): Response
    {
        $eventsActive = $eventRepository->findBy(['isArchived' => false]);
        $eventsArchived = $eventRepository->findExpiredEvents(); // 🔥 Modifier ici
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
    public function showEvent(Event $event, RatingRepository $ratingRepository): Response
    {
        $user = $this->getUser();
        $userRating = null;

        if ($user) {
            $rating = $ratingRepository->findOneBy(['user' => $user, 'event' => $event]);
            if ($rating) {
                $userRating = $rating->getRating();
            }
        }

        return $this->render('front/event/medecin_show.html.twig', [
            'event' => $event,
            'userRating' => $userRating
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
    public function frontNew(Request $request, EntityManagerInterface $entityManager, ?LoggerInterface $logger = null): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de l'affiche
            $afficheFile = $form->get('afficheFile')->getData();
            if ($afficheFile) {
                // Générer un nom unique avec UUID pour l'image
                $uniqueId = bin2hex(random_bytes(16));
                $newFilename = $uniqueId . '.' . $afficheFile->guessExtension();
                
                // Déplacer l'image dans le répertoire des affiches
                $afficheFile->move($this->getParameter('affiches_directory'), $newFilename);
                $event->setAffiche($newFilename);
                
                // Essayer de synchroniser manuellement les images
                try {
                    $symfonyDir = $this->getParameter('affiches_directory');
                    $javafxDir = $this->getParameter('kernel.project_dir') . '/../src/main/resources/affiches/symfony';
                    
                    // Créer le répertoire de destination s'il n'existe pas
                    if (!is_dir($javafxDir)) {
                        mkdir($javafxDir, 0777, true);
                    }
                    
                    // Copier le fichier vers JavaFX
                    copy($symfonyDir . '/' . $newFilename, $javafxDir . '/' . $newFilename);
                    
                    if ($logger) {
                        $logger->info("Fichier copié avec succès: {$newFilename}");
                    }
                } catch (\Exception $e) {
                    if ($logger) {
                        $logger->error("Erreur lors de la copie de l'image: " . $e->getMessage());
                    }
                    // Continuer malgré l'erreur
                }
            }
            
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
    public function frontEdit(Request $request, Event $event, EntityManagerInterface $entityManager, ?LoggerInterface $logger = null): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $afficheFile = $form->get('afficheFile')->getData();
            if ($afficheFile) {
                // Générer un nom unique avec UUID pour l'image
                $uniqueId = bin2hex(random_bytes(16));
                $newFilename = $uniqueId . '.' . $afficheFile->guessExtension();
                
                // Déplacer l'image dans le répertoire des affiches
                $afficheFile->move($this->getParameter('affiches_directory'), $newFilename);

                // Remove old file if it exists
                if ($event->getAffiche()) {
                    $oldFilePath = $this->getParameter('affiches_directory') . '/' . $event->getAffiche();
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }

                $event->setAffiche($newFilename);
                
                // Essayer de synchroniser manuellement les images
                try {
                    $symfonyDir = $this->getParameter('affiches_directory');
                    $javafxDir = $this->getParameter('kernel.project_dir') . '/../src/main/resources/affiches/symfony';
                    
                    // Créer le répertoire de destination s'il n'existe pas
                    if (!is_dir($javafxDir)) {
                        mkdir($javafxDir, 0777, true);
                    }
                    
                    // Copier le fichier vers JavaFX
                    copy($symfonyDir . '/' . $newFilename, $javafxDir . '/' . $newFilename);
                    
                    if ($logger) {
                        $logger->info("Fichier copié avec succès: {$newFilename}");
                    }
                } catch (\Exception $e) {
                    if ($logger) {
                        $logger->error("Erreur lors de la copie de l'image: " . $e->getMessage());
                    }
                    // Continuer malgré l'erreur
                }
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
    public function index(Request $request, EventRepository $eventRepository, CategorieEventRepository $categorieEventRepository, PaginatorInterface $paginator): Response
    {
        // Récupérer les paramètres de pagination
        $pageActive = $request->query->getInt('page_active', 1);
        $pageArchived = $request->query->getInt('page_archived', 1);
        $itemsPerPage = 5;
        
        // Créer les requêtes pour les événements actifs et archivés
        $queryActive = $eventRepository->createQueryBuilder('e')
            ->where('e.isArchived = :isArchived')
            ->setParameter('isArchived', false)
            ->orderBy('e.startDate', 'DESC')
            ->getQuery();
            
        $queryArchived = $eventRepository->createQueryBuilder('e')
            ->where('e.isArchived = :isArchived')
            ->setParameter('isArchived', true)
            ->orderBy('e.startDate', 'DESC')
            ->getQuery();
        
        // Paginer les résultats
        $pagination_active = $paginator->paginate(
            $queryActive,
            $pageActive,
            $itemsPerPage,
            ['pageParameterName' => 'page_active']
        );
        
        $pagination_archived = $paginator->paginate(
            $queryArchived,
            $pageArchived,
            $itemsPerPage,
            ['pageParameterName' => 'page_archived']
        );

        // Récupérer toutes les catégories d'événements
        $categories = $categorieEventRepository->findAll();

        return $this->render('back/event/index.html.twig', [
            'events_active' => $pagination_active,
            'events_archived' => $pagination_archived,
            'pagination_active' => $pagination_active,
            'pagination_archived' => $pagination_archived,
            'categories' => $categories,
        ]);
    }

    // BACK-OFFICE: Créer un nouvel événement
    #[Route('/back/new', name: 'back_event_new', methods: ['GET', 'POST'])]
    public function backNew(Request $request, EntityManagerInterface $entityManager, ?LoggerInterface $logger = null): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si une image générée a été fournie
            $generatedImageName = $request->request->get('generated_image_name');
            
            // Si une image a été générée, l'utiliser
            if (!empty($generatedImageName)) {
                $event->setAffiche($generatedImageName);
                
                if ($logger) {
                    $logger->info("Utilisation de l'image générée: {$generatedImageName}");
                }
            } 
            // Sinon utiliser le fichier téléchargé si disponible
            else {
                $afficheFile = $form->get('afficheFile')->getData();
                if ($afficheFile) {
                    // Générer un nom unique avec UUID pour l'image
                    $uniqueId = bin2hex(random_bytes(16));
                    $newFilename = $uniqueId . '.' . $afficheFile->guessExtension();
                    
                    // Déplacer l'image dans le répertoire des affiches
                    $afficheFile->move($this->getParameter('affiches_directory'), $newFilename);
                    $event->setAffiche($newFilename);
                    
                    // Essayer de synchroniser manuellement les images
                    try {
                        $symfonyDir = $this->getParameter('affiches_directory');
                        $javafxDir = $this->getParameter('kernel.project_dir') . '/../src/main/resources/affiches/symfony';
                        
                        // Créer le répertoire de destination s'il n'existe pas
                        if (!is_dir($javafxDir)) {
                            mkdir($javafxDir, 0777, true);
                        }
                        
                        // Copier le fichier vers JavaFX
                        copy($symfonyDir . '/' . $newFilename, $javafxDir . '/' . $newFilename);
                        
                        if ($logger) {
                            $logger->info("Fichier copié avec succès: {$newFilename}");
                        }
                    } catch (\Exception $e) {
                        if ($logger) {
                            $logger->error("Erreur lors de la copie de l'image: " . $e->getMessage());
                        }
                        // Continuer malgré l'erreur
                    }
                }
            }

            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('back_event_index');
        }

        return $this->render('back/event/new.html.twig', [
            'event' => $event,
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
    public function backEdit(Request $request, Event $event, EntityManagerInterface $entityManager, ?LoggerInterface $logger = null): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si une image générée a été fournie
            $generatedImageName = $request->request->get('generated_image_name');
            
            // Si une image a été générée, l'utiliser
            if (!empty($generatedImageName)) {
                // Supprimer l'ancienne image si elle existe
                $oldAffiche = $event->getAffiche();
                if ($oldAffiche) {
                    $oldFilePath = $this->getParameter('affiches_directory') . '/' . $oldAffiche;
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }
                
                $event->setAffiche($generatedImageName);
                
                if ($logger) {
                    $logger->info("Utilisation de l'image générée: {$generatedImageName}");
                }
            } 
            // Sinon utiliser le fichier téléchargé si disponible
            else {
                $afficheFile = $form->get('afficheFile')->getData();
                if ($afficheFile) {
                    // Générer un nom unique avec UUID pour l'image
                    $uniqueId = bin2hex(random_bytes(16));
                    $newFilename = $uniqueId . '.' . $afficheFile->guessExtension();
                    
                    // Déplacer l'image dans le répertoire des affiches
                    $afficheFile->move($this->getParameter('affiches_directory'), $newFilename);

                    // Supprimer l'ancienne image si elle existe
                    $oldAffiche = $event->getAffiche();
                    if ($oldAffiche) {
                        $oldFilePath = $this->getParameter('affiches_directory') . '/' . $oldAffiche;
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }

                    $event->setAffiche($newFilename);
                    
                    // Essayer de synchroniser manuellement les images
                    try {
                        $symfonyDir = $this->getParameter('affiches_directory');
                        $javafxDir = $this->getParameter('kernel.project_dir') . '/../src/main/resources/affiches/symfony';
                        
                        // Créer le répertoire de destination s'il n'existe pas
                        if (!is_dir($javafxDir)) {
                            mkdir($javafxDir, 0777, true);
                        }
                        
                        // Copier le fichier vers JavaFX
                        copy($symfonyDir . '/' . $newFilename, $javafxDir . '/' . $newFilename);
                        
                        if ($logger) {
                            $logger->info("Fichier copié avec succès: {$newFilename}");
                        }
                    } catch (\Exception $e) {
                        if ($logger) {
                            $logger->error("Erreur lors de la copie de l'image: " . $e->getMessage());
                        }
                        // Continuer malgré l'erreur
                    }
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('back_event_index');
        }

        return $this->render('back/event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
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
    public function archiveEvent(
        Request $request,
        Event $event,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        InscriptionRepository $inscriptionRepository
    ): JsonResponse {
        // 🔥 Vérifier si l'événement est déjà archivé
        if ($event->isArchived()) {
            return new JsonResponse(['success' => false, 'message' => 'Cet événement est déjà archivé.'], 400);
        }

        // 🗑️ Récupérer toutes les inscriptions liées à l'événement
        $inscriptions = $inscriptionRepository->findBy(['event' => $event]);

        if (!empty($inscriptions)) {
            // 📬 Liste des emails des utilisateurs inscrits
            $emails = [];
            foreach ($inscriptions as $inscription) {
                $userEmail = $inscription->getUser()->getEmail();
                $emails[] = $userEmail;
            }

            // 📧 Envoi des emails en une seule fois
            $email = (new Email())
                ->from('mohamedsaidboubaker10@gmail.com')
                ->to(...$emails)
                ->subject("Annulation de l'événement: " . $event->getTitle())
                ->html("
                    <p>Bonjour,</p>
                    <p>Nous vous informons que l'événement <strong>{$event->getTitle()}</strong> prévu le <strong>{$event->getStartDate()->format('d/m/Y')}</strong> a été annulé.</p>
                    <p>Nous nous excusons pour la gêne occasionnée.</p>
                    <p>Cordialement,</p>
                    <p>L'équipe d'organisation</p>
                ");

            $mailer->send($email);

            // 🔥 Supprimer toutes les inscriptions après envoi des emails
            foreach ($inscriptions as $inscription) {
                $entityManager->remove($inscription);
            }
            // 🔥 Confirmer la suppression des inscriptions
            $entityManager->flush();
        }

        // 📌 Archiver l'événement
        $event->setIsArchived(true);
        $entityManager->persist($event);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => (!empty($inscriptions))
                ? "L'événement a été archivé, toutes les inscriptions supprimées et un email d'annulation envoyé."
                : "L'événement a été archivé (aucune inscription à notifier)."
        ]);
    }


    /**
     * Désarchive un événement (remet isArchived à false)
     */
    #[Route('/event/unarchive/{id}', name: 'event_unarchive', methods: ['POST'])]
    public function unarchiveEvent(
        Event $event,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        if (!$event->isArchived()) {
            return new JsonResponse([
                'success' => false,
                'message' => "Cet événement n'est pas archivé."
            ], 400);
        }
        // 🚫 Empêcher la désarchivation des événements expirés
        if ($event->isExpired()) {
            return new JsonResponse([
                'success' => false,
                'message' => "Les événements expirés ne peuvent pas être désarchivés."
            ], 400);
        }
        $event->setIsArchived(false);
        $entityManager->persist($event);
        $entityManager->flush();
        return new JsonResponse([
            'success' => true,
            'message' => "L'événement a été désarchivé avec succès."
        ]);
    }

    private EventService $eventService;
    private GeminiService $geminiService;

    public function __construct(EventService $eventService, GeminiService $geminiService)
    {
        $this->eventService = $eventService;
        $this->geminiService = $geminiService;
    }

    #[Route('/archive-expired-events', name: 'archive_expired_events')]
    public function archiveExpiredEvents(): Response
    {
        $this->eventService->archiveExpiredEvents();

        return new Response('Les événements expirés ont été archivés.');
    }


    #[Route('/back/search', name: 'back_event_search', methods: ['POST'])]
    public function search(Request $request, EventRepository $eventRepository, PaginatorInterface $paginator): Response
    {
        // Récupérer les paramètres de recherche et de pagination
        $title = $request->request->get('title');
        $startDate = $request->request->get('startDate');
        $categorieId = $request->request->get('categorie');
        $isArchived = $request->request->get('isArchived') === "true";
        $page = (int)$request->request->get('page', 1);
        $pageParamName = $request->request->get('pageParamName', 'page');
        $itemsPerPage = 5;
        
        // S'assurer que le paramètre de page est un entier positif
        if ($page <= 0) {
            $page = 1;
        }
        
        try {
            // Convertir `categorieId` en `int` ou `null`
        $categorieId = !empty($categorieId) ? (int) $categorieId : null;

            // Construire la requête de recherche
            $queryBuilder = $eventRepository->createQueryBuilder('e')
                ->where('e.isArchived = :isArchived')
                ->setParameter('isArchived', $isArchived);
                
            if (!empty($title)) {
                $queryBuilder->andWhere('e.title LIKE :title')
                    ->setParameter('title', '%' . $title . '%');
            }
            
            if (!empty($startDate)) {
                $queryBuilder->andWhere('e.startDate >= :startDate')
                    ->setParameter('startDate', new \DateTime($startDate));
            }
            
            if (!empty($categorieId)) {
                $queryBuilder->andWhere('e.categorie = :categorieId')
                    ->setParameter('categorieId', $categorieId);
            }
            
            $queryBuilder->orderBy('e.startDate', 'DESC');

            // Calculer l'offset pour la pagination manuelle
            $offset = ($page - 1) * $itemsPerPage;
            
            // Compter le nombre total d'éléments
            $countQuery = clone $queryBuilder;
            $totalItems = $countQuery->select('COUNT(e.id)')
                                    ->getQuery()
                                    ->getSingleScalarResult();
            
            // Ajouter la pagination à la requête
            $queryBuilder->setFirstResult($offset)
                        ->setMaxResults($itemsPerPage);
            
            // Exécuter la requête
            $events = $queryBuilder->getQuery()->getResult();
            
            // Créer un objet PaginatorInterface compatible
            $pagination = $paginator->paginate(
                $queryBuilder->getQuery(),
                $page,
                $itemsPerPage,
                [
                    'pageParameterName' => $pageParamName,
                    'sortDirectionParameterName' => 'direction',
                    'sortFieldParameterName' => 'sort',
                    'filterFieldParameterName' => 'filterField',
                    'filterValueParameterName' => 'filterValue',
                    'distinct' => true
                ]
            );
            
            // Retourner le template avec les résultats paginés
            return $this->render('back/event/_event_table.html.twig', [
                'events' => $pagination,
                'isArchived' => $isArchived,
                'pagination' => $pagination,
                'current_page' => $page
            ]);
        } catch (\Exception $e) {
            // Log l'erreur et retourner une réponse avec un message d'erreur
            // Retourner un message d'erreur dans le template
        return $this->render('back/event/_event_table.html.twig', [
                'events' => [],
                'isArchived' => $isArchived,
                'pagination' => null,
                'error_message' => 'Une erreur est survenue lors de la recherche : ' . $e->getMessage()
        ]);
        }
    }

    #[Route('/rate/{id}', name: 'event_rate', methods: ['POST'])]
    public function rateEvent(Request $request, Event $event, EntityManagerInterface $entityManager, RatingRepository $ratingRepository): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Vous devez être connecté pour noter un événement.'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $ratingValue = $data['rating'] ?? null;

        if (!$ratingValue || $ratingValue < 1 || $ratingValue > 5) {
            return new JsonResponse(['success' => false, 'message' => 'La note doit être entre 1 et 5.'], 400);
        }

        // Vérifie si l'utilisateur a déjà noté cet événement
        $existingRating = $ratingRepository->findOneBy(['user' => $user, 'event' => $event]);

        if ($existingRating) {
            $existingRating->setRating($ratingValue);
        } else {
            $newRating = new Rating();
            $newRating->setUser($user);
            $newRating->setEvent($event);
            $newRating->setRating($ratingValue);
            $entityManager->persist($newRating);
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true, 'message' => 'Votre note a été enregistrée !']);
    }
    #[Route('/event/generate', name: 'event_generate', methods: ['POST'])]
    public function generateDescription(Request $request, GeminiService $geminiService): JsonResponse
    {
        $title = $request->toArray()['title'] ?? '';

        if (empty($title)) {
            return new JsonResponse(['description' => 'Veuillez fournir un titre pour générer une description.'], 400);
        }

        // Utilisez le service Gemini pour générer la description en français
        try {
            $description = $geminiService->generateText($title);
            return new JsonResponse(['description' => $description]);
        } catch (\Exception $e) {
            return new JsonResponse(['description' => 'Erreur lors de la génération de la description : ' . $e->getMessage()], 500);
        }
    }

    #[Route('/event/generate-deepai-image', name: 'event_generate_deepai_image', methods: ['POST'])]
    public function generateDeepAiImage(Request $request, DeepAiImageService $deepAiImageService): JsonResponse
    {
        $title = trim($request->toArray()['title'] ?? '');

        if (empty($title)) {
            return new JsonResponse(['error' => 'Veuillez fournir un titre valide pour générer une image.'], 400);
        }

        try {
            $imageUrl = $deepAiImageService->generateImage($title);
            return new JsonResponse(['image_url' => $imageUrl]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la génération de l\'image : ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/generate-stability-image', name: 'event_generate_stability_image', methods: ['POST'])]
    public function generateStabilityImage(Request $request, StabilityAIService $stabilityService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $title = $data['title'] ?? '';
        $description = $data['description'] ?? '';

        if (empty($title)) {
            return new JsonResponse(['error' => 'Veuillez fournir un titre valide pour générer une image.'], 400);
        }

        try {
            $imageInfo = $stabilityService->generateEventPoster($title, $description);
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Image générée avec succès via Stability AI',
                'image_name' => $imageInfo['imageName'],
                'image_url' => $imageInfo['imageUrl']
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la génération de l\'image: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/admin/stats', name: 'admin_stats')]
    public function statistics(EventRepository $eventRepository, InscriptionRepository $inscriptionRepository): Response
    {
        $eventsByMonth = $eventRepository->countEventsByMonth();
        $inscriptionsByEvent = $inscriptionRepository->countInscriptionsByEvent();

        return $this->render('back/index.html.twig', [ // Vérifie bien le nom du fichier Twig !
            'eventsByMonth' => $eventsByMonth,
            'inscriptionsByEvent' => $inscriptionsByEvent,
        ]);
    }

    #[Route('/api/test-stability', name: 'event_test_stability', methods: ['GET'])]
    public function testStabilityAPI(StabilityAIService $stabilityService): JsonResponse
    {
        try {
            // Tester si le service est disponible
            $isAvailable = $stabilityService->isAvailable();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Service Stability AI disponible',
                'is_available' => $isAvailable
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur lors du test de l\'API Stability: ' . $e->getMessage()
            ], 500);
        }
    }









}