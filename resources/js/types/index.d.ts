export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    profile_photo_url?: string | null;
    initials?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User | null;
        roles?: string[];
        permissions?: string[];
        isSuperAdmin?: boolean;
    };
};
