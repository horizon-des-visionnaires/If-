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
    `IsPro` tinyint(1) DEFAULT '0',
    `ProfilPicture` LONGBLOB DEFAULT NULL,
    `ProfilDescription` varchar(255) DEFAULT NULL,
    `IsAdmin` tinyint(1) DEFAULT '0',
    `ProfilPromotion` varchar(100) DEFAULT NULL,
    `Location` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`IdUser`),
    CONSTRAINT unique_User_Email UNIQUE (`Email`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8mb4 COLLATE utf8mb4_unicode_ci");
$dsn->exec($createTableUser);

$createTableTempUser = ("CREATE TABLE IF NOT EXISTS 
`TempUser` (
    `IdTempUser` int(11) NOT NULL AUTO_INCREMENT,
    `FirstName` varchar(255) DEFAULT NULL,
    `LastName` varchar(255) DEFAULT NULL,
    `Email` varchar(255) DEFAULT NULL,
    `UserPassword` varchar(255) DEFAULT NULL,
    `token` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`IdTempUser`),
    CONSTRAINT unique_TempUser_Email UNIQUE (`Email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
$dsn->exec($createTableTempUser);

$createTableTempTokenResetPassword = ("CREATE TABLE IF NOT EXISTS 
`TempTokenResetPassword` (
    `IdtempReset` int(11) NOT NULL AUTO_INCREMENT,
    `Email` varchar(255) DEFAULT NULL,
    `token` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`IdtempReset`),
    CONSTRAINT unique_TempUser_Email UNIQUE (`Email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
$dsn->exec($createTableTempTokenResetPassword);

$createTablePost = ("CREATE TABLE IF NOT EXISTS
`Post` (
    `IdPost` int(11) NOT NULL AUTO_INCREMENT,
    `TitlePost` varchar(255) DEFAULT NULL,
    `ContentPost` TEXT DEFAULT NULL,
    `DatePost` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `Views` int(11) DEFAULT '0',
    `IdUser` int(11) DEFAULT NULL,
    PRIMARY KEY (`IdPost`),
    CONSTRAINT fk_User_UserPost FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8mb4 COLLATE utf8mb4_unicode_ci");
$dsn->exec($createTablePost);

$createTablePicturePost = ("CREATE TABLE IF NOT EXISTS
`PicturePost` (
    `IdPicturePost` int(11) NOT NULL AUTO_INCREMENT,
    `IdPost` int(11) DEFAULT NULL,
    `PicturePost` LONGBLOB DEFAULT NULL,
    PRIMARY KEY (`IdPicturePost`),
    CONSTRAINT fk_Post_PicturePost FOREIGN KEY (`IdPost`) REFERENCES Post (`IdPost`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTablePicturePost);

$createTableComment = ("CREATE TABLE IF NOT EXISTS
`Comment` (
    `IdComment`int(11) NOT NULL AUTO_INCREMENT,
    `ContentComment` TEXT DEFAULT NULL,
    `DateComment` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `IdUser` int(11) DEFAULT NULL,
    `IdPost` int(11) DEFAULT NULL,
    PRIMARY KEY (`IdComment`),
    CONSTRAINT fk_User_Comment FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`),
    CONSTRAINT fk_Post_Comment FOREIGN KEY (`IdPost`) REFERENCES Post (`IdPost`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableComment);

$createTableLike = ("CREATE TABLE IF NOT EXISTS
`LikeFavorites` (
    `Id` int(11) NOT NULL AUTO_INCREMENT,
    `IdUser` int(11) DEFAULT NULL,
    `IdPost` int(11) DEFAULT NULL,
    `IsLike` tinyint(1) DEFAULT '0',
    `IsFavorites` tinyint(1) DEFAULT '0',
    PRIMARY KEY (`Id`),
    CONSTRAINT fk_User_Like FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`),
    CONSTRAINT fk_Post_Like FOREIGN KEY (`IdPost`) REFERENCES Post (`IdPost`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableLike);

// $createTableSubscriber = ("CREATE TABLE IF NOT EXISTS
// `Subscriber` (
//     `Id` int(11) NOT NULL AUTO_INCREMENT,
//     `IdUser` int(11) DEFAULT NULL,
//     `IdSubscriber` int(11) DEFAULT NULL,
//     PRIMARY KEY (`Id`),
//     CONSTRAINT fk_IdUser_Subscriber FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`),
//     CONSTRAINT fk_IdSubscriber_Subscriber FOREIGN KEY (`IdSubscriber`) REFERENCES User (`IdUser`)
// ) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
// $dsn->exec($createTableSubscriber);

$createTableRequestPassPro = ("CREATE TABLE IF NOT EXISTS
`RequestPassPro` (
    `IdRequest` int(11) NOT NULL AUTO_INCREMENT,
    `IdUser` int(11) DEFAULT NULL,
    `UserJob` varchar(255) DEFAULT NULL,
    `UserAge` int(11) DEFAULT NULL,
    `Description` TEXT DEFAULT NULL,
    `IdentityCardRecto` LONGBLOB DEFAULT NULL,
    `IdentityCardVerso` LONGBLOB DEFAULT NULL,
    `IsRequestValid` tinyint(1) DEFAULT '0',
    `UserPicture` LONGBLOB DEFAULT NULL,
    `UserAdress` varchar(255) DEFAULT NULL,
    `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`IdRequest`),
    CONSTRAINT fk_IdUser_RequestPassPro FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableRequestPassPro);

$createTableUserMessages = ("CREATE TABLE IF NOT EXISTS
`UserMessages` (
    `IdMessage` int(11) NOT NULL AUTO_INCREMENT,
    `IdUser` int(11) DEFAULT NULL,
    `Message` TEXT DEFAULT NULL,
    `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`IdMessage`),
    CONSTRAINT fk_IdUser_UserMessages FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableUserMessages);

$createTableNotaions = ("CREATE TABLE IF NOT EXISTS
`Notations` (
    `idNotations` int(11) NOT NULL AUTO_INCREMENT,
    `Note` int(11) DEFAULT NULL,
    `CommentNote` TEXT DEFAULT NULL,
    `DateNotation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `IdUser` int(11) DEFAULT NULL,
    `IdUserIsPro` int(11) DEFAULT NULL,
    PRIMARY KEY (`idNotations`),
    CONSTRAINT fk_IdUser_Notations FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`),
    CONSTRAINT fk_IdUserIsPro_Notations FOREIGN KEY (`IdUserIsPro`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableNotaions);

$createTableConversations = ("CREATE TABLE IF NOT EXISTS
`Conversations` (
    `IdConversations` int(11) NOT NULL AUTO_INCREMENT,
    `IdUser_1` int(11) DEFAULT NULL,
    `IdUser_2` int(11) DEFAULT NULL,
    `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`IdConversations`),
    CONSTRAINT fk_IdUser_1_Conversations FOREIGN KEY (`IdUser_1`) REFERENCES User (`IdUser`),
    CONSTRAINT fk_IdUser_2_Conversations FOREIGN KEY (`IdUser_2`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableConversations);

$createTableConversationMessages = ("CREATE TABLE IF NOT EXISTS
`ConversationMessages` (
    `IdMessages` int(11) NOT NULL AUTO_INCREMENT,
    `IdConversations` int(11) DEFAULT NULL,
    `IdSender` int(11) DEFAULT NULL,
    `ContentMessages` TEXT DEFAULT NULL,
    `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`IdMessages`),
    CONSTRAINT fk_IdConversations_ConversationMessages FOREIGN KEY (`IdConversations`) REFERENCES Conversations (`IdConversations`),
    CONSTRAINT fk_IdSender_ConversationMessages FOREIGN KEY (`IdSender`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableConversationMessages);

$createTableCategory = ("CREATE TABLE IF NOT EXISTS
`Category` (
    `IdCategory` int(11) NOT NULL AUTO_INCREMENT,
    `CategoryName` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`IdCategory`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableCategory);

$createTableAdvice = ("CREATE TABLE IF NOT EXISTS
`Advice` (
    `IdAdvice` int(11) NOT NULL AUTO_INCREMENT,
    `AdviceType` varchar(255) DEFAULT NULL,
    `AdviceDescription` TEXT DEFAULT NULL,
    `IdUser` int(11) DEFAULT NULL,
    `DaysOfWeek` VARCHAR(255) DEFAULT NULL,
    `StartTime` TIME NOT NULL,
    `EndTime` TIME NOT NULL,
    `IdCategory` int(11) DEFAULT NULL,
    `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`IdAdvice`),
    CONSTRAINT fk_IdUser_Advice FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`),
    CONSTRAINT fk_IdCategory_Advice FOREIGN KEY (`IdCategory`) REFERENCES Category (`IdCategory`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableAdvice);

$createTableBuyAdvice = ("CREATE TABLE IF NOT EXISTS
`BuyAdvice` (
    `IdBuyAdvice` int(11) NOT NULL AUTO_INCREMENT,
    `IdAdvice` int(11) DEFAULT NULL,
    `IdBuyer` int(11) DEFAULT NULL,
    `Date` DATE DEFAULT NULL,
    `StartTime` TIME NOT NULL,
    `EndTime` TIME NOT NULL,
    `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `IsAdviceValid` tinyint(1) DEFAULT NULL,
    PRIMARY KEY (`IdBuyAdvice`),
    CONSTRAINT fk_IdAdvice_BuyAdvice FOREIGN KEY (`IdAdvice`) REFERENCES Advice (`IdAdvice`),
    CONSTRAINT fk_IdBuyer_BuyAdvice FOREIGN KEY (`IdBuyer`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableBuyAdvice);

$createTablePictureAdvice = ("CREATE TABLE IF NOT EXISTS
`PictureAdvice` (
    `IdPictureAdvice` int(11) NOT NULL AUTO_INCREMENT,
    `IdAdvice` int(11) DEFAULT NULL,
    `PictureAdvice` LONGBLOB DEFAULT NULL,
    PRIMARY KEY (`IdPictureAdvice`),
    CONSTRAINT fk_Advice_PictureAdvice FOREIGN KEY (`IdAdvice`) REFERENCES Advice (`IdAdvice`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTablePictureAdvice);

$createTableNotifications = ("CREATE TABLE IF NOT EXISTS
`Notifications` (
    `IdNotification` int(11) NOT NULL AUTO_INCREMENT,
    `IdUser` int(11) DEFAULT NULL,
    `MessageNotif` TEXT DEFAULT NULL,
    `IsRead` tinyint(1) DEFAULT '0',
    `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`IdNotification`),
    CONSTRAINT fk_IdUser_Notifications FOREIGN KEY (`IdUser`) REFERENCES User (`IdUser`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableNotifications);

$createTableNumberBuyAdvice = ("CREATE TABLE IF NOT EXISTS
`NumberBuyAdvice` (
    `Id` int(11) NOT NULL AUTO_INCREMENT,
    `Number` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`Id`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = latin1");
$dsn->exec($createTableNumberBuyAdvice);