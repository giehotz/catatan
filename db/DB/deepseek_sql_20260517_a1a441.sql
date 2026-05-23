-- User contoh
INSERT INTO users (name, email, password_hash) VALUES ('Budi', 'budi@mail.com', 'hash_rahasia');

-- Kategori Pemasukan
INSERT INTO income_categories (user_id, name) VALUES (1, 'Gaji');
INSERT INTO income_categories (user_id, name) VALUES (1, 'Hadiah');

-- Kategori Pengeluaran (induk & anak)
INSERT INTO expense_categories (user_id, name) VALUES (1, 'Pembelian');
SET @pembelian_id = LAST_INSERT_ID();
INSERT INTO expense_categories (user_id, parent_id, name) VALUES (1, @pembelian_id, 'Sembako');
INSERT INTO expense_categories (user_id, parent_id, name) VALUES (1, @pembelian_id, 'Elektronik');
INSERT INTO expense_categories (user_id, name) VALUES (1, 'Sumbangan');
INSERT INTO expense_categories (user_id, name) VALUES (1, 'Piutang');