<?php

namespace App\Tests\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HeadersTest extends WebTestCase
{
	public function testSecurityHeadersPresent(): void
	{
		$client = static::createClient();
		$client->request('GET', '/');
		$response = $client->getResponse();

		self::assertTrue($response->headers->has('Content-Security-Policy'));
		self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
		self::assertSame('DENY', $response->headers->get('X-Frame-Options'));
		self::assertSame('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
	}
}


