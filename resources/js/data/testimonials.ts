export interface Testimonial {
  id: string;
  name: string;
  location: string;
  avatar: string;
  rating: number;
  quote: string;
}

export const testimonials: Testimonial[] = [
{
  id: '1',
  name: 'David Kim',
  location: 'Los Angeles, CA',
  avatar:
  'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&q=80&w=200',
  rating: 5,
  quote:
  "I've been a loyal customer of MILANA for years, and I can confidently say that their products have truly transformed my personal style. The attention to detail and quality of their denim is unmatched."
},
{
  id: '2',
  name: 'Michael Chang',
  location: 'New York, NY',
  avatar:
  'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=200',
  rating: 5,
  quote:
  "The fit of their t-shirts is perfect. Not too tight, not too loose. I've bought them in almost every color now. Customer service is also incredibly responsive."
},
{
  id: '3',
  name: 'James Wilson',
  location: 'Chicago, IL',
  avatar:
  'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&q=80&w=200',
  rating: 4,
  quote:
  'Great streetwear essentials. The heavyweight hoodies are my go-to for the winter. Shipping was fast and the packaging felt very premium.'
},
{
  id: '4',
  name: 'Kevin Nguyen',
  location: 'Seattle, WA',
  avatar:
  'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=200',
  rating: 5,
  quote:
  "Finally found a brand that understands modern men's fashion without being overly complicated. Clean lines, great fabrics, and versatile pieces."
}];