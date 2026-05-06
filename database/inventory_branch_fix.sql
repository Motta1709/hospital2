USE pharmacrm;

ALTER TABLE inventory_movements ADD COLUMN branch_id INT DEFAULT 1 AFTER user_id;
ALTER TABLE inventory_movements ADD CONSTRAINT fk_movement_branch FOREIGN KEY (branch_id) REFERENCES branches(id);

-- Asegurarse de que el stock general en products también sea relativo a la sucursal
-- (Ya lo hicimos en branches_schema.sql, pero verificamos que sea obligatorio)
ALTER TABLE products MODIFY branch_id INT NOT NULL DEFAULT 1;
ALTER TABLE product_batches MODIFY branch_id INT NOT NULL DEFAULT 1;
