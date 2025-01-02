<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Restaurant;

class OrderController
{
    private $stripe;
    private $frontendUrl;
    private $webhookSecret;

    public function __construct()
    {
        $this->frontendUrl = env('FRONTEND_URL');
    }

    public function getMyOrders(Request $request)
    {
        try {
            $userId = $request->userId;
            $userId = explode('|', $userId)[1];
    
            // Fetch orders for the user
            $orders = Order::where('user', $userId)->get();

            // Map through the orders and fetch the corresponding restaurant for each order
            foreach ($orders as $order) {
                // Use the restaurant string directly for the query
                $restaurant = Restaurant::where('_id', $order->restaurant)->first();
                // Handle the case where the restaurant is not found
                if (!$restaurant) {
                    return response()->json($order->restaurant);
                }
                $order->setAttribute('restaurant', $restaurant);
            }
    
            return response()->json($orders);
    
        } catch (\Exception $error) {
            logger()->error($error);
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }
    
    public function createCheckoutSession(Request $request)
    {
        try {
            $checkoutSessionRequest = $request->all();
            $restaurant = Restaurant::where('_id', $checkoutSessionRequest['restaurantId'])->first();

            if (!$restaurant) {
                return response()->json(['message' => 'Restaurant not found'], 404);
            }

            $userId = explode('|', $request->userId)[1];

            $order = new Order([
                'user' => $userId,
                'restaurant' => (string) $restaurant->_id,
                'status' => 'placed', 
                'deliveryDetails' => $checkoutSessionRequest['deliveryDetails'],
                'cartItems' => $checkoutSessionRequest['cartItems'],
                'createdAt' => now(),
            ]);
            $order->save();

            return response()->json([
                'order' => $order,
                'message' => 'Order created successfully',
            ], 201);

        } catch (\Exception $error) {
            logger()->error($error);
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }
    
}
