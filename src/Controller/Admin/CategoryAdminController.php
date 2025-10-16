<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/categories')]
class CategoryAdminController extends AbstractController
{
	#[Route('/', name: 'admin_category_index')]
	public function index(CategoryRepository $repository): Response
	{
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$categories = $repository->findAll();
		return $this->render('admin/category/index.html.twig', [
			'categories' => $categories,
		]);
	}

	#[Route('/new', name: 'admin_category_new')]
	public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
	{
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$category = new Category();
		$form = $this->createForm(CategoryType::class, $category);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			// Gestion de l'upload de l'image
			$imageFile = $form->get('imageFile')->getData();
			if ($imageFile) {
				$imagePath = $this->handleImageUpload($imageFile, $slugger);
				if ($imagePath) {
					$category->setImage($imagePath);
				} else {
					$this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
				}
			} else {
				// Si pas d'upload, vérifier si une URL a été fournie
				$imageUrl = $form->get('image')->getData();
				if ($imageUrl && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
					$category->setImage($imageUrl);
				}
			}

			$em->persist($category);
			$em->flush();
			$this->addFlash('success', 'Catégorie créée avec succès !');
			return $this->redirectToRoute('admin_category_index');
		} else {
			// Afficher les erreurs de validation
			foreach ($form->getErrors(true) as $error) {
				$this->addFlash('error', $error->getMessage());
			}
		}

		return $this->render('admin/category/new.html.twig', [
			'form' => $form->createView(),
		]);
	}

	#[Route('/edit/{id}', name: 'admin_categories_edit', methods: ['GET', 'POST'])]
	public function edit(Request $request, int $id, EntityManagerInterface $em, SluggerInterface $slugger, CategoryRepository $categoryRepository): Response
	{
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$category = $categoryRepository->find($id);
		if (!$category) {
			throw $this->createNotFoundException('Catégorie non trouvée.');
		}
		
		$form = $this->createForm(CategoryType::class, $category);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			// Gestion de l'upload de la nouvelle image
			$imageFile = $form->get('imageFile')->getData();
			if ($imageFile) {
				$imagePath = $this->handleImageUpload($imageFile, $slugger);
				if ($imagePath) {
					$category->setImage($imagePath);
				} else {
					$this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
				}
			} else {
				// Si pas d'upload, vérifier si une URL a été fournie
				$imageUrl = $form->get('image')->getData();
				if ($imageUrl && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
					$category->setImage($imageUrl);
				}
			}

			$em->flush();
			$this->addFlash('success', 'Catégorie mise à jour avec succès !');
			return $this->redirectToRoute('admin_category_index');
		} else {
			// Afficher les erreurs de validation
			foreach ($form->getErrors(true) as $error) {
				$this->addFlash('error', $error->getMessage());
			}
		}

		return $this->render('admin/category/edit.html.twig', [
			'form' => $form->createView(),
			'category' => $category,
		]);
	}

	#[Route('/delete/{id}', name: 'admin_categories_delete', methods: ['POST'])]
	public function delete(Request $request, int $id, EntityManagerInterface $em, CategoryRepository $categoryRepository): Response
	{
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$category = $categoryRepository->find($id);
		if (!$category) {
			throw $this->createNotFoundException('Catégorie non trouvée.');
		}
		
		if (!$this->isCsrfTokenValid('delete_category_' . $category->getId(), (string) $request->request->get('_token'))) {
			throw $this->createAccessDeniedException('Token CSRF invalide.');
		}

		// Vérifier s'il y a des produits associés à cette catégorie
		if ($category->getProducts()->count() > 0) {
			$this->addFlash('error', 'Impossible de supprimer cette catégorie car elle contient des produits. Supprimez d\'abord tous les produits de cette catégorie.');
			return $this->redirectToRoute('admin_category_index');
		}

		// Supprimer l'image associée si elle existe
		if ($category->getImage() && file_exists($this->getParameter('kernel.project_dir') . '/public' . $category->getImage())) {
			unlink($this->getParameter('kernel.project_dir') . '/public' . $category->getImage());
		}

		$em->remove($category);
		$em->flush();
		$this->addFlash('success', 'Catégorie supprimée avec succès.');
		return $this->redirectToRoute('admin_category_index');
	}

	/**
	 * Gère l'upload d'une image pour une catégorie
	 */
	private function handleImageUpload(UploadedFile $file, SluggerInterface $slugger): ?string
	{
		// Vérifier que le fichier est valide
		if (!$file->isValid()) {
			$this->addFlash('error', 'Fichier invalide : ' . $file->getErrorMessage());
			return null;
		}

		// Vérifier la taille du fichier (max 5MB)
		if ($file->getSize() > 5 * 1024 * 1024) {
			$this->addFlash('error', 'Le fichier est trop volumineux. Taille maximale : 5MB');
			return null;
		}

		// Vérifier le type MIME
		$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
		if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
			$this->addFlash('error', 'Type de fichier non autorisé. Formats acceptés : JPEG, PNG, GIF, WebP');
			return null;
		}

		$originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		$safeFilename = $slugger->slug($originalFilename);
		
		// Gérer le cas où l'extension fileinfo n'est pas disponible
		$extension = $this->getFileExtension($file);
		$newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

		try {
			$uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/categories';
			
			// Créer le répertoire s'il n'existe pas
			if (!is_dir($uploadDir)) {
				if (!mkdir($uploadDir, 0755, true)) {
					$this->addFlash('error', 'Impossible de créer le répertoire d\'upload : ' . $uploadDir);
					return null;
				}
			}

			// Vérifier les permissions d'écriture
			if (!is_writable($uploadDir)) {
				$this->addFlash('error', 'Le répertoire d\'upload n\'est pas accessible en écriture : ' . $uploadDir);
				return null;
			}

			// Déplacer le fichier
			$file->move($uploadDir, $newFilename);
			
			// Vérifier que le fichier a bien été déplacé
			$fullPath = $uploadDir . '/' . $newFilename;
			if (!file_exists($fullPath)) {
				$this->addFlash('error', 'Le fichier n\'a pas pu être sauvegardé.');
				return null;
			}
			
			// Vérifier que le fichier n'est pas vide
			if (filesize($fullPath) === 0) {
				unlink($fullPath);
				$this->addFlash('error', 'Le fichier uploadé est vide.');
				return null;
			}
			
			// Retourner le chemin relatif pour la base de données
			return '/uploads/categories/' . $newFilename;
		} catch (FileException $e) {
			$this->addFlash('error', 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
			return null;
		} catch (\Exception $e) {
			$this->addFlash('error', 'Erreur inattendue lors de l\'upload : ' . $e->getMessage());
			return null;
		}
	}

	/**
	 * Obtient l'extension d'un fichier, avec fallback si fileinfo n'est pas disponible
	 */
	private function getFileExtension(UploadedFile $file): string
	{
		try {
			// Essayer d'utiliser la méthode Symfony
			return $file->guessExtension();
		} catch (\Exception $e) {
			// Fallback : utiliser l'extension du nom de fichier original
			$originalName = $file->getClientOriginalName();
			$extension = pathinfo($originalName, PATHINFO_EXTENSION);
			
			// Si pas d'extension, essayer de deviner à partir du type MIME
			if (empty($extension)) {
				$mimeType = $file->getMimeType();
				switch ($mimeType) {
					case 'image/jpeg':
						return 'jpg';
					case 'image/png':
						return 'png';
					case 'image/gif':
						return 'gif';
					case 'image/webp':
						return 'webp';
					default:
						return 'bin';
				}
			}
			
			return $extension;
		}
	}
}


