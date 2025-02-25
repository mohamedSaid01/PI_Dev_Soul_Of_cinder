<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Form\Reclamation1Type;
use App\Repository\ReclamationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/reclamation/controller/php')]
final class ReclamationControllerPhpController extends AbstractController
{

    #[Route('/reclamations', name: 'app_reclamation_controller_php_index', methods: ['GET'])]
public function index(ReclamationRepository $reclamationRepository): Response
{
    return $this->render('reclamation_controller_php/index.html.twig', [
        'reclamations' => $reclamationRepository->findAll(),
    ]);
}

#[Route('/reclamations/back', name: 'app_reclamation_controller_php_copy', methods: ['GET'])]
public function indexcopy(ReclamationRepository $reclamationRepository): Response
{
    return $this->render('reclamation_controller_php/back.html.twig', [
        'reclamations' => $reclamationRepository->findAll(),
    ]);
}
  
    
//     public function new(Request $request, EntityManagerInterface $entityManager): Response
// {
//     $reclamation = new Reclamation();
//     $form = $this->createForm(Reclamation1Type::class, $reclamation);
//     $form->handleRequest($request);

//     if ($form->isSubmitted() && $form->isValid()) {
//         // /** @var photo_dir $photoFile */
//         $photoFile = $form->get('photo')->getData();

//         // Si un fichier est téléversé
//         if ($photoFile) {
//             $newFilename = uniqid().'.'.$photoFile->guessExtension();

//             // Déplacez le fichier vers le répertoire où vous stockez les photos
//             $photoFile->move(
//                 $this->getParameter('photo_dir'), // Configurez ce paramètre dans services.yaml
//                 $newFilename
//             );

//             // Enregistrez le nom du fichier dans l'entité
//             $reclamation->setPhoto($photoFile ? $newFilename : '');
//          } //else {
//         //     // Si aucun fichier n'est téléversé, définissez photo à NULL
//         //     $reclamation->setPhoto(NULL);
//         // }

//         $entityManager->persist($reclamation);
//         $entityManager->flush();

//         return $this->redirectToRoute('app_reclamation_controller_php_index', [], Response::HTTP_SEE_OTHER);
//     }

//     return $this->render('reclamation_controller_php/new.html.twig', [
//         'reclamation' => $reclamation,
//         'form' => $form->createView(),
//     ]);
// }
#[Route('/new', name: 'app_reclamation_controller_php_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $reclamation = new Reclamation();
    $form = $this->createForm(Reclamation1Type::class, $reclamation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Gestion de l'upload de la photo
        $photoFile = $form->get('photo')->getData();

        if ($photoFile) {
            $newFilename = uniqid().'.'.$photoFile->guessExtension();
            $photoFile->move(
                $this->getParameter('photo_dir'), // Configurez ce paramètre dans services.yaml
                $newFilename
            );
            $reclamation->setPhoto($newFilename);
        } else {
            $reclamation->setPhoto(''); // Définir photo à NULL si aucun fichier n'est téléversé
        }

        // Gestion de idmedecin
        $idmedecin = $form->get('idmedecin')->getData();
        if (empty($idmedecin)) {
            $reclamation->setIdmedecin(''); // Définir idmedecin à NULL si vide
        } else {
            $reclamation->setIdmedecin($idmedecin); // Sinon, enregistrer la valeur
        }

        // Enregistrer la réclamation en base de données
        $entityManager->persist($reclamation);
        $entityManager->flush();

        // Rediriger vers la page d'accueil ou une autre route
        return $this->redirectToRoute('app_reclamation_controller_php_index', [], Response::HTTP_SEE_OTHER);
    }

    // Afficher le formulaire
    return $this->render('reclamation_controller_php/new.html.twig', [
        'reclamation' => $reclamation,
        'form' => $form->createView(),
    ]);
}


    #[Route('/{id}', name: 'app_reclamation_controller_php_show', methods: ['GET'])]
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
            'form' => $form,
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

}
