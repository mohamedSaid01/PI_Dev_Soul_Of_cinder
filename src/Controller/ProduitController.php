<?php

namespace App\Controller;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Endroid\QrCode\Builder\QrCodeBuilder;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Repository\CategoryRepository;
use App\Entity\Category;
use App\Entity\Commande;
use App\Entity\CommandeLigne;
use App\Entity\Favori;
use App\Entity\Commentaire;
use Knp\Component\Pager\PaginatorInterface; // Add this line
use App\Repository\CommandeRepository;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;








#[Route('/produit')]
class ProduitController extends AbstractController
{
    #[Route('/front', name: 'front_list', methods: ['GET'])]
    public function listProducts(Request $request, ManagerRegistry $doctrine, PaginatorInterface $paginator): Response
    {
        $categoryId = $request->query->get('category');
        $searchQuery = $request->query->get('search', '');
        $minPrice = $request->query->get('minPrice');
        $maxPrice = $request->query->get('maxPrice');

        $produitRepository = $doctrine->getRepository(Produit::class);
        $categoryRepository = $doctrine->getRepository(Category::class);

        $queryBuilder = $produitRepository->createQueryBuilder('p')
            ->where('p.quantity > 0');

        if ($categoryId) {
            $category = $categoryRepository->find($categoryId);
            $queryBuilder->andWhere('p.Category = :category')
                          ->setParameter('category', $category);
        }

        if ($searchQuery) {
            $queryBuilder->andWhere('p.name LIKE :searchQuery OR p.desciption LIKE :searchQuery')
                          ->setParameter('searchQuery', '%' . $searchQuery . '%');
        }

        if ($minPrice) {
            $queryBuilder->andWhere('p.price >= :minPrice')
                          ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice) {
            $queryBuilder->andWhere('p.price <= :maxPrice')
                          ->setParameter('maxPrice', $maxPrice);
        }

        $produits = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            2
        );

        return $this->render('produit/indexClient.html.twig', [
            'produits' => $produits,
            'categories' => $categoryRepository->findAll(),
            'selectedCategory' => $categoryId,
            'searchQuery' => $searchQuery,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ]);
    }
     
   
    #[Route(name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/produit/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('image')->getData();
            if ($file) {
                $filename = md5(uniqid()) . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('images_directory'),  // Directory defined in parameters
                        $filename
                    );
                    $produit->setImage($filename);
                } catch (\Exception $e) {
                    // Handle the exception if something goes wrong
                    $this->addFlash('error', 'Failed to upload image.');
                }
            }
            // Enregistrer le produit
            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté avec succès !');
            return $this->redirectToRoute('app_produit_index');
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/front', name: 'app_produit_show', methods: ['GET'])]
    public function showproduitfront(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();
    return $this->render('produit/indexClient.html.twig', [
        'produits' => $produits,
    ]);
}

#[Route('/produit/{id}', name: 'app_produit_show_back', methods: ['GET'])]
public function show(?Produit $produit): Response
{
    if (!$produit) {
        throw $this->createNotFoundException('Product not found');
    }

    return $this->render('produit/show.html.twig', [
        'produit' => $produit,
    ]);
}

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Contrôle de saisie pour le prix
            $prix = $produit->getPrice();
            if ($prix < 0.50 || $prix > 1000) {
                $this->addFlash('error', 'Le prix doit être compris entre 0,50DT et 1000 DT');
                return $this->redirectToRoute('app_produit_edit', ['id' => $produit->getId()]);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }
         
        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }
// src/Controller/ProduitController.php
#[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
{
    // Vérifier le token CSRF
    if (!$this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
        $this->addFlash('error', 'Invalid CSRF token.');
        return $this->redirectToRoute('app_produit_index');
    }

    // Vérifier si le produit est utilisé dans des commandes
    if ($produit->getCommandeLignes()->count() > 0) {
        $this->addFlash('error', 'Ce produit ne peut pas être supprimé car il est utilisé dans des commandes.');
    } else {
        // Supprimer le produit
        $entityManager->remove($produit);
        $entityManager->flush();

        $this->addFlash('success', 'Le produit a été supprimé avec succès.');
    }

    // Rediriger vers la page d'index des produits
    return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
}


