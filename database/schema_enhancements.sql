-- =====================================================
-- Barangay e-Services - DATABASE ENHANCEMENTS
-- SQL Server Database Schema Additions (T-SQL)
-- Run this AFTER the main schema.sql
-- =====================================================

USE BarangayEServices;
GO

-- =====================================================
-- 1. PERMISSIONS TABLE (Role-Based Access Control)
-- =====================================================
CREATE TABLE Permissions (
    PermissionID INT PRIMARY KEY IDENTITY(1,1),
    PermissionName NVARCHAR(100) NOT NULL UNIQUE,
    PermissionDescription NVARCHAR(255),
    CreatedAt DATETIME DEFAULT GETDATE()
);
GO

-- =====================================================
-- 2. ROLE_PERMISSIONS TABLE (Many-to-Many)
-- =====================================================
CREATE TABLE RolePermissions (
    RolePermissionID INT PRIMARY KEY IDENTITY(1,1),
    Role NVARCHAR(100) NOT NULL,
    PermissionID INT NOT NULL,
    GrantedAt DATETIME DEFAULT GETDATE(),
    GrantedBy INT, -- StaffID of who granted it
    FOREIGN KEY (PermissionID) REFERENCES Permissions(PermissionID),
    FOREIGN KEY (GrantedBy) REFERENCES Staff(StaffID),
    UNIQUE (Role, PermissionID)
);

CREATE INDEX IX_RolePermissions_Role ON RolePermissions(Role);
GO

-- =====================================================
-- 3. AUDIT_LOG TABLE (Track all changes)
-- =====================================================
CREATE TABLE AuditLog (
    AuditID INT PRIMARY KEY IDENTITY(1,1),
    UserID INT, -- Can be ClientID, StaffID, or AdminID
    UserType NVARCHAR(50), -- 'Client', 'Staff', 'Admin'
    Action NVARCHAR(255) NOT NULL,
    TableName NVARCHAR(128),
    RecordID INT,
    OldValues NVARCHAR(MAX), -- JSON format
    NewValues NVARCHAR(MAX), -- JSON format
    IPAddress NVARCHAR(45),
    ActionDate DATETIME DEFAULT GETDATE()
);

CREATE INDEX IX_AuditLog_UserID ON AuditLog(UserID);
CREATE INDEX IX_AuditLog_ActionDate ON AuditLog(ActionDate);
CREATE INDEX IX_AuditLog_TableName ON AuditLog(TableName);
GO

-- =====================================================
-- 4. BUDGET_ALLOCATION TABLE (Finance Management)
-- =====================================================
CREATE TABLE BudgetAllocation (
    BudgetID INT PRIMARY KEY IDENTITY(1,1),
    FiscalYear INT NOT NULL,
    Category NVARCHAR(100) NOT NULL, -- 'Permits', 'Services', 'Operations', 'Maintenance', 'Personnel', 'Supplies', 'Utilities', 'Emergency'
    AllocatedAmount DECIMAL(12,2) NOT NULL,
    SpentAmount DECIMAL(12,2) DEFAULT 0,
    RemainingAmount DECIMAL(12,2),
    AllocatedBy INT NOT NULL, -- StaffID
    AllocatedDate DATETIME DEFAULT GETDATE(),
    LastUpdated DATETIME DEFAULT GETDATE(),
    Notes NVARCHAR(MAX),
    FOREIGN KEY (AllocatedBy) REFERENCES Staff(StaffID)
);

CREATE INDEX IX_BudgetAllocation_FiscalYear ON BudgetAllocation(FiscalYear);
CREATE INDEX IX_BudgetAllocation_Category ON BudgetAllocation(Category);
GO

-- =====================================================
-- 5. MESSAGES TABLE (Internal Communication)
-- =====================================================
CREATE TABLE Messages (
    MessageID INT PRIMARY KEY IDENTITY(1,1),
    SenderID INT NOT NULL, -- StaffID
    RecipientID INT NOT NULL, -- StaffID or NULL for general broadcasts
    MessageType NVARCHAR(50), -- 'Personal', 'Broadcast', 'System'
    Subject NVARCHAR(255),
    MessageBody NVARCHAR(MAX),
    IsRead BIT DEFAULT 0,
    RelatedApplicationID INT, -- Link to application if applicable
    CreatedAt DATETIME DEFAULT GETDATE(),
    ReadAt DATETIME,
    FOREIGN KEY (SenderID) REFERENCES Staff(StaffID),
    FOREIGN KEY (RecipientID) REFERENCES Staff(StaffID),
    FOREIGN KEY (RelatedApplicationID) REFERENCES Applications(ApplicationID)
);

