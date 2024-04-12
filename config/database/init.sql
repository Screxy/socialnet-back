CREATE DATABASE IF NOT EXISTS social_net;
USE social_net;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT,
    PRIMARY KEY (id),
    email VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    access_token VARCHAR(255),
    created_at timestamp default current_timestamp
);

CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT,
    PRIMARY KEY (id),
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    title VARCHAR(255),
    text VARCHAR(1600),
    created_at timestamp default current_timestamp
);

CREATE TABLE IF NOT EXISTS post_likes (
    id INT AUTO_INCREMENT,
    PRIMARY KEY (id),
    user_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    post_id INT,
    FOREIGN KEY (post_id) REFERENCES posts(id),
    created_at timestamp default current_timestamp
);
