import { cn } from "@/utils/cn";
import {
    forwardRef,
    TextareaHTMLAttributes,
    useEffect,
    useImperativeHandle,
    useRef,
} from "react";

export interface TextAreaProps extends TextareaHTMLAttributes<HTMLTextAreaElement> {
    label?: string;
    error?: string;
    isFocused?: boolean;
}

export default forwardRef(function TextArea(
    {
        className = "",
        label,
        error,
        id,
        isFocused = false,
        required, // required prop එක destruct කරගන්නවා
        ...props
    }: TextAreaProps,
    ref,
) {
    const localRef = useRef<HTMLTextAreaElement>(null);

    useImperativeHandle(ref, () => ({
        focus: () => localRef.current?.focus(),
    }));

    useEffect(() => {
        if (isFocused) {
            localRef.current?.focus();
        }
    }, [isFocused]);

    const inputId = id || label?.toLowerCase().replace(/\s+/g, "-");

    return (
        <div className="w-full font-sans">
            {label && (
                <label
                    htmlFor={inputId}
                    className="block text-sm font-medium text-fg mb-1.5"
                >
                    {label}
                    {/* required නම් රතු පාට තරුව පෙන්වීම */}
                    {required && <span className="text-red-500 ml-1">*</span>}
                </label>
            )}

            <textarea
                {...props}
                id={inputId}
                ref={localRef}
                className={cn(
                    "flex w-full border border-border bg-bg px-4 py-3 text-sm text-fg transition-colors duration-200 rounded-none min-h-[150px] resize-y",
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
