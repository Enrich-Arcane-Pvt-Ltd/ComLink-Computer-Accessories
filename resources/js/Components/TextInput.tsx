import { cn } from "@/utils/cn"; // classNames merge කරන helper එක
import {
    forwardRef,
    InputHTMLAttributes,
    useEffect,
    useImperativeHandle,
    useRef,
    useState,
} from "react";
import { Eye, EyeOff } from "lucide-react";

export interface TextInputProps extends InputHTMLAttributes<HTMLInputElement> {
    label?: string;
    error?: string;
    isFocused?: boolean;
    showPasswordToggle?: boolean;
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
        showPasswordToggle = true,
        ...props
    }: TextInputProps,
    ref,
) {
    const localRef = useRef<HTMLInputElement>(null);
    const [showPassword, setShowPassword] = useState(false);

    // Laravel Breeze එකට අවශ්‍ය focus methods ටික පාලනය කිරීම
    useImperativeHandle(ref, () => ({
        focus: () => localRef.current?.focus(),
    }));

    useEffect(() => {
        if (isFocused) {
            localRef.current?.focus();
        }
    }, [isFocused]);

    const isPassword = type === "password" && showPasswordToggle;
    const computedType = isPassword ? (showPassword ? "text" : "password") : type;

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

            <div className="relative">
                <input
                    {...props}
                    id={inputId}
                    type={computedType}
                    ref={localRef}
                    className={cn(
                        "flex h-12 w-full border border-border bg-bg px-4 py-2 text-sm text-fg transition-colors duration-200 rounded-none",
                        "placeholder:text-muted/60 focus-visible:outline-none focus-visible:border-primary focus-visible:ring-1 focus-visible:ring-primary",
                        "disabled:cursor-not-allowed disabled:opacity-50",
                        isPassword && "pr-11",
                        error &&
                            "border-accent focus-visible:border-accent focus-visible:ring-accent",
                        className,
                    )}
                />

                {isPassword && (
                    <button
                        type="button"
                        onClick={() => setShowPassword((prev) => !prev)}
                        className="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-muted hover:text-fg transition-colors focus:outline-none cursor-pointer"
                        tabIndex={-1}
                        aria-label={showPassword ? "Hide password" : "Show password"}
                        title={showPassword ? "Hide password" : "Show password"}
                    >
                        {showPassword ? (
                            <EyeOff className="h-4 w-4" />
                        ) : (
                            <Eye className="h-4 w-4" />
                        )}
                    </button>
                )}
            </div>

            {error && (
                <p className="mt-1.5 text-xs text-accent font-medium">
                    {error}
                </p>
            )}
        </div>
    );
});
