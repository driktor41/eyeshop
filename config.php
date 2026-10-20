<?php
session_start();

$dbFile = __DIR__ . '/../database.db';

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("PRAGMA foreign_keys = ON;");
    
    // Создаём таблицы (если их нет)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            gold REAL DEFAULT 0,
            eyes_count INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            description TEXT,
            cost REAL NOT NULL,
            type TEXT DEFAULT 'charm',
            multiplier REAL DEFAULT 1.00
        );
        
        CREATE TABLE IF NOT EXISTS user_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            item_id INTEGER NOT NULL,
            quantity INTEGER DEFAULT 1,
            UNIQUE(user_id, item_id),
            FOREIGN KEY (user_id) REFERENCES users(id),
            FOREIGN KEY (item_id) REFERENCES items(id)
        );
    ");
    
    // Заполняем предметами только если таблица пуста
    $count = (int)$pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
    if ($count === 0) {
        $pdo->exec("
            INSERT INTO items (name, description, cost, type, multiplier) VALUES 
            ('Summoning Star', 'Unlocks the Shade subclass.', 50, 'upgrade', 1.10),
            ('Hat of Vermin', '12 CHA, 6 INT, Max HP +5.', 80, 'charm', 1.08),
            ('Dark Hood', '8 LCK, Aggro -10%, Crit DMG +4%.', 120, 'charm', 1.12),
            ('Drifter''s Hat', '12 DEX, 6 CHA, Crit DMG +7%.', 180, 'charm', 1.15),
            ('Steel Platebody', '15 CON, 8 STR, Aggro +20%.', 250, 'charm', 1.20),
            ('Gemstone Crown', '12 FTH, 10 INT.', 350, 'charm', 1.25),
            ('Litch''s Skullcap', 'Lifesteal +2.5%.', 500, 'upgrade', 1.30),
            ('Mutated Rat Hide Cloak', 'Critical Chance +6%.', 150, 'charm', 1.07),
            ('Gothic Padded Chestpiece', 'Lifesteal +2%.', 300, 'charm', 1.18),
            ('Phoenix Feather', 'Revives and fully heals on death.', 1500, 'upgrade', 1.50),
            ('Golden Ticket', 'Merchant prices -25%.', 1200, 'upgrade', 1.40),
            ('Skeleton Key', 'Opens all locks.', 1000, 'upgrade', 1.35),
            ('Bandage', '30% chance to avoid damage.', 200, 'charm', 1.12),
            ('Meat Shield', 'Max HP +25%.', 100, 'charm', 1.06),
            ('Piercing Rounds', 'Projectiles pierce and deal +100%.', 220, 'charm', 1.10),
            ('Assassination', '+50% damage for first 10 sec.', 160, 'charm', 1.09),
            ('Speedster', 'Dash cooldown halved, +15% speed.', 90, 'charm', 1.05),
            ('Power From Pain', '+2% damage per 10 HP missing.', 280, 'charm', 1.15),
            ('Consistent Killer', 'Kill doubles next hit damage.', 140, 'charm', 1.08),
            ('Hate Forged', '10% chance for triple damage.', 260, 'charm', 1.12),
            ('Auto Melee', 'Auto-melee anything that touches you.', 110, 'charm', 1.07),
            ('Dual Wield', 'Use Nutriblaster with lasso.', 320, 'charm', 1.14),
            ('Erupting Jump', 'Dismount clears nearby shots.', 190, 'charm', 1.10);
        ");
    }
} catch (PDOException $e) {
    die('Ошибка БД: ' . $e->getMessage());
}
?>