CREATE INDEX idx_transactions_user_date ON transactions(user_id, transaction_date);
CREATE INDEX idx_debts_user ON debts(user_id);
CREATE INDEX idx_receivables_user ON receivables(user_id);