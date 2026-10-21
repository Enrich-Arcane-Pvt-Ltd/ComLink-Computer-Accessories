import React, { useEffect, useState, createContext, useContext } from 'react';
import { toast } from 'sonner';
export interface CartItem {
  id: string;
  productId: string;
  variantId?: string | number;
  name: string;
  price: number;
  image: string;
  quantity: number;
  size?: string;
  color?: string;
}
interface CartContextType {
  items: CartItem[];
  addToCart: (item: Omit<CartItem, 'id'>) => void;
  removeFromCart: (id: string) => void;
  updateQuantity: (id: string, quantity: number) => void;
  clearCart: () => void;
  totalItems: number;
  totalPrice: number;
}
const CartContext = createContext<CartContextType | undefined>(undefined);
export function CartProvider({ children }: {children: React.ReactNode;}) {
  const [items, setItems] = useState<CartItem[]>(() => {
    const saved = localStorage.getItem('milana-cart');
    return saved ? JSON.parse(saved) : [];
  });
  useEffect(() => {
    localStorage.setItem('milana-cart', JSON.stringify(items));
  }, [items]);
  const addToCart = (newItem: Omit<CartItem, 'id'>) => {
    setItems((prev) => {
      const existingItemIndex = prev.findIndex(
        (item) =>
        item.productId === newItem.productId &&
        item.variantId === newItem.variantId &&
        item.size === newItem.size &&
        item.color === newItem.color
      );
      if (existingItemIndex > -1) {
        const updated = [...prev];
        updated[existingItemIndex].quantity += newItem.quantity;
        toast.success(`Updated quantity for ${newItem.name}`);
        return updated;
      }
      toast.success(`Added ${newItem.name} to cart`);
      return [
      ...prev,
      {
        ...newItem,
        id: Math.random().toString(36).substr(2, 9)
      }];

    });
  };
  const removeFromCart = (id: string) => {
    setItems((prev) => prev.filter((item) => item.id !== id));
    toast.info('Item removed from cart');
  };
  const updateQuantity = (id: string, quantity: number) => {
    if (quantity < 1) return;
    setItems((prev) =>
    prev.map((item) =>
    item.id === id ?
    {
      ...item,
      quantity
    } :
    item
    )
    );
  };
  const clearCart = () => {
    setItems([]);
  };
  const totalItems = items.reduce((sum, item) => sum + item.quantity, 0);
  const totalPrice = items.reduce(
    (sum, item) => sum + item.price * item.quantity,
    0
  );
  return (
    <CartContext.Provider
      value={{
        items,
        addToCart,
        removeFromCart,
        updateQuantity,
        clearCart,
        totalItems,
        totalPrice
      }}>
      
      {children}
    </CartContext.Provider>);

}
export function useCart() {
  const context = useContext(CartContext);
  if (context === undefined) {
    throw new Error('useCart must be used within a CartProvider');
  }
  return context;
}