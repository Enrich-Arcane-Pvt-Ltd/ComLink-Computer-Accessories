import { cn } from "@/utils/cn"; // classNames merge කරන helper එක
import {
    forwardRef,
    InputHTMLAttributes,
    useEffect,
    useImperativeHandle,
    useRef,
} from "react";

export interface TextInputProps extends InputHTMLAttributes<HTMLInputElement> {
    label?: string;
    error?: string;
    isFocused?: boolean;
}

export default forwardRef(function TextInput(
    {
        type = "text",
        className = "",
        label,
        error,
        id,
        isFocused = false,
        required,
        ...props
    }: TextInputProps,
    ref,
) {
    const localRef = useRef<HTMLInputElement>(null);

    // Laravel Breeze එකට අවශ්‍ය focus methods ටික පාලනය කිරීම
    useImperativeHandle(ref, () => ({
        focus: () => localRef.current?.focus(),
    }));

    useEffect(() => {
        if (isFocused) {
            localRef.current?.focus();
        }
    }, [isFocused]);

    // Label එක සහ Error එක සඳහා Id එකක් ස්වයංක්‍රීයව සකසා ගැනීම
    const inputId = id || label?.toLowerCase().replace(/\s+/g, "-");

    return (
        <div className="w-full font-sans">
            {label && (
                <label
                    htmlFor={inputId}
                    className="block text-sm font-medium text-fg mb-1.5"
                >
                    {label}
                    {required && <span className="text-red-500 ml-1">*</span>}
                </label>
            )}

            <input
                {...props}
                id={inputId}
                type={type}
                ref={localRef}
                className={cn(
                    "flex h-12 w-full border border-border bg-bg px-4 py-2 text-sm text-fg transition-colors duration-200 rounded-none",
                    "placeholder:text-muted/60 focus-visible:outline-none focus-visible:border-primary focus-visible:ring-1 focus-visible:ring-primary",
                    "disabled:cursor-not-allowed disabled:opacity-50",

                    error &&
                        "border-accent focus-visible:border-accent focus-visible:ring-accent",
                    className,
                )}
            />

            {error && (
                <p className="mt-1.5 text-xs text-accent font-medium">
                    {error}
                </p>
            )}
        </div>
    );
});
