import React from 'react';
import { Instagram } from 'lucide-react';
const instagramImages = [
'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&q=80&w=400',
'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&q=80&w=400',
'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&q=80&w=400',
'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&q=80&w=400',
'https://images.unsplash.com/photo-1469334031218-e382a71b716b?auto=format&fit=crop&q=80&w=400',
'https://images.unsplash.com/photo-1487222477894-8943e31ef7b2?auto=format&fit=crop&q=80&w=400',
'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&q=80&w=400',
'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&q=80&w=400'];

export function InstagramGrid() {
  return (
    <section className="py-12 bg-bg border-t border-border">
      <div className="container mx-auto px-4 md:px-6 mb-8">
        <div className="text-center">
          <div className="inline-flex items-center gap-3 mb-2">
            <Instagram className="h-6 w-6 text-primary" />
            <h3 className="font-display text-2xl font-bold text-fg">
              @MilanaStore
            </h3>
          </div>
          <p className="text-muted">
            Follow us on Instagram for daily inspiration
          </p>
        </div>
      </div>

      <div className="relative overflow-hidden">
        <div className="flex animate-marquee gap-4">
          {[...instagramImages, ...instagramImages].map((img, idx) =>
          <a
            key={idx}
            href="https://instagram.com"
            target="_blank"
            rel="noopener noreferrer"
            className="relative w-64 h-64 flex-shrink-0 group overflow-hidden">
            
              <img
              src={img}
              alt={`Instagram post ${idx + 1}`}
              className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
            
              <div className="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center">
                <Instagram className="h-8 w-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
              </div>
            </a>
          )}
        </div>
      </div>
    </section>);

}