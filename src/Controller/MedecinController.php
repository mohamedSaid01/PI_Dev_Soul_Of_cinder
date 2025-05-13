<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Medecin;
use App\Form\MedecinType;
use App\Service\PasswordGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use App\Service\ZeroBounceEmailValidator;
use App\Form\SearchMedecinType;
use App\Enum\Specialite;
use Knp\Component\Pager\PaginatorInterface;


class MedecinController extends AbstractController
{
    private ZeroBounceEmailValidator $emailValidator;

    public function __construct(ZeroBounceEmailValidator $emailValidator)
    {
        $this->emailValidator = $emailValidator;
    }

    #[Route('/admin/new-register/medecin', name: 'app_new_register_medecin')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        PasswordGenerator $passwordGenerator,
        MailerInterface $mailer,
        UserRepository $userRepository,
        ZeroBounceEmailValidator $emailValidator // Injection directe
    ): Response {
        $user = new User();
        $form = $this->createForm(MedecinType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        // // Récupérer l'email depuis l'objet User (validé par le formulaire)
        // $email = $user->getEmail();
        //     if (!$this->emailValidator->isValid($email)) {
        //         $this->addFlash('error', 'L\'email n\'est pas valide ou n\'existe pas.');
        //         return $this->redirectToRoute('app_register');
        //     }

            // Vérifier si l'email existe déjà
            $existingUser = $userRepository->findOneBy(['email' => $user->getEmail()]);
        
            if ($existingUser) {
                $this->addFlash('error', 'Un utilisateur avec cet email existe déjà.');
                return $this->redirectToRoute('app_new_register_medecin');
            }
         

            // Générer un mot de passe aléatoire
            $plainPassword = $passwordGenerator->generateRandomPassword();

            // Encoder le mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $plainPassword
                )
            );

            // Attribuer le rôle ROLE_MEDECIN
            $user->setRoles(['ROLE_MEDECIN']);

            // Définir isVerified à true (1) pour ce patient
            $user->setIsVerified(true);

            $user->setStatus('verifie');

            $medecin = new Medecin();
            $medecin->setUser($user);

            // Enregistrer l'utilisateur en base de données
            $entityManager->persist($user);
            $entityManager->persist($medecin);
            $entityManager->flush();

            // Envoyer un email avec les informations de connexion
            $email = (new Email())
                ->from('mohamedsaidboubaker10@gmail.com')
                ->to($user->getEmail())
                ->subject('Vos informations de connexion')
                ->html($this->renderView(
                    'emails/medecin_registration.html.twig',
                    [
                        'email' => $user->getEmail(),
                        'password' => $plainPassword,
                        'firstName' => $user->getFirstName(),
                        'lastName' => $user->getLastName(),
                    ]
                ));

            $mailer->send($email);

            $this->addFlash('success', 'Compte médecin créé avec succès. Un email a été envoyé avec les informations de connexion.');
            return $this->redirectToRoute('app_medecins_list');
        }

        return $this->render('registration/medecin_register.html.twig', [
            'medecinRegistrationForm' => $form->createView(),
        ]);
    }

    
    #[Route('/admin/medecins', name: 'app_medecins_list')]
    public function listMedecins(UserRepository $userRepository, Request $request, PaginatorInterface $paginator): Response
    {
        // Récupérer la valeur de la spécialité depuis la requête
        $selectedSpecialite = $request->query->get('specialite');
    
        // Créer la requête de base pour récupérer les médecins
        $medecinsQuery = $userRepository->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->andWhere('u.isVerified = :isVerified')
            ->andWhere('u.status = :status')
            ->setParameter('role', '%"ROLE_MEDECIN"%')
            ->setParameter('isVerified', 1)
            ->setParameter('status', 'verifie');
    
        // Ajouter le filtre sur la spécialité si elle est spécifiée
        if ($selectedSpecialite) {
            $medecinsQuery->andWhere('u.specialite = :specialite')
                ->setParameter('specialite', $selectedSpecialite);
        }
    
        // Paginer les résultats
        $medecins = $paginator->paginate(
            $medecinsQuery,
            $request->query->getInt('page', 1),
            2   
        );
    
        // Passer les choix de spécialités et la spécialité sélectionnée au template
        $specialites = Specialite::getChoices();
    
        return $this->render('medecin/medecins_list.html.twig', [
            'medecins' => $medecins,
            'specialites' => $specialites,
            'selectedSpecialite' => $selectedSpecialite, // Ajout de cette variable
        ]);
    }
    

    #[Route('/admin/medecin/{id}', name: 'app_medecin_show', methods: ['GET'])]
    public function showMedecin(User $medecin): Response
    {
        return $this->render('medecin/show.html.twig', [
            'medecin' => $medecin,
        ]);
    }

    #[Route('/admin/medecin/{id}/edit', name: 'app_medecin_edit', methods: ['GET', 'POST'])]
    public function editMedecin(Request $request, User $medecin, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MedecinType::class, $medecin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le médecin a été mis à jour avec succès.');
            return $this->redirectToRoute('app_medecins_list');
        }

        return $this->render('medecin/edit.html.twig', [
            'medecin' => $medecin,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/medecin/{id}/delete', name: 'app_medecin_delete', methods: ['POST'])]
    public function deleteMedecin(Request $request, User $medecin, EntityManagerInterface $entityManager): Response
    {
        // Vérifier le token CSRF pour sécuriser la suppression
        if ($this->isCsrfTokenValid('delete' . $medecin->getId(), $request->request->get('_token'))) {
            // Supprimer le médecin de la base de données
            $entityManager->remove($medecin);
            $entityManager->flush();

            // Ajouter un message de succès
            $this->addFlash('success', 'Le médecin a été supprimé avec succès.');
        } else {
            // Ajouter un message d'erreur si le token CSRF est invalide
            $this->addFlash('error', 'Token CSRF invalide, suppression annulée.');
        }

        // Rediriger vers la liste des médecins
        return $this->redirectToRoute('app_medecins_list');
    }

}