-- Enable Row Level Security across application tables

alter table users enable row level security;
alter table categories enable row level security;
alter table tags enable row level security;
alter table products enable row level security;
alter table product_images enable row level security;
alter table product_tags enable row level security;
alter table wishlists enable row level security;
alter table orders enable row level security;
alter table transactions enable row level security;
alter table messages enable row level security;
alter table ratings enable row level security;
alter table reports enable row level security;
alter table notifications enable row level security;
alter table email_verifications enable row level security;

-- Public read tables
create policy "categories_read_all" on categories
for select to anon, authenticated
using (true);

create policy "tags_read_all" on tags
for select to anon, authenticated
using (true);

create policy "products_read_all" on products
for select to anon, authenticated
using (true);

create policy "product_images_read_all" on product_images
for select to anon, authenticated
using (true);

create policy "product_tags_read_all" on product_tags
for select to anon, authenticated
using (true);

-- Users: only own profile row
create policy "users_select_own" on users
for select to authenticated
using (id::text = auth.uid()::text);

create policy "users_update_own" on users
for update to authenticated
using (id::text = auth.uid()::text)
with check (id::text = auth.uid()::text);

-- Products: owner writes
create policy "products_insert_own" on products
for insert to authenticated
with check (user_id::text = auth.uid()::text);

create policy "products_update_own" on products
for update to authenticated
using (user_id::text = auth.uid()::text)
with check (user_id::text = auth.uid()::text);

create policy "products_delete_own" on products
for delete to authenticated
using (user_id::text = auth.uid()::text);

-- Product images/tags: tied to owned product
create policy "product_images_write_owner_product" on product_images
for all to authenticated
using (
  exists (
    select 1 from products p
    where p.id = product_images.product_id
      and p.user_id::text = auth.uid()::text
  )
)
with check (
  exists (
    select 1 from products p
    where p.id = product_images.product_id
      and p.user_id::text = auth.uid()::text
  )
);

create policy "product_tags_write_owner_product" on product_tags
for all to authenticated
using (
  exists (
    select 1 from products p
    where p.id = product_tags.product_id
      and p.user_id::text = auth.uid()::text
  )
)
with check (
  exists (
    select 1 from products p
    where p.id = product_tags.product_id
      and p.user_id::text = auth.uid()::text
  )
);

-- Wishlists: own rows only
create policy "wishlists_all_own" on wishlists
for all to authenticated
using (user_id::text = auth.uid()::text)
with check (user_id::text = auth.uid()::text);

-- Orders: buyer or product owner (seller)
create policy "orders_select_buyer_or_seller" on orders
for select to authenticated
using (
  buyer_id::text = auth.uid()::text
  or exists (
    select 1 from products p
    where p.id = orders.product_id and p.user_id::text = auth.uid()::text
  )
);

create policy "orders_insert_buyer" on orders
for insert to authenticated
with check (buyer_id::text = auth.uid()::text);

create policy "orders_update_buyer_or_seller" on orders
for update to authenticated
using (
  buyer_id::text = auth.uid()::text
  or exists (
    select 1 from products p
    where p.id = orders.product_id and p.user_id::text = auth.uid()::text
  )
)
with check (
  buyer_id::text = auth.uid()::text
  or exists (
    select 1 from products p
    where p.id = orders.product_id and p.user_id::text = auth.uid()::text
  )
);

-- Transactions: visible/writable by buyer or seller on related order
create policy "transactions_select_related_parties" on transactions
for select to authenticated
using (
  exists (
    select 1
    from orders o
    join products p on p.id = o.product_id
    where o.id = transactions.order_id
      and (o.buyer_id::text = auth.uid()::text or p.user_id::text = auth.uid()::text)
  )
);

create policy "transactions_write_related_parties" on transactions
for all to authenticated
using (
  exists (
    select 1
    from orders o
    join products p on p.id = o.product_id
    where o.id = transactions.order_id
      and (o.buyer_id::text = auth.uid()::text or p.user_id::text = auth.uid()::text)
  )
)
with check (
  exists (
    select 1
    from orders o
    join products p on p.id = o.product_id
    where o.id = transactions.order_id
      and (o.buyer_id::text = auth.uid()::text or p.user_id::text = auth.uid()::text)
  )
);

-- Messages: participants only
create policy "messages_participants_select" on messages
for select to authenticated
using (sender_id::text = auth.uid()::text or receiver_id::text = auth.uid()::text);

create policy "messages_participants_insert" on messages
for insert to authenticated
with check (sender_id::text = auth.uid()::text);

create policy "messages_participants_update" on messages
for update to authenticated
using (sender_id::text = auth.uid()::text or receiver_id::text = auth.uid()::text)
with check (sender_id::text = auth.uid()::text or receiver_id::text = auth.uid()::text);

-- Ratings: reviewer writes own rows, seller/reviewer can read
create policy "ratings_select_related" on ratings
for select to authenticated
using (reviewer_id::text = auth.uid()::text or seller_id::text = auth.uid()::text);

create policy "ratings_insert_reviewer" on ratings
for insert to authenticated
with check (reviewer_id::text = auth.uid()::text);

create policy "ratings_update_reviewer" on ratings
for update to authenticated
using (reviewer_id::text = auth.uid()::text)
with check (reviewer_id::text = auth.uid()::text);

-- Reports: reporter writes/reads own
create policy "reports_all_own" on reports
for all to authenticated
using (reporter_id::text = auth.uid()::text)
with check (reporter_id::text = auth.uid()::text);

-- Notifications: own only
create policy "notifications_all_own" on notifications
for all to authenticated
using (user_id::text = auth.uid()::text)
with check (user_id::text = auth.uid()::text);

-- Email verification rows: own only
create policy "email_verifications_all_own" on email_verifications
for all to authenticated
using (user_id::text = auth.uid()::text)
with check (user_id::text = auth.uid()::text);

