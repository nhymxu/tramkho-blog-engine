create table tag
(
    id      integer not null,
    slug    text    not null,
    name    text    not null,
    constraint tag_pk
        primary key (id autoincrement)
);

create table post_tag
(
    post_id int not null,
    tag_id int not null,
    constraint post_tag_pk
        unique (post_id, tag_id)
);

create table comment
(
    id              integer,
    post_id         integer not null,
    author_name     text,
    author_email    text,
    author_ip       text,
    author_url      text,
    author_ua       text,
    content         text,
    created_at      text    not null,
    updated_at      text    not null,
    constraint comment_pk
        primary key (id autoincrement)
);

create table post
(
    id         integer not null,
    slug       text default '' not null,
    title      text,
    content    text,
    wp_content text,
    status     text not null,
    created_at text not null,
    updated_at text not null,
    constraint post_pk
        primary key (id autoincrement)
);

create unique index post_slug_uindex
    on post (slug);
