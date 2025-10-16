<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SecurityHeadersSubscriber implements EventSubscriberInterface
{
	public static function getSubscribedEvents(): array
	{
		return [
			KernelEvents::RESPONSE => 'onKernelResponse',
		];
	}

	public function onKernelResponse(ResponseEvent $event): void
	{
		$response = $event->getResponse();
		$request = $event->getRequest();

		// Content Security Policy tuned for CDN assets used in the app
		$csp = implode('; ', [
			"default-src 'self'",
			"script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
			"style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com",
			"font-src 'self' https://fonts.gstatic.com data:",
			"img-src 'self' data: https://images.unsplash.com",
			"connect-src 'self'",
			"frame-ancestors 'none'",
		]);

		$response->headers->set('Content-Security-Policy', $csp);
		$response->headers->set('X-Content-Type-Options', 'nosniff');
		$response->headers->set('X-Frame-Options', 'DENY');
		$response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
		$response->headers->set('Permissions-Policy', "geolocation=(), microphone=(), camera=()");

		// Set HSTS only on HTTPS requests
		if ($request->isSecure()) {
			$response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
		}
	}
}


