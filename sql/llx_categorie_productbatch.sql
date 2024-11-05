create table llx_categorie_productbatch
(
    fk_categorie  integer NOT NULL,
    fk_productbatch    integer NOT NULL,
    import_key    varchar(14)
)ENGINE=innodb;
