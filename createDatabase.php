<?php

require_once 'database/connectDB.php';
require 'vendor/autoload.php';

$dsn = new PDO("mysql:host=mysql;dbname=ifa_database", "ifa_user", "ifa_password");
$dsn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);



$createTableUser = ("CREATE TABLE IF NOT EXISTS
`User` (
    `IdUser` int(11) NOT NULL AUTO_INCREMENT,
    `FirstName` varchar(255) DEFAULT NULL,
    `LastName` varchar(255) DEFAULT NULL,
    `Email` varchar(255) DEFAULT NULL,
    `UserPassword` varchar(255) DEFAULT NULL,
    `ProfilPicture` varchar(255) DEFAULT NULL,
    `ProfilDescription` varchar(255) DEFAULT NULL,
    `DateCreationCompte` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `Birthday` TIMESTAMP DEFAULT NULL,
    `UserTag` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`IdUser`),
    CONSTRAINT unique_User_Email UNIQUE (`Email`),
    CONSTRAINT unique_User_Tag UNIQUE (`UserTag`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8mb4 COLLATE utf8mb4_unicode_ci");
$dsn->exec($createTableUser);


$createTablePro = ("CREATE TABLE IF NOT EXISTS 
`Pro` (
    `IdUser` int(11) NOT NULL,
PRIMARY KEY (`IdUser`),
CONSTRAINT fk_User_Id FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTablePro);


$createTableAdmin = ("CREATE TABLE IF NOT EXISTS
`Admin` (
    `IdUser` int(11) NOT NULL,
PRIMARY KEY (`IdUser`),
CONSTRAINT fk_User_Id FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableAdmin);



$createTableFollower= ("CREATE TABLE IF NOT EXISTS
`Follower` (
    `IdFollower` int(11) NOT NULL AUTO_INCREMENT,
    `IdUser` int(11) NOT NULL,
    `IdUser_Follower` int(11) NOT NULL,
    `DateFollower` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`IdUser`,`IdFollower`),
CONSTRAINT fk_User_Follower FOREIGN KEY (`IdUser_Follower`) REFERENCES User (`IdUser`),
CONSTRAINT fk_User_Id FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`),
CHECK (IdUser!=IdUser_Follower)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableFollower);

$createTableFollowed= ("CREATE TABLE IF NOT EXISTS
`Followed` (
    `IdFollowed` int(11) NOT NULL AUTO_INCREMENT,
    `IdUser` int(11) NOT NULL,
    `IdUser_Followed` int(11) NOT NULL,
    `DateFollowed` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`IdUser`,`IdFollowed`),
CONSTRAINT fk_User_Followed FOREIGN KEY (`IdUser_Followed`) REFERENCES User (`IdUser`),
CONSTRAINT fk_User_Id FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`),
CHECK (IdUser!=IdUser_Followed)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableFollowed);

$createTableReseaux= ("CREATE TABLE IF NOT EXISTS
`Reseaux` (
    `IdReseaux` int(11) NOT NULL AUTO_INCREMENT,
    `IdUser` int(11) NOT NULL,
    `Nom_Reseaux` varchar(255) DEFAULT NULL,
    `Description_Reseaux` varchar(255) DEFAULT NULL,
    `Link_Icone` varchar(255) DEFAULT NULL,
    `Link` varchar(255) DEFAULT NULL,
    `DateReseaux` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`IdUser`,`IdReseaux`),
CONSTRAINT fk_User_Id FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableReseaux);


$createTablePostSaved= ("CREATE TABLE IF NOT EXISTS
`PostSaved` (
    `IdPostSaved` int(11) NOT NULL AUTO_INCREMENT,
    `IdUser` int(11) NOT NULL,
    `IdPost` int(11) NOT NULL,
    `DatePostSaved` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`IdUser`,`IdPostSaved`),
CONSTRAINT fk_User_PostSaved FOREIGN KEY (`IdPost`) REFERENCES Post (`IdPost`),
CONSTRAINT fk_User_Id FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTablePostSaved);



$createTablePost = ("CREATE TABLE IF NOT EXISTS
`Post` (
    `IdPost` int(11) NOT NULL AUTO_INCREMENT,
    `Titre` varchar(255) DEFAULT NULL,
    `Content` varchar(255) DEFAULT NULL,
    `DatePost` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `Nb_Like` int(11) DEFAULT '0',
    `Nb_View` int(11) DEFAULT '0',
    `IdUser` int(11) NOT NULL,
    `IdCanal` int(11) NOT NULL,
PRIMARY KEY (`IdPost`),
CONSTRAINT fk_Canal_Id FOREIGN KEY (`IdCanal`) REFERENCES Canal (`IdCanal`),
CONSTRAINT fk_User_Id FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTablePost);

$createTableCanal= ("CREATE TABLE IF NOT EXISTS
`Canal` (
    `IdCanal` int(11) NOT NULL AUTO_INCREMENT,
    `NomCanal` varchar(255) DEFAULT NULL,
    `BioCanal` varchar(255) DEFAULT NULL,
    `DateCreationCanal` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `Nb_Membre` int(11) DEFAULT '0',
PRIMARY KEY (`IdCanal`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableCanal);

$createTableMembre= ("CREATE TABLE IF NOT EXISTS
`Membre` (
    `IdMembre` int(11) NOT NULL AUTO_INCREMENT,
    `IdCanal` int(11) NOT NULL,
    `BioCanal` varchar(255) DEFAULT NULL,
    `Role` varchar(255) DEFAULT NULL,
    `IdUser` int(11) NOT NULL,
    `DateJoinCanal` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`IdCanal`,`IdMembre`),
CONSTRAINT fk_Canal_Id FOREIGN KEY (`IdCanal`) REFERENCES Canal (`IdCanal`),
CONSTRAINT fk_User_Id FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableMembre);


$createTableMedia= ("CREATE TABLE IF NOT EXISTS
`Media` (
    `IdMedia` int(11) NOT NULL AUTO_INCREMENT,
    `IdPost` int(11) NOT NULL,
    `DateMedia` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `Type` ENUM ('video', 'photo') NOT NULL,
PRIMARY KEY (`IdMedia`,`IdPost` ),
CONSTRAINT fk_Post_Id FOREIGN KEY (`IdPost`) REFERENCES Post (`IdPost`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableMedia);

$createTableVideo= ("CREATE TABLE IF NOT EXISTS
`Video` (
    `IdMedia` int(11) NOT NULL,
    `IdPost` int(11) NOT NULL,
    `LinkVideo` varchar(255) DEFAULT NULL,    
PRIMARY KEY (`IdMedia`,`IdPost`),
CONSTRAINT fk_Media_Id FOREIGN KEY (`IdMedia`) REFERENCES Media (`IdMedia`)
/*CHECK (Media.type == Video)  */
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableVideo);


/*trigger pour les check car pas de support avec mysql  pb avec Video/Image/Follower/Followed
pour verif si idfollow!=iduser et si bien une video dans Video

CREATE TRIGGER before_insert_video
BEFORE INSERT ON Video
FOR EACH ROW
BEGIN
    DECLARE media_type VARCHAR(20);
    
    -- Récupérer le type de média correspondant à l'id du nouveau média dans la table media
    SELECT type INTO media_type FROM media WHERE id = NEW.id;
    
    -- Vérifier si le type récupéré est bien 'video'
    IF media_type <> 'video' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Le type de média n''est pas vidéo.';
    END IF;
END //

*/

$createTableImage= ("CREATE TABLE IF NOT EXISTS
`Image` (
    `IdMedia` int(11) NOT NULL,
    `IdPost` int(11) NOT NULL,
    `LinkImage` varchar(255) DEFAULT NULL,    
PRIMARY KEY (`IdMedia`),
CONSTRAINT fk_Media_Id FOREIGN KEY (`IdMedia`) REFERENCES Media (`IdMedia`)
/*CHECK (Media.type == photo )  */
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableVideo);

$createTableLike= ("CREATE TABLE IF NOT EXISTS
`Like` (
    `IdLike` int(11) NOT NULL AUTO_INCREMENT,
    `IdPost` int(11) NOT NULL,
    `IdUser` int(11) NOT NULL,
    `DateLike` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`IdLike`),
CONSTRAINT fk_Post_Id FOREIGN KEY (`IdPost`) REFERENCES Post (`IdPost`),
CONSTRAINT fk_User_Id FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableLike);


$createTableView= ("CREATE TABLE IF NOT EXISTS
`View` (
    `IdView` int(11) NOT NULL AUTO_INCREMENT,
    `IdPost` int(11) NOT NULL,
    `IdUser` int(11) NOT NULL,
    `DateView` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (`IdView`),
CONSTRAINT fk_Post_Id FOREIGN KEY (`IdPost`) REFERENCES Post (`IdPost`),
CONSTRAINT fk_User_Id FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableView);


$createTableCommentaire= ("CREATE TABLE IF NOT EXISTS
`Commentaire` (
    `IdPost` int(11) NOT NULL,
    `IdPost_up` int(11) NOT NULL,
PRIMARY KEY (`IdPost`),
CONSTRAINT fk_Post_Id FOREIGN KEY (`IdPost`) REFERENCES Post (`IdPost`),
CONSTRAINT fk_Post_Id_up FOREIGN KEY (`IdPost_up`) REFERENCES Post (`IdPost`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableCommentaire);

$createTableReshare= ("CREATE TABLE IF NOT EXISTS
`Reshare` (
    `IdPost` int(11) NOT NULL,
    `IdPost_up` int(11) NOT NULL,
PRIMARY KEY (`IdPost`),
CONSTRAINT fk_Post_Id FOREIGN KEY (`IdPost`) REFERENCES Post (`IdPost`),
CONSTRAINT fk_Post_Id_up FOREIGN KEY (`IdPost_up`) REFERENCES Post (`IdPost`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableReshare);