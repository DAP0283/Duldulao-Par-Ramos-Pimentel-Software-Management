-- =====================================================
-- Barangay e-Services Appointment System
-- SQL Server Database Schema (T-SQL)
-- =====================================================

-- Create Database
CREATE DATABASE BarangayEServices;
GO

USE BarangayEServices;
GO

-- =====================================================
-- 1. CLIENTS TABLE
-- =====================================================
CREATE TABLE Clients (
    ClientID INT PRIMARY KEY IDENTITY(1,1),
    Email NVARCHAR(255) NOT NULL UNIQUE,
    Password NVARCHAR(255) NOT NULL,
    FirstName NVARCHAR(100) NOT NULL,
    LastName NVARCHAR(100) NOT NULL,
    MiddleName NVARCHAR(100),
    PhoneNumber NVARCHAR(20),
    Address NVARCHAR(255),
    BirthDate DATE,
    Gender NVARCHAR(10),
    CivilStatus NVARCHAR(50),
    Occupation NVARCHAR(100),
    CreatedAt DATETIME DEFAULT GETDATE(),
    UpdatedAt DATETIME DEFAULT GETDATE(),
    IsActive BIT DEFAULT 1
);

-- Create index on Email for faster login queries
CREATE INDEX IX_Clients_Email ON Clients(Email);
GO

-- =====================================================
-- 2. ADMIN TABLE
-- =====================================================
CREATE TABLE Admins (
    AdminID INT PRIMARY KEY IDENTITY(1,1),
    Username NVARCHAR(100) NOT NULL UNIQUE,
    Password NVARCHAR(255) NOT NULL,
    FullName NVARCHAR(200) NOT NULL,
    Email NVARCHAR(255),
    PhoneNumber NVARCHAR(20),
    CreatedAt DATETIME DEFAULT GETDATE(),
    UpdatedAt DATETIME DEFAULT GETDATE(),
    IsActive BIT DEFAULT 1
);

-- Create index on Username
CREATE INDEX IX_Admins_Username ON Admins(Username);
GO

-- =====================================================
-- 3. STAFF TABLE
-- =====================================================
CREATE TABLE Staff (
    StaffID INT PRIMARY KEY IDENTITY(1,1),
    Email NVARCHAR(255) NOT NULL UNIQUE,
    Password NVARCHAR(255) NOT NULL,
    FirstName NVARCHAR(100) NOT NULL,
    LastName NVARCHAR(100) NOT NULL,
    MiddleName NVARCHAR(100),
    PhoneNumber NVARCHAR(20),
    Position NVARCHAR(100) NOT NULL,
    Role NVARCHAR(100) NOT NULL,
    Department NVARCHAR(100),
    CreatedAt DATETIME DEFAULT GETDATE(),
    UpdatedAt DATETIME DEFAULT GETDATE(),
    IsActive BIT DEFAULT 1
);

-- Create index on Email for faster login queries
CREATE INDEX IX_Staff_Email ON Staff(Email);
CREATE INDEX IX_Staff_Role ON Staff(Role);
GO

-- =====================================================
-- 4. APPLICATIONS TABLE
-- =====================================================
CREATE TABLE Applications (
    ApplicationID INT PRIMARY KEY IDENTITY(1,1),
    ClientID INT NOT NULL,
    ServiceType NVARCHAR(100) NOT NULL, -- 'Barangay ID', 'Burial', 'Clearance', 'Complaint'
    Status NVARCHAR(50) DEFAULT 'Pending', -- 'Pending', 'Approved', 'Rejected', 'Processing'
    ApplicationData NVARCHAR(MAX), -- JSON format for flexible field storage
    CreatedAt DATETIME DEFAULT GETDATE(),
    UpdatedAt DATETIME DEFAULT GETDATE(),
    AssignedToStaffID INT,
    ProcessingNotes NVARCHAR(MAX),
    ApprovedBy INT,
    ApprovalDate DATETIME,
    FOREIGN KEY (ClientID) REFERENCES Clients(ClientID),
    FOREIGN KEY (AssignedToStaffID) REFERENCES Staff(StaffID),
    FOREIGN KEY (ApprovedBy) REFERENCES Staff(StaffID)
);

-- Create indexes for application queries
CREATE INDEX IX_Applications_ClientID ON Applications(ClientID);
CREATE INDEX IX_Applications_Status ON Applications(Status);
CREATE INDEX IX_Applications_ServiceType ON Applications(ServiceType);
CREATE INDEX IX_Applications_AssignedToStaffID ON Applications(AssignedToStaffID);
GO

