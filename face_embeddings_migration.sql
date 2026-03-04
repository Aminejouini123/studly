CREATE TABLE face_embeddings (
    id INT AUTO_INCREMENT NOT NULL,
    user_id INT NOT NULL,
    descriptor JSON NOT NULL,
    UNIQUE INDEX UNIQ_user_id (user_id),
    PRIMARY KEY(id),
    CONSTRAINT FK_face_embeddings_user FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
