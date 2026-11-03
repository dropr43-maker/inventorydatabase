CREATE DATABASE IF NOT EXISTS inventory_db;
USE inventory_db;

CREATE TABLE types (
  type_id INT AUTO_INCREMENT PRIMARY KEY,
  type_name VARCHAR(60) NOT NULL UNIQUE
);

CREATE TABLE categories (
  category_id INT AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(60) NOT NULL UNIQUE
);

CREATE TABLE statuses (
  status_id INT AUTO_INCREMENT PRIMARY KEY,
  status_name VARCHAR(60) NOT NULL UNIQUE,
  badge_color VARCHAR(20) NOT NULL DEFAULT 'grey'
);

CREATE TABLE suppliers (
  supplier_id INT AUTO_INCREMENT PRIMARY KEY,
  supplier_name VARCHAR(100) NOT NULL UNIQUE,
  phone VARCHAR(20),
  email VARCHAR(100)
);

CREATE TABLE items (
  item_id INT AUTO_INCREMENT PRIMARY KEY,
  description VARCHAR(255) NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  type_id INT NOT NULL,
  category_id INT NOT NULL,
  status_id INT NOT NULL,
  supplier_id INT NOT NULL,
  location VARCHAR(100),
  manufacture_date DATE NULL,
  purchase_date DATE NULL,
  use_date DATE NULL,
  warranty_date DATE NULL,
  expiry_date DATE NULL,
  date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (type_id) REFERENCES types(type_id) ON DELETE RESTRICT,
  FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT,
  FOREIGN KEY (status_id) REFERENCES statuses(status_id) ON DELETE RESTRICT,
  FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE RESTRICT
);

INSERT INTO statuses (status_name, badge_color) VALUES
  ('Available', 'grey'),
  ('Ongoing', 'blue'),
  ('Delivered', 'green'),
  ('Out of Stock', 'orange'),
  ('Lost', 'red'),
  ('Cancelled', 'grey');

INSERT INTO types (type_name) VALUES ('Goods'), ('Service');
INSERT INTO categories (category_name) VALUES ('Beverages'), ('Electronics'), ('Stationery');
INSERT INTO suppliers (supplier_name) VALUES ('Demo Supplier Ltd');