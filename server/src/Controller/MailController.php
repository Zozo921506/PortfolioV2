
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Psr\Log\LoggerInterface;

final class MailController extends AbstractController
{
    public function __construct(private RateLimiterFactory $apiLimiter, private LoggerInterface $logger,) 
    {
        
    }

    #[Route('/api/mail', name: 'app_mail', methods: ['POST'])]
    public function sendEmail(Request $request, MailerInterface $mailer): JsonResponse 
    {
        $limiter = $this->apiLimiter->create($request->getClientIp() ?? 'unknown');

        if (!$limiter->consume(1)->isAccepted()) {
            return $this->json(['error' => 'Trop de requêtes. Réessayez plus tard.'], 429);
        }

        $data = json_decode($request->getContent(), true);

        if ( !is_array($data) || !filter_var($data['from'] ?? '', FILTER_VALIDATE_EMAIL) || !is_string($data['message'] ?? null) || trim($data['message']) === '') {
            return $this->json(['error' => 'Données invalides.'], 400);
        }

        $subject = trim((string) ($data['subject'] ?? ''));
        $subject = $subject !== '' ? $subject : 'Message du formulaire';

        $email = (new Email())
            ->from('enzo.falla.1506@gmail.com')
            ->replyTo($data['from'])
            ->to('enzo.falla.1506@gmail.com')
            ->subject($subject)
            ->text("Expéditeur : {$data['from']}\n\n". $data['message']);

        try {
            $mailer->send($email);
            return $this->json(['message' => 'Email envoyé.']);
        } catch (\Throwable $e) {
            $this->logger->error('Échec de l’envoi du mail.', ['exception' => $e]);
            return $this->json(['error' => 'Impossible d’envoyer le message.'], 500);
        }
    }
}