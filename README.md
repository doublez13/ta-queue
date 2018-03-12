# suzie-queue

Don't commit changes to config.php to git!
The file is tracked, but with empty settings
After checking out the repo, run...
```
git update-index --assume-unchanged model/config.php
```
to ignore changes to the file
