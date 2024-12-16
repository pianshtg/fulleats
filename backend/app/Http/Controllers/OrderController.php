<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Restaurant;
use Stripe\StripeClient;
use Stripe\Exception\SignatureVerificationException;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    private $stripe;
    private $frontendUrl;
    private $webhookSecret;

    public function __construct()
    {
        $this->stripe = new StripeClient(env('STRIPE_API_KEY'));
        $this->frontendUrl = env('FRONTEND_URL');
        $this->webhookSecret = env('STRIPE_WEBHOOK_SECRET');
    }

    public function getMyOrders(Request $request)
    {
        try {
            $userId = $request->userId;
            $userId = explode('|', $userId)[1];
            $orders = Order::where('user', $userId)
                ->with(['restaurant', 'user'])
                ->get();

            return response()->json($orders);

        } catch (\Exception $error) {
            logger()->error($error);
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function stripeWebhookHandler(Request $request)
    {
        try {
            $payload = $request->getContent();
            $sigHeader = $request->header('Stripe-Signature');
            $event = $this->stripe->webhooks->constructEvent(
                $payload,
                $sigHeader,
                $this->webhookSecret
            );
        } catch (SignatureVerificationException $error) {
            logger()->error($error);
            return response()->json(['message' => 'Webhook error: ' . $error->getMessage()], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $order = Order::find($session->metadata->orderId);

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            $order->total_amount = $session->amount_total / 100; // Stripe amounts are in cents
            $order->status = 'paid';
            $order->save();
        }

        return response()->json([], 200);
    }

    public function createCheckoutSession(Request $request)
    {
        try {
            $checkoutSessionRequest = $request->all();
            $restaurant = Restaurant::find($checkoutSessionRequest['restaurantId']);

            if (!$restaurant) {
                return response()->json(['message' => 'Restaurant not found'], 404);
            }
            
            $userId = explode('|', $request->userId)[1];

            $lineItems = $this->createLineItems(
                $checkoutSessionRequest['cartItems'],
                $restaurant->cartItems
            );

            $order = new Order([
                'user' => $userId,
                'restaurant' => $restaurant->id,
                'status' => 'placed',
                'deliveryDetails' => $checkoutSessionRequest['deliveryDetails'],
                'cartItems' => $checkoutSessionRequest['cartItems'],
                'created_at' => now(),
            ]);
            $order->save();

            $session = $this->createSession(
                $lineItems,
                $order->id,
                $restaurant->deliveryPrice,
                $restaurant->id
            );

            if (!isset($session->url)) {
                return response()->json(['message' => 'Error creating Stripe session'], 500);
            }

            return response()->json(['url' => $session->url]);

        } catch (\Exception $error) {
            logger()->error($error);
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    private function createSession(array $lineItems, string $orderId, float $deliveryPrice, string $restaurantId)
    {
        return $this->stripe->checkout->sessions->create([
            'line_items' => $lineItems,
            'shipping_options' => [
                [
                    'shipping_rate_data' => [
                        'display_name' => 'Delivery',
                        'type' => 'fixed_amount',
                        'fixed_amount' => [
                            'amount' => $deliveryPrice * 100, // Convert to cents
                            'currency' => 'gbp',
                        ],
                    ],
                ],
            ],
            'mode' => 'payment',
            'metadata' => [
                'orderId' => $orderId,
                'restaurantId' => $restaurantId,
            ],
            'success_url' => "{$this->frontendUrl}/order-status?success=true",
            'cancel_url' => "{$this->frontendUrl}/detail/{$restaurantId}?cancelled=true",
        ]);
    }

    private function createLineItems(array $cartItems, array $menuItems)
    {
        $lineItems = [];

        foreach ($cartItems as $cartItem) {
            $menuItem = collect($menuItems)->firstWhere('id', $cartItem['menuItemId']);

            if (!$menuItem) {
                throw new \Exception("Menu item not found: {$cartItem['menuItemId']}");
            }

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'gbp',
                    'unit_amount' => $menuItem['price'] * 100, // Convert to cents
                    'product_data' => [
                        'name' => $menuItem['name'],
                    ],
                ],
                'quantity' => (int) $cartItem['quantity'],
            ];
        }

        return $lineItems;
    }
}
