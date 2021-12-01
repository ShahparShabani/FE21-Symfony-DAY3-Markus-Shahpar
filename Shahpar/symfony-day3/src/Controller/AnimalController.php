<?php

namespace App\Controller;

use App\Entity\Animal;
use App\Form\AnimalType;
use App\Repository\AnimalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Service\FileUploader;

#[Route('/animal')]
class AnimalController extends AbstractController
{
    #[Route('/', name: 'animal_index', methods: ['GET'])]
    public function index(AnimalRepository $animalRepository): Response
    {
        return $this->render('animal/index.html.twig', [
            'animals' => $animalRepository->findAll(),
        ]);
    }

    #[Route('/list', name: 'animal_list', methods: ['GET'])]
    public function list(AnimalRepository $animalRepository): Response
    {
        return $this->render('animal/animal_list.html.twig', [
            'animals' => $animalRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'animal_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, FileUploader $FileUploader): Response
    {
        $animal = new Animal();
        // $form = $this->getDoctrine();
        $form = $this->createForm(AnimalType::class, $animal);
        // $form = $this->createFormBuilder($animal)
        // ->add('name', TextType::class, array('attr' => array('class'=> 'form-control', 'style'=>'margin-bottom:15px')))->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $animalPicture = $form->get('picture')->getData();
            if ($animalPicture) {
                $pictureFilename = $FileUploader->upload($animalPicture);
                // updates the 'picture' property to store the PDF file name
                // instead of its contents
                $animal->setPicture($pictureFilename);
            } 
            
            $entityManager->persist($animal);
            $entityManager->flush();
            return $this->redirectToRoute('animal_index', [], Response::HTTP_SEE_OTHER);

        }

        return $this->renderForm('animal/new.html.twig', [
            'animal' => $animal,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'animal_show', methods: ['GET'])]
    public function show(Animal $animal): Response
    {
        return $this->render('animal/show.html.twig', [
            'animal' => $animal,
        ]);
    }

    #[Route('/{id}/edit', name: 'animal_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Animal $animal, EntityManagerInterface $entityManager, FileUploader $FileUploader): Response
    {
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $animalPicture = $form->get('picture')->getData();
            if ($animalPicture) {
                $pictureFilename = $FileUploader->upload($animalPicture);
                // updates the 'picture' property to store the PDF file name
                // instead of its contents
                $animal->setPicture($pictureFilename);

                $entityManager->flush();

            } else {

                $entityManager->flush();
    
                return $this->redirectToRoute('animal_index', [], Response::HTTP_SEE_OTHER);

            }
            // $entityManager->flush();

            return $this->redirectToRoute('animal_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('animal/edit.html.twig', [
            'animal' => $animal,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'animal_delete', methods: ['GET','POST'])]
    public function delete(Request $request, Animal $animal, EntityManagerInterface $entityManager): Response
    {
        
        // if ($this->isCsrfTokenValid('delete' . $animal->getId(), $request->request->get('_token'))) {
            $entityManager->remove($animal);
            $entityManager->flush();
        // }

        return $this->redirectToRoute('animal_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/adopt', name: 'animal_adopt', methods: ['POST'])]
    public function adopt(Request $request, $id, Animal $animal, EntityManagerInterface $entityManager): Response
    {
       
        if ($this->isCsrfTokenValid('adopt' . $animal->getId(), $request->request->get('_token'))) {
            $animal->setAdopted(true);
            $entityManager->flush();
        }

        return $this->redirectToRoute('animal_index', [], Response::HTTP_SEE_OTHER);
    }
}
