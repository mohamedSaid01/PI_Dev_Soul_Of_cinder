<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/participant')]
class ParticipantController extends AbstractController
{
    // FRONT-OFFICE : Créer un nouveau participant
    #[Route('/new', name: 'front_participant_new', methods: ['GET', 'POST'])]
    public function frontNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $participant = new Participant();
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($participant);
            $entityManager->flush();

            return $this->redirectToRoute('front_patient_event_index');
        }

        return $this->render('front/participant/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // BACK-OFFICE : Liste des participants
    #[Route('/admin', name: 'admin_participant_index', methods: ['GET'])]
    public function adminIndex(ParticipantRepository $participantRepository): Response
    {
        return $this->render('back/participant/index.html.twig', [
            'participants' => $participantRepository->findAll(),
        ]);
    }

    // BACK-OFFICE : Créer un nouveau participant
    #[Route('/admin/new', name: 'admin_participant_new', methods: ['GET', 'POST'])]
    public function adminNew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $participant = new Participant();
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($participant);
            $entityManager->flush();

            return $this->redirectToRoute('admin_participant_index');
        }

        return $this->render('back/participant/new.html.twig', [
            'participant' => $participant,
            'form' => $form->createView(),
        ]);
    }

    // BACK-OFFICE : Afficher les détails d'un participant
    #[Route('/admin/{id}', name: 'admin_participant_show', methods: ['GET'])]
    public function adminShow(Participant $participant): Response
    {
        return $this->render('back/participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    // BACK-OFFICE : Éditer un participant
    #[Route('/admin/{id}/edit', name: 'admin_participant_edit', methods: ['GET', 'POST'])]
    public function adminEdit(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_participant_index');
        }

        return $this->render('back/participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form->createView(),
        ]);
    }

    // BACK-OFFICE : Supprimer un participant
    #[Route('/admin/{id}', name: 'admin_participant_delete', methods: ['POST'])]
    public function adminDelete(Request $request, Participant $participant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $participant->getId(), $request->request->get('_token'))) {
            $entityManager->remove($participant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_participant_index');
    }
}