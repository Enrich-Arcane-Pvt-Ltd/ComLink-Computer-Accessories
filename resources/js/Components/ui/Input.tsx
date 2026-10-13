import React, { forwardRef } from 'react';
import { cn } from '../../utils/cn';
export interface InputProps extends
  React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
}
export const Input = forwardRef<HTMLInputElement, InputProps>(
  ({ className, label, error, id, ...props }, ref) => {
    const inputId = id || label?.toLowerCase().replace(/\s+/g, '-');
    return (
      <div className="w-full">
        {label &&
        <label
          htmlFor={inputId}
          className="block text-sm font-medium text-fg mb-1.5">
          
            {label}
          </label>
        }
        <input
          id={inputId}
          ref={ref}
          className={cn(
            'flex h-12 w-full border border-border bg-bg px-4 py-2 text-sm text-fg transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted focus-visible:outline-none focus-visible:border-primary focus-visible:ring-1 focus-visible:ring-primary disabled:cursor-not-allowed disabled:opacity-50',
            error &&
            'border-accent focus-visible:border-accent focus-visible:ring-accent',
            className
          )}
          {...props} />
        
        {error && <p className="mt-1.5 text-sm text-accent">{error}</p>}
      </div>);

  }
);
Input.displayName = 'Input';