-- =====================================================
-- Barangay e-Services - SQL Query Reference
-- Common queries for testing, debugging, and reporting
-- Use these in SSMS to verify database functionality
-- =====================================================

USE BarangayEServices;
GO

-- =====================================================
-- 1. VERIFICATION QUERIES
-- =====================================================

-- Verify all new tables exist
SELECT TABLE_NAME 
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'dbo' 
ORDER BY TABLE_NAME;
GO

-- Verify all new stored procedures exist
SELECT ROUTINE_NAME, ROUTINE_TYPE
FROM INFORMATION_SCHEMA.ROUTINES
WHERE ROUTINE_SCHEMA = 'dbo' AND ROUTINE_TYPE = 'PROCEDURE'
ORDER BY ROUTINE_NAME;
GO

-- Verify all new functions/views exist
SELECT TABLE_NAME 
FROM INFORMATION_SCHEMA.VIEWS
WHERE TABLE_SCHEMA = 'dbo'
ORDER BY TABLE_NAME;
GO

-- =====================================================
-- 2. APPLICATION TESTING QUERIES
-- =====================================================

-- Get all applications with client details
SELECT 
    a.ApplicationID,
    a.ServiceType,
    a.Status,
    CONCAT(c.FirstName, ' ', c.LastName) AS ClientName,
    c.Email,
    a.CreatedAt,
    a.UpdatedAt
FROM Applications a
JOIN Clients c ON a.ClientID = c.ClientID
ORDER BY a.CreatedAt DESC;
GO

-- Get applications by status
SELECT 
    Status,
    COUNT(*) AS Count,
    CONVERT(VARCHAR, MIN(CreatedAt), 101) AS OldestDate
FROM Applications
GROUP BY Status
ORDER BY Count DESC;
GO

-- Get applications by service type
SELECT 
    ServiceType,
    COUNT(*) AS Count,
    SUM(CASE WHEN Status = 'Pending' THEN 1 ELSE 0 END) AS PendingCount,
    SUM(CASE WHEN Status = 'Approved' THEN 1 ELSE 0 END) AS ApprovedCount
FROM Applications
GROUP BY ServiceType
ORDER BY Count DESC;
GO

-- Get application processing statistics
SELECT 
    ServiceType,
    COUNT(*) AS TotalApplications,
    CAST(AVG(DATEDIFF(DAY, CreatedAt, ISNULL(ApprovalDate, GETDATE()))) AS INT) AS AverageDaysToProcess,
    MAX(DATEDIFF(DAY, CreatedAt, ISNULL(ApprovalDate, GETDATE()))) AS MaxDays,
    MIN(DATEDIFF(DAY, CreatedAt, ISNULL(ApprovalDate, GETDATE()))) AS MinDays
FROM Applications
WHERE Status = 'Approved'
GROUP BY ServiceType;
GO

-- Get pending applications requiring action
SELECT 
    a.ApplicationID,
    CONCAT(c.FirstName, ' ', c.LastName) AS ClientName,
    a.ServiceType,
    a.CreatedAt,
    DATEDIFF(DAY, a.CreatedAt, GETDATE()) AS DaysPending,
    ISNULL(CONCAT(s.FirstName, ' ', s.LastName), 'UNASSIGNED') AS AssignedStaff
FROM Applications a
JOIN Clients c ON a.ClientID = c.ClientID
LEFT JOIN Staff s ON a.AssignedToStaffID = s.StaffID
WHERE a.Status = 'Pending'
ORDER BY a.CreatedAt ASC;
GO

-- Get application with JSON data
SELECT 
    ApplicationID,
    JSON_VALUE(ApplicationData, '$.full_name') AS FullName,
    JSON_VALUE(ApplicationData, '$.birth_date') AS BirthDate,
    JSON_VALUE(ApplicationData, '$.address') AS Address,
    JSON_VALUE(ApplicationData, '$.contact_number') AS ContactNumber
FROM Applications
WHERE ApplicationID = 1;  -- Change to test different applications
GO

-- =====================================================
-- 3. MESSAGES & COMMUNICATION QUERIES
-- =====================================================

-- Get all unread messages by recipient
SELECT 
    RecipientID,
    CONCAT(s.FirstName, ' ', s.LastName) AS Recipient,
    COUNT(*) AS UnreadCount
FROM Messages m
JOIN Staff s ON m.RecipientID = s.StaffID
WHERE m.IsRead = 0
GROUP BY RecipientID, CONCAT(s.FirstName, ' ', s.LastName);
GO

-- Get message thread for specific recipient
SELECT 
    MessageID,
    CONCAT(s.FirstName, ' ', s.LastName) AS From,
    Subject,
    MessageBody,
    CreatedAt,
    IsRead
