CREATE DATABASE aurora_theater;
USE aurora_theater;

CREATE TABLE medewerkers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naam VARCHAR(100) NOT NULL,
    functie VARCHAR(100) NOT NULL,
    afdeling VARCHAR(100) NOT NULL
);

INSERT INTO medewerkers (naam, functie, afdeling) VALUES
('Marlies de Vries', 'Front of House', 'Zaal'),
('Jan Bakker', 'Geluidstechnicus', 'Techniek'),
('Sophie Jansen', 'Ticketverkoop', 'Kassa'),
('Emma de Wit', 'Marketing', 'Communicatie'),
('Daan Visser', 'Geluidstechnicus', 'Techniek'),
('Nina van der Veen', 'Kassa', 'Kassa'),
('Tim Smit', 'Horeca', 'Horeca'),
('Fenna de Jonge', 'Front of House', 'Zaal');