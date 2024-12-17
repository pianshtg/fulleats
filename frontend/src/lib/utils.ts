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