CREATE INDEX IX_Messages_RecipientID ON Messages(RecipientID);
CREATE INDEX IX_Messages_SenderID ON Messages(SenderID);
CREATE INDEX IX_Messages_CreatedAt ON Messages(CreatedAt);
GO

-- =====================================================
-- 6. APPLICATION_REMARKS TABLE (Track processing notes)
-- =====================================================
CREATE TABLE ApplicationRemarks (
    RemarkID INT PRIMARY KEY IDENTITY(1,1),
    ApplicationID INT NOT NULL,
    StaffID INT NOT NULL,
    RemarkText NVARCHAR(MAX) NOT NULL,
    RemarkType NVARCHAR(50), -- 'Note', 'Approval', 'Rejection', 'Request'
    IsInternal BIT DEFAULT 1, -- If 0, client can see it
    CreatedAt DATETIME DEFAULT GETDATE(),
    FOREIGN KEY (ApplicationID) REFERENCES Applications(ApplicationID) ON DELETE CASCADE,
    FOREIGN KEY (StaffID) REFERENCES Staff(StaffID)
);

CREATE INDEX IX_ApplicationRemarks_ApplicationID ON ApplicationRemarks(ApplicationID);
CREATE INDEX IX_ApplicationRemarks_CreatedAt ON ApplicationRemarks(CreatedAt);
GO

-- =====================================================
-- 7. SESSIONS TABLE (Track active sessions)
-- =====================================================
CREATE TABLE UserSessions (
    SessionID INT PRIMARY KEY IDENTITY(1,1),
    UserID INT NOT NULL,
    UserType NVARCHAR(50) NOT NULL, -- 'Client', 'Staff', 'Admin'
    SessionToken NVARCHAR(255) NOT NULL UNIQUE,
    IPAddress NVARCHAR(45),
    UserAgent NVARCHAR(500),
    LoginTime DATETIME NOT NULL,
    LastActivityTime DATETIME NOT NULL,
    LogoutTime DATETIME,
    IsActive BIT DEFAULT 1
);

CREATE INDEX IX_UserSessions_SessionToken ON UserSessions(SessionToken);
CREATE INDEX IX_UserSessions_UserID ON UserSessions(UserID);
GO

-- =====================================================
-- 8. INSERT DEFAULT PERMISSIONS
-- =====================================================
INSERT INTO Permissions (PermissionName, PermissionDescription)
VALUES 
    ('view_applications', 'View applications'),
    ('approve_applications', 'Approve or reject applications'),
    ('edit_records', 'Edit client and application records'),
    ('view_budget', 'View budget information'),
    ('manage_budget', 'Manage budget allocations'),
    ('send_messages', 'Send internal messages'),
    ('view_transactions', 'View financial transactions'),
    ('record_transactions', 'Record new transactions'),
    ('manage_staff', 'Manage staff members'),
    ('assign_roles', 'Assign roles and permissions'),
    ('view_reports', 'View reports'),
    ('generate_reports', 'Generate custom reports'),
    ('add_remarks', 'Add remarks to applications'),
    ('view_audit_log', 'View audit logs');
GO

-- =====================================================
-- 9. ASSIGN DEFAULT PERMISSIONS BY ROLE
-- =====================================================
INSERT INTO RolePermissions (Role, PermissionID)
SELECT 'Punong Barangay', PermissionID FROM Permissions WHERE PermissionName IN (
    'view_applications', 'approve_applications', 'edit_records', 'view_budget', 'manage_budget',
    'send_messages', 'view_transactions', 'record_transactions', 'manage_staff', 'assign_roles',
    'view_reports', 'generate_reports', 'add_remarks', 'view_audit_log'
);

INSERT INTO RolePermissions (Role, PermissionID)
SELECT 'Barangay Secretary', PermissionID FROM Permissions WHERE PermissionName IN (
    'view_applications', 'approve_applications', 'edit_records', 'send_messages',
    'view_reports', 'add_remarks'
);

