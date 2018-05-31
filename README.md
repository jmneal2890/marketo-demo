Build with Docker-compose

`docker-compose up --build`

May have to run twice to get sql server running properly, not just excited.

Have not set up persistent data with sql server, so have to build new database when new docker container is built.

- Bash into mysql command line

`docker exec -it marketo-demo_mysql_1`

`mysql -p`

`password: password`

- Build proper database table for hit counter to register to. Currently set up for two pages, easily expandable to more.

`USE marketo_demo;`

`CREATE TABLE pageviewcount (user_id int(11) not null AUTO_INCREMENT primary key, user_ip varchar(255), user_timestamp datetime, bloga_timestamp datetime, bloga_total int(11) default 0, bloga_registered int(11) default 0, blogb_timestamp datetime, blogb_total int(11) default 0, blogb_registered int(11) default 0);`

- In order for 'UNKNOWN' IP counter to work, initial entry must be created:

`INSERT INTO pageviewcount (user_id, user_ip, user_timestamp, bloga_timestamp, bloga_total, bloga_registered, blogb_timestamp, blogb_total, blogb_registered) VALUES (null, 'UNKNOWN', null, null, 0, 0, null, 0, 0);`

- Should now be able to check current entries in user database and track their page views.

`SELECT * FROM pageviewcount;`

- Current logic is set to:
  1. Establish current page variables for database call's based on blog style name passed through the function.
  2. Set current Datetime for later reference.
  3. Pull user IP, if unable to set user IP to 'UNKNOWN'.
  4. Check if IP is set to 'UNKNOWN' or proper IP has been obtained.
    - If IP is set to 'UNKNOWN', update entry in DB with current user_timestamp, current blog_timestamp, and add total hit count to that blog(no registered hits are attributed to unknown visitors).
  5. Check if user IP is currently registered in the database
    - If user is registered, check if most recent timestamp for this current blog page is more than 12 hours old.
      - If most recent registered visit is less than 12 hours old, only updated main user_timestamp and total hit count for current blog page, not registered hit count or blog page specific Timestamp.
      - If most recent registered visit is greater than 12 hours old, update main user_timestamp, add total hit count for current blog page, add registered hit count for current blog page, and update blog page specific Timestamp to current time.
  6. If User is not registered, create new entry in database with users IP, current time as new global user_timestamp, current time as current blog page timestamp, add 1 total hit count to current blog page, add 1 registered hit to current blog page.

- To expand table for more blog styles, simply add new columns to DB

`ALTER TABLE pageviewcount ADD COLUMN ([new blog title]_timestamp datetime, [new blog title]_total int(11), [new blog title]_registered int(11));`
