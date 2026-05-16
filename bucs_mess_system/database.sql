-- BUCS Messaging System Database

CREATE DATABASE IF NOT EXISTS bucs_messaging;
USE bucs_messaging;

CREATE TABLE IF NOT EXISTS users (
    id_no      VARCHAR(20)  PRIMARY KEY,
    fname      VARCHAR(50)  NOT NULL,
    mname      VARCHAR(50)  DEFAULT '',
    lname      VARCHAR(50)  NOT NULL,
    uname      VARCHAR(50)  NOT NULL UNIQUE,
    pwd        VARCHAR(255) NOT NULL,
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS classes (
    class_id   INT AUTO_INCREMENT PRIMARY KEY,
    classname  VARCHAR(100) NOT NULL,
    created_at DATETIME     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_classes (
    id_no     VARCHAR(20),
    class_id  INT,
    PRIMARY KEY (id_no, class_id),
    FOREIGN KEY (id_no)    REFERENCES users(id_no)      ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS files (
    file_id     INT AUTO_INCREMENT PRIMARY KEY,
    filename    VARCHAR(255) NOT NULL,
    file        VARCHAR(500) NOT NULL,
    uploaded_by VARCHAR(20)  NOT NULL,
    uploaded_at DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id_no) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS messages (
    mess_id   INT AUTO_INCREMENT PRIMARY KEY,
    sender_id VARCHAR(20)  NOT NULL,
    receiver  VARCHAR(20)  NOT NULL,
    message   TEXT         NOT NULL,
    status    ENUM('sent','read','deleted') DEFAULT 'sent',
    sent_at   DATETIME     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id_no) ON DELETE CASCADE,
    FOREIGN KEY (receiver)  REFERENCES users(id_no) ON DELETE CASCADE
);

-- Sample data
INSERT INTO classes (classname) VALUES
  ('BSIT 3-A'), ('BSCS 2-B'), ('BSIS 4-A'), ('BSIT 1-C');

INSERT INTO users (id_no, fname, mname, lname, uname, pwd) VALUES
  ('2021-0001','Juan',  'Reyes',  'dela Cruz','juandc',   MD5('password1')),
  ('2021-0002','Maria', 'Santos', 'Garcia',   'mgarcia',  MD5('password2')),
  ('2021-0003','Pedro', 'Miguel', 'Reyes',    'preyes',   MD5('password3')),
  ('2021-0004','Ana',   'Lopez',  'Bautista', 'abautista',MD5('password4'));

INSERT INTO user_classes VALUES
  ('2021-0001',1),('2021-0001',3),
  ('2021-0002',1),('2021-0002',2),
  ('2021-0003',2),('2021-0003',4),
  ('2021-0004',3),('2021-0004',4);

INSERT INTO files (filename, file, uploaded_by) VALUES
  ('Syllabus_BSIT3A.pdf', 'uploads/Syllabus_BSIT3A.pdf', '2021-0001'),
  ('Assignment1.docx',    'uploads/Assignment1.docx',    '2021-0002');

INSERT INTO messages (sender_id, receiver, message, status) VALUES
  ('2021-0001','2021-0002','Hello Maria, did you get the notes?','read'),
  ('2021-0002','2021-0001','Yes Juan, I already downloaded them.','sent'),
  ('2021-0003','2021-0004','Ana, are you joining the group study?','sent');
