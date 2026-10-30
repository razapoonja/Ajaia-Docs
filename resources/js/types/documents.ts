export type ShareRole = 'viewer' | 'editor';

export type OwnedDocumentSummary = {
    id: number;
    title: string;
    updated_at: string;
    shares_count: number;
};

export type SharedDocumentSummary = {
    id: number;
    title: string;
    updated_at: string;
    owner: string;
    role: ShareRole;
};

export type DocumentDetail = {
    id: number;
    title: string;
    content: string | null;
    updated_at: string;
    owner: { id: number; name: string };
};

export type DocumentPermissions = {
    update: boolean;
    share: boolean;
    delete: boolean;
};

export type DocumentShare = {
    id: number;
    role: ShareRole;
    user: { id: number; name: string; email: string };
};
