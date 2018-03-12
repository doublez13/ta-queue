#!/bin/bash
ROOT_DIR=/srv/queue/$USER

php -l $ROOT_DIR/model/auth.php
php -l $ROOT_DIR/model/config.php
php -l $ROOT_DIR/model/courses.php
php -l $ROOT_DIR/model/queue.php
php -l $ROOT_DIR/model/tests.php
