(function () {
  // Read Supabase config from window.__env (injected by PHP), NOT from meta tags.
  var env = window.__env || {};
  var url = (env.SUPABASE_URL || "").trim();
  var anonKey = (env.SUPABASE_ANON_KEY || "").trim();

  if (!url || !anonKey || !window.supabase || !window.supabase.createClient) {
    return;
  }

  window.CampusMarketSupabase = window.supabase.createClient(url, anonKey, {
    auth: {
      persistSession: true,
      autoRefreshToken: true
    }
  });
})();
