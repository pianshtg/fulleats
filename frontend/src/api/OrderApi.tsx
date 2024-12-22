import { Order } from "@/types";
import { useAuth0 } from "@auth0/auth0-react";
import { useMutation, useQuery } from "react-query";
import { useNavigate } from "react-router-dom";
import { toast } from "sonner";

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

export function useGetMyOrders() {
  const { getAccessTokenSilently } = useAuth0();

  async function getMyOrdersRequest(): Promise<Order[]> {
    const accessToken = await getAccessTokenSilently();

    const response = await fetch(`${API_BASE_URL}/api/order`, {
      headers: {
        Authorization: `Bearer ${accessToken}`,
      },
    });

    if (!response.ok) {
      throw new Error("Failed to get orders");
    }

    // Parse the JSON response
    const orders = await response.json();

    // Convert the `restaurant` field to JSON object if it's a string
    return orders.map((order: Order) => {
      if (typeof order.restaurant === "string") {
        order.restaurant = JSON.parse(order.restaurant);
      }
      // console.log(order) //Debug.
      return order;
    });
  }

  const { data: orders, isLoading } = useQuery(
    "fetchMyOrders",
    getMyOrdersRequest,
    { refetchInterval: 5000 }
  );

  return { orders, isLoading };
}

type CheckoutSessionRequest = {
  cartItems: {
    menuItemId: string;
    name: string;
    quantity: string;
  }[];
  deliveryDetails: {
    email: string;
    name: string;
    addressLine1: string;
    city: string;
  };
  restaurantId: string;
};

export function useCreateCheckoutSession () {
  const { getAccessTokenSilently } = useAuth0();
  const navigate = useNavigate()

  async function createCheckoutSessionRequest (
    checkoutSessionRequest: CheckoutSessionRequest
  ) {
    const accessToken = await getAccessTokenSilently();

    const response = await fetch(
      `${API_BASE_URL}/api/order/checkout/create-checkout-session`,
      {
        method: "POST",
        headers: {
          Authorization: `Bearer ${accessToken}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify(checkoutSessionRequest),
      }
    );

    if (!response.ok) {
      // const data = await response.json()
      // console.log(data.message);
      throw new Error("Unable to create checkout session");
    }
    return response.json();
  };

  const {
    mutateAsync: createCheckoutSession,
    isLoading,
    isSuccess,
    error,
    reset,
  } = useMutation(createCheckoutSessionRequest);

  if (error) {
    toast.error(error.toString());
    reset();
  }
  
  if (isSuccess) {
    navigate('/order-status')
  }

  return { createCheckoutSession, isLoading };
};
