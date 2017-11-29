DROP TABLE IF EXISTS menu_categories;

CREATE TABLE menu_categories (
	id INT PRIMARY KEY,
	menu_id INT NOT NULL,
	group_id INT NOT NULL,
	name VARCHAR(50),
	description VARCHAR(256),
	active TINYINT(1)
)
	DEFAULT CHARACTER SET utf8mb4;

DROP TABLE IF EXISTS menu_groups;

CREATE TABLE menu_groups (
	id INT PRIMARY KEY,
	menu_id INT NOT NULL,
	group_name varchar(50)
)
	DEFAULT CHARACTER SET utf8mb4;

DROP TABLE IF EXISTS menu_items;

CREATE TABLE menu_items (
	id INT PRIMARY KEY,
	category_id INT NOT NULL,
	menu_id INT NOT NULL,
	name VARCHAR(50),
	price float,
	active TINYINT(1)
)
	DEFAULT CHARACTER SET utf8mb4;

DROP TABLE IF EXISTS orders;

CREATE TABLE orders (
	id INT PRIMARY KEY,
	order_id VARCHAR(20) NOT NULL,
	opened DATETIME,
	closed datetime,
	subtotal FLOAT,
	total FLOAT,
	server VARCHAR(20),
	server_id INT,
	cashier VARCHAR(20),
	cashier_id INT,
	guests INT,
	order_status VARCHAR(20)
)
	DEFAULT CHARACTER SET utf8mb4;

CREATE INDEX ix_order_id
ON orders (order_id);

DROP TABLE IF EXISTS order_contents;

CREATE TABLE order_contents (
	id INT PRIMARY KEY,
	order_id VARCHAR(20) NOT NULL,
	item VARCHAR(100),
	price FLOAT,
	quantity FLOAT,
	item_id INT,
	category_id INT
)
	DEFAULT CHARACTER SET utf8mb4;
	
CREATE INDEX ix_order_id
ON order_contents (order_id);