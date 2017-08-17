CREATE TABLE docs (
  id SERIAL PRIMARY KEY,
  title varchar(100) NOT NULL
)
;

CREATE TABLE suggest (
  id SERIAL PRIMARY KEY,
  keyword varchar(255) NOT NULL,
  trigrams varchar(255) NOT NULL,
  freq integer NOT NULL,
  UNIQUE (keyword)
)
;
