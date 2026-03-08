# Role-Based Access Control - Testing Guide

## Quick Start: Testing Each Role

### Test Account 1: Punong Barangay
**URL**: `http://localhost/softeng-prog-1/auth/staff-login.php`
- **Email**: `punong@barangay.gov.ph`
- **Password**: `staff123`

**Expected Behavior**:
- Redirects to `pages/staff/punong-dashboard.php`
- Shows staff and application statistics
- Access to all menus:
  - Dashboard
  - Staff Management
  - Roles & Permissions
  - Applications
  - Reports

**Can Do**:
- ✓ Manage staff (add, edit, change roles)
- ✓ Configure role permissions
- ✓ Approve/reject applications
- ✓ View all financial data
- ✓ Generate reports
- ✓ View audit logs

---

### Test Account 2: Barangay Treasurer
**URL**: `http://localhost/softeng-prog-1/auth/staff-login.php`
- **Email**: `treasurer@barangay.gov.ph`
- **Password**: `staff123`

**Expected Behavior**:
- Redirects to `pages/staff/treasurer-dashboard.php`
- Shows financial statistics and recent transactions
- Access to menus:
  - Dashboard
  - Transactions
  - Reports (Financial only)
  - Budget
  - Applications

**Can Do**:
- ✓ Record financial transactions
- ✓ View/manage budget allocations
- ✓ Generate financial reports
- ✓ Add remarks to flagged applications
- ✓ View transaction history
- ✗ Cannot: Manage staff, edit permissions, view audit logs

**Test Steps**:
1. Login with treasurer credentials
2. Should auto-redirect to treasurer-dashboard.php
3. Click "Record Transaction" - see transaction form
4. Click "Reports" - see financial report options
5. Click "Budget" - see budget allocation interface
6. Try to navigate to staff-management.php directly - should redirect

---

### Test Account 3: Barangay Secretary
**URL**: `http://localhost/softeng-prog-1/auth/staff-login.php`
- **Email**: `secretary@barangay.gov.ph`
- **Password**: `staff123`

**Expected Behavior**:
- Redirects to `pages/staff/dashboard.php` (main staff dashboard)
- Shows application statistics
- Access to menus:
  - Dashboard
  - Applications
  - Clients
  - Messages
  - Review Application
  - View Application

**Can Do**:
- ✓ View applications
- ✓ Approve/reject applications
- ✓ Edit client records
- ✓ Send messages
- ✓ Add remarks to applications
- ✗ Cannot: Record transactions, manage budget, manage staff

---

### Test Account 4: Sanggunian Member
**URL**: `http://localhost/softeng-prog-1/auth/staff-login.php`
- **Email**: `sanggunian1@barangay.gov.ph`
- **Password**: `staff123`

**Expected Behavior**:
- Redirects to `pages/staff/dashboard.php` (oversight mode)
- Shows limited statistics for oversight
- Can view applications and reports
- Send messages

**Can Do**:
- ✓ View applications
- ✓ View reports
- ✓ Send messages
- ✓ View budget information
- ✗ Cannot: Approve applications, edit records, manage transactions

---

## Database Setup

Before testing, ensure the database is created:

```sql
-- Run this in SQL Server Management Studio
USE BarangayEServices;
GO

-- Verify tables exist
SELECT name FROM sys.tables;

-- Verify transactions table was added
SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'Transactions';

-- Check staff accounts
SELECT StaffID, Email, FirstName, LastName, Role FROM Staff;
```

Expected staff records:
```
StaffID=1, Email=punong@barangay.gov.ph, Role=Punong Barangay
StaffID=2, Email=secretary@barangay.gov.ph, Role=Barangay Secretary
StaffID=3, Email=treasurer@barangay.gov.ph, Role=Barangay Treasurer
StaffID=4, Email=sanggunian1@barangay.gov.ph, Role=Sanggunian Member
StaffID=5, Email=sanggunian2@barangay.gov.ph, Role=Sanggunian Member
```

---

## Test Cases

### Test Case 1: Auto-Dashboard Routing
**Steps**:
1. Login with `treasurer@barangay.gov.ph`
2. System automatically goes to `treasurer-dashboard.php`

**Result**: ✓ Pass if redirected, ✗ Fail if goes to main dashboard

### Test Case 2: Role-Specific Access
**Steps**:
1. Login as Treasurer
2. Try to manually access `staff-management.php`
3. Should redirect to main dashboard

**Result**: ✓ Pass if redirected, ✗ Fail if allowed access

### Test Case 3: Session Validation
**Steps**:
1. Close browser/clear session
2. Try to access `treasurer-dashboard.php` directly
3. Should redirect to login

**Result**: ✓ Pass if redirected to login, ✗ Fail if shows page

### Test Case 4: Permission Display
**Steps**:
1. Login as Punong Barangay
2. Go to Staff Roles & Permissions
3. Check each role's permission configuration

**Result**: ✓ Pass if permissions correctly shown per role

### Test Case 5: Dashboard Statistics
**Steps**:
1. Login as Punong Barangay
2. Check dashboard statistics (total staff, total apps, pending apps)
3. Statistics should match database counts

