# suzie-queue

## Technical Documentation and Setup
Suzie Queue is an attempt to replace the current TA queue with a better looking, more reliable, and feature rich system. Starting from scratch, we are building a new queue using standard web technologies. On the front end, our queue is making use of jQuery, CSS5, and Bootstrap. The backend is written entirely in PHP, leveraging MySQL for all database work, and LDAP for all authentication and authorization.

### Example Setup
The queue is written to allow for a fairly flexible setup. At the most basic level, the queue is hosted on a standard web server like Apache or NGINX. Additionally, all the data is stored in a MySQL database. Finally, all user authentication and information is done off of an external Active Directory (LDAP) server.

### Web Server
The queue requires a web server for hosting. We have chosen to test against Apache 2.4. However, we are not making use of any Apache specific features/settings, so in theory any web server should be able to host the queue. Since user credentials are being sent over the network, TLS is a must.

### MySQL Server
All SQL code is written using the standard php-mysql library. As so, a MySQL server is needed for all the backend data. At this point in time (2018), MaraiDB is considered a drop in replacement for MySQL, and should work as well. On our testing setup, we host a MariaDB server on the same machine as the web server. However, the code is written so that these two servers are not coupled, and may reside on different machines. In this case, the corresponding SQL port(s) will need to be opened on the machine hosting the database. When the SQL server resides on the same machine as the web server, localhost may be entered for the SQL_SERVER global variable in the config.php file, and no corresponding firewall ports need to be opened for SQL.

### LDAP Server
All authentication and authorization is done against Active Directory using the standard php-ldap library. Because of this, switching to a different Active Directory domain is as simple as modifying the master config.php file. All authentication is done using LDAP by simply attempting to bind to the LDAP server with the given username and password. Additionally, all user information (currently first and last name) is pulled from LDAP and stored locally in SQL the first time a user logs in. When creating a course, all authorization is handled via LDAP. This means that each course needs to have an LDAP group created in the LDAP server. This LDAP group is required on the course creation page. Changing a users role in a course is as simple as adding them to, or removing them from the LDAP group that corresponds to the course. Because of the generic LDAP schema in use, the queue could be moved to CIS accounts by simply changing a few lines in the config file if desired. Additionally, because of the sensitive information going over the network, all LDAP traffic should be using TLS.

#### LDAP TLS Note 
By default, LDAP prefers to verify server certificates before connecting. The queue does not make use of server certificates, and disables them. In PHP >= 7.0.5, the php-ldap library respects this setting, and disables certificate checking. In PHP versions < 7.0.5, the TLS_REQCERT flag should be set to false in the system ldap config. After this is done, the TLS_REQCERT_DISABLED flag can be set to true in the queue configuration indicating that cert checking is disabled on the system side. If this flag is NOT set in the queue config with PHP < 7.0.5, the queue will fall back to unencrypted LDAP communication with is NOT RECOMMENDED.

### Network Ports
Port 80:   HTTP redirect  
Port 443:  HTTPS  
Port 3306: MySQL (Only needed if running on different server)

