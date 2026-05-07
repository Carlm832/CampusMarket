<?php
/**
 * Optional Supabase safe-mode configuration helpers.
 * This does not replace the primary MySQL/PDO app database.
 */

function supabaseUrl(): string {
    return defined('SUPABASE_URL') ? trim((string) SUPABASE_URL) : '';
}

function supabaseAnonKey(): string {
    return defined('SUPABASE_ANON_KEY') ? trim((string) SUPABASE_ANON_KEY) : '';
}

function isSupabaseConfigured(): bool {
    return supabaseUrl() !== '' && supabaseAnonKey() !== '';
}
