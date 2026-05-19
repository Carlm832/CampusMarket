-- Migration: Add student_id column to users table safely if not exists
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_schema = 'public' 
          AND table_name = 'users' 
          AND column_name = 'student_id'
    ) THEN
        ALTER TABLE public.users ADD COLUMN student_id VARCHAR(20) NULL;
    END IF;
END $$;
