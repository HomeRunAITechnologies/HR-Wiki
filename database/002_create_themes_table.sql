-- Create themes table
CREATE TABLE IF NOT EXISTS themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    type ENUM('builtin', 'custom') DEFAULT 'builtin',
    css_url VARCHAR(500) NULL,
    css_content LONGTEXT NULL,
    is_dark TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add theme preference to users table
ALTER TABLE users ADD COLUMN theme_slug VARCHAR(100) DEFAULT 'cosmo' AFTER role;

-- Insert default Bootswatch themes
INSERT INTO themes (name, slug, type, css_url, is_dark, is_active, sort_order) VALUES
('Cosmo', 'cosmo', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/cosmo/bootstrap.min.css', 0, 1, 1),
('Cerulean', 'cerulean', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/cerulean/bootstrap.min.css', 0, 1, 2),
('Flatly', 'flatly', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/flatly/bootstrap.min.css', 0, 1, 3),
('Journal', 'journal', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/journal/bootstrap.min.css', 0, 1, 4),
('Litera', 'litera', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/litera/bootstrap.min.css', 0, 1, 5),
('Lumen', 'lumen', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/lumen/bootstrap.min.css', 0, 1, 6),
('Lux', 'lux', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/lux/bootstrap.min.css', 0, 1, 7),
('Materia', 'materia', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/materia/bootstrap.min.css', 0, 1, 8),
('Minty', 'minty', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/minty/bootstrap.min.css', 0, 1, 9),
('Morph', 'morph', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/morph/bootstrap.min.css', 0, 1, 10),
('Pulse', 'pulse', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/pulse/bootstrap.min.css', 0, 1, 11),
('Quartz', 'quartz', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/quartz/bootstrap.min.css', 0, 1, 12),
('Sandstone', 'sandstone', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/sandstone/bootstrap.min.css', 0, 1, 13),
('Simplex', 'simplex', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/simplex/bootstrap.min.css', 0, 1, 14),
('Sketchy', 'sketchy', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/sketchy/bootstrap.min.css', 0, 1, 15),
('Spacelab', 'spacelab', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/spacelab/bootstrap.min.css', 0, 1, 16),
('Superhero', 'superhero', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/superhero/bootstrap.min.css', 1, 1, 17),
('United', 'united', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/united/bootstrap.min.css', 0, 1, 18),
('Vapor', 'vapor', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/vapor/bootstrap.min.css', 1, 1, 19),
('Yeti', 'yeti', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/yeti/bootstrap.min.css', 0, 1, 20),
('Zephyr', 'zephyr', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/zephyr/bootstrap.min.css', 0, 1, 21),
('Darkly', 'darkly', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/darkly/bootstrap.min.css', 1, 1, 22),
('Cyborg', 'cyborg', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/cyborg/bootstrap.min.css', 1, 1, 23),
('Slate', 'slate', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/slate/bootstrap.min.css', 1, 1, 24),
('Solar', 'solar', 'builtin', 'https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/solar/bootstrap.min.css', 1, 1, 25);
