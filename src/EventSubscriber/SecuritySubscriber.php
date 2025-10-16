<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecuritySubscriber implements EventSubscriberInterface
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        $request = $event->getRequest();
        $response = $event->getResponse();

        // Check if user has admin role
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $targetUrl = $this->urlGenerator->generate('admin_dashboard');
        } else {
            $targetUrl = $this->urlGenerator->generate('user_dashboard');
        }

        $response->headers->set('Location', $targetUrl);
    }
}
