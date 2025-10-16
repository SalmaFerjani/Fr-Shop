<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/products')]
class ProductAdminController extends AbstractController
{
	#[Route('/', name: 'admin_product_index')]
	public function index(ProductRepository $repository): Response
	{
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$products = $repository->findAll();
		return $this->render('admin/product/index.html.twig', [
			'products' => $products,
		]);
	}

	#[Route('/new', name: 'admin_product_new')]
	public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
	{
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$product = new Product();
		$form = $this->createForm(ProductType::class, $product);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			// Gestion de l'upload de l'image principale
			$mainImageFile = $form->get('mainImageFile')->getData();
			if ($mainImageFile) {
				$mainImagePath = $this->handleImageUpload($mainImageFile, $slugger, 'main');
				if ($mainImagePath) {
					$product->setMainImage($mainImagePath);
				} else {
					$this->addFlash('error', 'Erreur lors de l\'upload de l\'image principale.');
				}
			} else {
				// Si pas d'upload, vérifier si une URL a été fournie
				$mainImageUrl = $form->get('mainImage')->getData();
				if ($mainImageUrl && filter_var($mainImageUrl, FILTER_VALIDATE_URL)) {
					$product->setMainImage($mainImageUrl);
				}
			}

			// Gestion des images supplémentaires
			$additionalImages = $form->get('additionalImages')->getData();
			if ($additionalImages) {
				$imagePaths = [];
				foreach ($additionalImages as $imageFile) {
					$imagePath = $this->handleImageUpload($imageFile, $slugger, 'additional');
					if ($imagePath) {
						$imagePaths[] = $imagePath;
					}
				}
				if (!empty($imagePaths)) {
					$product->setImages($imagePaths);
				} else {
					$this->addFlash('warning', 'Aucune image supplémentaire n\'a pu être uploadée.');
				}
			}

			$em->persist($product);
			$em->flush();
			$this->addFlash('success', 'Produit créé avec succès !');
			return $this->redirectToRoute('admin_product_index');
		} else {
			// Afficher les erreurs de validation
			foreach ($form->getErrors(true) as $error) {
				$this->addFlash('error', $error->getMessage());
			}
		}

		return $this->render('admin/product/new.html.twig', [
			'form' => $form->createView(),
		]);
	}

	#[Route('/edit/{id}', name: 'admin_products_edit', methods: ['GET', 'POST'])]
	public function edit(Request $request, int $id, EntityManagerInterface $em, SluggerInterface $slugger, ProductRepository $productRepository): Response
	{
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$product = $productRepository->find($id);
		if (!$product) {
			throw $this->createNotFoundException('Produit non trouvé.');
		}
		
		$form = $this->createForm(ProductType::class, $product);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			// Gestion de l'upload de la nouvelle image principale
			$mainImageFile = $form->get('mainImageFile')->getData();
			if ($mainImageFile) {
				$mainImagePath = $this->handleImageUpload($mainImageFile, $slugger, 'main');
				if ($mainImagePath) {
					$product->setMainImage($mainImagePath);
				} else {
					$this->addFlash('error', 'Erreur lors de l\'upload de l\'image principale.');
				}
			} else {
				// Si pas d'upload, vérifier si une URL a été fournie
				$mainImageUrl = $form->get('mainImage')->getData();
				if ($mainImageUrl && filter_var($mainImageUrl, FILTER_VALIDATE_URL)) {
					$product->setMainImage($mainImageUrl);
				}
			}

			// Gestion des nouvelles images supplémentaires
			$additionalImages = $form->get('additionalImages')->getData();
			if ($additionalImages) {
				$existingImages = $product->getImages();
				$newImagePaths = [];
				foreach ($additionalImages as $imageFile) {
					$imagePath = $this->handleImageUpload($imageFile, $slugger, 'additional');
					if ($imagePath) {
						$newImagePaths[] = $imagePath;
					}
				}
				if (!empty($newImagePaths)) {
					$allImages = array_merge($existingImages, $newImagePaths);
					$product->setImages($allImages);
				}
			}

			$em->flush();
			$this->addFlash('success', 'Produit mis à jour avec succès !');
			return $this->redirectToRoute('admin_product_index');
		}

		return $this->render('admin/product/edit.html.twig', [
			'form' => $form->createView(),
			'product' => $product,
		]);
	}

	#[Route('/delete/{id}', name: 'admin_products_delete', methods: ['POST'])]
	public function delete(Request $request, int $id, EntityManagerInterface $em, ProductRepository $productRepository): Response
	{
		$this->denyAccessUnlessGranted('ROLE_ADMIN');
		$product = $productRepository->find($id);
		if (!$product) {
			throw $this->createNotFoundException('Produit non trouvé.');
		}
		
		if (!$this->isCsrfTokenValid('delete_product_' . $product->getId(), (string) $request->request->get('_token'))) {
			throw $this->createAccessDeniedException('Token CSRF invalide.');
		}

		// Supprimer les images associées si elles existent
		$projectDir = $this->getParameter('kernel.project_dir') . '/public';
		
		// Supprimer l'image principale
		if ($product->getMainImage() && file_exists($projectDir . $product->getMainImage())) {
			unlink($projectDir . $product->getMainImage());
		}
		
		// Supprimer les images supplémentaires
		foreach ($product->getImages() as $imagePath) {
			if (file_exists($projectDir . $imagePath)) {
				unlink($projectDir . $imagePath);
			}
		}

		$em->remove($product);
		$em->flush();
		$this->addFlash('success', 'Produit supprimé avec succès.');
		return $this->redirectToRoute('admin_product_index');
	}

	/**
	 * Gère l'upload d'une image
	 */
	private function handleImageUpload(UploadedFile $file, SluggerInterface $slugger, string $type): ?string
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
			$uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/products/' . $type;
			
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
			return '/uploads/products/' . $type . '/' . $newFilename;
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


