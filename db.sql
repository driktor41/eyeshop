CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    gold DECIMAL(15,2) DEFAULT 0,
    eyes_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    cost DECIMAL(15,2) NOT NULL,
    type ENUM('charm', 'boon', 'upgrade') DEFAULT 'charm',
    multiplier DECIMAL(5,2) DEFAULT 1.00,
    image_url VARCHAR(255)
);

CREATE TABLE user_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    item_id INT,
    quantity INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (item_id) REFERENCES items(id)
);

INSERT INTO items (name, description, cost, type, multiplier) VALUES 
('Печать Рыцаря', 'CON +5, Block Chance +0.08', 50, 'charm', 1.05),
('Кольцо Гоблинских Ушей', 'STR +5, DEX +5', 100, 'charm', 1.10),
('Флакон Токсина', 'DEX +14, Healing +0.1', 250, 'charm', 1.25),
('Ожерелье Пальца Гиганта', 'STR +9, CON +9', 500, 'charm', 1.50),
('Око Немигающее', 'Increases all damage by 15%', 1000, 'upgrade', 1.15);