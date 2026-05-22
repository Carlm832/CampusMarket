-- Migration: Create product_views and promotion_payments tables

-- 1. Create product_views table
CREATE TABLE IF NOT EXISTS public.product_views (
    id SERIAL PRIMARY KEY,
    product_id INT NOT NULL REFERENCES public.products(id) ON DELETE CASCADE,
    user_id INT NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
    viewed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Enable RLS on product_views
ALTER TABLE public.product_views ENABLE ROW LEVEL SECURITY;

-- 2. Create promotion_payments table
CREATE TABLE IF NOT EXISTS public.promotion_payments (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
    product_id INT NULL REFERENCES public.products(id) ON DELETE SET NULL,
    payment_type VARCHAR(50) NOT NULL CHECK (payment_type IN ('promotion', 'donation')),
    payment_method VARCHAR(50) NOT NULL DEFAULT 'other' CHECK (payment_method IN ('cash', 'venmo', 'zelle', 'other')),
    amount DECIMAL(10,2) NOT NULL,
    transaction_ref VARCHAR(255) NULL,
    notes TEXT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
    admin_note VARCHAR(255) NULL,
    approved_at TIMESTAMP NULL,
    approved_by INT NULL REFERENCES public.users(id) ON DELETE SET NULL,
    consumed_at TIMESTAMP NULL,
    consumed_for VARCHAR(50) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_promo_user_status ON public.promotion_payments (user_id, status);
CREATE INDEX IF NOT EXISTS idx_promo_product_status ON public.promotion_payments (product_id, status);

-- Enable RLS on promotion_payments
ALTER TABLE public.promotion_payments ENABLE ROW LEVEL SECURITY;
