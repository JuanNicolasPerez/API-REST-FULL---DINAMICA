----------------
-- TABLA ROLES_PERMISOS
----------------
    CREATE TABLE courses(
        id_course               INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        id_instructor_course    INT(11) NOT NULL,

        title_course            TEXT NULL,
        description_course      TEXT NULL,
        image_course            TEXT NULL,
        price_course            FLOAT NULL,

        date_created_course     DATE NULL,
        date_updated_course     TIMESTAMP NULL,

        --CREAMOS LA RELACION CON LA TABLA INSTRUCTORS
        FOREIGN KEY (id_instructor_course) REFERENCES instructors (id_instructor)  on delete no action 
                                                                                    on update cascade
    ) ENGINE=InnoDB;

----------------
-- TABLA INSTRUCTORS
----------------
    CREATE TABLE instructors(
        id_instructor            INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        name_intructor           TEXT NULL,
        username_instructor      TEXT NULL,
        email_instructor         TEXT NULL,
        password_instructor      TEXT NULL,
        token_instructor         TEXT NULL,
        token_exp_instructor     TEXT NULL,

        date_created_course     DATE NULL,
        date_updated_course     TIMESTAMP NULL

    ) ENGINE=InnoDB;