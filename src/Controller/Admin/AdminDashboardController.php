<?php

namespace App\Controller\Admin;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminDashboardController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function index(
        ProductRepository $productRepository,
        UserRepository $userRepository,
        OrderRepository $orderRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $stats = [
            'total_products' => $productRepository->count([]),
            'total_users' => $userRepository->count([]),
            'total_orders' => $orderRepository->countByStatus(\App\Entity\Order::STATUS_CONFIRMED),
            'recent_products' => $productRepository->findBy([], ['createdAt' => 'DESC'], 5),
            'recent_orders' => $orderRepository->findRecentOrders(5),
        ];

        return $this->render('admin/dashboard/index.html.twig', [
            'stats' => $stats,
        ]);
    }
}
