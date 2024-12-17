create table llx_categorie_project_task
(
    fk_categorie    integer NOT NULL,
    fk_project_task integer NOT NULL,
    import_key      varchar(14)
)ENGINE=innodb;
