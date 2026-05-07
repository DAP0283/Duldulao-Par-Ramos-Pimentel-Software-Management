# Barangay e-Services Security Setup Guide

## Overview
This guide will help you set up the advanced security features for the Barangay e-Services system, including login attempt limits and Google OAuth with Google Authenticator.

## Features Implemented

### 1. Login Timeout Security
- **Failed Attempt Limit**: 5 failed login attempts
- **Timeout Duration**: 2 minutes lockout
- **Tracking Method**: IP-based attempt tracking
- **Automatic Reset**: Successful login resets attempt counter

### 2. Google Authenticator (TOTP) 2FA
- **Standard**: RFC 6238 TOTP (Time-based One-Time Password)
- **App**: Google Authenticator compatible
- **Security**: 30-second time windows with 1-window tolerance
- **Storage**: Encrypted secrets in database

## Database Setup

### Step 1: Run Security Enhancements
Execute the security enhancements script in SQL Server:

```sql
-- Run this in SQL Server Management Studio
-- File: database/security_enhancements.sql
```

Or run the script via command line:
```bash
sqlcmd -S your-server -d BarangayEServices -i database/security_enhancements.sql
```

### Step 2: Verify Table Changes
Check that the following columns were added:
- `Clients.TOTPSecret` (NVARCHAR(100), NULL)
- `Clients.Is2FAEnabled` (BIT, DEFAULT 0)
- `Staff.TOTPSecret` (NVARCHAR(100), NULL)
- `Staff.Is2FAEnabled` (BIT, DEFAULT 0)
- `Admins.TOTPSecret` (NVARCHAR(100), NULL)
- `Admins.Is2FAEnabled` (BIT, DEFAULT 0)

## Google OAuth Setup (Optional)

**Note**: The current implementation uses Google Authenticator for TOTP only. Full Google OAuth integration would require additional setup. The system currently implements TOTP 2FA which is more secure and doesn't require Google API keys.

### Why TOTP Instead of Full OAuth?
- **Security**: TOTP is more secure than OAuth for 2FA
- **Privacy**: No user data shared with Google
- **Reliability**: Works offline, no API dependencies
- **Compatibility**: Works with any TOTP app (Google Authenticator, Authy, etc.)

### If You Still Want Full Google OAuth:

#### Step 1: Create Google Cloud Project
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable the Google+ API and Google OAuth2 API

#### Step 2: Create OAuth Credentials
1. Go to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "OAuth 2.0 Client IDs"
3. Configure OAuth consent screen
4. Set application type to "Web application"
5. Add authorized redirect URIs:
   - `http://localhost/barangay/auth/oauth-callback.php`
   - `https://yourdomain.com/auth/oauth-callback.php`

#### Step 3: Get API Keys
You'll receive:
- **Client ID**: Public identifier
- **Client Secret**: Keep this secret!

#### Step 4: Configure Environment
Create a `.env` file in the project root:
```env
GOOGLE_CLIENT_ID=your_client_id_here
GOOGLE_CLIENT_SECRET=your_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost/barangay/auth/oauth-callback.php
```

#### Step 5: Implement OAuth Callback
Create `auth/oauth-callback.php` to handle OAuth responses.

## How to Use the Security Features

### For Users:

#### Setting Up 2FA:
1. Login to your account
2. Go to Profile or Account Settings
3. Click "Enable 2FA" or visit `/auth/2fa-setup.php`
4. Download Google Authenticator app
5. Scan the QR code or enter the secret manually
6. Enter the 6-digit code to verify

#### Login Process:
1. Enter email and password
2. If 2FA is enabled, enter TOTP code
3. System validates credentials and 2FA code
4. Access granted or denied with appropriate messages

### For Administrators:

#### Monitoring Failed Attempts:
The system automatically tracks failed login attempts by IP address. Failed attempts are stored in:
```
cache/login_attempts.json
```

#### Managing 2FA:
- Users can enable/disable 2FA through their profile
- Administrators can view 2FA status in user management
- No admin intervention required for 2FA setup

## Security Best Practices

### 1. File Permissions
```bash
# Set proper permissions for cache directory
chmod 755 cache/
chmod 644 cache/login_attempts.json
```

### 2. Environment Variables
Store sensitive data in environment variables:
```php
// Use environment variables instead of hardcoding
$api_key = getenv('NEWS_API_KEY') ?: 'fallback_key';
```

### 3. SSL/TLS
Always use HTTPS in production to protect TOTP codes in transit.

### 4. Backup Security Data
Regularly backup the TOTP secrets along with user data.

## Troubleshooting

### Common Issues:

#### 1. "Unclosed '{'" Error
- Check PHP syntax in `includes/auth_functions.php`
- Ensure all braces are properly matched
- Use a PHP syntax checker

#### 2. 2FA Codes Not Working
- Check system time synchronization
- Verify TOTP secret is stored correctly
- Test with multiple time windows (±30 seconds)

#### 3. Login Lockouts
- Clear `cache/login_attempts.json` to reset
- Check server time for timeout calculations
- Verify IP address detection

#### 4. Database Connection Issues
- Ensure SQL Server is running
- Check connection string in `db_config.php`
- Verify user permissions for ALTER TABLE

### Debug Mode
Add temporary debugging to `auth_functions.php`:
```php
// Temporary debug logging
error_log("TOTP Debug: Secret=$secret, Code=$code, Time=" . time());
```

## Testing the Security Features

### Test Login Timeout:
1. Attempt login 5 times with wrong credentials
2. Verify account is locked for 2 minutes
3. Try correct credentials after timeout expires

### Test 2FA Setup:
1. Enable 2FA for a test account
2. Logout and attempt login
3. Verify 2FA code is required
4. Test with correct and incorrect codes

### Test TOTP Validation:
1. Generate codes manually using an online TOTP calculator
2. Verify the system accepts valid codes
3. Test time window tolerance (±30 seconds)

## API Documentation

### TOTP Functions:
- `generateTOTPSecret()`: Creates a new TOTP secret
- `generateTOTPCode($secret)`: Generates current TOTP code
- `verifyTOTPCode($secret, $code)`: Validates a TOTP code
- `generateTOTPURI($secret, $accountName)`: Creates QR code URI

### Security Functions:
- `isLoginLockedOut($ip)`: Checks if IP is locked out
- `recordFailedLoginAttempt($ip)`: Records failed attempt
- `resetLoginAttempts($ip)`: Resets attempts after success
- `validateLoginWith2FA($email, $password, $totpCode, $userType)`: Validates login with 2FA

## Support

For issues with this security implementation:
1. Check PHP error logs
2. Verify database schema
3. Test with a fresh user account
4. Ensure all files are properly uploaded

## Security Notes

- **TOTP Secrets**: Stored encrypted in database
- **Failed Attempts**: Tracked by IP address only
- **Session Security**: Uses PHP sessions with proper validation
- **Password Security**: Uses bcrypt hashing (cost factor 12)

## Future Enhancements

Potential improvements:
- Email/SMS backup codes for 2FA
- Hardware security keys (FIDO2/WebAuthn)
- Advanced rate limiting with Redis
- Audit logging for security events
- Geo-blocking for suspicious locations