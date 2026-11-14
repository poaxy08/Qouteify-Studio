ALTER TABLE users
MODIFY COLUMN role ENUM('Admin', 'Member')
NOT NULL DEFAULT 'Member';


UPDATE users
SET role = 'Admin'
WHERE email = 'quoteify@gmail.com';