export interface User {
    id: number;
    username: string;
    email: string;
    member_group_id: number;
    created_at: string;
    updated_at: string;
    group?: MemberGroup;
}

export interface MemberGroup {
    id: number;
    group_name: string;
}

export interface Stream {
    id: number;
    stream_display_name: string;
    stream_source: string;
    type: 'live' | 'created' | 'radio';
    status: number;
    category_id: number | null;
    epg_id: number | null;
    server_id: number | null;
    pid: number | null;
    added: string;
    category?: StreamCategory;
    server?: Server;
}

export interface StreamCategory {
    id: number;
    category_name: string;
    category_type: string;
    parent_id: number | null;
    cat_order: number;
}

export interface Movie {
    id: number;
    stream_display_name: string;
    stream_source: string;
    tmdb_id: string | null;
    cover: string | null;
    plot: string | null;
    cast: string | null;
    director: string | null;
    genre: string | null;
    rating: string | null;
    category_id: number | null;
    added: string;
    category?: StreamCategory;
}

export interface Series {
    id: number;
    title: string;
    cover: string | null;
    plot: string | null;
    cast: string | null;
    genre: string | null;
    tmdb_id: string | null;
    category_id: number | null;
    episodes?: Episode[];
    category?: StreamCategory;
}

export interface Episode {
    id: number;
    series_id: number;
    season_number: number;
    episode_number: number;
    stream_source: string;
    title: string | null;
    cover: string | null;
    plot: string | null;
    added: string;
}

export interface Line {
    id: number;
    username: string;
    password: string;
    exp_date: string | null;
    max_connections: number;
    is_trial: boolean;
    admin_enabled: boolean;
    bouquet: string;
    created_by: number | null;
    active_connections: number;
    added: string;
    packages?: Package[];
}

export interface Package {
    id: number;
    package_name: string;
    is_trial: boolean;
    is_official: boolean;
    is_addon: boolean;
}

export interface Server {
    id: number;
    server_name: string;
    server_ip: string;
    domain_name: string | null;
    http_port: number;
    rtmp_port: number;
    status: number;
    total_clients: number;
    cpu_usage: number | null;
    mem_usage: number | null;
    disk_usage: number | null;
    uptime: string | null;
}

export interface Bouquet {
    id: number;
    bouquet_name: string;
    bouquet_channels: string;
    bouquet_movies: string;
    bouquet_series: string;
    bouquet_radios: string;
}

export interface Epg {
    id: number;
    epg_name: string;
    epg_url: string;
}

export interface Setting {
    id: number;
    key: string;
    value: string;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
}

export interface DashboardStats {
    total_streams: number;
    active_streams: number;
    total_connections: number;
    total_lines: number;
    active_lines: number;
    total_movies: number;
    total_series: number;
    total_users: number;
    servers: ServerStat[];
    stream_activity: ActivityPoint[];
    connection_history: ActivityPoint[];
}

export interface ServerStat {
    id: number;
    name: string;
    status: number;
    cpu: number;
    memory: number;
    disk: number;
    clients: number;
}

export interface ActivityPoint {
    label: string;
    value: number;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
    flash: {
        success?: string;
        error?: string;
    };
};
