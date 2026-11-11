-- Extend installation tracking and add deinstallation and requisition workflows.
ALTER TABLE gps_devices
  MODIFY COLUMN imei VARCHAR(100) NULL,
  ADD COLUMN camera_sim_product_id INT NULL,
  ADD COLUMN camera_sim_number VARCHAR(50) NULL,
  ADD COLUMN client_id INT NULL,
  ADD COLUMN installer_id INT NULL,
  ADD COLUMN support_id INT NULL,
  ADD COLUMN registration_number VARCHAR(100) NULL,
  ADD COLUMN chassis_number VARCHAR(100) NULL,
  ADD COLUMN deinstalled_at DATETIME NULL;

ALTER TABLE requisitions
  ADD COLUMN request_type VARCHAR(30) NOT NULL DEFAULT 'other',
  ADD COLUMN client_id INT NULL,
  ADD COLUMN beneficiary_name VARCHAR(150) NULL,
  ADD COLUMN requester_signature MEDIUMTEXT NULL,
  ADD COLUMN approver_signature MEDIUMTEXT NULL,
  ADD COLUMN installation_items MEDIUMTEXT NULL,
  ADD COLUMN items_json MEDIUMTEXT NULL,
  ADD CONSTRAINT requisitions_client_fk FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL;

CREATE TABLE deinstallations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  gps_device_id INT NULL,
  client_id INT NULL,
  device_type VARCHAR(80) NOT NULL,
  imei VARCHAR(100),
  camera_serial VARCHAR(100),
  registration_number VARCHAR(100) NOT NULL,
  chassis_number VARCHAR(100) NOT NULL,
  removed_at DATE NOT NULL,
  reason TEXT,
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (gps_device_id) REFERENCES gps_devices(id) ON DELETE SET NULL,
  FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