INSERT INTO RolePermissions (Role, PermissionID)
SELECT 'Barangay Treasurer', PermissionID FROM Permissions WHERE PermissionName IN (
    'view_applications', 'view_budget', 'manage_budget', 'send_messages',
    'view_transactions', 'record_transactions', 'view_reports', 'generate_reports', 'add_remarks'
);

INSERT INTO RolePermissions (Role, PermissionID)
SELECT 'Sanggunian Member', PermissionID FROM Permissions WHERE PermissionName IN (
    'view_applications', 'view_budget', 'view_reports'
);
GO

-- =====================================================
-- 10. NEW STORED PROCEDURES FOR ENHANCEMENTS
-- =====================================================

-- Get all permissions for a role
CREATE PROCEDURE sp_GetRolePermissions
    @Role NVARCHAR(100)
AS
BEGIN
    SELECT p.PermissionID, p.PermissionName, p.PermissionDescription
    FROM RolePermissions rp
    JOIN Permissions p ON rp.PermissionID = p.PermissionID
    WHERE rp.Role = @Role;
END;
GO

-- Check if user has permission
CREATE PROCEDURE sp_HasPermission
    @StaffID INT,
    @PermissionName NVARCHAR(100)
AS
BEGIN
    SELECT COUNT(*) AS HasPermission
    FROM Staff s
    JOIN RolePermissions rp ON s.Role = rp.Role
    JOIN Permissions p ON rp.PermissionID = p.PermissionID
    WHERE s.StaffID = @StaffID AND p.PermissionName = @PermissionName;
END;
GO

-- Record audit log entry
CREATE PROCEDURE sp_LogAuditEvent
    @UserID INT,
    @UserType NVARCHAR(50),
    @Action NVARCHAR(255),
    @TableName NVARCHAR(128),
    @RecordID INT,
    @OldValues NVARCHAR(MAX) = NULL,
    @NewValues NVARCHAR(MAX) = NULL,
    @IPAddress NVARCHAR(45) = NULL
AS
BEGIN
    INSERT INTO AuditLog (UserID, UserType, Action, TableName, RecordID, OldValues, NewValues, IPAddress)
    VALUES (@UserID, @UserType, @Action, @TableName, @RecordID, @OldValues, @NewValues, @IPAddress);
END;
GO

-- Get budget summary for fiscal year
CREATE PROCEDURE sp_GetBudgetSummary
    @FiscalYear INT
AS
BEGIN
    SELECT 
        Category,
        AllocatedAmount,
        SpentAmount,
        (AllocatedAmount - SpentAmount) AS RemainingAmount,
        CAST((SpentAmount * 100.0 / AllocatedAmount) AS DECIMAL(5,2)) AS PercentageSpent
    FROM BudgetAllocation
    WHERE FiscalYear = @FiscalYear
    ORDER BY Category;
END;
GO

-- Update budget spent amount
CREATE PROCEDURE sp_UpdateBudgetSpent
    @BudgetID INT,
    @AmountToAdd DECIMAL(12,2)
AS
BEGIN
    UPDATE BudgetAllocation
    SET SpentAmount = SpentAmount + @AmountToAdd,
        RemainingAmount = AllocatedAmount - (SpentAmount + @AmountToAdd),
        LastUpdated = GETDATE()
    WHERE BudgetID = @BudgetID;
END;
GO

-- Send message
CREATE PROCEDURE sp_SendMessage
    @SenderID INT,
    @RecipientID INT,
    @Subject NVARCHAR(255),
    @MessageBody NVARCHAR(MAX),
    @MessageType NVARCHAR(50) = 'Personal',
    @RelatedApplicationID INT = NULL
AS
BEGIN
    INSERT INTO Messages (SenderID, RecipientID, MessageType, Subject, MessageBody, RelatedApplicationID)
    VALUES (@SenderID, @RecipientID, @MessageType, @Subject, @MessageBody, @RelatedApplicationID);
    
    SELECT 'Message sent successfully' AS Result;
END;
GO

-- Get unread messages for user
CREATE PROCEDURE sp_GetUnreadMessages
    @StaffID INT
AS
BEGIN
    SELECT MessageID, SenderID, Subject, MessageBody, CreatedAt, RelatedApplicationID
    FROM Messages
    WHERE RecipientID = @StaffID AND IsRead = 0
    ORDER BY CreatedAt DESC;
END;
GO

-- Mark message as read
CREATE PROCEDURE sp_MarkMessageAsRead
    @MessageID INT