FROM Messages m
JOIN Staff s ON m.SenderID = s.StaffID
WHERE m.RecipientID = 1  -- Change to recipient staff ID
ORDER BY CreatedAt DESC;
GO

-- Get messages related to specific application
SELECT 
    m.MessageID,
    CONCAT(s.FirstName, ' ', s.LastName) AS From,
    m.Subject,
    m.CreatedAt
FROM Messages m
JOIN Staff s ON m.SenderID = s.StaffID
WHERE m.RelatedApplicationID = 1  -- Change to application ID
ORDER BY m.CreatedAt DESC;
GO

-- =====================================================
-- 4. STAFF & PERMISSIONS QUERIES
-- =====================================================

-- Get all staff with their roles
SELECT 
    StaffID,
    CONCAT(FirstName, ' ', LastName) AS StaffName,
    Email,
    Role,
    Position,
    Department,
    IsActive,
    CreatedAt
FROM Staff
ORDER BY FirstName, LastName;
GO

-- Get staff and their assigned applications
SELECT 
    s.StaffID,
    CONCAT(s.FirstName, ' ', s.LastName) AS StaffName,
    s.Role,
    COUNT(a.ApplicationID) AS AssignedApplications,
    SUM(CASE WHEN a.Status = 'Pending' THEN 1 ELSE 0 END) AS PendingApps
FROM Staff s
LEFT JOIN Applications a ON s.StaffID = a.AssignedToStaffID
WHERE s.IsActive = 1
GROUP BY s.StaffID, CONCAT(s.FirstName, ' ', s.LastName), s.Role
ORDER BY s.FirstName;
GO

-- Get permissions for specific role
SELECT 
    rp.Role,
    p.PermissionName,
    p.PermissionDescription
FROM RolePermissions rp
JOIN Permissions p ON rp.PermissionID = p.PermissionID
WHERE rp.Role = 'Barangay Secretary'  -- Change role as needed
ORDER BY p.PermissionName;
GO

-- Get all roles and their permission counts
SELECT 
    rp.Role,
    COUNT(DISTINCT rp.PermissionID) AS PermissionCount
FROM RolePermissions rp
GROUP BY rp.Role
ORDER BY PermissionCount DESC;
GO

-- Get staff who can approve applications
SELECT 
    s.StaffID,
    CONCAT(s.FirstName, ' ', s.LastName) AS StaffName,
    s.Role,
    COUNT(a.ApplicationID) AS AppsToReview
FROM Staff s
LEFT JOIN Applications a ON s.StaffID = a.AssignedToStaffID AND a.Status IN ('Pending', 'Processing')
WHERE s.IsActive = 1 
  AND EXISTS (
      SELECT 1 FROM RolePermissions rp
      JOIN Permissions p ON rp.PermissionID = p.PermissionID
      WHERE rp.Role = s.Role AND p.PermissionName = 'approve_applications'
  )
GROUP BY s.StaffID, CONCAT(s.FirstName, ' ', s.LastName), s.Role;
GO

-- =====================================================
-- 5. FINANCIAL TRACKING QUERIES
-- =====================================================

-- Get budget vs actual spending
SELECT 
    FiscalYear,
    Category,
    AllocatedAmount,
    SpentAmount,
    (AllocatedAmount - SpentAmount) AS RemainingAmount,
    CAST((SpentAmount * 100.0 / AllocatedAmount) AS DECIMAL(5,2)) AS PercentSpent
FROM BudgetAllocation
ORDER BY FiscalYear DESC, Category;
GO

-- Get transaction summary by category
SELECT 
    YEAR(TransactionDate) AS Year,
    MONTH(TransactionDate) AS Month,
    Category,
    TransactionType,
    COUNT(*) AS TransactionCount,
    SUM(Amount) AS TotalAmount
FROM Transactions
WHERE Status = 'Approved'
GROUP BY YEAR(TransactionDate), MONTH(TransactionDate), Category, TransactionType
ORDER BY Year DESC, Month DESC, Category;
GO

-- Get monthly financial report
SELECT 
    CONVERT(VARCHAR(7), TransactionDate, 120) AS Month,
    'Income' AS Type,
    SUM(Amount) AS Total
FROM Transactions
WHERE TransactionType = 'Income' AND Status = 'Approved'
GROUP BY CONVERT(VARCHAR(7), TransactionDate, 120)
UNION ALL
SELECT 
    CONVERT(VARCHAR(7), TransactionDate, 120) AS Month,
    'Expense' AS Type,
    SUM(Amount) AS Total
