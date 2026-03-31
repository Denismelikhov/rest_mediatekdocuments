CREATE TABLE service (
    id INT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE utilisateur (
    id INT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    pwd VARCHAR(255) NOT NULL,
    idService INT NOT NULL,
    FOREIGN KEY (idService) REFERENCES service(id)
);

INSERT INTO service (id, libelle) VALUES
(1, 'Administratif'),
(2, 'Prêts'),
(3, 'Culture'),
(4, 'Administrateur');

INSERT INTO utilisateur (id, login, pwd, idService) VALUES
(1, 'gestionAdmin', 'admin', 1),
(2, 'prets', 'prets', 2),
(3, 'culture', 'culture', 3),
(4, 'admin', 'admin', 4);