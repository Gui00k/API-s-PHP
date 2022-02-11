CREATE DATABASE db_waifu;

USE db_waifu;

CREATE TABLE tb_users (
    user_id CHAR(24) PRIMARY KEY,
    user_addres VARCHAR(249)
);

CREATE TABLE tb_cards (
    card_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    card_name VARCHAR(50),
    card_type CHAR(25),
    card_img_src VARCHAR(50)
);

CREATE TABLE tb_assets (
    assets_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id CHAR(24) NOT NULL,
    card_id INT NOT NULL,
    assets_last_acess DATETIME,
    FOREIGN KEY (card_id) REFERENCES tb_cards(card_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES tb_users(user_id) ON DELETE CASCADE
);

/* 
 #Commum
 #Rare
 #Epic
 #Legendary
 */