FROM Transactions
WHERE TransactionType = 'Expense' AND Status = 'Approved'
GROUP BY CONVERT(VARCHAR(7), TransactionDate, 120)
ORDER BY Month DESC, Type;
GO

-- Get pending transaction approvals
SELECT 
    TransactionID,
    TransactionDate,
    Category,
    Amount,
    Description,
    CONCAT(s.FirstName, ' ', s.LastName) AS CreatedBy
FROM Transactions t
JOIN Staff s ON t.CreatedBy = s.StaffID
WHERE t.Status = 'Pending'
ORDER BY t.TransactionDate ASC;
GO

-- =====================================================
-- 6. AUDIT LOG QUERIES
-- =====================================================

-- Get recent audit log entries
SELECT TOP 50
    AuditID,
    CASE UserType
        WHEN 'Client' THEN CONCAT('Client #', UserID)
        WHEN 'Staff' THEN CONCAT('Staff: ', (SELECT CONCAT(FirstName, ' ', LastName) FROM Staff WHERE StaffID = UserID))
        WHEN 'Admin' THEN 'Admin'
    END AS User,
    Action,
    TableName,
    ActionDate,
    IPAddress
FROM AuditLog
ORDER BY ActionDate DESC;
GO

-- Get audit log for specific table
SELECT 
    AuditID,
    Action,
    RecordID,
    OldValues,
    NewValues,
    ActionDate
FROM AuditLog
WHERE TableName = 'Applications'  -- Change table name as needed
ORDER BY ActionDate DESC;
GO

-- Get all changes made by specific user
SELECT 
    AuditID,
    Action,
    TableName,
    RecordID,
    ActionDate
FROM AuditLog
WHERE UserID = 1 AND UserType = 'Staff'  -- Change UserID as needed
ORDER BY ActionDate DESC;
GO

-- Get application history (all changes)
SELECT 
    a.AuditID,
    a.Action,
    a.ActionDate,
    CASE UserType
        WHEN 'Staff' THEN (SELECT CONCAT(FirstName, ' ', LastName) FROM Staff WHERE StaffID = a.UserID)
    END AS ChangedBy,
    a.OldValues,
    a.NewValues
FROM AuditLog a
WHERE a.TableName = 'Applications' AND a.RecordID = 1  -- Change to application ID
ORDER BY a.ActionDate DESC;
GO

-- =====================================================
-- 7. APPLICATION REMARKS QUERIES
-- =====================================================

-- Get remarks on specific application
SELECT 
    r.RemarkID,
    CONCAT(s.FirstName, ' ', s.LastName) AS StaffName,
    r.RemarkType,
    r.RemarkText,
    r.CreatedAt,
    r.IsInternal
FROM ApplicationRemarks r
JOIN Staff s ON r.StaffID = s.StaffID
WHERE r.ApplicationID = 1  -- Change to application ID
ORDER BY r.CreatedAt DESC;
GO

-- Get all internal notes (not visible to clients)
SELECT 
    r.RemarkID,
    a.ApplicationID,
    CONCAT(c.FirstName, ' ', c.LastName) AS ClientName,
    r.RemarkText,
    r.CreatedAt
FROM ApplicationRemarks r
JOIN Applications a ON r.ApplicationID = a.ApplicationID
JOIN Clients c ON a.ClientID = c.ClientID
WHERE r.IsInternal = 1
ORDER BY r.CreatedAt DESC;
GO

-- =====================================================
-- 8. CLIENT QUERIES
-- =====================================================

-- Get most active clients
SELECT 
    c.ClientID,
    CONCAT(c.FirstName, ' ', c.LastName) AS ClientName,
    c.Email,
    COUNT(a.ApplicationID) AS TotalApplications,
    MAX(a.CreatedAt) AS LastApplication
FROM Clients c
LEFT JOIN Applications a ON c.ClientID = a.ClientID
WHERE c.IsActive = 1
GROUP BY c.ClientID, CONCAT(c.FirstName, ' ', c.LastName), c.Email
HAVING COUNT(a.ApplicationID) > 0
ORDER BY COUNT(a.ApplicationID) DESC;
GO

-- Get client profile with application summary
SELECT 
    c.ClientID,
    CONCAT(c.FirstName, ' ', c.LastName) AS FullName,
    c.Email,
    c.PhoneNumber,
    c.Address,
    COUNT(DISTINCT a.ApplicationID) AS TotalApplications,
    SUM(CASE WHEN a.Status = 'Approved' THEN 1 ELSE 0 END) AS ApprovedApps,
    SUM(CASE WHEN a.Status = 'Pending' THEN 1 ELSE 0 END) AS PendingApps
