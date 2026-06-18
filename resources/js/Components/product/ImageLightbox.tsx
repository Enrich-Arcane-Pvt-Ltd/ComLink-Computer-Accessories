import React, { useEffect } from 'react';
import { X, ChevronLeft, ChevronRight } from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';
interface ImageLightboxProps {
  images: string[];
  currentIndex: number;
  onClose: () => void;
  onNavigate: (index: number) => void;
}
export function ImageLightbox({
  images,
  currentIndex,
  onClose,
  onNavigate
}: ImageLightboxProps) {
  useEffect(() => {
    document.body.style.overflow = 'hidden';
    return () => {
      document.body.style.overflow = 'unset';
    };
  }, []);
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') onClose();
      if (e.key === 'ArrowLeft') onNavigate(Math.max(0, currentIndex - 1));
      if (e.key === 'ArrowRight')
      onNavigate(Math.min(images.length - 1, currentIndex + 1));
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [currentIndex, images.length, onClose, onNavigate]);
  return (
    <AnimatePresence>
      <motion.div
        initial={{
          opacity: 0
        }}
        animate={{
          opacity: 1
        }}
        exit={{
          opacity: 0
        }}
        className="fixed inset-0 z-50 bg-black/95 flex items-center justify-center"
        onClick={onClose}>
        
        <button
          onClick={onClose}
          className="absolute top-4 right-4 z-10 w-12 h-12 flex items-center justify-center bg-white/10 hover:bg-white/20 transition-colors"
          aria-label="Close lightbox">
          
          <X className="h-6 w-6 text-white" />
        </button>

        <div
          className="relative w-full h-full flex items-center justify-center p-4 md:p-12"
          onClick={(e) => e.stopPropagation()}>
          
          <motion.img
            key={currentIndex}
            initial={{
              opacity: 0,
              scale: 0.9
            }}
            animate={{
              opacity: 1,
              scale: 1
            }}
            exit={{
              opacity: 0,
              scale: 0.9
            }}
            transition={{
              duration: 0.3
            }}
            src={images[currentIndex]}
            alt={`Product image ${currentIndex + 1}`}
            className="max-w-full max-h-full object-contain" />
          

          {currentIndex > 0 &&
          <button
            onClick={() => onNavigate(currentIndex - 1)}
            className="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center bg-white/10 hover:bg-white/20 transition-colors"
            aria-label="Previous image">
            
              <ChevronLeft className="h-6 w-6 text-white" />
            </button>
          }

          {currentIndex < images.length - 1 &&
          <button
            onClick={() => onNavigate(currentIndex + 1)}
            className="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center bg-white/10 hover:bg-white/20 transition-colors"
            aria-label="Next image">
            
              <ChevronRight className="h-6 w-6 text-white" />
            </button>
          }

          <div className="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
            {images.map((_, idx) =>
            <button
              key={idx}
              onClick={() => onNavigate(idx)}
              className={`h-2 transition-all ${idx === currentIndex ? 'w-8 bg-white' : 'w-2 bg-white/50 hover:bg-white/70'}`}
              aria-label={`Go to image ${idx + 1}`} />

            )}
          </div>
        </div>
      </motion.div>
    </AnimatePresence>);

}