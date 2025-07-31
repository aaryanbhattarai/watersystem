CREATE DATABASE IF NOT EXISTS inventory_db; 
USE inventory_db; 


CREATE TABLE IF NOT EXISTS users ( 
user_id INT AUTO_INCREMENT PRIMARY KEY, 
name VARCHAR(100) NOT NULL, 
email VARCHAR(100) NOT NULL UNIQUE, 
password_hash VARCHAR(255) NOT NULL, 
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
); 

CREATE TABLE IF NOT EXISTS uploads ( 
upload_id INT AUTO_INCREMENT PRIMARY KEY, 
user_id INT NOT NULL, 
upload_filename VARCHAR(255) NOT NULL, 
upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
FOREIGN KEY (user_id) REFERENCES users(user_id) 
); 

CREATE TABLE IF NOT EXISTS forecasts (
    forecast_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    category VARCHAR(100),
    monthly_sales INT NOT NULL,
    time_period VARCHAR(7) NOT NULL,  -- Format 'YYYY-MM'
    price DECIMAL(10,2) DEFAULT NULL,
    stock_level INT DEFAULT NULL,
    promotion VARCHAR(50) DEFAULT NULL,
    predicted_demand INT NOT NULL,
    model_used VARCHAR(100) NOT NULL,
    mae FLOAT DEFAULT NULL,
    rmse FLOAT DEFAULT NULL,
    r2_score FLOAT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
 






