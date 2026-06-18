import { Slot } from "@radix-ui/react-slot";
import React, { forwardRef } from "react";
import { cn } from "../../utils/cn";

interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
    variant?: "primary" | "accent" | "outline" | "ghost" | "secondary";
    asChild?: boolean;
    size?: "sm" | "md" | "lg" | "icon";
    fullWidth?: boolean;
}
export const Button = forwardRef<HTMLButtonElement, ButtonProps>(
    (
        {
            className,
            variant = "primary",
            size = "md",
            fullWidth,
            asChild = false,
            children,
            ...props
        },
        ref,
    ) => {
        const Component = asChild ? Slot : "button";

        const baseStyles =
            "inline-flex items-center justify-center font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none rounded-none";
        const variants = {
            primary:
                "bg-primary text-primary-fg hover:bg-primary/90 focus:ring-primary",
            accent: "bg-accent text-accent-fg hover:bg-accent/90 focus:ring-accent",
            secondary: "bg-fg text-bg hover:bg-fg/90 focus:ring-fg",
            outline:
                "border-2 border-primary text-primary hover:bg-primary hover:text-primary-fg focus:ring-primary",
            ghost: "text-fg hover:bg-muted/10 focus:ring-muted",
        };
        const sizes = {
            sm: "h-9 px-4 text-sm",
            md: "h-11 px-8 text-base",
            lg: "h-14 px-10 text-lg",
            icon: "h-10 w-10",
        };
        return (
            <Component
                ref={ref}
                className={cn(
                    baseStyles,
                    variants[variant],
                    sizes[size],
                    fullWidth && "w-full",
                    className,
                )}
                {...props}
            >
                {children}
            </Component>
        );
    },
);
Button.displayName = "Button";
