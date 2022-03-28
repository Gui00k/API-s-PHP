CREATE DATABASE db_waifu;

USE db_waifu;

CREATE TABLE tb_users (
    user_address CHAR(42) PRIMARY KEY,
    user_balance FLOAT DEFAULT(0),
    balance_unlock DATETIME DEFAULT(ADDDATE(NOW(), INTERVAL 5 DAY)),
    balance_unlock_value FLOAT DEFAULT(0)
);

CREATE TABLE tb_cards (
    card_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    card_name VARCHAR(50),
    card_type CHAR(25),
    card_img_src VARCHAR(50)
);

CREATE TABLE tb_assets (
    asset_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    asset_unlock DATETIME DEFAULT(NOW()),
    user_address CHAR(42),
    card_id INT NOT NULL,
    FOREIGN KEY (user_address) REFERENCES tb_users(user_address) ON DELETE CASCADE,
    FOREIGN KEY (card_id) REFERENCES tb_cards(card_id) ON DELETE CASCADE
);

CREATE TABLE tb_deposit (
    deposit_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_address CHAR(42),
    deposit_hash CHAR(66),
    deposit_value FLOAT,
    deposit_date DATETIME DEFAULT(NOW()),
    FOREIGN KEY (user_address) REFERENCES tb_users(user_address) ON DELETE CASCADE,
    UNIQUE (deposit_hash)
);

INSERT INTO
    tb_cards(card_name, card_type, card_img_src)
VALUES
    ('Yulia Natsume', 'common', 'yulia_natsume_common.png'),
    ('Seraphin', 'rare', 'seraphin-rare.png'),
    ('Eve "Fire Queen"', 'legendary', 'eve_fire_queen_legendary.png'),
    ('Hoshizora', 'epic', 'hoshizora_epic.png');