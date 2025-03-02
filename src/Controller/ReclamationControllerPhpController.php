<?php

namespace App\Controller;

use App\Service\EmailService;

use App\Entity\Medecin;
use App\Entity\TypeReclamation;
use App\Entity\Reclamation;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Form\Reclamation1Type;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\TypeReclamationRepository;
use Symfony\Component\Mailer\MailerInterface;
// Importez MailerInterface
use Symfony\Component\Mime\Email;
use App\Service\NotificationService;




#[Route('/reclamation/controller/php')]
final class ReclamationControllerPhpController extends AbstractController
{
    
    private EmailService $emailService;

    public function __construct(EmailService $emailService )
    {
        $this->emailService = $emailService;
        
    }
   

    #[Route('/reclamations', name: 'app_reclamation_controller_php_index', methods: ['GET'])]
    public function index(Request $request, ReclamationRepository $reclamationRepository, TypeReclamationRepository $typeReclamationRepository): Response
    {
        $typeReclamationId = $request->query->get('typeReclamation');

        // Récupérer tous les types de réclamation pour le filtre
        $typesReclamation = $typeReclamationRepository->findAll();

        if ($typeReclamationId) {
            $typeReclamation = $typeReclamationRepository->find($typeReclamationId);
            $reclamations = $reclamationRepository->findBy(['typeReclamation' => $typeReclamation]);
        } else {
            $reclamations = $reclamationRepository->findAll();
        }

        return $this->render('reclamation_controller_php/index.html.twig', [
            'reclamations' => $reclamations,
            'typesReclamation' => $typesReclamation,
            'selectedType' => $typeReclamationId
        ]);
    }

    #[Route('/reclamations/back', name: 'app_reclamation_controller_php_copy', methods: ['GET'])]
    public function indexcopy(ReclamationRepository $reclamationRepository): Response
    {
        return $this->render('reclamation_controller_php/back.html.twig', [
            'reclamations' => $reclamationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_reclamation_controller_php_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        EmailService $emailService // Utilisation du service d'email
    ): Response {
        $reclamation = new Reclamation();
        $form = $this->createForm(Reclamation1Type::class, $reclamation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload de la photo
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $newFilename = uniqid().'.'.$photoFile->guessExtension();
                try {
                    $photoFile->move(
                        $this->getParameter('photo_dir'),
                        $newFilename
                    );
                    $reclamation->setPhoto($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléversement de la photo.');
                }
            } else {
                $reclamation->setPhoto(''); // Si pas de fichier uploadé
            }
    
            // Gestion du médecin
            $medecin = $form->get('medecin')->getData();
            $reclamation->setMedecin(empty($medecin) ? null : $medecin);
    
            // Enregistrer la réclamation en base de données
            $entityManager->persist($reclamation);
            $entityManager->flush();
    
            // ✅ **Envoi de l'e-mail après l'ajout de la réclamation**
            try {
                $emailService->sendEmail(
                    'sourournajjar2@gmail.com', // Destinataire
                    'Nouvelle Réclamation', // Sujet
                    "Une nouvelle réclamation a été ajoutée par l'utilisateur." // Contenu
                );
    
                $this->addFlash('success', 'Réclamation ajoutée avec succès et e-mail envoyé.');
            } catch (\Exception $e) {
                $this->addFlash('error', "Réclamation ajoutée, mais l'envoi de l'e-mail a échoué.");
            }
    
            return $this->redirectToRoute('app_reclamation_controller_php_new');
        }
    
        // Afficher le formulaire
        return $this->render('reclamation_controller_php/new.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }
    

    #[Route('/reclamation/{id}', name: 'app_reclamation_controller_php_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('reclamation_controller_php/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reclamation_controller_php_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(Reclamation1Type::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reclamation_controller_php_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reclamation_controller_php/edit.html.twig', [
            'reclamation' => $reclamation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_reclamation_controller_php_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
{
    // Vérifier si la requête est bien AJAX
    if (!$request->isXmlHttpRequest()) {
        return $this->json(['success' => false, 'message' => 'Requête invalide'], Response::HTTP_BAD_REQUEST);
    }

    // Récupérer le token CSRF depuis la requête JSON
    $data = json_decode($request->getContent(), true);
    $csrfToken = $data['_token'] ?? '';

    // // Valider le token CSRF
    // if (!$this->isCsrfTokenValid('delete' . $reclamation->getId(), $csrfToken)) {
    //     return $this->json(['success' => false, 'message' => 'Token CSRF invalide'], Response::HTTP_FORBIDDEN);
    // }
    if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->getPayload()->getString('_token'))) {
        $entityManager->remove($reclamation);
        $entityManager->flush();
    }

    // Supprimer l'entité
    $entityManager->remove($reclamation);
    $entityManager->flush();

    return $this->json(['success' => true, 'message' => 'Réclamation supprimée avec succès']);
}

    // #[Route('/{id}', name: 'app_reclamation_controller_php_delete', methods: ['POST'])]
    // public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    // {
    //     if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->getPayload()->getString('_token'))) {
    //         $entityManager->remove($reclamation);
    //         $entityManager->flush();
    //     }

    //     return $this->redirectToRoute('app_reclamation_controller_php_index');
    // }

    #[Route('/reclamations/search', name: 'reclamation_search', methods: ['GET'])]
    public function search(Request $request, ReclamationRepository $reclamationRepository): Response
    {
        $query = $request->query->get('q');
        $reclamations = [];

        if ($query) {
            $reclamations = $reclamationRepository->searchReclamations($query, null, null);
        }

        // Si c'est une requête Ajax, on renvoie seulement les résultats
        if ($request->isXmlHttpRequest()) {
            return $this->render('reclamation_controller_php/search_results.html.twig', [
                'reclamations' => $reclamations
            ]);
        }

        return $this->render('reclamation_controller_php/search.html.twig', [
            'reclamations' => $reclamations
        ]);
    }
    
}