import React from 'react';
import { Link } from '@inertiajs/react';

const CONTACT = {
  email: 'info@comlink.lk',
  phone: '+94 77 123 4567',
};

const LINKS = [
  { label: 'Home', href: '/' },
  { label: 'Shop', href: '/shop' },
  { label: 'About Us', href: '/about' },
  { label: 'Contact Us', href: '/contact' },
];

function Divider() {
  return <span aria-hidden="true" className="h-3 w-px bg-primary-fg/30" />;
}

export function AnnouncementBar() {
  return (
    <div className="bg-primary text-primary-fg text-xs sm:text-sm font-medium tracking-wide">
      <div className="mx-auto flex max-w-7xl flex-col items-center justify-between gap-1 px-4 py-2 sm:flex-row sm:gap-4">
        {/* Left: email | contact no */}
        <div className="flex items-center gap-3">
          <a
            href={`mailto:${CONTACT.email}`}
            className="transition-colors hover:text-accent-300"
          >
            {CONTACT.email}
          </a>
          <Divider />
          <a
            href={`tel:${CONTACT.phone.replace(/\s/g, '')}`}
            className="transition-colors hover:text-accent-300"
          >
            {CONTACT.phone}
          </a>
        </div>

        {/* Right: Home | About Us | Contact Us */}
        <nav aria-label="Top links" className="flex items-center gap-3">
          {LINKS.map((link, i) => (
            <React.Fragment key={link.href}>
              {i > 0 && <Divider />}
              <Link
                href={link.href}
                className="transition-colors hover:text-accent-300"
              >
                {link.label}
              </Link>
            </React.Fragment>
          ))}
        </nav>
      </div>
    </div>
  );
}