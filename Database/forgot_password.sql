-- SQL Commands to update the database for the Forgot Password and Verify OTP pages

-- Select the proper database
USE kk_pharma;

-- We are adding the 'reset_otp' and 'reset_otp_expiry' columns to the existing 'register' table.
-- If you run db_config.php in the browser, these columns are added automatically!
-- You can run these commands manually in your SQL client (e.g. phpMyAdmin, HeidiSQL) if needed.

ALTER TABLE register 
ADD COLUMN reset_otp VARCHAR(10) NULL,
ADD COLUMN reset_otp_expiry DATETIME NULL;
