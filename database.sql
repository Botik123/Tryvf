CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') DEFAULT 'student',
    token VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(30) NOT NULL,
    description VARCHAR(100),
    hours INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    img VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(50) NOT NULL,
    content TEXT NOT NULL,
    video_link VARCHAR(255),
    hours INT NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    certificate_number VARCHAR(12) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Insert default admin
INSERT INTO users (name, email, password, role) 
VALUES ('Administrator', 'admin@edu.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Password is 'course2025' hashed with bcrypt

-- Sample Courses
INSERT INTO courses (name, description, hours, price, start_date, end_date, img) VALUES 
('Основы PHP', 'Изучите основы самого популярного языка для веб-разработки.', 10, 1500.00, '2026-05-01', '2026-06-01', 'mpic_php.jpg'),
('Веб-дизайн для начинающих', 'Создавайте красивые и удобные интерфейсы с нуля.', 8, 2500.00, '2026-05-15', '2026-06-15', 'mpic_design.jpg'),
('Базы данных SQL', 'Проектирование и оптимизация реляционных баз данных.', 6, 1200.00, '2026-04-20', '2026-05-20', 'mpic_sql.jpg');

-- Sample Lessons for PHP Course
INSERT INTO lessons (course_id, title, content, video_link, hours) VALUES 
(1, 'Введение в PHP', 'В этом уроке мы разберем синтаксис PHP и переменные.', 'https://super-tube.cc/video/v10001', 2),
(1, 'Циклы и массивы', 'Работа с данными в PHP: циклы for, while и ассоциативные массивы.', 'https://super-tube.cc/video/v10002', 2),
(1, 'Функции и области видимости', 'Создание собственных функций и понимание глобальных переменных.', 'https://super-tube.cc/video/v10003', 2);
