# Media Organizer
## About
This is a replacement to Griffith, an older application for linux and windows used to organize video media.

This will use the existing schema of griffith but allow for a modern UI and more features.

## Setup
Copy the .env.example file to .env and fill in the values.
Make sure to update your initial password.
The only required values are the database setup and credentials and the initial password.
All others can be defined via the webui or be overridden by the .env file.

Set up write permissions for the writeable folder
```
# From the main folder run the following command:
find writable/ -type d -exec chmod 777 {} \; -print
# Feel free to be proper about permissions, as 777 is not secure.
```
# Existing setup
Perform a database backup before making any changes!
Point the .env file to the existing database and load the webui.

# New setup
Feel free to use this for a new setup, by simply creating a new database with our provided schema

Once you create the database with the command below, load the web interface for initial setup or follow the steps below for a manual setup.

1. Create the database and user (adjust 'your_username', 'your_password', and 'griffith' as needed):
```sql
CREATE DATABASE griffith;
CREATE USER 'your_username'@'localhost' IDENTIFIED BY 'your_password';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, REFERENCES, INDEX, ALTER, CREATE TEMPORARY TABLES, LOCK TABLES, CREATE VIEW, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EXECUTE ON `griffith`.* TO 'your_username'@'localhost';     
-- Only use the following if having permissions issues:
-- GRANT ALL PRIVILEGES ON griffith.* TO 'your_username'@'localhost';
FLUSH PRIVILEGES;
```

2. Import the schema:
3. 
```bash
mysql -u your_username -p griffith < _install/griffith_schema.sql
```

3. (Optional) Populate with default values:
```bash
mysql -u your_username -p griffith < _install/griffith_default_data.sql
```