AS
BEGIN
    UPDATE Messages
    SET IsRead = 1, ReadAt = GETDATE()
    WHERE MessageID = @MessageID;
END;
GO

-- Add application remark
CREATE PROCEDURE sp_AddApplicationRemark
    @ApplicationID INT,
    @StaffID INT,
    @RemarkText NVARCHAR(MAX),
    @RemarkType NVARCHAR(50) = 'Note',
    @IsInternal BIT = 1
AS
BEGIN
    INSERT INTO ApplicationRemarks (ApplicationID, StaffID, RemarkText, RemarkType, IsInternal)
    VALUES (@ApplicationID, @StaffID, @RemarkText, @RemarkType, @IsInternal);
    
    SELECT 'Remark added successfully' AS Result;
END;
GO

-- Get application remarks
CREATE PROCEDURE sp_GetApplicationRemarks
    @ApplicationID INT,
    @ShowInternalOnly BIT = 0
AS
BEGIN
    SELECT r.RemarkID, r.StaffID, s.FirstName + ' ' + s.LastName AS StaffName,
           r.RemarkText, r.RemarkType, r.IsInternal, r.CreatedAt
    FROM ApplicationRemarks r
    JOIN Staff s ON r.StaffID = s.StaffID
    WHERE r.ApplicationID = @ApplicationID
      AND (@ShowInternalOnly = 0 OR r.IsInternal = 1)
    ORDER BY r.CreatedAt DESC;
END;
GO

-- Get staff by role for assignment
CREATE PROCEDURE sp_GetStaffByRole
    @Role NVARCHAR(100)
AS
BEGIN
    SELECT StaffID, FirstName, LastName, Email, Position, Role
    FROM Staff
    WHERE Role = @Role AND IsActive = 1
    ORDER BY FirstName, LastName;
END;
GO

-- Get dashboard statistics for staff
CREATE PROCEDURE sp_GetStaffDashboardStats
    @StaffID INT
AS
BEGIN
    DECLARE @StaffRole NVARCHAR(100);
    SELECT @StaffRole = Role FROM Staff WHERE StaffID = @StaffID;
    
    SELECT 
        (SELECT COUNT(*) FROM Applications WHERE Status = 'Pending') AS PendingCount,
        (SELECT COUNT(*) FROM Applications WHERE Status = 'Processing') AS ProcessingCount,
        (SELECT COUNT(*) FROM Applications WHERE Status = 'Approved') AS ApprovedCount,
        (SELECT COUNT(*) FROM Staff WHERE IsActive = 1) AS TotalStaff,
        (SELECT COUNT(*) FROM Messages WHERE RecipientID = @StaffID AND IsRead = 0) AS UnreadMessages,
        (SELECT COUNT(*) FROM Transactions WHERE CreatedAt >= DATEADD(MONTH, -1, GETDATE())) AS MonthlyTransactions;
END;
GO

-- Create application with complete data
CREATE PROCEDURE sp_CreateApplicationComplete
    @ClientID INT,
    @ServiceType NVARCHAR(100),
    @FullName NVARCHAR(200),
    @BirthDate DATE,
    @Gender NVARCHAR(10),
    @Address NVARCHAR(255),
    @ContactNumber NVARCHAR(20),
    @CivilStatus NVARCHAR(50),
    @Purpose NVARCHAR(500) = NULL,
    @AdditionalData NVARCHAR(MAX) = NULL
AS
BEGIN
    BEGIN TRY
        DECLARE @ApplicationData NVARCHAR(MAX);
        
        -- Build JSON data
        SET @ApplicationData = JSON_QUERY(
            (SELECT 
                @FullName AS full_name,
                @BirthDate AS birth_date,
                @Gender AS gender,
                @Address AS address,
                @ContactNumber AS contact_number,
                @CivilStatus AS civil_status,
                @Purpose AS purpose,
                @AdditionalData AS additional_data
            FOR JSON PATH, WITHOUT_ARRAY_WRAPPER
            )
        );
        
        INSERT INTO Applications (ClientID, ServiceType, ApplicationData, Status)
        VALUES (@ClientID, @ServiceType, @ApplicationData, 'Pending');
        
        DECLARE @ApplicationID INT = SCOPE_IDENTITY();
        
        -- Log audit
        EXEC sp_LogAuditEvent 
            @UserID = @ClientID,
            @UserType = 'Client',
            @Action = 'Created Application',
            @TableName = 'Applications',
            @RecordID = @ApplicationID;
        
        SELECT 'Success' AS Result, @ApplicationID AS ApplicationID;
    END TRY
    BEGIN CATCH
        SELECT 'Error' AS Result, ERROR_MESSAGE() AS Message;
    END CATCH