-- =====================================================
-- 5. TRANSACTIONS TABLE (Finance Management)
-- =====================================================
CREATE TABLE Transactions (
    TransactionID INT PRIMARY KEY IDENTITY(1,1),
    TransactionDate DATE NOT NULL,
    TransactionType NVARCHAR(50) NOT NULL, -- 'Income', 'Expense', 'Transfer'
    Category NVARCHAR(100) NOT NULL, -- 'Permits', 'Services', 'Operations', 'Maintenance', 'Personnel', 'Supplies', 'Utilities', 'Other'
    Description NVARCHAR(500) NOT NULL,
    Amount DECIMAL(12,2) NOT NULL,
    Source NVARCHAR(100), -- 'Cash', 'Bank Account', etc.
    ReferenceNumber NVARCHAR(100), -- Receipt #, Invoice #, Check #
    CreatedBy INT NOT NULL, -- StaffID of the treasurer
    Status NVARCHAR(50) DEFAULT 'Pending', -- 'Pending', 'Approved', 'Rejected'
    ApprovedBy INT, -- StaffID who approved
    ApprovalDate DATETIME,
    Remarks NVARCHAR(MAX), -- Additional notes or reasons for rejection/approval
    CreatedAt DATETIME DEFAULT GETDATE(),
    UpdatedAt DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (CreatedBy) REFERENCES Staff(StaffID),
    FOREIGN KEY (ApprovedBy) REFERENCES Staff(StaffID)
);

-- Create indexes for transaction queries
CREATE INDEX IX_Transactions_TransactionDate ON Transactions(TransactionDate);
CREATE INDEX IX_Transactions_TransactionType ON Transactions(TransactionType);
CREATE INDEX IX_Transactions_Category ON Transactions(Category);
CREATE INDEX IX_Transactions_Status ON Transactions(Status);
CREATE INDEX IX_Transactions_CreatedBy ON Transactions(CreatedBy);
GO

-- =====================================================
-- 5. INSERT DEFAULT ADMIN
-- =====================================================
INSERT INTO Admins (Username, Password, FullName, Email, IsActive)
VALUES ('admin', 'admin123', 'System Administrator', 'admin@barangay.gov.ph', 1);
GO

-- =====================================================
-- 5B. INSERT DEFAULT STAFF ACCOUNTS
-- =====================================================
INSERT INTO Staff (Email, Password, FirstName, LastName, Position, Role, Department, IsActive)
VALUES 
    ('punong@barangay.gov.ph', 'staff123', 'Juan', 'Dela Cruz', 'Punong Barangay', 'Punong Barangay', 'Administration', 1),
    ('secretary@barangay.gov.ph', 'staff123', 'Maria', 'Santos', 'Barangay Secretary', 'Barangay Secretary', 'Administration', 1),
    ('treasurer@barangay.gov.ph', 'staff123', 'Pedro', 'Garcia', 'Barangay Treasurer', 'Barangay Treasurer', 'Finance', 1),
    ('sanggunian1@barangay.gov.ph', 'staff123', 'Ana', 'Reyes', 'Sangguniang Member', 'Sanggunian Member', 'Legislative', 1),
    ('sanggunian2@barangay.gov.ph', 'staff123', 'Luis', 'Lopez', 'Sangguniang Member', 'Sanggunian Member', 'Legislative', 1);
GO

-- =====================================================
-- 6. CREATE VIEWS FOR EASIER QUERIES
-- =====================================================

-- View for application statistics
CREATE VIEW vw_ApplicationStats AS
SELECT 
    COUNT(*) AS TotalApplications,
    SUM(CASE WHEN Status = 'Pending' THEN 1 ELSE 0 END) AS PendingCount,
    SUM(CASE WHEN Status = 'Processing' THEN 1 ELSE 0 END) AS ProcessingCount,
    SUM(CASE WHEN Status = 'Approved' THEN 1 ELSE 0 END) AS ApprovedCount,
    SUM(CASE WHEN Status = 'Rejected' THEN 1 ELSE 0 END) AS RejectedCount
FROM Applications;
GO

-- View for staff with their assigned applications
CREATE VIEW vw_StaffApplications AS
SELECT 
    s.StaffID,
    s.FirstName + ' ' + s.LastName AS StaffName,
    s.Role,
    COUNT(a.ApplicationID) AS AssignedApplications,
    SUM(CASE WHEN a.Status = 'Pending' THEN 1 ELSE 0 END) AS PendingApplications
FROM Staff s
LEFT JOIN Applications a ON s.StaffID = a.AssignedToStaffID
GROUP BY s.StaffID, s.FirstName, s.LastName, s.Role;
GO

-- =====================================================
-- 7. STORED PROCEDURES
-- =====================================================

-- Procedure to validate client login
CREATE PROCEDURE sp_ValidateClientLogin
    @Email NVARCHAR(255),
    @Password NVARCHAR(255)
