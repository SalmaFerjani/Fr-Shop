<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart')]
class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart')]
    public function index(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('cart', []);
        $cartItems = [];
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->find($productId);
            if ($product && $product->isIsActive() && $product->isInStock()) {
                $subtotal = $product->getPriceWithTva() * $quantity;
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ];
                $total += $subtotal;
            } else {
                // Supprimer le produit du panier s'il n'est plus disponible
                unset($cart[$productId]);
            }
        }

        // Mettre à jour la session
        $session->set('cart', $cart);

        return $this->render('cart/index.html.twig', [
            'cart_items' => $cartItems,
            'total' => $total,
            'tva' => $total * 0.20,
            'subtotal' => $total / 1.20,
        ]);
    }

    #[Route('/add/{id}', name: 'app_cart_add', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function add(int $id, Request $request, SessionInterface $session, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('cart_add_' . $product->getId(), $token)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }
        if (!$product->isIsActive() || !$product->isInStock()) {
            $this->addFlash('error', 'Ce produit n\'est pas disponible.');
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        $quantity = (int) $request->request->get('quantity', 1);
        if ($quantity <= 0) {
            $quantity = 1;
        }

        if ($quantity > $product->getStock()) {
            $this->addFlash('error', 'Quantité non disponible en stock.');
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        $cart = $session->get('cart', []);
        
        if (isset($cart[$product->getId()])) {
            $cart[$product->getId()] += $quantity;
        } else {
            $cart[$product->getId()] = $quantity;
        }

        $session->set('cart', $cart);

        $this->addFlash('success', 'Produit ajouté au panier.');
        
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/update/{id}', name: 'app_cart_update', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function update(int $id, Request $request, SessionInterface $session, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('cart_update_' . $product->getId(), $token)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }
        $quantity = (int) $request->request->get('quantity', 0);
        $cart = $session->get('cart', []);

        if ($quantity <= 0) {
            unset($cart[$product->getId()]);
        } else {
            if ($quantity > $product->getStock()) {
                $this->addFlash('error', 'Quantité non disponible en stock.');
                return $this->redirectToRoute('app_cart');
            }
            $cart[$product->getId()] = $quantity;
        }

        $session->set('cart', $cart);

        $this->addFlash('success', 'Panier mis à jour.');
        
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/remove/{id}', name: 'app_cart_remove', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function remove(int $id, Request $request, SessionInterface $session, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('cart_remove_' . $product->getId(), $token)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }
        $cart = $session->get('cart', []);
        unset($cart[$product->getId()]);
        $session->set('cart', $cart);

        $this->addFlash('success', 'Produit retiré du panier.');
        
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/clear', name: 'app_cart_clear', methods: ['POST'])]
    public function clear(Request $request, SessionInterface $session): Response
    {
        $token = (string) $request->request->get('_token');
        if (!$this->isCsrfTokenValid('cart_clear', $token)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }
        $session->remove('cart');

        $this->addFlash('success', 'Panier vidé.');
        
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/checkout', name: 'app_checkout')]
    public function checkout(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('cart', []);
        if (empty($cart)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart');
        }

        $cartItems = [];
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->find($productId);
            if ($product && $product->isIsActive() && $product->isInStock()) {
                $subtotal = $product->getPriceWithTva() * $quantity;
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal,
                ];
                $total += $subtotal;
            }
        }

        return $this->render('cart/checkout.html.twig', [
            'cart_items' => $cartItems,
            'total' => $total,
            'tva' => $total * 0.20,
            'subtotal' => $total / 1.20,
        ]);
    }

    #[Route('/checkout/process', name: 'app_checkout_process', methods: ['POST'])]
    public function processCheckout(Request $request, SessionInterface $session, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        $cart = $session->get('cart', []);
        if (empty($cart)) {
            $this->addFlash('warning', 'Votre panier est vide.');
            return $this->redirectToRoute('app_cart');
        }

        // Validate Visa-only payment fields (basic checks, placeholder for a real PSP)
        $cardNumber = preg_replace('/\s+/', '', (string) $request->request->get('cardNumber', ''));
        $cardHolder = (string) $request->request->get('cardHolder', '');
        $expiryMonth = (int) $request->request->get('expiryMonth', 0);
        $expiryYear = (int) $request->request->get('expiryYear', 0);
        $cvc = (string) $request->request->get('cvc', '');

        // Visa numbers start with 4 and are 13-19 digits (most commonly 16)
        if ($cardNumber === '' || $cardNumber[0] !== '4' || !preg_match('/^4\d{12,18}$/', $cardNumber)) {
            $this->addFlash('error', 'Veuillez entrer un numéro de carte Visa valide.');
            return $this->redirectToRoute('app_checkout');
        }
        if ($expiryMonth < 1 || $expiryMonth > 12) {
            $this->addFlash('error', 'Mois d\'expiration invalide.');
            return $this->redirectToRoute('app_checkout');
        }
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');
        if ($expiryYear < $currentYear || $expiryYear > ($currentYear + 15)) {
            $this->addFlash('error', 'Année d\'expiration invalide.');
            return $this->redirectToRoute('app_checkout');
        }
        if ($expiryYear === $currentYear && $expiryMonth < $currentMonth) {
            $this->addFlash('error', 'La carte est expirée.');
            return $this->redirectToRoute('app_checkout');
        }
        if (!preg_match('/^\d{3,4}$/', $cvc)) {
            $this->addFlash('error', 'CVC invalide.');
            return $this->redirectToRoute('app_checkout');
        }
        if ($cardHolder === '') {
            $this->addFlash('error', 'Le titulaire de la carte est requis.');
            return $this->redirectToRoute('app_checkout');
        }

        // Paiement simulé ok -> créer la commande en statut en attente (pending)
        $order = new Order();
        $order->setUser($this->getUser());
        $order->setStatus(Order::STATUS_PENDING);
        $order->setShippingAddress((string) $request->request->get('address'));
        $order->setShippingPostalCode((string) $request->request->get('postalCode'));
        $order->setShippingCity((string) $request->request->get('city'));
        $order->setShippingCountry((string) $request->request->get('country', 'France'));
        $order->setShippingPhone($request->request->get('phone'));
        $order->setNotes($request->request->get('notes'));

        // Créer les OrderItem à partir du panier
        foreach ($cart as $productId => $quantity) {
            $product = $productRepository->find($productId);
            if (!$product) {
                continue;
            }
            $item = new OrderItem();
            $item->setProduct($product);
            $item->setQuantity((int) $quantity);
            $order->addOrderItem($item);
        }

        // Calculer les totaux
        $order->calculateTotals();

        $entityManager->persist($order);
        $entityManager->flush();

        // Ne pas vider le panier avant confirmation explicite, mais on peut le garder pour retour; on le vide pour éviter doubles commandes
        $session->remove('cart');

        $this->addFlash('info', 'Commande créée et en attente de confirmation. Veuillez confirmer votre commande.');

        return $this->redirectToRoute('app_order_confirm_show', ['orderNumber' => $order->getOrderNumber()]);
    }
} 