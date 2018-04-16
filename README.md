# suzie-queue

## Overview
Suzie Queue is an attempt to replace the current TA queue with a better looking, more reliable, and feature rich system. Starting from scratch, we are building a new queue using standard web technologies. On the front end, our queue is making use of jQuery, CSS5, and Bootstrap. The backend is written entirely in PHP5, leveraging MySQL for all database work, and LDAP for all athentication and authorization.

## Example Setup:
Our queue is written to allow a fairly flexible setup. At the most basic level, the queue is hosted on a standard web server like Apache or NGINX. Additionally, all the data is stored in a MySQL database. Finally, all user authentication and information is done off of an external Active Directory (LDAP) server.

## Web Server:
The queue requires a web server for hosting. We have chosen to develop against Apache 2.6. However, we are not making use of any Apache specific features/settings, so in theory any web server should be able to host the queue. Since user credentials are being sent over the network, SSL is a must.

## MySQL Server:
All SQL code is written using the standard php-mysql library. As so, a MySQL server is needed for all the backend data. At this point in time (2018), MaraiDB is considered a drop in repacement MySQL, and should work as well. On our testing setup, we host the MariaDB server on the same machine as the web server. However, the code is written so that these two servers are not coupled, and may reside on a different machine. In this case, the corresponding SQL port(s) will need to be opened on the machine hosting the database. When the SQL server resides on the same machine as the web server, localhost may be entered for the SQL_SERVER global variable in the config.php file, and no corresponding firewall ports need to be opened for SQL.

## LDAP Server:
All authentication and authorization is done against Active Direcory using the standard php-ldap library. Because of this, switching to a different Active Directory domain is as simple as modifying the master config.php file. Switching to a different LDAP provider (like OpenLDAP) should be fairly straight forward. In the future, I'd like to take out all Active Directory specific code, allowing the LDAP code to be as generic as possible. All authenticaino is done using LDAP by simply attempting to bind to the LDAP server with the given username and password. Additionally, all user information (currently first and last name) is pulled from LDAP and stored locally in SQL the first time a user logs in. When creating a course, all authorization is handeled via LDAP. This means that each course needs to have an LDAP group created in the LDAP server. This LDAP group is required on the course creation page. Changing a users role in a course is as simple as adding them to, or removing them from the LDAP group that corresponds to the course. 

## Network Ports
Port 80: HTTPS redirect
Port 443: HTTPS
Optionally SQL port(s)