AS
BEGIN
    SELECT ClientID, Email, FirstName, LastName, IsActive
    FROM Clients
    WHERE Email = @Email AND Password = @Password AND IsActive = 1;
END;
GO

-- Procedure to validate admin login
CREATE PROCEDURE sp_ValidateAdminLogin
    @Username NVARCHAR(100),
    @Password NVARCHAR(255)
AS
BEGIN
    SELECT AdminID, Username, FullName, IsActive
    FROM Admins
    WHERE Username = @Username AND Password = @Password AND IsActive = 1;
END;
GO

-- Procedure to validate staff login
CREATE PROCEDURE sp_ValidateStaffLogin
    @Email NVARCHAR(255),
    @Password NVARCHAR(255)
AS
BEGIN
    SELECT StaffID, Email, FirstName, LastName, Role, Position, IsActive
    FROM Staff
    WHERE Email = @Email AND Password = @Password AND IsActive = 1;
END;
GO

-- Procedure to register new client
CREATE PROCEDURE sp_RegisterClient
    @Email NVARCHAR(255),
    @Password NVARCHAR(255),
    @FirstName NVARCHAR(100),
    @LastName NVARCHAR(100),
    @MiddleName NVARCHAR(100) = NULL,
    @PhoneNumber NVARCHAR(20) = NULL
AS
BEGIN
    BEGIN TRY
        INSERT INTO Clients (Email, Password, FirstName, LastName, MiddleName, PhoneNumber)
        VALUES (@Email, @Password, @FirstName, @LastName, @MiddleName, @PhoneNumber);
        
        SELECT 'Success' AS Result, SCOPE_IDENTITY() AS ClientID;
    END TRY
    BEGIN CATCH
        SELECT 'Error' AS Result, ERROR_MESSAGE() AS Message;
    END CATCH
END;
GO

-- Procedure to check if email exists
CREATE PROCEDURE sp_CheckEmailExists
    @Email NVARCHAR(255),
    @UserType NVARCHAR(50) -- 'Client' or 'Staff'
AS
BEGIN
    IF @UserType = 'Client'
        SELECT COUNT(*) AS Count FROM Clients WHERE Email = @Email;
    ELSE IF @UserType = 'Staff'
        SELECT COUNT(*) AS Count FROM Staff WHERE Email = @Email;
END;
GO

-- Procedure to get client profile
CREATE PROCEDURE sp_GetClientProfile
    @ClientID INT
AS
BEGIN
    SELECT ClientID, Email, FirstName, LastName, MiddleName, PhoneNumber, 
           Address, BirthDate, Gender, CivilStatus, Occupation, CreatedAt, UpdatedAt
    FROM Clients
    WHERE ClientID = @ClientID AND IsActive = 1;
END;
GO

-- Procedure to update client profile
CREATE PROCEDURE sp_UpdateClientProfile
    @ClientID INT,
    @PhoneNumber NVARCHAR(20) = NULL,
    @Address NVARCHAR(255) = NULL,
    @BirthDate DATE = NULL,
    @Gender NVARCHAR(10) = NULL,
    @CivilStatus NVARCHAR(50) = NULL,
    @Occupation NVARCHAR(100) = NULL
AS
BEGIN
    UPDATE Clients
    SET PhoneNumber = ISNULL(@PhoneNumber, PhoneNumber),
        Address = ISNULL(@Address, Address),
        BirthDate = ISNULL(@BirthDate, BirthDate),
        Gender = ISNULL(@Gender, Gender),
        CivilStatus = ISNULL(@CivilStatus, CivilStatus),
        Occupation = ISNULL(@Occupation, Occupation),
        UpdatedAt = GETDATE()
    WHERE ClientID = @ClientID;
END;
GO

-- Procedure to create application
CREATE PROCEDURE sp_CreateApplication
    @ClientID INT,
    @ServiceType NVARCHAR(100),
    @ApplicationData NVARCHAR(MAX) = NULL
AS
BEGIN
    BEGIN TRY
        INSERT INTO Applications (ClientID, ServiceType, ApplicationData)
        VALUES (@ClientID, @ServiceType, @ApplicationData);
        
        SELECT 'Success' AS Result, SCOPE_IDENTITY() AS ApplicationID;
    END TRY
    BEGIN CATCH
        SELECT 'Error' AS Result, ERROR_MESSAGE() AS Message;
    END CATCH
END;
GO

-- Procedure to get client applications
CREATE PROCEDURE sp_GetClientApplications
    @ClientID INT
AS
BEGIN
    SELECT ApplicationID, ClientID, ServiceType, Status, CreatedAt, UpdatedAt, ProcessingNotes
    FROM Applications
    WHERE ClientID = @ClientID
    ORDER BY CreatedAt DESC;
END;
GO

-- Procedure to get application details
CREATE PROCEDURE sp_GetApplicationDetails
    @ApplicationID INT
