-- Payments table and stored procedures
-- Run this script on the database to add payments support

IF OBJECT_ID('dbo.Payments', 'U') IS NULL
BEGIN
    CREATE TABLE dbo.Payments (
        PaymentID INT IDENTITY(1,1) PRIMARY KEY,
        ApplicationID INT NOT NULL,
        ClientID INT NOT NULL,
        Amount DECIMAL(18,2) NOT NULL,
        Method NVARCHAR(100) NOT NULL,
        TransactionID NVARCHAR(100) NOT NULL,
        CreatedAt DATETIME2 DEFAULT SYSUTCDATETIME()
    );
END

-- Stored procedure to create payment record
IF OBJECT_ID('dbo.sp_CreatePayment', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_CreatePayment;
GO
CREATE PROCEDURE dbo.sp_CreatePayment
    @ApplicationID INT,
    @ClientID INT,
    @Amount DECIMAL(18,2),
    @Method NVARCHAR(100),
    @TransactionID NVARCHAR(100)
AS
BEGIN
    SET NOCOUNT ON;
    INSERT INTO dbo.Payments (ApplicationID, ClientID, Amount, Method, TransactionID)
    VALUES (@ApplicationID, @ClientID, @Amount, @Method, @TransactionID);

    SELECT SCOPE_IDENTITY() AS PaymentID;
END
GO

-- Stored procedure to get recent payments
IF OBJECT_ID('dbo.sp_GetRecentPayments', 'P') IS NOT NULL
    DROP PROCEDURE dbo.sp_GetRecentPayments;
GO
CREATE PROCEDURE dbo.sp_GetRecentPayments
    @Days INT = 30
AS
BEGIN
    SET NOCOUNT ON;
    SELECT TOP (100) PaymentID, ApplicationID, ClientID, Amount, Method, TransactionID, CreatedAt
    FROM dbo.Payments
    WHERE CreatedAt >= DATEADD(DAY, -@Days, SYSUTCDATETIME())
    ORDER BY CreatedAt DESC;
END
GO

-- Optional: view for payments summary
IF OBJECT_ID('dbo.vw_PaymentSummary', 'V') IS NULL
BEGIN
    EXEC('CREATE VIEW dbo.vw_PaymentSummary AS
        SELECT COUNT(*) AS TotalPayments, SUM(Amount) AS TotalAmount FROM dbo.Payments');
END
