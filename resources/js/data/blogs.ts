export interface BlogPost {
  id: string;
  slug: string;
  title: string;
  excerpt: string;
  content: string;
  image: string;
  author: string;
  date: string;
  category: string;
  tags: string[];
}

export const blogs: BlogPost[] = [
{
  id: '1',
  slug: 'how-to-style-denim-jacket',
  title: 'How to Style a Denim Jacket for Any Occasion',
  excerpt:
  'Discover the versatility of the classic denim jacket and learn how to incorporate it into your everyday wardrobe.',
  content: `
      <p>The denim jacket is a timeless wardrobe staple that transcends seasons and trends. Whether you're aiming for a casual weekend look or a smart-casual office ensemble, a well-fitted denim jacket can be your go-to piece.</p>
      
      <h3>1. The Classic Double Denim</h3>
      <p>Don't be afraid of the "Canadian Tuxedo." The key to pulling off double denim is contrast. Pair a light wash denim jacket with dark indigo or black jeans. This creates a visual break and adds depth to your outfit.</p>
      
      <h3>2. Layered Over a Hoodie</h3>
      <p>For a quintessential streetwear look, layer your denim jacket over a comfortable hoodie. This combination is perfect for transitional weather and adds a relaxed, urban edge to your style. Stick to neutral hoodie colors like grey, black, or olive for maximum versatility.</p>
      
      <h3>3. Smart-Casual with Chinos</h3>
      <p>Elevate your denim jacket by pairing it with tailored chinos and a crisp white button-down shirt. This high-low mix is ideal for casual Fridays or weekend brunches. Finish the look with clean white sneakers or leather loafers.</p>
      
      <p>Remember, the fit is crucial. A denim jacket should sit comfortably on your shoulders and end right at your waist. Don't be afraid to experiment and make it your own!</p>
    `,
  image:
  'https://images.unsplash.com/photo-1495105787522-5334e3ffa0ef?auto=format&fit=crop&q=80&w=1000',
  author: 'Alex Chen',
  date: 'Oct 12, 2026',
  category: 'Style Guides',
  tags: ['Denim', 'Streetwear', 'Menswear']
},
{
  id: '2',
  slug: 'essential-tshirts-every-man-needs',
  title: '5 Essential T-Shirts Every Man Needs',
  excerpt:
  'Build a solid foundation for your wardrobe with these five must-have t-shirt styles.',
  content: `
      <p>A great wardrobe starts with the basics, and nothing is more basic—or essential—than a good t-shirt. Here are the five styles you need to build a versatile foundation.</p>
      
      <h3>1. The Classic White Crew Neck</h3>
      <p>The undisputed king of t-shirts. A crisp, well-fitting white crew neck can be worn with literally anything. Look for medium-weight cotton that isn't transparent.</p>
      
      <h3>2. The Black V-Neck</h3>
      <p>Slightly dressier than a crew neck, a black v-neck is perfect for evening outings or layering under a blazer. It draws the eye down and can create a slimming effect.</p>
      
      <h3>3. The Striped Breton</h3>
      <p>Add some Parisian flair to your casual wear. The Breton stripe adds visual interest without being overly loud. Pair it with dark denim or tailored shorts.</p>
      
      <h3>4. The Heavyweight Pocket Tee</h3>
      <p>For days when you want a bit more structure, a heavyweight pocket tee is ideal. It holds its shape well and the pocket adds a subtle utilitarian detail.</p>
      
      <h3>5. The Graphic Vintage Tee</h3>
      <p>Show some personality with a carefully selected graphic tee. Whether it's a vintage band shirt or a subtle logo, it's a great conversation starter.</p>
    `,
  image:
  'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&q=80&w=1000',
  author: 'Marcus Johnson',
  date: 'Sep 28, 2026',
  category: 'Wardrobe Essentials',
  tags: ['T-Shirts', 'Basics', 'Style']
},
{
  id: '3',
  slug: 'sustainable-fashion-milana',
  title: 'Our Commitment to Sustainable Fashion',
  excerpt:
  'Learn about the steps MILANA is taking to reduce our environmental impact and promote ethical manufacturing.',
  content: `
      <p>At MILANA, we believe that looking good shouldn't come at the expense of the planet. We are committed to making our production processes more sustainable and transparent.</p>
      
      <h3>Ethical Sourcing</h3>
      <p>We work closely with our suppliers to ensure fair wages and safe working conditions for all garment workers. We regularly audit our factories to maintain high ethical standards.</p>
      
      <h3>Eco-Friendly Materials</h3>
      <p>We are steadily increasing our use of organic cotton, recycled polyester, and innovative sustainable fabrics like Tencel. Our goal is to have 80% of our collection made from sustainable materials by 2028.</p>
      
      <h3>Reducing Waste</h3>
      <p>From minimizing fabric offcuts during production to using biodegradable packaging for our shipments, we are looking at every step of our supply chain to reduce waste.</p>
      
      <p>Sustainability is a journey, not a destination. We invite you to join us as we continue to learn, adapt, and improve our practices for a better future.</p>
    `,
  image:
  'https://images.unsplash.com/photo-1581044777550-4cfa60707c03?auto=format&fit=crop&q=80&w=1000',
  author: 'Sarah Lin',
  date: 'Sep 15, 2026',
  category: 'Inside MILANA',
  tags: ['Sustainability', 'Brand Story', 'Ethics']
}];


export const blogCategories = [
{ name: 'Style Guides', count: 12 },
{ name: 'Wardrobe Essentials', count: 8 },
{ name: 'Inside MILANA', count: 5 },
{ name: 'Trend Reports', count: 9 }];