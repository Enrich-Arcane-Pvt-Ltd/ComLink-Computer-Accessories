import React from 'react';
import { cn } from '../../utils/cn';
interface BadgeProps extends React.HTMLAttributes<HTMLDivElement> {
  variant?: 'default' | 'accent' | 'success' | 'outline';
}
export function Badge({
  className,
  variant = 'default',
  children,
  ...props
}: BadgeProps) {
  const variants = {
    default: 'bg-primary text-primary-fg',
    accent: 'bg-accent text-accent-fg',
    success: 'bg-green-600 text-white',
    outline: 'border border-border text-fg'
  };
  return (
    <div
      className={cn(
        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold transition-colors',
        variants[variant],
        className
      )}
      {...props}>
      
      {children}
    </div>);

}