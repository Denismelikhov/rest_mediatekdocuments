CREATE TABLE suivi (
  id varchar(5) NOT NULL,
  libelle varchar(20) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO suivi (id, libelle) VALUES
('00001', 'en cours'),
('00002', 'relancee'),
('00003', 'livree'),
('00004', 'reglee');

ALTER TABLE commande
ADD COLUMN idSuivi varchar(5) NOT NULL DEFAULT '00001';

ALTER TABLE commande
ADD CONSTRAINT commande_ibfk_suivi
FOREIGN KEY (idSuivi) REFERENCES suivi(id);