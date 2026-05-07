(function () {
  var urlMeta = document.querySelector('meta[name="supabase-url"]');
  var keyMeta = document.querySelector('meta[name="supabase-anon-key"]');

  if (!urlMeta || !keyMeta || !window.supabase || !window.supabase.createClient) {
    return;
  }

  var url = (urlMeta.content || "").trim();
  var anonKey = (keyMeta.content || "").trim();
  if (!url || !anonKey) {
    return;
  }

  window.CampusMarketSupabase = window.supabase.createClient(url, anonKey, {
    auth: {
      persistSession: true,
      autoRefreshToken: true
    }
  });
})();