AS
BEGIN
    SELECT ApplicationID, ClientID, ServiceType, Status, ApplicationData, 
           CreatedAt, UpdatedAt, AssignedToStaffID, ProcessingNotes, ApprovedBy, ApprovalDate
    FROM Applications
    WHERE ApplicationID = @ApplicationID;
END;
GO

-- Procedure to update application status
CREATE PROCEDURE sp_UpdateApplicationStatus
    @ApplicationID INT,
    @Status NVARCHAR(50),
    @ProcessingNotes NVARCHAR(MAX) = NULL,
    @ApprovedByStaffID INT = NULL
AS
BEGIN
    UPDATE Applications
    SET Status = @Status,
        ProcessingNotes = ISNULL(@ProcessingNotes, ProcessingNotes),
        ApprovedBy = ISNULL(@ApprovedByStaffID, ApprovedBy),
        ApprovalDate = CASE WHEN @Status = 'Approved' THEN GETDATE() ELSE ApprovalDate END,
        UpdatedAt = GETDATE()
    WHERE ApplicationID = @ApplicationID;
END;
GO

-- Procedure to assign application to staff
CREATE PROCEDURE sp_AssignApplicationToStaff
    @ApplicationID INT,
    @StaffID INT
AS
BEGIN
    UPDATE Applications
    SET AssignedToStaffID = @StaffID,
        UpdatedAt = GETDATE()
    WHERE ApplicationID = @ApplicationID;
END;
GO

-- Procedure to get all applications for admin
CREATE PROCEDURE sp_GetAllApplications
    @Status NVARCHAR(50) = NULL
AS
BEGIN
    SELECT a.ApplicationID, a.ClientID, a.ServiceType, a.Status, 
           c.FirstName + ' ' + c.LastName AS ClientName, c.Email AS ClientEmail,
           a.CreatedAt, a.UpdatedAt, a.AssignedToStaffID,
           ISNULL(s.FirstName + ' ' + s.LastName, 'Unassigned') AS AssignedStaff
    FROM Applications a
    JOIN Clients c ON a.ClientID = c.ClientID
    LEFT JOIN Staff s ON a.AssignedToStaffID = s.StaffID
    WHERE (@Status IS NULL OR a.Status = @Status)
    ORDER BY a.CreatedAt DESC;
END;
GO

-- Procedure to get staff members
CREATE PROCEDURE sp_GetStaffMembers
    @Role NVARCHAR(100) = NULL
AS
BEGIN
    SELECT StaffID, FirstName, LastName, Email, Position, Role, Department, IsActive, CreatedAt
    FROM Staff
    WHERE (@Role IS NULL OR Role = @Role) AND IsActive = 1
    ORDER BY FirstName, LastName;
END;
GO

-- Procedure to get pending applications for staff
CREATE PROCEDURE sp_GetPendingApplicationsForStaff
    @StaffID INT
AS
BEGIN
    SELECT a.ApplicationID, a.ClientID, a.ServiceType, a.Status,
           c.FirstName + ' ' + c.LastName AS ClientName, c.Email AS ClientEmail,
           a.CreatedAt, a.ApplicationData
    FROM Applications a
    JOIN Clients c ON a.ClientID = c.ClientID
    WHERE (a.AssignedToStaffID = @StaffID OR a.AssignedToStaffID IS NULL) 
      AND a.Status IN ('Pending', 'Processing')
    ORDER BY a.CreatedAt ASC;
END;
GO

PRINT '========================================';
PRINT 'Database Setup Complete!';
PRINT '========================================';
PRINT 'Tables Created:';
PRINT '  - Clients';
PRINT '  - Admins (with default admin account)';
PRINT '  - Staff';
PRINT '  - Applications';
PRINT '';
PRINT 'Stored Procedures Created:';
PRINT '  - sp_ValidateClientLogin';
PRINT '  - sp_ValidateAdminLogin';
PRINT '  - sp_ValidateStaffLogin';
PRINT '  - sp_RegisterClient';
PRINT '  - sp_CheckEmailExists';
PRINT '  - sp_GetClientProfile';
PRINT '  - sp_UpdateClientProfile';
PRINT '  - sp_CreateApplication';
PRINT '  - sp_GetClientApplications';
PRINT '  - sp_GetApplicationDetails';
PRINT '  - sp_UpdateApplicationStatus';
PRINT '  - sp_AssignApplicationToStaff';
PRINT '  - sp_GetAllApplications';
PRINT '  - sp_GetStaffMembers';
PRINT '  - sp_GetPendingApplicationsForStaff';
PRINT '';
PRINT 'Views Created:';
PRINT '  - vw_ApplicationStats';
PRINT '  - vw_StaffApplications';
PRINT '========================================';
