<?php

	// IMPORTANT:
	// ==========
	// If you wish to translate the interface of AppGini,
	// DO NOT TRANSLATE THIS FILE.
	//
	// Instead, you should translate the language.php file.
	// =====================================================


	// datalist.php
	$Translation['powered by'] = "Powered by";
	$Translation['quick search'] = "Quick Search";
	$Translation['records x to y of z'] = "Records <FirstRecord> to <LastRecord> of <RecordCount>";
	$Translation['filters'] = "Filters";
	$Translation['filter'] = "Filter";
	$Translation['filtered field'] = "Filtered field";
	$Translation['comparison operator'] = "Comparison Operator";
	$Translation['comparison value'] = "Comparison Value";
	$Translation['and'] = "And";
	$Translation['or'] = "Or";
	$Translation['equal to'] = "Equal to";
	$Translation['not equal to'] = "Not equal to";
	$Translation['greater than'] = "Greater than";
	$Translation['greater than or equal to'] = "Greater than or equal to";
	$Translation['less than'] = "Less than";
	$Translation['less than or equal to'] = "Less than or equal to";
	$Translation['like'] = "Like";
	$Translation['not like'] = "Not like";
	$Translation['is empty'] = "Is empty";
	$Translation['is not empty'] = "Is not empty";
	$Translation['apply filters'] = "Apply filters";
	$Translation['save filters'] = "Save and apply filters";
	$Translation['saved filters title'] = "HTML Code For The Applied Filters";
	$Translation['saved filters instructions'] = "Copy the code below and paste it to an HTML file to save the filter you just defined so that you can return to it at any time in the future without having to redefine it. You can save this HTML code on your computer or on any server and access this prefiltered table view through it.";
	$Translation['hide code'] = "Hide this code";
	$Translation['printer friendly view'] = "Printer-friendly view";
	$Translation['save as csv'] = "Download as csv file (comma-separated values)";
	$Translation['edit filters'] = "Edit filters";
	$Translation['clear filters'] = "Clear filters";
	$Translation['order by'] = 'Order by';
	$Translation['go to page'] = 'Go to page:';
	$Translation['none'] = 'None';
	$Translation['Select all records'] = 'Select all records';
	$Translation['With selected records'] = 'With selected records';
	$Translation['Print Preview Detail View'] = 'Print Preview Detail View';
	$Translation['Print Preview Table View'] = 'Print Preview Table View';
	$Translation['Print'] = 'Print';
	$Translation['Cancel Printing'] = 'Cancel Printing';
	$Translation['Cancel Selection'] = 'Cancel Selection';
	$Translation['Maximum records allowed to enable this feature is'] = 'Maximum records allowed to enable this feature is';

	// _dml.php
	$Translation['are you sure?'] = 'Are you sure you want to delete this record?';
	$Translation['add new record'] = 'Add new record';
	$Translation['update record'] = 'Update record';
	$Translation['delete record'] = 'Delete record';
	$Translation['deselect record'] = 'Deselect record';
	$Translation["couldn't delete"] = 'Could not the delete record due to the presence of <RelatedRecords> related record(s) in table [<TableName>]';
	$Translation['confirm delete'] = 'This record has <RelatedRecords> related record(s) in table [<TableName>]. Do you still want to delete it? <Delete> &nbsp; <Cancel>';
	$Translation['yes'] = 'Yes';
	$Translation['no'] = 'No';
	$Translation['pkfield empty'] = ' field is a primary key field and cannot be empty.';
	$Translation['upload image'] = 'Upload new file ';
	$Translation['select image'] = 'Select an image ';
	$Translation['remove image'] = 'Remove file';
	$Translation['month names'] = 'January,February,March,April,May,June,July,August,September,October,November,December';
	$Translation['field not null'] = 'You cannot leave this field empty.';
	$Translation['*'] = '*';
	$Translation['today'] = 'Today';
	$Translation['Hold CTRL key to select multiple items from the above list.'] = 'Hold CTRL key to select multiple items from the above list.';

	// lib.php
	$Translation['select a table'] = "Jump to ...";
	$Translation['homepage'] = "Homepage";
	$Translation['error:'] = "Error:";
	$Translation['sql error:'] = "SQL error:";
	$Translation['query:'] = "Query:";
	$Translation['< back'] = "&lt; Back";
	$Translation["if you haven't set up"] = "If you haven't set up the database yet, you can do so by clicking <a href='setup.php'>here</a>.";
	$Translation['file too large']="Error: The file you uploaded exceeds the maximum allowed size of <MaxSize> KB";
	$Translation['invalid file type']="Error: This file type is not allowed. Only <FileTypes> files can be uploaded";

	// setup.php
	$Translation['goto start page'] = "Back to start page";
	$Translation['no db connection'] = "Couldn't establish a database connection.";
	$Translation['no db name'] = "Couldn't access the database named '<DBName>' on this server.";
	$Translation['provide connection data'] = "Please provide the following data to connect to the database:";
	$Translation['mysql server'] = "MySQL server (host)";
	$Translation['mysql username'] = "MySQL Username";
	$Translation['mysql password'] = "MySQL password";
	$Translation['mysql db'] = "Database name";
	$Translation['connect'] = "Connect";
	$Translation['couldnt save config'] = "Couldn't save connection data into 'config.php'.<br />Please make sure that the folder:<br />'".dirname(__FILE__)."'<br />is writable (chmod 775 or chmod 777).";
	$Translation['setup performed'] = "Setup already performed on";
	$Translation['delete md5'] = "If you want to force setup to run again, you should first delete the file 'setup.md5' from this folder.";
	$Translation['table exists'] = "Table <b><TableName></b> exists, containing <NumRecords> records.";
	$Translation['failed'] = "Failed";
	$Translation['ok'] = "Ok";
	$Translation['mysql said'] = "MySQL said:";
	$Translation['table uptodate'] = "Table is up-to-date.";
	$Translation['couldnt count'] = "Couldn't count records of table <b><TableName></b>";
	$Translation['creating table'] = "Creating table <b><TableName></b> ... ";

	// separateDVTV.php
	$Translation['please wait'] = "Please wait";

	// _view.php
	$Translation['tableAccessDenied']="Sorry! You don't have permission to access this table. Please contact the admin.";

	// incCommon.php
	$Translation['not signed in']="You are not signed in";
	$Translation['sign in']="Sign In";
	$Translation['signed as']="Signed in as";
	$Translation['sign out']="Sign Out";
	$Translation['admin setup needed']="Admin setup was not performed. Please log in to the <a href=admin/>admin control panel</a> to perform the setup.";
	$Translation['db setup needed']="Program setup was not performed yet. Please log in to the <a href=setup.php>setup page</a> first.";
	$Translation['new record saved']="The new record has been saved successfully.";
	$Translation['record updated']="The changes have been saved successfully.";

	// index.php
	$Translation['admin area']="Admin Area";
	$Translation['login failed']="Your previous login attempt failed. Try again.";
	$Translation['sign in here']="Sign In Here";
	$Translation['remember me']="Remember me";
	$Translation['username']="Username";
	$Translation['password']="Password";
	$Translation['go to signup']="Don't have a username? <br />&nbsp; <a href=membership_signup.php>Sign up here</a>";
	$Translation['forgot password']="Forgot your password? <a href=membership_passwordReset.php>Click here</a>";
	$Translation['browse as guest']="Or <a href=index.php>click here</a> to continue <br />&nbsp; browsing as a guest.";
	$Translation['no table access']="You don't have enough permissions to access any page here. Please sign in first.";

	// checkMemberID.php
	$Translation['user already exists']="Username '<MemberID>' already exists. Try another username.";
	$Translation['user available']="Username '<MemberID>' is available and you can take it.";
	$Translation['empty user']="Please type a username in the box first then click 'Check availability'.";

	// membership_thankyou.php
	$Translation['thanks']="Thank you for signing up!";
	$Translation['sign in no approval']="If you have chosen a group that doesn't require admin approval, you can sign in right now <a href=index.php?signIn=1>here</a>.";
	$Translation['sign in wait approval']="If you have chosen a group that requires admin approval, please wait for an email confirming your approval.";

	// membership_signup.php
	$Translation['username empty']="You must provide a username. Please go back and type a username";
	$Translation['password invalid']="You must provide a password of 4 characters or more, without spaces. Please go back and type a valid password";
	$Translation['password no match']="Password doesn't match. Please go back and correct the password";
	$Translation['username exists']="Username already exists. Please go back and choose a different username.";
	$Translation['email invalid']="Invalid email address. Please go back and correct your email address.";
	$Translation['group invalid']="Invalid group. Please go back and correct the group selection.";
	$Translation['sign up here']="Sign Up Here!";
	$Translation['registered? sign in']="Already registered? <a href=index.php?signIn=1>Sign in here</a>.";
	$Translation['sign up disabled']="Sorry! Sign-up is temporarily disabled by admin. Try again later.";
	$Translation['check availability']="Check if this username is available";
	$Translation['confirm password']="Confirm Password";
	$Translation['email']="Email Address";
	$Translation['group']="Group";
	$Translation['groups *']="If you choose to sign up to a group marked with an asterisk (*), you won't be able to log in until the admin approves you. You'll receive an email when you are approved.";
	$Translation['sign up']="Sign Up";

	// membership_passwordReset.php
	$Translation['password reset']="Password Reset Page";
	$Translation['password reset details']="Enter your username or email address below. We'll then send a special link to your email. After you click on that link, you'll be asked to enter a new password.";
	$Translation['password reset subject']="Password reset instructions";
	$Translation['password reset message']="Dear member, \n If you have requested to reset/change your password, please click on this link: \n <ResetLink> \n\n If you didn't request a password reset/change, please ignore this message. \n\n Regards.";
	$Translation['password reset ready']="An email with password reset instructions has been sent to your registered email address. Please keep this browser window open and follow the instructions in the email message.<br /><br />If you don't receive this email within 5 minutes, try resetting your password again, and make sure you enter a correct username or email address.";
	$Translation['password reset invalid']="Invalid username or password. <a href=membership_passwordReset.php>Try again</a>, or go <a href=index.php>back to homepage</a>.";
	$Translation['password change']="Password Change Page";
	$Translation['new password']="New password";
	$Translation['password reset done']="Your password was changed successfully. You can <a href=index.php?signOut=1>log in with the new password here</a>.";

?>