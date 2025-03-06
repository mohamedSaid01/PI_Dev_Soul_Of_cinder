<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Entity\Reclamation;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ReclamationRepository;
use App\Service\NotificationService;
use App\Entity\User;
use App\Entity\Patient;


#[Route('/reponse/controller/php')]
final class ReponseControllerPhpController extends AbstractController
{
    


    #[Route(name: 'app_reponse_controller_php_index', methods: ['GET'])]
public function index(ReponseRepository $reponseRepository, ReclamationRepository $reclamationRepository): Response
{
    // Fetch all reponses
    $reponses = $reponseRepository->findAll();

    // Fetch the first reclamation (or any specific reclamation you want)
    $reclamation = $reclamationRepository->findOneBy([], ['id' => 'ASC']); // Adjust the query as needed

    return $this->render('reponse_controller_php/index.html.twig', [
        'reponses' => $reponses,
        'reclamation' => $reclamation, // Pass the reclamation variable
    ]);
}
    
    
    #[Route('/reponse/new/{id}', name: 'app_reponse_controller_php_new', methods: ['GET', 'POST'])]
    public function new(
        ?int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ReclamationRepository $reclamationRepository
    ): Response {
        if (!$id) {
            throw new \Exception("L'ID de la réclamation est requis !");
        }
    
        $reclamation = $reclamationRepository->find($id);
    
        if (!$reclamation) {
            throw $this->createNotFoundException("Réclamation introuvable avec l'ID " . $id);
        }
    
        // Récupérer l'utilisateur connecté
        $user = $this->getUser(); // Récupère l'utilisateur actuel
        if (!$user instanceof User) {
            throw new \Exception("Utilisateur non trouvé !");
        }
    
        // Vérifier que l'utilisateur a un Patient associé
        $patient = $user->getPatient();
        if (!$patient) {
            throw new \Exception("Aucun patient associé à cet utilisateur !");
        }
    
        $reponse = new Reponse();
        $reponse->setDateReponse(new \DateTime());
        $reponse->setReclamation($reclamation);
        $reponse->setPatient($patient); // Associer le patient à la réponse
    
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reponse);
            $entityManager->flush();
    
        
            $this->addFlash(
                'success',
                'Votre réclamation a reçu une réponse : "' . $reponse->getContenu() . '"
                    Cliquez ici pour voir la réponse
                </a>'
            );
            
    
            return $this->redirectToRoute('app_reponse_controller_php_index');
        }
    
        return $this->render('reponse_controller_php/new.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }
    
    
    

    #[Route('/{id}', name: 'app_reponse_controller_php_show', methods: ['GET'])]
    public function show(Reponse $reponse): Response
    {
        return $this->render('reponse_controller_php/show.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reponse_controller_php_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reponse_controller_php_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reponse_controller_php/edit.html.twig', [
            'reponse' => $reponse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reponse_controller_php_delete', methods: ['POST'])]
    public function delete(Request $request, Reponse $reponse, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reponse->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reponse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reponse_controller_php_index', [], Response::HTTP_SEE_OTHER);
    }

}