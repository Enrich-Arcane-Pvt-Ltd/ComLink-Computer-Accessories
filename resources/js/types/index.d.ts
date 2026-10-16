import { Config } from "ziggy-js";

export interface User {
    id: number;
    customer_id?: string;
    first_name?: string;
    last_name?: string;
    name?: string;
    email: string;
    phone?: string;
    secondary_phone?: string;
    avatar_url?: string;
    role?: string;
    status?: string;
    email_verified_at?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    ziggy: Config & { location: string };
    flash: {
        error: any;
        success: any;
        message: string;
        type: string;
    };
};
