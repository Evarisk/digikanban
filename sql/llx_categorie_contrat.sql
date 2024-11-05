create table llx_categorie_contrat
(
    fk_categorie  integer NOT NULL,
    fk_contrat    integer NOT NULL,
    import_key    varchar(14)
)ENGINE=innodb;