**Result**: ✓ Pass if stats are reasonable numbers

### Test Case 6: Transaction Form
**Steps**:
1. Login as Treasurer
2. Click "Record Transaction"
3. Fill in transaction details
4. Click "Record Transaction" button

**Result**: ✓ Pass if form appears and processes (will show "coming soon" message)

### Test Case 7: Navigation Links
**Steps**:
1. Verify each role's sidebar has appropriate links
2. Check that Punong Barangay has all menu items
3. Check that Treasurer is missing Staff Management

**Result**: ✓ Pass if menus match role requirements

---

## Common Issues & Solutions

### Issue: Login Fails
**Solution**: 
- Verify database is running and accessible
- Check SQL Server connection string in `includes/db_config.php`
- Verify staff accounts exist in database

### Issue: Dashboard Doesn't Auto-Redirect
**Solution**:
- Clear browser session
- Verify code in `pages/staff/dashboard.php` lines 23-30
- Check that $_SESSION['role'] is being set correctly in login

### Issue: Pages Show Database Errors
**Solution**:
- Verify Transactions table was created (for Treasurer pages)
- Check SQL Server is running
- Verify connection parameters in `db_config.php`

### Issue: Styling Looks Wrong
**Solution**:
- Hard refresh browser (Ctrl+F5)
- Clear browser cache
- Verify `assets/css/style.css` file exists

---

## File Paths Reference

```
Login Pages:
- auth/staff-login.php

Punong Barangay Pages:
- pages/staff/punong-dashboard.php
- pages/staff/staff-management.php
- pages/staff/staff-roles.php
- pages/staff/applications.php
- pages/staff/reports.php

Barangay Treasurer Pages:
- pages/staff/treasurer-dashboard.php
- pages/staff/transactions.php
- pages/staff/financial-reports.php
- pages/staff/budget-management.php

Shared Pages:
- pages/staff/dashboard.php (routing hub)
- pages/staff/applications.php (shared)

Database:
- database/schema.sql
- includes/db_config.php
- includes/auth_functions.php
```

---

## URL Quick Reference

### Punong Barangay
- Dashboard: http://localhost/softeng-prog-1/pages/staff/punong-dashboard.php
- Staff Mgmt: http://localhost/softeng-prog-1/pages/staff/staff-management.php
- Roles: http://localhost/softeng-prog-1/pages/staff/staff-roles.php
- Reports: http://localhost/softeng-prog-1/pages/staff/reports.php

### Barangay Treasurer
- Dashboard: http://localhost/softeng-prog-1/pages/staff/treasurer-dashboard.php
- Transactions: http://localhost/softeng-prog-1/pages/staff/transactions.php
- Financial Reports: http://localhost/softeng-prog-1/pages/staff/financial-reports.php
- Budget: http://localhost/softeng-prog-1/pages/staff/budget-management.php

### Login
- http://localhost/softeng-prog-1/auth/staff-login.php

---

## Expected Browser Behavior

### Treasurer Auto-Redirect
1. Navigate to `staff-login.php`
2. Enter `treasurer@barangay.gov.ph`
3. Enter `staff123`
4. Click Login
5. **Expected**: Browser navigates to `pages/staff/treasurer-dashboard.php`

### Role Access Control
1. Login as Treasurer
2. Modify URL to `pages/staff/staff-management.php`
3. Press Enter
4. **Expected**: Browser redirects to `pages/staff/dashboard.php` (or shows message)

### Logout
1. Click "Logout" button in any page
2. **Expected**: Session cleared, redirected to homepage or login

---

## Frontend Features to Verify

### Treasurer Dashboard
- [ ] Monthly transaction count displays
- [ ] Pending remarks count shows
- [ ] Recent transactions table populated
- [ ] Quick action buttons are clickable
- [ ] Status badges show correct colors

### Staff Management
- [ ] Add New Staff button opens modal
- [ ] Edit button opens edit modal
- [ ] Change Role button opens role modal
- [ ] Staff list displays all active staff
- [ ] Active/Inactive status badge colors correct

### Roles & Permissions
- [ ] Tab switching works correctly
- [ ] Permissions checked/unchecked as expected
- [ ] Permission count updates
- [ ] Punong Barangay permissions show as disabled (all checked)

### Transactions
- [ ] Transaction date defaults to today
- [ ] Transaction form fields properly labeled
- [ ] Amount field formats to 2 decimals
- [ ] Filter section displays correctly
- [ ] Summary statistics show (will be 0 until DB populated)

### Budget Management
- [ ] Budget cards display with correct colors
- [ ] Progress bars show correct percentages
- [ ] Budget breakdown table displays categories
- [ ] Alert boxes show for high-usage categories (>75%)
- [ ] Allocation form has all required fields

---

## Next Steps After Testing

1. **Database Integration**: Connect forms to actually save/update data
2. **Report Generation**: Implement PDF export for reports
3. **Notifications**: Add email notifications for approvals
4. **Logging**: Implement audit trail for all transactions
5. **Security**: Add CSRF tokens and password hashing
6. **Performance**: Optimize queries with proper indexing
