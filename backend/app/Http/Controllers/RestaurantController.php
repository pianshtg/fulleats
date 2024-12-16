<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Restaurant;

class RestaurantController extends Controller
{
    public function getRestaurant($restaurantId)
    {
        try {
            $restaurant = Restaurant::find($restaurantId);

            if (!$restaurant) {
                return response()->json(['message' => 'Restaurant not found'], 404);
            }

            return response()->json($restaurant);

        } catch (\Exception $error) {
            logger()->error($error);
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    public function searchRestaurant(Request $request, $city)
    {
        try {
            $searchQuery = $request->query('searchQuery', '');
            $selectedCuisines = $request->query('selectedCuisines', '');
            $sortOption = $request->query('sortOption', 'lastUpdated');
            $page = (int) $request->query('page', 1);

            $query = Restaurant::query();

            // Filter by city
            $query->where('city', 'LIKE', "%{$city}%");
            $cityCheck = $query->count();
            if ($cityCheck === 0) {
                return response()->json([
                    'data' => [],
                    'pagination' => [
                        'total' => 0,
                        'page' => 1,
                        'pages' => 1,
                    ],
                ], 404);
            }

            // Filter by cuisines
            if (!empty($selectedCuisines)) {
                $cuisinesArray = array_map('trim', explode(',', $selectedCuisines));
                $query->where(function ($subQuery) use ($cuisinesArray) {
                    foreach ($cuisinesArray as $cuisine) {
                        $subQuery->orWhere('cuisines', 'LIKE', "%{$cuisine}%");
                    }
                });
            }

            // Filter by search query
            if (!empty($searchQuery)) {
                $query->where(function ($subQuery) use ($searchQuery) {
                    $subQuery->orWhere('restaurantName', 'LIKE', "%{$searchQuery}%")
                        ->orWhere('cuisines', 'LIKE', "%{$searchQuery}%");
                });
            }

            // Pagination
            $pageSize = 10;
            $restaurants = $query
                ->orderBy($sortOption, 'asc')
                ->skip(($page - 1) * $pageSize)
                ->take($pageSize)
                ->get();

            $total = $query->count();

            $response = [
                'data' => $restaurants,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'pages' => ceil($total / $pageSize),
                ],
            ];

            return response()->json($response);

        } catch (\Exception $error) {
            logger()->error($error);
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }
}
