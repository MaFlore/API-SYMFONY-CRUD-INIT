<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class ArticleController extends AbstractController
{
    //Fonction pour la recupération des articles
    #[Route('/articles', name: 'liste_articles', methods: ['GET'])]
    public function listeArticles(ArticleRepository $articleRepository): JsonResponse
    {
        $articlesList = $articleRepository->findAll();
        return $this->json($articlesList, Response::HTTP_OK);
    }

    //Fonction pour afficher les détails d'un article
    #[Route('/article/{id<\d+>}', name: 'detail_article', methods: ['GET'])]
    public function detailArticle($id, ArticleRepository $articleRepository): JsonResponse
    {
        $article = $articleRepository->find($id);
        if(!$article){
            return $this->json(['message'=>'Article introuvable.'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($article, Response::HTTP_OK);
    }

    //Fonction pour ajouter un article
    #[Route('/article{id<\d+>}', name: 'ajouter_article', methods: ['POST'])]
    public function ajouterArticle(Request $request, SerializerInterface $serializer, EntityManagerInterface $em): JsonResponse
    {
        $jsonData = $request->getContent();
        $article = $serializer->deserialize($jsonData, Article::class, 'json');
        
        $em->persist($article);
        $em->flush();

        return $this->json($article, Response::HTTP_CREATED); 
    }

    //Fonction pour modifier un article
    #[Route('/article{id<\d+>}', name: 'modifier_article', methods: ['PUT'])]
    public function modifierArticle($id, Request $request, SerializerInterface $serializer, 
        ArticleRepository $articleRepository, EntityManagerInterface $em): JsonResponse
    {
        $article = $articleRepository->find($id);
        if(!$article){
            return $this->json(['message'=>"Article introuvable"], Response::HTTP_NOT_FOUND);
        }

        $jsonData = $request->getContent();
        $articleUpdate = $serializer->deserialize($jsonData, Article::class, 'json',
            ['object_to_populate'=>$article]);
        
        $em->persist($articleUpdate);
        $em->flush();

        return $this->json($articleUpdate, Response::HTTP_OK); 
    }

    //Fonction pour supprimer un article
    #[Route('/article/{id<\d+>}', name: 'supprimer_categorie', methods: ['DELETE'])]
    public function supprimerArticle($id, ArticleRepository $articleRepository, EntityManagerInterface $em): JsonResponse
    {
        $article = $articleRepository->find($id);
        if(!$article){
            return $this->json(['message'=>'Article introuvable.'], Response::HTTP_NOT_FOUND);
        }
        $em->remove($article);
        $em->flush();
        return $this->json(['message'=>'Article supprimé avec succès.'], Response::HTTP_OK);
    }
}
