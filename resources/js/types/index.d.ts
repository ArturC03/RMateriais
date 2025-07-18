import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    role: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Category {
    id: number;
    name: string;
    materials: Material[];
}

export interface Request {
    id: number;
    user: User;
    status: 'pendente' | 'aprovado' | 'rejeitado' | 'devolvido';
    request_items?: RequestItem[];
    requested_at: Date;
    approved_at?: Date | null;
    returned_at?: Date | null;
    isOverdue: boolean;

}
export interface RequestItem {
    id: number;
    request?: Request;
    material?: Material;
    quantity: number;
    requested_days: number;
    due_date: Date; // or Date, if you're working with actual Date objects
    isOverdue: boolean;
    returned: boolean;
}
export interface Material {
    id: number;
    name: string;
    description: string;
    quantity: number;
    max_days_per_request: number;
    category?: Category;
    requestItems?: RequestItem[];
    available_quantity: number;
    is_available: boolean;
    currently_borrowed_quantity: number;
}

export type BreadcrumbItemType = BreadcrumbItem;
