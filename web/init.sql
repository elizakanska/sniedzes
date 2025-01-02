-- CREATE DATABASE sniedzes;
USE sniedzes;

DROP TABLE IF EXISTS majina, lietotajs, atsauksme, rezervacija;

CREATE TABLE Majina (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    nosaukums VARCHAR(255),
    cena DOUBLE,
    maxCilveki INT,
    pieejama BOOLEAN
);

INSERT INTO Majina (nosaukums, cena, maxCilveki, pieejama) VALUES
    ('Žubes', 90.00, 6, 1),
    ('Sīļi', 90.00, 6, 1),
    ('Cīruļi', 90.00, 6, 1),
    ('Cielavas', 90.00, 6, 1),
    ('Kaijas', 90.00, 6, 1),
    ('Irbes', 90.00, 6, 1),
    ('Gārņi', 70.00, 5, 1),
    ('Dūjas', 60.00, 4, 1),
    ('Dzeņi', 60.00, 4, 1),
    ('Pūces', 70.00, 5, 1),
    ('Pirts augša', 60.00, 4, 1);

CREATE TABLE Lietotajs (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    vards VARCHAR(255),
    uzvards VARCHAR(255),
    dzGads INT,
    telefons VARCHAR(50),
    epasts VARCHAR(255),
    parole VARCHAR(255),
    isAdmin BOOLEAN
);

INSERT INTO Lietotajs (vards, uzvards, dzGads, telefons, epasts, parole, isAdmin) VALUES
    ('Jānis', 'Bērziņš', 1990, '+37126123456', 'janis.berzins@example.com', 'parole123', 0),
    ('Anna', 'Ozoliņa', 1995, '+37127876543', 'anna.ozolina@example.com', 'drošaParole', 0),
    ('Ilze', 'Kalniņa', 2000, '+37129123456', 'ilze.kalnina@example.com', 'ilzePass', 0),
    ('Elīza', 'Kanska', 2003, '+37126262626', 'eliza.kanska@example.com', 'parole', 1),
    ('Mārtiņš', 'Kļaviņš', 1998, '+37123456789', 'martins.klavins@example.com', 'martinsPass', 0),
    ('Līga', 'Siliņa', 2001, '+37122098765', 'liga.silina@example.com', 'ligaPass', 0);

CREATE TABLE Rezervacija (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    majinaID INT,
    viesisID INT,
    vards VARCHAR(255),
    uzvards VARCHAR(255),
    telefons VARCHAR(50),
    epasts VARCHAR(255),
    viesuSk INT,
    cena DOUBLE,
    iebrauksana DATETIME,
    izbrauksana DATETIME,
    FOREIGN KEY (majinaID) REFERENCES Majina(ID),
    FOREIGN KEY (viesisID) REFERENCES Lietotajs(ID)
);

INSERT INTO Rezervacija (viesisID, vards, uzvards, telefons, epasts, majinaID, viesuSk, cena, iebrauksana, izbrauksana)
VALUES
	(1, 'Jānis', 'Bērziņš', '+37126123456', 'janis.berzins@example.com', 8, 4, 60.00, '2024-12-02 14:00:00', '2024-12-03 11:00:00'),
    (NULL, 'Kārlis', 'Priedītis', '25123456', 'karlis.prieditis@example.com', 2, 5, 90.00, '2024-12-01 14:00:00', '2024-12-02 11:00:00'),
    (NULL, 'Elīna', 'Zaļā', '26345678', 'elina.zala@example.com', 3, 6, 180.00, '2024-12-10 16:00:00', '2024-12-12 10:00:00');

CREATE TABLE Atsauksme (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    viesisID INT,
    teksts VARCHAR(255),
    vertejums INT,
    attels VARCHAR(255),
    FOREIGN KEY (viesisID) REFERENCES Lietotajs(ID)
);

INSERT INTO Atsauksme (viesisID, teksts, vertejums, attels) VALUES
(1, 'Lieliska pieredze, ļoti ieteicams!', 10, 'media/atsauksmes/image1.jpg'),
(2, 'Pakalpojums bija apmierinošs, bet ir vietas uzlabojumiem.', 8, 'image2.jpg'),
(4, 'Nebija tā, ko gaidīju, bet bija ok.', 7, 'image3.jpg'),
(3, 'Fantastisks serviss! Atgriezīšos vēlreiz.', 10, 'image4.jpg'),
(6, 'Nepatika, nebija labi.', 4, 'image5.jpg'),
(5, 'Lieliski! Viss bija lieliski.', 9, 'image6.jpg');

