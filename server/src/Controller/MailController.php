<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class MailController extends AbstractController
{
    private $limiter;

    public function __construct(RateLimiterFactory $apiLimiter)
    {
        $this->limiter = $apiLimiter;
    }
    
    #[Route('/api/mail', name: 'app_mail', methods: 'POST')]
    public function sendEmail(Request $request, MailerInterface $mailer): Response
    {
        $limiter = $this->limiter->create($request->getClientIp());
        
        if (!$limiter->consume(1)->isAccepted())
        {
            return new Response('Trop de requêtes. Veuillez réessayer plus tard.', 429);
        }

        $data = json_decode($request->getContent(), true);
        $email = (new Email())
            -> from($data['from'])
            -> to('enzo.falla.1506@gmail.com')
            -> subject($data['subject'])
            -> html('<p>From '. $data['from']. '</p><p>'. $data['message']. '</p>');
        $mailer->send($email);
        return new Response("Email envoyé");
    }
}
