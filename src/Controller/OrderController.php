<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/order/{orderNumber}', name: 'app_order_confirm_show')]
    public function showConfirmation(string $orderNumber, OrderRepository $orderRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $order = $orderRepository->findByOrderNumber($orderNumber);
        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        return $this->render('order/confirm.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/order/{orderNumber}/confirm', name: 'app_order_confirm', methods: ['POST'])]
    public function confirm(string $orderNumber, OrderRepository $orderRepository, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $order = $orderRepository->findByOrderNumber($orderNumber);
        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        if ($order->getStatus() !== Order::STATUS_PENDING) {
            $this->addFlash('info', 'Cette commande a déjà été traitée.');
            return $this->redirectToRoute('app_products');
        }

        // Décrémenter les stocks
        foreach ($order->getOrderItems() as $item) {
            $product = $item->getProduct();
            $newStock = max(0, (int) $product->getStock() - (int) $item->getQuantity());
            $product->setStock($newStock);
        }

        // Marquer comme confirmée
        $order->setStatus(Order::STATUS_CONFIRMED);
        $entityManager->flush();

        $this->addFlash('success', 'Votre commande est confirmée. Merci !');

        return $this->redirectToRoute('app_products');
    }
}


