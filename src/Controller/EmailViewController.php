<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmailViewController extends AbstractController
{
    #[Route('/admin/emails', name: 'admin_email_viewer')]
    public function index(): Response
    {
        $mailDir = $this->getParameter('kernel.project_dir') . '/var/mail';
        $emails = [];

        if (is_dir($mailDir)) {
            // Get all files, including subdirectories
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($mailDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            $files = [];
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'txt') {
                    $files[] = $file->getPathname();
                }
            }

            // Sort by modification time, most recent first
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            foreach (array_slice($files, 0, 50) as $file) {
                $content = file_get_contents($file);
                
                // Parse email content (Symfony file transport format)
                $email = [
                    'to' => 'Unknown',
                    'from' => 'noreply@juicestore.com',
                    'subject' => 'No Subject',
                    'body' => $content,
                    'date' => new \DateTime('@' . filemtime($file)),
                    'raw' => $content,
                ];

                // Try to extract headers
                if (preg_match('/To:\s*(.+?)(?:\r?\n|$)/i', $content, $toMatch)) {
                    $email['to'] = trim($toMatch[1]);
                }
                if (preg_match('/From:\s*(.+?)(?:\r?\n|$)/i', $content, $fromMatch)) {
                    $email['from'] = trim($fromMatch[1]);
                }
                if (preg_match('/Subject:\s*(.+?)(?:\r?\n|$)/i', $content, $subjectMatch)) {
                    $email['subject'] = trim($subjectMatch[1]);
                }

                // Extract HTML body if present
                if (preg_match('/<html>.*?<\/html>/is', $content, $htmlMatch)) {
                    $email['body'] = $htmlMatch[0];
                } elseif (preg_match('/Content-Type:\s*text\/html.*?\r?\n\r?\n(.*)/is', $content, $htmlMatch)) {
                    $email['body'] = $htmlMatch[1];
                }

                $emails[] = $email;
            }
        }

        return $this->render('email/viewer.html.twig', [
            'emails' => $emails,
        ]);
    }
}

