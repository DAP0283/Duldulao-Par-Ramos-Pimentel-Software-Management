CREATE TABLE transactions (
    transaction_id INT PRIMARY KEY IDENTITY(1,1),
    transaction_type NVARCHAR(20) NOT NULL, -- 'Income' or 'Expense'
    amount DECIMAL(18, 2) NOT NULL,
    description NVARCHAR(MAX),
    created_by INT, -- References your staff/user ID
    status NVARCHAR(20) DEFAULT 'Completed',
    created_at DATETIME DEFAULT GETDATE()
);