END;
GO

-- Update staff role (with audit logging)
CREATE PROCEDURE sp_UpdateStaffRole
    @StaffID INT,
    @NewRole NVARCHAR(100),
    @UpdatedByStaffID INT,
    @Notes NVARCHAR(MAX) = NULL
AS
BEGIN
    BEGIN TRY
        DECLARE @OldRole NVARCHAR(100);
        SELECT @OldRole = Role FROM Staff WHERE StaffID = @StaffID;
        
        UPDATE Staff
        SET Role = @NewRole, UpdatedAt = GETDATE()
        WHERE StaffID = @StaffID;
        
        -- Log audit
        EXEC sp_LogAuditEvent 
            @UserID = @UpdatedByStaffID,
            @UserType = 'Staff',
            @Action = 'Updated Staff Role',
            @TableName = 'Staff',
            @RecordID = @StaffID,
            @OldValues = @OldRole,
            @NewValues = @NewRole;
        
        SELECT 'Success' AS Result, 'Staff role updated successfully' AS Message;
    END TRY
    BEGIN CATCH
        SELECT 'Error' AS Result, ERROR_MESSAGE() AS Message;
    END CATCH
END;
GO

-- Get transaction report by category
CREATE PROCEDURE sp_GetTransactionReportByCategory
    @FiscalYear INT
AS
BEGIN
    SELECT 
        Category,
        TransactionType,
        COUNT(*) AS TransactionCount,
        SUM(Amount) AS TotalAmount,
        AVG(Amount) AS AverageAmount
    FROM Transactions
    WHERE YEAR(TransactionDate) = @FiscalYear
    GROUP BY Category, TransactionType
    ORDER BY Category, TransactionType;
END;
GO

-- Get application processing time statistics
CREATE PROCEDURE sp_GetApplicationProcessingStats
AS
BEGIN
    SELECT 
        ServiceType,
        COUNT(*) AS TotalApplications,
        AVG(DATEDIFF(DAY, CreatedAt, ISNULL(ApprovalDate, GETDATE()))) AS AverageDaysToComplete,
        MAX(DATEDIFF(DAY, CreatedAt, ISNULL(ApprovalDate, GETDATE()))) AS MaxDaysToComplete,
        MIN(DATEDIFF(DAY, CreatedAt, ISNULL(ApprovalDate, GETDATE()))) AS MinDaysToComplete
    FROM Applications
    WHERE Status = 'Approved'
    GROUP BY ServiceType;
END;
GO

PRINT '========================================';
PRINT 'Database Enhancements Complete!';
PRINT '========================================';
PRINT 'New Tables Added:';
PRINT '  - Permissions';
PRINT '  - RolePermissions';
PRINT '  - AuditLog';
PRINT '  - BudgetAllocation';
PRINT '  - Messages';
PRINT '  - ApplicationRemarks';
PRINT '  - UserSessions';
PRINT '';
PRINT 'Sample Data Inserted:';
PRINT '  - Default Permissions (14)';
PRINT '  - Role Permissions Mappings (4 roles)';
PRINT '';
PRINT 'New Stored Procedures Created:';
PRINT '  - sp_GetRolePermissions';
PRINT '  - sp_HasPermission';
PRINT '  - sp_LogAuditEvent';
PRINT '  - sp_GetBudgetSummary';
PRINT '  - sp_UpdateBudgetSpent';
PRINT '  - sp_SendMessage';
PRINT '  - sp_GetUnreadMessages';
PRINT '  - sp_MarkMessageAsRead';
PRINT '  - sp_AddApplicationRemark';
PRINT '  - sp_GetApplicationRemarks';
PRINT '  - sp_GetStaffByRole';
PRINT '  - sp_GetStaffDashboardStats';
PRINT '  - sp_CreateApplicationComplete';
PRINT '  - sp_UpdateStaffRole';
PRINT '  - sp_GetTransactionReportByCategory';
PRINT '  - sp_GetApplicationProcessingStats';
PRINT '========================================';
