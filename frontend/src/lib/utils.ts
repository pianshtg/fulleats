import { type ClassValue, clsx } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function formatCurrency(value: number): string {
  return new Intl.NumberFormat('de-DE', { 
    minimumFractionDigits: 2, 
    maximumFractionDigits: 2 
  }).format(value);
}

export function calculateTotalAmount(order: any) {
  let totalAmount = 0;

  // Map through cart items
  order.cartItems.forEach((cartItem: any) => {
    const menuItem = order.restaurant.menuItems.find(
      (menuItem: any) => menuItem.name === cartItem.name
    );

    // Multiply quantity by price and add to total
    if (menuItem) {
      totalAmount += parseInt(cartItem.quantity) * menuItem.price;
    }
  });
  const deliveryPrice = parseInt(String(order.restaurant.deliveryPrice))
  return totalAmount + deliveryPrice;
}