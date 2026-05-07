-- =====================================================
-- Security Enhancements for Barangay e-Services
-- Add Two-Factor Authentication Support
-- =====================================================

USE BarangayEServices;
GO

-- Add TOTP columns to Clients table
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('Clients') AND name = 'TOTPSecret')
BEGIN
    ALTER TABLE Clients ADD TOTPSecret NVARCHAR(100) NULL;
END

IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('Clients') AND name = 'Is2FAEnabled')
BEGIN
    ALTER TABLE Clients ADD Is2FAEnabled BIT DEFAULT 0;
END

-- Add TOTP columns to Staff table
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('Staff') AND name = 'TOTPSecret')
BEGIN
    ALTER TABLE Staff ADD TOTPSecret NVARCHAR(100) NULL;
END

IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('Staff') AND name = 'Is2FAEnabled')
BEGIN
    ALTER TABLE Staff ADD Is2FAEnabled BIT DEFAULT 0;
END

-- Add TOTP columns to Admins table
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('Admins') AND name = 'TOTPSecret')
BEGIN
    ALTER TABLE Admins ADD TOTPSecret NVARCHAR(100) NULL;
END

IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('Admins') AND name = 'Is2FAEnabled')
BEGIN
    ALTER TABLE Admins ADD Is2FAEnabled BIT DEFAULT 0;
END

-- Create indexes for TOTP queries
IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID('Clients') AND name = 'IX_Clients_Is2FAEnabled')
BEGIN
    CREATE INDEX IX_Clients_Is2FAEnabled ON Clients(Is2FAEnabled);
END

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID('Staff') AND name = 'IX_Staff_Is2FAEnabled')
BEGIN
    CREATE INDEX IX_Staff_Is2FAEnabled ON Staff(Is2FAEnabled);
END

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID('Admins') AND name = 'IX_Admins_Is2FAEnabled')
BEGIN
    CREATE INDEX IX_Admins_Is2FAEnabled ON Admins(Is2FAEnabled);
END

PRINT 'Security enhancements applied successfully!';
GO