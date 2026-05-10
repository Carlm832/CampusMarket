-- CampusMarket initial PostgreSQL schema for Supabase (safe-mode migration track)

create type user_role as enum ('user', 'admin');
create type product_condition as enum ('new', 'like_new', 'used', 'poor');
create type product_status as enum ('active', 'sold', 'flagged');
create type order_status as enum ('pending', 'completed', 'cancelled');
create type payment_method as enum ('cash', 'venmo', 'zelle', 'other');
create type transaction_status as enum ('pending', 'success', 'failed');
create type report_status as enum ('pending', 'reviewed', 'dismissed');
create type notification_type as enum ('message', 'order', 'wishlist', 'system');

create table users (
    id bigserial primary key,
    username varchar(50) not null unique,
    email varchar(100) not null unique,
    password_hash varchar(255) not null,
    role user_role not null default 'user',
    phone varchar(20),
    avatar varchar(255),
    is_verified boolean not null default false,
    created_at timestamptz not null default now()
);

create table categories (
    id bigserial primary key,
    name varchar(100) not null,
    slug varchar(100) not null unique
);

create table tags (
    id bigserial primary key,
    name varchar(50) not null unique,
    slug varchar(50) not null unique
);

create table products (
    id bigserial primary key,
    user_id bigint not null references users(id) on delete cascade,
    category_id bigint not null references categories(id) on delete restrict,
    title varchar(200) not null,
    description text,
    price numeric(10, 2) not null,
    discount_percent smallint not null default 0 check (discount_percent between 0 and 100),
    discount_set_at timestamp,
    condition product_condition not null default 'used',
    status product_status not null default 'active',
    is_featured boolean not null default false,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table product_images (
    id bigserial primary key,
    product_id bigint not null references products(id) on delete cascade,
    image_path varchar(255) not null,
    is_primary boolean not null default false,
    uploaded_at timestamptz not null default now()
);

create table product_tags (
    id bigserial primary key,
    product_id bigint not null references products(id) on delete cascade,
    tag_id bigint not null references tags(id) on delete cascade,
    unique (product_id, tag_id)
);

create table wishlists (
    id bigserial primary key,
    user_id bigint not null references users(id) on delete cascade,
    product_id bigint not null references products(id) on delete cascade,
    created_at timestamptz not null default now(),
    unique (user_id, product_id)
);

create table orders (
    id bigserial primary key,
    buyer_id bigint not null references users(id) on delete cascade,
    product_id bigint not null references products(id) on delete cascade,
    amount numeric(10, 2) not null,
    status order_status not null default 'pending',
    meeting_point varchar(255),
    notes text,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create table transactions (
    id bigserial primary key,
    order_id bigint not null unique references orders(id) on delete cascade,
    payment_method payment_method not null default 'cash',
    transaction_ref varchar(255),
    amount numeric(10, 2) not null,
    status transaction_status not null default 'pending',
    created_at timestamptz not null default now()
);

create table messages (
    id bigserial primary key,
    sender_id bigint not null references users(id) on delete cascade,
    receiver_id bigint not null references users(id) on delete cascade,
    product_id bigint not null references products(id) on delete cascade,
    body text not null,
    is_read boolean not null default false,
    created_at timestamptz not null default now()
);

create table ratings (
    id bigserial primary key,
    reviewer_id bigint not null references users(id) on delete cascade,
    seller_id bigint not null references users(id) on delete cascade,
    product_id bigint not null references products(id) on delete cascade,
    rating smallint not null check (rating between 1 and 5),
    comment text,
    created_at timestamptz not null default now(),
    unique (reviewer_id, product_id)
);

create table reports (
    id bigserial primary key,
    reporter_id bigint not null references users(id) on delete cascade,
    product_id bigint not null references products(id) on delete cascade,
    reason text not null,
    status report_status not null default 'pending',
    created_at timestamptz not null default now()
);

create table notifications (
    id bigserial primary key,
    user_id bigint not null references users(id) on delete cascade,
    type notification_type not null,
    title varchar(200) not null,
    body text not null,
    is_read boolean not null default false,
    reference_id bigint,
    created_at timestamptz not null default now()
);

create table email_verifications (
    id bigserial primary key,
    user_id bigint not null references users(id) on delete cascade,
    token varchar(128) not null unique,
    created_at timestamptz not null default now(),
    expires_at timestamptz not null
);

create index idx_email_verifications_user_id on email_verifications(user_id);
create index idx_messages_receiver_unread on messages(receiver_id, is_read);
create index idx_notifications_user_unread on notifications(user_id, is_read);

create or replace function set_updated_at()
returns trigger as $$
begin
  new.updated_at = now();
  return new;
end;
$$ language plpgsql;

create trigger trg_products_updated_at
before update on products
for each row
execute function set_updated_at();

create trigger trg_orders_updated_at
before update on orders
for each row
execute function set_updated_at();
