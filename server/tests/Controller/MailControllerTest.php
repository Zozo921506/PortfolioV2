<?php

namespace App\Tests\Controller;

use App\Controller\MailController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\RateLimiter;
use Symfony\Component\RateLimiter\LimiterInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;

final class MailControllerTest extends TestCase
{
    
    
    private function createController(bool $accepted = true): array
    {
        $storage = new \Symfony\Component\RateLimiter\Storage\InMemoryStorage();
        $factory = new RateLimiterFactory(
            [
                'id' => 'mail_test_' . ($accepted ? 'allowed' : 'limited'),
                'policy' => 'fixed_window',
                'limit' => 1,
                'interval' => '1 minute',
            ],
            $storage
        );

        $controller = new MailController($factory);
        return [$controller, $factory];
    }

    public function testSendEmailSuccessfully(): void
    {
        [$controller, $limiter] = $this->createController();
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())->method('send')->with($this->callback(function (Email $email): bool {
            $this->assertSame(
                ['enzo.falla.1506@gmail.com'],
                $email->getTo()[0]->getAddress() !== ''
                    ? array_map(
                        fn ($address) => $address->getAddress(),
                        $email->getTo()
                    )
                    : []
            );

            $this->assertSame(
                'Test subject',
                $email->getSubject()
            );

            return true;
        }));

        $request = Request::create(
            '/api/mail',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'from' => 'sender@example.com',
                'subject' => 'Test subject',
                'message' => 'Hello from PHPUnit',
            ])
        );

        $response = $controller->sendEmail($request, $mailer);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('Email envoyé', $response->getContent());
    }

    public function testSendEmailIsRateLimited(): void
    {   
        [$controller, $factory] = $this->createController();
        $request = Request::create(
            '/api/mail',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'from' => 'sender@example.com',
                'subject' => 'Test subject',
                'message' => 'Hello',
            ])
        );

        $factory->create($request->getClientIp())->consume(1);
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->never())->method('send');
        $response = $controller->sendEmail($request, $mailer);
        $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
    }
}