CREATE DATABASE doctors;

/* setup postgresql doctors database */
CREATE TABLE resource (
    id              SERIAL,
    name            TEXT,
    type            TEXT,
    description     TEXT,
    urlmain         TEXT,
    watch           TEXT,
    watchurl        TEXT,
    watchkeys       TEXT,
    resource_type   TEXT,
    year            TEXT,
    month           TEXT,
    volume          TEXT,
    number          TEXT,
    publisher       TEXT,
    editor          TEXT,
    series          TEXT,
    address         TEXT,
    organisation    TEXT,
    edition         TEXT,
    author          TEXT,
    keywords        TEXT,
    note            TEXT,
    annote          TEXT
);

CREATE TABLE reference (
    id              SERIAL,
    title           TEXT,
    reference_id    TEXT,
    reference_type  TEXT,
    description     TEXT,
    abstract        TEXT,
    content         TEXT,
    weblink         TEXT,
    author          TEXT,
    keywords        TEXT,
    year            TEXT,
    volume          TEXT,
    number          TEXT,
    month           TEXT,
    journal         TEXT,
    pages           TEXT,
    booktitle       TEXT,
    publisher       TEXT,
    editor          TEXT,
    series          TEXT,
    type            TEXT,
    chapter         TEXT,
    address         TEXT,
    edition         TEXT,
    organisation    TEXT,
    note            TEXT,
    annote          TEXT,
    resource_id     TEXT,
    howpublished    TEXT,
    school          TEXT,
    thesis_type     TEXT,
    institution     TEXT
);


CREATE TABLE links (
    id              SERIAL,
    from_id         TEXT,
    to_id           TEXT,
    type            TEXT,
    description     TEXT

);


CREATE TABLE category (
    id              SERIAL,
    name            TEXT,
    description     TEXT

);

CREATE TABLE "user" (
    id              SERIAL,
    username        TEXT,
    password        TEXT,
    fullname        TEXT,
    administrator   TEXT

);

CREATE TABLE document (
    id              SERIAL,
    name            TEXT,
    description     TEXT,
    filename        TEXT

);

