USE pharmacrm;

-- Agregar branch_id a inventory_movements si no existe
SET @exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = 'pharmacrm' AND table_name = 'inventory_movements' AND column_name = 'branch_id');
SET @query = IF(@exists = 0, 'ALTER TABLE inventory_movements ADD COLUMN branch_id INT DEFAULT 1 AFTER user_id', 'SELECT "Column already exists"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar el constraint si no existe
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.table_constraints WHERE constraint_schema = 'pharmacrm' AND table_name = 'inventory_movements' AND constraint_name = 'fk_movement_branch');
SET @fk_query = IF(@fk_exists = 0, 'ALTER TABLE inventory_movements ADD CONSTRAINT fk_movement_branch FOREIGN KEY (branch_id) REFERENCES branches(id)', 'SELECT "FK already exists"');
PREPARE stmt2 FROM @fk_query;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;