### Dependencies
Apache >= 2.4 (Ealier versions most likely work, but haven't been tested)  
MySQL  >= 5.5 (Ealier versions most likely work, but haven't been tested. MariaDB should work too)  
PHP    >= 5.4 (PHP 7.0.5 or later recommended for utilizing some features of php-ldap)

### Configuration file (config.php)
LDAP_SERVER: FQDN or IP address of the LDAP server.  
LDAP_DOMAIN: Active Directory domain FQDN.  
BIND_USER:   User to bind to LDAP with. Simple username, not DN format.  
BIND_PASSWD: Password for BIND_USER.  
BASE_OU:     LDAP OU where all searches start. DN format.  
ADMIN_GROUP: LDAP groups for queue admins. Simple group name, not DN format.  
SQL_SERVER:  FQDN or IP address of the MySQL server.  
SQL_USER:    User to connect to MySQL with.  
SQL_PASSWD:  Password for SQL_USER.  
DATABASE:    Database for the queue.  

### MySQL Database Setup
In the resources directory at the root of the project is the DB_setup.sql file that initializes the queue database. Simply set the database name at the top of the file to match what's set in the config.php file. In a mysql shell, the script can be ran using 'mysql> source path/to/DB_setup.sql'.

### Swagger API Documentation
The public API is documented according to the Swagger 2.0 specification. The documentation file is found at "/swagger/ta_queue.yaml". CHANGES TO THE API SHOULD BE UPDATED ACCORDINGLY IN THIS FILE. The Swagger webpage is viewed by browsing to "/swagger/index.html" (the Swagger UI version is 3.10.0). The webpage has a "Try it out" feature which allows users to actually use each endpoint. For this to work correctly, the "host" and "basePath" values at the top of the documentation file should point to the desired API (perhaps the real one).

#
## User Documentation

### Account Creation
The queue currently makes use of College of Engineering CADE accounts. Any student that has a CADE account can log into the queue using their CADE credentials, and their personal information will automatically be pulled from the servers. Students without a CADE account can create one here: https://webhandin.eng.utah.edu/cade/create_account/index.php

### Roles
The queue currently has three major roles: Student, Teaching Assistant and Administrator
#### Student
On the Courses page, students may enroll in any course. If desired, Administrators may require an access code in order to enroll in a course, or see any statistics. 
#### Teaching Assistant
Corresponding to each course is a TA group. If a user is in the group for that course, the user is then granted TA permissions for that course, and may not register as a student. If the user was already enrolled as a student in the course before being added to the TA group, they are automatically unenrolled as a student, and maintain their TA role.
#### Administrator
The queue also has an administrator group defined. If a user is in this group, the user is granted administrative permissions. In this case, the Admin dropdown menu appears which allows them to create courses.  
NOTE: Administrators also have access to a wider range of statistics. Unfortunately these are not visible graphically yet.

### Enrolling and Unenrolling in Courses
If a user is not enrolled in any courses, either as a student or a TA, they are dropped on the Courses page after login. Here they can enroll in any course by simply clicking enroll. If an access code is required to enter the course, students must enter this before proceeding. This page is also where a student would unenroll from a course. In this case, the user can simply select Leave for a particular course. If a user is enrolled in at least one course, they are dropped on the My Courses page after login. 

### Creating a new course
#### Access Codes
If desired, administrators may require an access code in order to enroll in a course. In this case, the course will appear yellow on the Courses page, and have a padlock icon next to it. Students will be required to enter an access code in order to join the course. Access codes can include any ascii character and be up to 16 characters long. 
#### Assigning and Removing Teaching Assistants
Corresponding to each course is a TA group. If a user is in the group corresponding to the course, the user is then seen as a TA for that course. During course creation, an administrator sets the LDAP group for a particular course. This LDAP group needs to be present before creating the course. After mapping the LDAP group for the course TAs, adding and removing TAs is then done completely via an LDAP interface. The most common ways administrators update groups is via the web interface at https://webhandin.eng.utah.edu/groupmodify/ or sending an email to opers@eng.utah.edu. To request an LDAP group for a new course, send an email to opers@eng.utah.edu.

### Statistics
Nearly every queue operation is logged. These includes a student's name, course, question, and timestamps for entering, getting helped, and exiting the queue, along with the TA that helped them.  
Currently however, the only metrics that are visible via the web interface are the number of students helped per day for a particular course.  
With this said however, public API endpoints are available that give much more detailed information on a per course, per student, and per TA basis.

### Queue Functionality
#### Announcements
TAs are allowed to post announcements for their course. All announcements appear at the top of the screen, with newer announcements appearing towards the top. Announcements within the fifteen minutes appear in green. TAs can delete announcements by pressing the X button corresponding to the particular announcement.
#### Queue States
##### Open
All students can enter the queue. Students on cool-down cannot enter.  
NOTE: The queue can be open, even if no TAs are on duty.
##### Closed
Student cannot enter the queue, and no TAs are on duty.
##### Frozen
No new students can enter the queue. Students in the queue when it was frozen remain in the queue.
#### Time Limits
TAs may choose to impose time limits when helping students. In this case, TAs can set the time limit using the Time Limit Per Student setting. When a student's time has passed, the row corresponding to that student turns red.  
#### Cool-down Timers
TAs may wish to impose cool-down timers for students. This means that if a student gets helped, they are not allowed to enter the queue again until X minutes after they left the queue. TAs can get the cool-down time using the Cool-down setting.
#### Reordering
TAs may wish to reorder the queue. In this case, TAs can use the up and down arrow buttons next to a student to shift their position in the queue.
