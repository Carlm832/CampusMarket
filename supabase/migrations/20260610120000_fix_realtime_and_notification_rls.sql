-- Fix Realtime delivery + RLS for CampusMarket (local bigint user ids vs Supabase auth UUIDs)

CREATE OR REPLACE FUNCTION public.current_app_user_id()
RETURNS bigint
LANGUAGE sql
STABLE
SET search_path = public
AS $$
  SELECT NULLIF(trim(both from coalesce(auth.jwt() -> 'app_metadata' ->> 'user_id', '')), '')::bigint;
$$;

-- Notifications: own rows only (SELECT for Realtime, UPDATE for mark-read)
DROP POLICY IF EXISTS "notifications_all_own" ON public.notifications;

CREATE POLICY "notifications_select_own" ON public.notifications
FOR SELECT TO authenticated
USING (user_id = public.current_app_user_id());

CREATE POLICY "notifications_update_own" ON public.notifications
FOR UPDATE TO authenticated
USING (user_id = public.current_app_user_id())
WITH CHECK (user_id = public.current_app_user_id());

-- Messages: participants only
DROP POLICY IF EXISTS "messages_participants_select" ON public.messages;
DROP POLICY IF EXISTS "messages_participants_insert" ON public.messages;
DROP POLICY IF EXISTS "messages_participants_update" ON public.messages;

CREATE POLICY "messages_participants_select" ON public.messages
FOR SELECT TO authenticated
USING (
  sender_id = public.current_app_user_id()
  OR receiver_id = public.current_app_user_id()
);

CREATE POLICY "messages_participants_insert" ON public.messages
FOR INSERT TO authenticated
WITH CHECK (sender_id = public.current_app_user_id());

CREATE POLICY "messages_participants_update" ON public.messages
FOR UPDATE TO authenticated
USING (
  sender_id = public.current_app_user_id()
  OR receiver_id = public.current_app_user_id()
)
WITH CHECK (
  sender_id = public.current_app_user_id()
  OR receiver_id = public.current_app_user_id()
);

-- Enable Realtime on tables used for live badge updates
DO $$
BEGIN
  IF NOT EXISTS (
    SELECT 1 FROM pg_publication_tables
    WHERE pubname = 'supabase_realtime'
      AND schemaname = 'public'
      AND tablename = 'notifications'
  ) THEN
    ALTER PUBLICATION supabase_realtime ADD TABLE public.notifications;
  END IF;

  IF NOT EXISTS (
    SELECT 1 FROM pg_publication_tables
    WHERE pubname = 'supabase_realtime'
      AND schemaname = 'public'
      AND tablename = 'messages'
  ) THEN
    ALTER PUBLICATION supabase_realtime ADD TABLE public.messages;
  END IF;
END $$;
