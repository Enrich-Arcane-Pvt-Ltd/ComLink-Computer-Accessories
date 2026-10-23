import React, { useEffect, useState, createContext, useContext } from 'react';
import { toast } from 'sonner';
import { Product } from '../data/products';
interface WishlistContextType {
  items: Product[];
  toggleWishlist: (product: Product) => void;
  removeFromWishlist: (id: string) => void;
  clearWishlist: () => void;
  isInWishlist: (id: string) => boolean;
  count: number;
}
const WishlistContext = createContext<WishlistContextType | undefined>(
  undefined
);
export function WishlistProvider({ children }: {children: React.ReactNode;}) {
  const [items, setItems] = useState<Product[]>(() => {
    const saved = localStorage.getItem('milana-wishlist');
    return saved ? JSON.parse(saved) : [];
  });
  useEffect(() => {
    localStorage.setItem('milana-wishlist', JSON.stringify(items));
  }, [items]);
  const isInWishlist = (id: string) => items.some((p) => p.id === id);
  const toggleWishlist = (product: Product) => {
    setItems((prev) => {
      if (prev.some((p) => p.id === product.id)) {
        toast.info(`Removed ${product.name} from wishlist`);
        return prev.filter((p) => p.id !== product.id);
      }
      toast.success(`Added ${product.name} to wishlist`);
      return [...prev, product];
    });
  };
  const removeFromWishlist = (id: string) => {
    setItems((prev) => prev.filter((p) => p.id !== id));
  };
  const clearWishlist = () => {
    setItems([]);
    toast.info('Wishlist cleared');
  };
  return (
    <WishlistContext.Provider
      value={{
        items,
        toggleWishlist,
        removeFromWishlist,
        clearWishlist,
        isInWishlist,
        count: items.length
      }}>
      
      {children}
    </WishlistContext.Provider>);

}
export function useWishlist() {
  const context = useContext(WishlistContext);
  if (context === undefined) {
    throw new Error('useWishlist must be used within a WishlistProvider');
  }
  return context;
}