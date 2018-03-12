drop database ta_queue;

create database ta_queue;

use ta_queue;

--User data;
create table users(
  username    VARCHAR(256),
  first_name  VARCHAR(32) NOT NULL,
  last_name   VARCHAR(32) NOT NULL,
  full_name   VARCHAR(64) NOT NULL,
  first_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_login  TIMESTAMP,
  primary key (username)
);

--Course data;
create table courses(
  course_id int NOT NULL AUTO_INCREMENT,
  depart_pref VARCHAR(16) NOT NULL,
  course_num  VARCHAR(16) NOT NULL,
  course_name VARCHAR(128) UNIQUE,
  professor   VARCHAR(128), 
  description TEXT,
  ldap_group  VARCHAR(256),
  access_code VARCHAR(16),
  primary key (course_id),
  foreign key (professor) references users(username) ON DELETE SET NULL
);

--Students enrolled in course;
create table enrolled(
  username    VARCHAR(256),
  course_id   int NOT NULL,
  primary key (username, course_id),
  foreign key (username) references users(username) ON DELETE CASCADE,
  foreign key (course_id) references courses(course_id) ON DELETE CASCADE
);

--State of each queue;
--Closed queues don't appear here
create table queue_state(
  course_id     int,
  state         ENUM('open','frozen') NOT NULL,
  time_lim      int UNSIGNED DEFAULT 0 NOT NULL,
  primary key (course_id),
  foreign key (course_id) references courses(course_id) ON DELETE CASCADE
);

--Master queue for all courses;
--foreign key contraints guarantee student is enrolled in course
--  and queue is open
create table queue(
  position   BIGINT AUTO_INCREMENT,
  username   VARCHAR(256) NOT NULL,
  course_id  int NOT NULL,
  question   TEXT,
  location   VARCHAR(256) NOT NULL,
  primary key (position),
  foreign key (username, course_id) references enrolled(username, course_id) ON DELETE CASCADE,
  foreign key (course_id) references queue_state(course_id) ON DELETE CASCADE,
  unique (username, course_id)
);

--State of each TA on duty--
create table ta_status(
  username     VARCHAR(256) NOT NULL,
  course_id    int NOT NULL,
  helping      BIGINT,
  state_tmstmp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  primary key  (username, course_id),
  foreign key  (username) references users(username) ON DELETE CASCADE,
  foreign key  (course_id) references queue_state(course_id) ON DELETE CASCADE,
  foreign key  (helping) references queue(position) ON DELETE SET NULL
);



--Announcements--
create table announcements(
  id             BIGINT AUTO_INCREMENT,
  course_id      int NOT NULL,
  announcement   TEXT,
  tmstmp         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  primary key    (id),
  foreign key    (course_id) references courses(course_id) ON DELETE CASCADE
);



--LOGS--
create table student_log(
  id             BIGINT AUTO_INCREMENT,
  username       VARCHAR(256) NOT NULL,
  course_id      int NOT NULL,
  question       TEXT,
  location       VARCHAR(256) NOT NULL,
  enter_tmstmp   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  help_tmstmp    TIMESTAMP,
  exit_tmstmp    TIMESTAMP,
  primary key    (id),
  foreign key    (username)  references users(username)    ON DELETE CASCADE,
  foreign key    (course_id) references courses(course_id) ON DELETE CASCADE  
);

CREATE TRIGGER log_student_entry AFTER INSERT ON queue FOR EACH ROW 
INSERT INTO student_log (username, course_id, question, location) 
VALUES (NEW.username, NEW.course_id, NEW.question, NEW.location);

CREATE TRIGGER log_student_exit AFTER DELETE ON queue FOR EACH ROW
UPDATE student_log SET exit_tmstmp = CURRENT_TIMESTAMP 
WHERE username=OLD.username AND course_id=OLD.course_id ORDER BY id DESC LIMIT 1;

CREATE TRIGGER log_student_help AFTER INSERT ON ta_status FOR EACH ROW
UPDATE student_log SET help_tmstmp = CURRENT_TIMESTAMP
WHERE username=(SELECT username FROM queue where position=NEW.helping) AND course_id=NEW.course_id  ORDER BY id DESC LIMIT 1;

