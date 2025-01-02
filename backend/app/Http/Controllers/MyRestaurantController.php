<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Order;

class MyRestaurantController
{
    public function createMyRestaurant(Request $request)
    {
        try {
            $userId = $request->userId;
            $userId = explode('|', $userId)[1];
            $existingRestaurant = Restaurant::where('user', $userId)->first();

            if ($existingRestaurant) {
                return response()->json(['message' => 'User restaurant already exists'], 409);
            }

            $imageUrl = cloudinary()->upload($request->file('imageFile')->getRealPath())->getSecurePath();

            // Create new restaurant
            $restaurant = new Restaurant($request->all());
            $restaurant->imageUrl = $imageUrl;
            $restaurant->user = $userId;
            $restaurant->lastUpdated = now();
            $restaurant->save();

            return response()->json($restaurant, 201);

        } catch (\Exception $error) {
            logger()->error($error);
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function getMyRestaurant(Request $request)
    {
        try {
            $userId = $request->userId;
            $userId = explode('|', $userId)[1];
            $restaurant = Restaurant::where('user', $userId)->first();

            if (!$restaurant) {
                return response()->json(['message' => 'Restaurant not found'], 404);
            }

            return response()->json($restaurant);
        } catch (\Exception $error) {
            logger()->error($error);
            return response()->json(['message' => 'Error fetching restaurant'], 500);
        }
    }

    public function updateMyRestaurant(Request $request)
    {
        try {
            $userId = $request->userId;
            $userId = explode('|', $userId)[1];
    
            // Fetch the restaurant associated with the user
            $restaurant = Restaurant::where('user', $userId)->first();
    
            if (!$restaurant) {
                return response()->json(['message' => 'Restaurant not found'], 404);
            }
    
            // Validate incoming request
            $request->validate([
                'restaurantName' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'country' => 'required|string|max:255',
                'deliveryPrice' => 'required|numeric',
                'estimatedDeliveryTime' => 'required|numeric',
                'cuisines' => 'required|array',
                'menuItems' => 'required|array',
            ]);
    
            // Update the restaurant fields
            $restaurant->fill($request->all());
    
            // Handle image upload
            if ($request->hasFile('imageFile')) {
                $imageUrl = cloudinary()->upload($request->file('imageFile')->getRealPath())->getSecurePath();
                // Replace with actual upload logic if needed
                $restaurant->imageUrl = $imageUrl;
            }
    
            $restaurant->lastUpdated = now();
            $restaurant->save();
    
            return response()->json($restaurant, 200);
        } catch (\Exception $error) {
            logger()->error($error);
            return response()->json(['message' => 'Something went wrong', 'error' => $error->getMessage()], 500);
        }
    }
    

    public function getMyRestaurantOrders(Request $request)
    {
        try {
            $userId = $request->userId;
            $userId = explode('|', $userId)[1];
    
            // Find the restaurant for the user
            $restaurant = Restaurant::where('user', $userId)->first();
    
            if (!$restaurant) {
                return response()->json(['message' => 'Restaurant not found'], 404);
            }
    
            // Fetch all orders for the restaurant
            $orders = Order::where('restaurant', $restaurant->_id)->get();
    
            // Add the restaurant details dynamically to each order
            foreach ($orders as $order) {
                $order->setAttribute('restaurant', $restaurant);
            }
    
            return response()->json($orders);
        } catch (\Exception $error) {
            logger()->error($error);
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }    

        public function updateOrderStatus(Request $request, $orderId)
        {
            try {
                $userId = $request->userId;
                $userId = explode('|', $userId)[1];
                
                $updateStatusRequest = $request->input('status');
                $order = Order::where('_id', $orderId)->first();

                if (!$order) {
                    return response()->json(['message' => 'Order not found'], 404);
                }

                $order->status = $updateStatusRequest;
                $order->save();

                return response()->json($order, 200);
            } catch (\Exception $error) {
                logger()->error($error);
                return response()->json(['message' => 'Unable to update order status'], 500);
            }
        }

    // private function uploadImage($file)
    // {
    //     try {
    //         $uploadResponse = Cloudinary::upload($file->getRealPath(), [
    //             'folder' => 'restaurants',
    //         ]);

    //         return $uploadResponse->getSecurePath();
    //     } catch (\Exception $error) {
    //         logger()->error($error);
    //         throw new \Exception('Image upload failed');
    //     }
    // }
}
