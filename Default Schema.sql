CREATE DATABASE db_waifu;

USE db_waifu;

CREATE TABLE tb_users (
    user_addres CHAR(42) PRIMARY KEY,
    user_moralis_id CHAR(24),
    user_balance FLOAT
);

CREATE TABLE tb_cards (
    card_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    card_name VARCHAR(50),
    card_type CHAR(25),
    card_img_src VARCHAR(50)
);

CREATE TABLE tb_assets (
    assets_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_addres CHAR(42),
    card_id INT NOT NULL,
    assets_first_acess DATETIME,
    assets_last_acess DATETIME,
    user_balance FLOAT DEFAULT(0),
    FOREIGN KEY (user_addres) REFERENCES tb_users(user_addres) ON DELETE CASCADE,
    FOREIGN KEY (card_id) REFERENCES tb_cards(card_id) ON DELETE CASCADE
);

CREATE TABLE tb_deposit (
    deposit_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_addres CHAR(42),
    deposit_hash CHAR(66),
    deposit_value FLOAT,
    deposit_date DATETIME DEFAULT(NOW()),
    FOREIGN KEY (user_addres) REFERENCES tb_users(user_addres) ON DELETE CASCADE
);

/* 
 #Commum
 #Rare
 #Epic
 #Legendary
 */