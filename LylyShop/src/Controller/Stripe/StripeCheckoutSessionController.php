<?php

namespace App\Controller\Stripe;

use Stripe\Stripe;
use App\Entity\Cart;
use Stripe\Checkout\Session;
use App\Services\OrderServices;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StripeCheckoutSessionController extends AbstractController
{
    /**
     * @Route("/create-checkout-session/{reference}", name="create_checkout_session")
     */
    public function index(?Cart $cart, OrderServices $orderServices, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();     

        if(!$cart) {
            return $this->redirectToRoute('home');
        }

        $order = $orderServices->createOrder($cart);

        Stripe::setApiKey('sk_test_51KWdtPJPOVaSXgzd9kGxi4PY5MtVWRfUAAfSOgoAvyZlGMYLEAUNKC2gSN4c2pF23Ra7zEwlyDmtjGDvW6KpnsJ100KmRqjNWG');
        
        $checkout_session = Session::create([
            'customer_email' => $user->getEmail() ,
            'payment_method_types' => ['card'],
            'line_items' => $orderServices->getLineItems($cart),
            'mode' => 'payment',
            'success_url' => $_ENV['YOUR_DOMAIN'] . '/stripe-payment-success/{CHECKOUT_SESSION_ID}',
            'cancel_url' => $_ENV['YOUR_DOMAIN'] . '/stripe-payment-cancel/{CHECKOUT_SESSION_ID}',
          ]);

          $order->setStripeCheckoutSessionId($checkout_session->id);
          $manager->flush();

        return $this->json([ 'id' => $checkout_session->id ]);
    }
}

