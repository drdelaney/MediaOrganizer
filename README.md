# Media Organizer
## About
This is a replacement to Griffith, an older application for linux and windows used to organize video media.

This will use the existing schema of griffith but allow for a modern UI and more features.

## Setup
Copy the .env.example file to .env and fill in the values.
Select only one database type.
Make sure to update your initial password.
The only required values are the database setup and credentials and the initial password.
All others can be defined via the webui or be overridden by the .env file.

Set up write permissions for the writeable folder
```bash
# From the root folder of this project, run the following command:
find writable/ -type d -exec chmod 775 {} \; -print
# Replace apache with your webserver user
find writable/ -type d -exec chown apache:apache {} \; -print
# Feel free adjust permissions as needed
# Use 777 only for testing
```
# Existing setup
Perform a database backup before making any changes!
Point the .env file to the existing database and load the webui.
If using SQLite3, copy the database file to writable/database.sqlite3.
Continue with the setup process below with the environment file and web UI.

# New setup
Select your database type!
We currently support
- MySQL / MariaDB
- SQLite3
- PostgreSQL (to be tested)
- MSSQL (to be tested)

- Create the database and user (adjust 'your_username', 'your_password', and 'griffith' as needed):
If using MySQL/MariaDB, PostgreSQL, or MSSQL, you will need to create your username and database first. SQLite3 will create the database for you.
  - MySQL/MariaDB
```bash
mysql -u root -p
```
```sql
sudo mysql -u root -p
CREATE DATABASE griffith;
CREATE USER 'griffith'@'localhost' IDENTIFIED BY 'your_password';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, REFERENCES, INDEX, ALTER, CREATE TEMPORARY TABLES, LOCK TABLES, CREATE VIEW, SHOW VIEW, CREATE ROUTINE, ALTER ROUTINE, EXECUTE ON `griffith`.* TO 'your_username'@'localhost';     
-- Only use the following if having permissions issues:
-- GRANT ALL PRIVILEGES ON griffith.* TO 'your_username'@'localhost';
FLUSH PRIVILEGES;
```
  - PostgreSQL
```bash
sudo -u postgres psql
```
```sql
CREATE DATABASE griffith;
CREATE USER griffith WITH ENCRYPTED PASSWORD <your_password> 
GRANT ALL PRIVILEGES ON DATABASE griffith TO griffith;
```
  - MSSQL (SQLSRV)
```sql
CREATE DATABASE griffith;
GO

USE master;
GO
CREATE LOGIN MyUserLogin WITH PASSWORD = '<your_password>';
GO

USE griffith;
GO
CREATE USER griffith FOR LOGIN griffith;
GO
ALTER ROLE db_owner ADD MEMBER griffith;
GO
```
  - SQLite3
    - No setup required outside of the environment file

# Environment file and Web UI-
- Create the environment file for CodeIgniter 4
  - Make a copy of .env.example to .env
  - Edit .env to include database settings
  - Make sure to define `auth.initialPassword`, `app.baseURL`, and `database` settings
  - May define other values as needed
- Load the Web UI
  - Load the URL that was defined as `app.baseURL` and follow the on screen instructions

# Docker
A basic docker instance running with SQLite3 is available by reviewing the README-docker.md file

## License
This project is licensed under the GNU General Public License v3.0 - see the [LICENSE](LICENSE) file for details.