FROM Clients c
LEFT JOIN Applications a ON c.ClientID = a.ClientID
WHERE c.ClientID = 1  -- Change to client ID
GROUP BY c.ClientID, CONCAT(c.FirstName, ' ', c.LastName), c.Email, c.PhoneNumber, c.Address;
GO

-- =====================================================
-- 9. CLEANUP QUERIES (Use with caution!)
-- =====================================================

-- Get orphaned messages (messages for deleted staff)
SELECT m.MessageID
FROM Messages m
LEFT JOIN Staff s ON m.RecipientID = s.StaffID
WHERE s.StaffID IS NULL;
GO

-- Get applications with no client
SELECT a.ApplicationID
FROM Applications a
LEFT JOIN Clients c ON a.ClientID = c.ClientID
WHERE c.ClientID IS NULL;
GO

-- Get unused budget allocations (fiscal year in past)
SELECT 
    BudgetID,
    FiscalYear,
    Category,
    AllocatedAmount
FROM BudgetAllocation
WHERE FiscalYear < YEAR(GETDATE());
GO

-- =====================================================
-- 10. DASHBOARD/REPORTING QUERIES
-- =====================================================

-- Executive Dashboard Summary
SELECT 
    (SELECT COUNT(*) FROM Applications WHERE Status = 'Pending') AS PendingApplications,
    (SELECT COUNT(*) FROM Applications WHERE Status = 'Approved') AS ApprovedThisMonth 
        FROM Applications WHERE MONTH(ApprovalDate) = MONTH(GETDATE())) AS ApprovedThisMonth,
    (SELECT SUM(Amount) FROM Transactions WHERE Status = 'Approved' AND MONTH(TransactionDate) = MONTH(GETDATE())) AS MonthlyExpenses,
    (SELECT COUNT(DISTINCT ClientID) FROM Applications WHERE MONTH(CreatedAt) = MONTH(GETDATE())) AS NewClientsThisMonth,
    (SELECT COUNT(*) FROM Staff WHERE IsActive = 1) AS ActiveStaffMembers;
GO

-- Weekly application summary
SELECT 
    DATENAME(WEEK, CreatedAt) AS Week,
    YEAR(CreatedAt) AS Year,
    COUNT(*) AS ApplicationsPerWeek,
    SUM(CASE WHEN Status = 'Approved' THEN 1 ELSE 0 END) AS ApprovedCount,
    SUM(CASE WHEN Status = 'Pending' THEN 1 ELSE 0 END) AS PendingCount
FROM Applications
WHERE CreatedAt >= DATEADD(MONTH, -3, GETDATE())
GROUP BY DATENAME(WEEK, CreatedAt), YEAR(CreatedAt), WEEK(CreatedAt)
ORDER BY YEAR DESC, WEEK DESC;
GO

-- =====================================================
-- 11. PERFORMANCE ANALYSIS
-- =====================================================

-- Check table sizes
SELECT 
    t.NAME AS TableName,
    SUM(s.in_row_data_page_count) AS Pages,
    SUM(s.in_row_data_page_count) * 8 / 1024 AS SizeMB
FROM sys.dm_db_partition_stats s
JOIN sys.tables t on s.object_id = t.object_id
GROUP BY t.NAME
ORDER BY SUM(s.in_row_data_page_count) DESC;
GO

-- Check index fragmentation
SELECT 
    OBJECT_NAME(ps.object_id) AS TableName,
    i.name AS IndexName,
    ps.avg_fragmentation_in_percent
FROM sys.dm_db_index_physical_stats(DB_ID(), NULL, NULL, NULL, 'LIMITED') ps
JOIN sys.indexes i ON ps.object_id = i.object_id AND ps.index_id = i.index_id
WHERE ps.avg_fragmentation_in_percent > 10 AND ps.page_count > 1000
ORDER BY ps.avg_fragmentation_in_percent DESC;
GO

-- =====================================================
-- DATA SANITY CHECKS
-- =====================================================

-- Verify referential integrity
SELECT 
    'Applications with missing Client' AS Issue,
    COUNT(*) AS Count
FROM Applications a
WHERE NOT EXISTS (SELECT 1 FROM Clients WHERE ClientID = a.ClientID)
UNION ALL
SELECT 
    'Applications assigned to deleted Staff',
    COUNT(*)
FROM Applications a
WHERE a.AssignedToStaffID IS NOT NULL 
  AND NOT EXISTS (SELECT 1 FROM Staff WHERE StaffID = a.AssignedToStaffID)
UNION ALL
SELECT 
    'Transactions by deleted Staff',
    COUNT(*)
FROM Transactions t
WHERE NOT EXISTS (SELECT 1 FROM Staff WHERE StaffID = t.CreatedBy);
GO

