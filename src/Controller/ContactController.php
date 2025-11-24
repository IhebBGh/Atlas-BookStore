<?php

namespace App\Controller;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $name = trim($request->request->get('name', ''));
            $email = trim($request->request->get('email', ''));
            $subject = trim($request->request->get('subject', ''));
            $content = trim($request->request->get('content', ''));

            // Validation
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'Name is required.';
            }
            
            if (empty($email)) {
                $errors[] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format.';
            }
            
            if (empty($subject)) {
                $errors[] = 'Subject is required.';
            }
            
            if (empty($content)) {
                $errors[] = 'Message content is required.';
            } elseif (strlen($content) < 10) {
                $errors[] = 'Message must be at least 10 characters long.';
            }

            if (empty($errors)) {
                // Create message
                $message = new Message();
                $message->setName($name);
                $message->setEmail($email);
                $message->setSubject($subject);
                $message->setContent($content);
                $message->setStatus('new');

                // If user is logged in, associate with user
                if ($this->getUser()) {
                    $message->setUser($this->getUser());
                    // Use user's email if not provided
                    if (empty($email)) {
                        $message->setEmail($this->getUser()->getEmail());
                    }
                }

                $entityManager->persist($message);
                $entityManager->flush();

                $this->addFlash('success', 'Your message has been sent successfully! We will get back to you soon.');
                return $this->redirectToRoute('app_contact');
            } else {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
            }
        }

        return $this->render('contact/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }
}

