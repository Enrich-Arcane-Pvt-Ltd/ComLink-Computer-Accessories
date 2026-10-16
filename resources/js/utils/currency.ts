export const CURRENCY_SYMBOL: string =
    import.meta.env.VITE_CURRENCY_SYMBOL || "Rs. ";

export const CURRENCY_CODE: string =
    import.meta.env.VITE_CURRENCY_CODE || "LKR";

/**
 * Formats a numeric price into a currency string (e.g. "Rs. 15,000.00")
 *
 * @param amount - The numeric price to format
 * @param decimals - Number of decimal places (default: 2)
 * @returns Formatted currency string
 */
export function formatPrice(
    amount: number | string | null | undefined,
    decimals: number = 2,
): string {
    const val = Number(amount || 0);
    return `${CURRENCY_SYMBOL}${val.toLocaleString("en-US", {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    })}`;
}
