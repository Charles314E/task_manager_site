-- REG: 1805098

-- If the database doesn't exist, create it.
CREATE DATABASE IF NOT EXISTS ce154_ck18334;

USE ce154_ck18334;

-- Create the necessary tables if they don't exist.
CREATE TABLE IF NOT EXISTS T_PROJECT( name varchar(64), user varchar(64), priority int, progress real, overdueCount real, expanded int, editing int, orderby varchar(16), showTasks varchar(16), PRIMARY KEY (name) );
CREATE TABLE IF NOT EXISTS T_USER( name varchar(64) PRIMARY KEY, password varchar(64), selectedProject varchar(64), FOREIGN KEY (selectedProject) REFERENCES T_PROJECT(name) ON UPDATE CASCADE ); 
CREATE TABLE IF NOT EXISTS T_TASK( name varchar(64), project varchar(64), user varchar(64), description varchar(1024), priority int, complete int, dueDate datetime(0), expanded int, editing int, PRIMARY KEY (name, project), FOREIGN KEY (project) REFERENCES T_PROJECT(name) ON UPDATE CASCADE );