<?php

namespace App\Service;

use App\Entity\Order;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private string $fromEmail = 'noreply@juicestore.com'
    ) {
    }

    public function getFromEmail(): string
    {
        return $this->fromEmail;
    }

    public function sendOrderConfirmation(Order $order): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($order->getUser()->getEmail())
            ->subject('Order Confirmation #' . $order->getId())
            ->html($this->getOrderConfirmationTemplate($order));

        $this->mailer->send($email);
    }

    public function sendOrderStatusUpdate(Order $order): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($order->getUser()->getEmail())
            ->subject('Order #' . $order->getId() . ' Status Updated')
            ->html($this->getOrderStatusUpdateTemplate($order));

        $this->mailer->send($email);
    }

    private function getOrderConfirmationTemplate(Order $order): string
    {
        $items = '';
        foreach ($order->getItems() as $item) {
            $items .= sprintf(
                '<tr><td>%s</td><td>%d</td><td>%s €</td><td>%s €</td></tr>',
                $item->getProduct()->getNom(),
                $item->getQuantity(),
                number_format((float)$item->getPrice(), 2, ',', ' '),
                number_format((float)$item->getSubtotal(), 2, ',', ' ')
            );
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #ff6b35, #f7931e); color: white; padding: 20px; text-align: center; }
        .content { background: #f9f9f9; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .total { font-size: 18px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍹 Order Confirmation</h1>
        </div>
        <div class="content">
            <p>Thank you for your order!</p>
            <p><strong>Order #{$order->getId()}</strong></p>
            <p>Date: {$order->getCreatedAt()->format('d/m/Y H:i')}</p>
            
            <h3>Order Items:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    {$items}
                </tbody>
            </table>
            
            <p class="total">Total: {$order->getTotal()} €</p>
            
            <p>We'll send you another email when your order ships.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getOrderStatusUpdateTemplate(Order $order): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #ff6b35, #f7931e); color: white; padding: 20px; text-align: center; }
        .content { background: #f9f9f9; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Order Status Update</h1>
        </div>
        <div class="content">
            <p>Your order #{$order->getId()} status has been updated.</p>
            <p><strong>New Status: {$order->getStatus()}</strong></p>
            <p>You can view your order details in your account.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}

