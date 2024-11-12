-- Copyright (C) 2024 EVARISK <technique@evarisk.com>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.

CREATE TABLE llx_digikanban_kanban(
    rowid           integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
    ref             varchar(128),
    entity          integer default 1,
    date_creation   datetime,
    tms             timestamp,
    import_key      varchar(255),
    label           varchar(255),
    description     text,
    image_path      varchar(255),
    track_id        varchar(255),
    status          tinyint(4) DEFAULT 1,
    object_type     varchar(255),
    fk_user_creat   integer
) ENGINE=innodb;
