<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Str;

class MyRestaurantController extends Controller
{
    public function createMyRestaurant(Request $request)
    {
        try {
            $userId = $request->userId; // Get authenticated user's ID
            $userId = explode('|', $userId)[1];
            $existingRestaurant = Restaurant::where('user', $userId)->first();

            if ($existingRestaurant) {
                return response()->json(['message' => 'User restaurant already exists'], 409);
            }

            // Handle the uploaded image
            // $imageUrl = $this->uploadImage($request->file('imageFile'));
            $imageUrl = 'https://res.cloudinary.com/ddhzxw6j8/image/upload/v1734267784/Mitra_Telkom_Property/Mitra%20Company%20One/Nomor_Kontrak_%5B1%5D/Nama_Pekerjaan_%5Bpekerjaan_1%5D/2024-12-15/E/gxzdbhesaphsxxeyglde.jpg';

            // Create a new restaurant
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
            $restaurant = Restaurant::where('user_id', $userId)->first();

            if (!$restaurant) {
                return response()->json(['message' => 'Restaurant not found'], 404);
            }

            // Update restaurant fields
            $restaurant->restaurantName = $request->input('restaurantName');
            $restaurant->city = $request->input('city');
            $restaurant->country = $request->input('country');
            $restaurant->deliveryPrice = $request->input('deliveryPrice');
            $restaurant->estimatedDeliveryTime = $request->input('estimatedDeliveryTime');
            $restaurant->cuisines = $request->input('cuisines');

            // Update menu items
            $restaurant->menu_items = collect($request->input('menuItems'))->map(function ($menuItem) {
                if (!isset($menuItem['id'])) {
                    $menuItem['id'] = (string) Str::uuid();
                }
                return $menuItem;
            });

            // Handle updated image
            if ($request->hasFile('imageFile')) {
                $imageUrl = 'https://res.cloudinary.com/ddhzxw6j8/image/upload/v1734267784/Mitra_Telkom_Property/Mitra%20Company%20One/Nomor_Kontrak_%5B1%5D/Nama_Pekerjaan_%5Bpekerjaan_1%5D/2024-12-15/D/d2bridjchyxoxky9nfe6.jpg';
                // $imageUrl = $this->uploadImage($request->file('imageFile'));
                $restaurant->imageUrl = $imageUrl;
            }

            $restaurant->lastUpdated = now();
            $restaurant->save();

            return response()->json($restaurant, 200);
        } catch (\Exception $error) {
            logger()->error($error);
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function getMyRestaurantOrders(Request $request)
    {
        try {
            $userId = $request->userId;
            $userId = explode('|', $userId)[1];
            $restaurant = Restaurant::where('user', $userId)->first();

            if (!$restaurant) {
                return response()->json(['message' => 'Restaurant not found'], 404);
            }

            $orders = Order::where('restaurant_id', $restaurant->id)
                ->with(['restaurant', 'user'])
                ->get();

            return response()->json($orders);
        } catch (\Exception $error) {
            logger()->error($error);
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function updateOrderStatus(Request $request, $orderId)
    {
        try {
            $status = $request->input('status');
            $order = Order::find($orderId);

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            $restaurant = Restaurant::find($order->restaurant_id);

            if ($restaurant->user_id != Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $order->status = $status;
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
