DROP DATABASE IF EXISTS `aurora_user_db`;

CREATE DATABASE `aurora_user_db`;

USE `aurora_user_db`;

CREATE TABLE users (
  Id 			INT 			UNSIGNED	NOT NULL	AUTO_INCREMENT 
  ,username 	VARCHAR(100)					NOT NULL
  ,email		VARCHAR(225)				NOT NULL
  ,password 	VARCHAR(225)				NOT NULL
  ,join_date	TIMESTAMP					NOT NULL	DEFAULT CURRENT_TIMESTAMP
  ,CONSTRAINT PK_User_Id PRIMARY KEY (Id)
) ENGINE=InnoDB;

