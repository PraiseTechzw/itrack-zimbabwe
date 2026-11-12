ALTER TABLE requisitions
  ADD COLUMN requester_email VARCHAR(150) NULL,
  ADD COLUMN location VARCHAR(150) NULL,
  ADD COLUMN required_date DATE NULL,
  ADD COLUMN dispatcher_name VARCHAR(150) NULL,
  ADD COLUMN dispatcher_signature MEDIUMTEXT NULL,
  ADD COLUMN receiver_name VARCHAR(150) NULL,
  ADD COLUMN receiver_signature MEDIUMTEXT NULL,
  ADD COLUMN receiver_date DATE NULL;