#[Route('/add-to-cart/{id}', name: 'add_to_cart', methods: ['POST'])]
public function addToCart(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
{

    // Vérifier le token CSRF
    $submittedToken = $request->request->get('_token');
    if (!$this->isCsrfTokenValid('add_to_cart' . $produit->getId(), $submittedToken)) {
        $this->addFlash('error', 'Invalid CSRF token.');
        return $this->redirectToRoute('front_list');
    }

    // Vérifier si l'utilisateur est connecté
    $user = $this->getUser();
    if (!$user) {
        $this->addFlash('error', 'You must be logged in to add products to the cart.');
        return $this->redirectToRoute('app_login');
    }

    // Vérifier si le produit est en stock
    if ($produit->getQuantity() < 1) {
        $this->addFlash('error', 'This product is out of stock.');
        return $this->redirectToRoute('front_list');
    }

    // Trouver ou créer une commande en attente pour l'utilisateur
    $commande = $entityManager->getRepository(Commande::class)->findOneBy([
        'user' => $user,
        'statut' => 'pending',
    ]);

    if (!$commande) {
        $commande = new Commande();
        $commande->setUser($user);
        $commande->setStatut('pending');
        $commande->setDateCommande(new \DateTime()); // Ensure the date is set
        $entityManager->persist($commande);
    }

    // Créer une nouvelle ligne de commande
    $ligne = new CommandeLigne();
    $ligne->setCommande($commande);
    $ligne->setProduit($produit);
    $ligne->setQuantity(1); // Quantité par défaut

    // Mettre à jour le stock du produit
    $produit->setQuantity($produit->getQuantity() - 1);

    // Enregistrer les modifications en base de données
    $entityManager->persist($ligne);
    $entityManager->flush();

    // Ajouter un message de succès
    $this->addFlash('success', 'Product added to cart!');
    return $this->redirectToRoute('front_list');
}

// src/Controller/ProduitController.php
#[Route('/toggle-favorite/{id}', name: 'toggle_favorite', methods: ['POST'])]
public function toggleFavorite(Produit $produit, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    if (!$user) {
        $this->addFlash('error', 'You must be logged in to add products to favorites.');
        return $this->redirectToRoute('app_login');
    }

    $favori = $entityManager->getRepository(Favori::class)->findOneBy([
        'user' => $user,
        'produit' => $produit,
    ]);

    if ($favori) {
        // Remove from favorites
        $entityManager->remove($favori);
        $this->addFlash('success', 'Product removed from favorites.');
    } else {
        // Add to favorites
        $favori = new Favori();
        $favori->setUser($user);
        $favori->setProduit($produit);
        $entityManager->persist($favori);
        $this->addFlash('success', 'Product added to favorites.');
    }

    $entityManager->flush();
    return $this->redirectToRoute('front_list');
}
#[Route('/produit/{id}/comment', name: 'produit_comment', methods: ['POST'])]
public function addComment(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    if (!$user) {
        $this->addFlash('error', 'You must be logged in to add a comment.');
        return $this->redirectToRoute('app_login');
    }

    $content = $request->request->get('content');
    if (empty($content)) {
        $this->addFlash('error', 'Comment cannot be empty.');
        return $this->redirectToRoute('front_list');
    }

    $commentaire = new Commentaire();
    $commentaire->setContent($content);
    $commentaire->setProduit($produit);
    $commentaire->setUser($user);
    $commentaire->setCreatedAt(new \DateTime());

    $entityManager->persist($commentaire);
    $entityManager->flush();

    $this->addFlash('success', 'Comment added successfully.');
    return $this->redirectToRoute('front_list');

}  

#[Route('/produit/{id}/qr-code', name: 'produit_qr_code', methods: ['GET'])]
public function generateQrCode(Produit $produit, UrlGeneratorInterface $urlGenerator): Response
{
    // Generate the absolute URL for the indexClient page
    $indexClientUrl = 'http://192.168.1.12:8000' . $urlGenerator->generate('front_list', []);

    // Create the QR code
    $qrCode = new QrCode($indexClientUrl);
    $writer = new PngWriter();
    $result = $writer->write($qrCode);

    // Return the QR code as an image response
    return new Response($result->getString(), 200, [
        'Content-Type' => 'image/png',
        'Content-Disposition' => 'inline; filename="qr-code.png"',
    ]);
}

#[Route('/produit/back/{id}', name: 'app_produit_show_back', methods: ['GET'])]
public function showBack(Produit $produit): Response
{
    if (!$produit) {
        throw $this->createNotFoundException('Product not found');
    }

    return $this->render('produit/show.html.twig', [
        'produit' => $produit,
    ]);
}



#[Route('/produit/{id}', name: 'produit_detailP', methods: ['GET'])]
public function showDetails(Produit $produit, ProduitRepository $produitRepository): Response
{
    $relatedProducts = $produitRepository->findBy(
        ['Category' => $produit->getCategory()],
        ['id' => 'DESC'], 
        4 
    );

    return $this->render('produit/detailP.html.twig', [
        'produit' => $produit,
        'relatedProducts' => $relatedProducts,
        'noRelatedProducts' => empty($relatedProducts),
    ]);
}




#[Route('/comment/{id}/delete', name: 'comment_delete', methods: ['POST'])]
public function deleteComment(Commentaire $commentaire, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    if (!$user || $user !== $commentaire->getUser()) {
        $this->addFlash('error', 'You cannot delete this comment.');
        return $this->redirectToRoute('front_list');
    }

    $entityManager->remove($commentaire);
    $entityManager->flush();

    $this->addFlash('success', 'Comment deleted successfully.');
    return $this->redirectToRoute('front_list');
}


#[Route('/search', name: 'produit_search', methods: ['GET'])]
public function search(Request $request, ProduitRepository $produitRepository, EntityManagerInterface $entityManager): Response
{
    $requestString = $request->get('q');
    
    // Create a query to search products by name or description
    $query = $entityManager
        ->createQuery(
            'SELECT p 
            FROM App\Entity\Produit p 
            WHERE p.name LIKE :str OR p.desciption LIKE :str'
        )
        ->setParameter('str', '%' . $requestString . '%');
    
    $products = $query->getResult();
    
    if (!$products) {
        $result['products']['error'] = "Produits non trouvés :(";
    } else {
        $result['products'] = $this->getRealEntities($products);
    }
    
    return new Response(json_encode($result));
}

// Helper method to transform product entities
public function getRealEntities($products)
{
    $realEntities = [];
    foreach ($products as $product) {
        $realEntities[$product->getId()] = [
            'image' => $product->getImage(), 
            'disponibility' => $product->getQuantity() > 0, 
            'name' => $product->getName(), 
            'price' => $product->getPrice()
        ];
    }
    return $realEntities;
}
#[Route('/statistics', name: 'app_statistics')]
public function statistics(CommandeRepository $commandeRepository, ProduitRepository $produitRepository): Response
{
    // Récupérer toutes les commandes confirmées
    $commandes = $commandeRepository->findBy(['statut' => 'confirmed']);

    // 1. Préparer les données pour le graphique des commandes par jour
    $chartDataCommandes = [['Jour', 'Nombre de Commandes Confirmées']]; // En-têtes du tableau
    $commandesParJour = [];

    // Parcourir les commandes et les regrouper par jour
    foreach ($commandes as $commande) {
        $dateCommande = $commande->getDateCommande()->format('Y-m-d'); // Format de date : année-mois-jour

        if (!isset($commandesParJour[$dateCommande])) {
            $commandesParJour[$dateCommande] = 0;
        }

        $commandesParJour[$dateCommande]++;
    }

    // Trier les données par jour (facultatif, mais utile pour l'affichage)
    ksort($commandesParJour);

    // Ajouter les données au tableau pour Google Charts
    foreach ($commandesParJour as $jour => $nombreCommandes) {
        $chartDataCommandes[] = [$jour, $nombreCommandes];
    }

    // 2. Préparer les données pour le graphique en camembert (catégories les plus commandées)
    $chartDataCategories = [['Catégorie', 'Nombre de Commandes']]; // En-têtes du tableau

    // Récupérer les catégories et leurs commandes
    $categories = $produitRepository->findMostOrderedCategories();

    foreach ($categories as $category) {
        $chartDataCategories[] = [$category['name'], $category['total']];
    }

    // Passer les données au template Twig
    return $this->render('statistics/index.html.twig', [
        'chartDataCommandes' => $chartDataCommandes,
        'chartDataCategories' => $chartDataCategories,
    ]);
}}