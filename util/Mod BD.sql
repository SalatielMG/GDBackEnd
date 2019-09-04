--
-- Table structure for table `usuarios`
--
CREATE TABLE IF NOT EXISTS `usuarios` (
   `id` INT NOT NULL AUTO_INCREMENT,
   `email` VARCHAR(50) NOT NULL unique,
   `password` VARCHAR(255) NOT NULL,
   `insert` TINYINT NOT NULL,
   `update` TINYINT NOT NULL,
   `delete` TINYINT NOT NULL,
   `export` TINYINT NOT NULL,
   PRIMARY KEY (`id`))
  ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

insert into usuarios value(0, 'ejemplo@ejemplo.com', '$2y$15$53WMNcZtZSNihJQ4LeUEg.mIc31EFj3iCm773Umt85hyb4s.R9srK', 1, 1, 1, 1);


--
-- Procedimiento almacenado `nombreCuenta`
--
DELIMITER $$
CREATE DEFINER=`root`@`localhost` FUNCTION `nombreCuenta`(`idBackup` INT(10), `idAccount` SMALLINT(5)) RETURNS varchar(50) CHARSET utf8
BEGIN
  DECLARE nameAccount VARCHAR(50);
  SET nameAccount = (SELECT name FROM backup_accounts WHERE id_backup = idBackup AND id_account = idAccount);

  if (nameAccount IS NULL) then
  	SET nameAccount = (SELECT account FROM backup_extras WHERE id_extra = idAccount and id_backup = idBackup LIMIT 1, 1);
  	if(nameAccount IS NULL) then
  		SET nameAccount = 'Cuenta no encontrada';
  	END if;
  END if;

  return nameAccount;
END$$
DELIMITER ;

--
-- Procedimiento almacenado `nombreCategoria`
--
DELIMITER $$
CREATE DEFINER=`root`@`localhost` FUNCTION `nombreCategoria`(`idBackup` INT(10), `idCategory` SMALLINT(5)) RETURNS varchar(50) CHARSET utf8
    NO SQL
BEGIN
DECLARE nameCategory VARCHAR(50);
  SET nameCategory = (SELECT name FROM backup_categories WHERE id_backup = idBackup AND id_category = idCategory);

  if (nameCategory IS NULL) then
  	SET nameCategory = (SELECT category FROM backup_extras WHERE id_extra = idCategory and id_backup = idBackup LIMIT 1, 1);
  	if(nameCategory IS NULL) then
  		SET nameCategory = 'Categoria no encontrada';
  	END if;
  END if;

  return nameCategory;

  END$$
DELIMITER ;

/*************************Vistas tabla duplicate_backup sin duplicidades*************************/
CREATE VIEW full_backup_accounts AS SELECT DISTINCTROW * FROM duplicate_backup_accounts;
CREATE VIEW full_backup_automatics AS SELECT DISTINCTROW * FROM duplicate_backup_automatics;
CREATE VIEW full_backup_budgets AS SELECT DISTINCTROW * FROM duplicate_backup_budgets;
CREATE VIEW full_backup_cardviews AS SELECT DISTINCTROW * FROM duplicate_backup_cardviews;
CREATE VIEW full_backup_categories AS SELECT DISTINCTROW * FROM duplicate_backup_categories;
CREATE VIEW full_backup_currencies AS SELECT DISTINCTROW * FROM duplicate_backup_currencies;
CREATE VIEW full_backup_extras AS SELECT DISTINCTROW * FROM duplicate_backup_extras;
CREATE VIEW full_backup_movements AS SELECT DISTINCTROW * FROM duplicate_backup_movements;
CREATE VIEW full_backup_preferences AS SELECT DISTINCTROW * FROM duplicate_backup_preferences;
/*************************Vistas tabla duplicate_backup sin duplicidades*************************/


/*************************Inconsistencia backup_accounts*************************/
CREATE TABLE backup_accounts_duplicado LIKE backup_accounts;
ALTER TABLE backup_accounts_duplicado ADD UNIQUE(id_backup, id_account);
INSERT IGNORE INTO backup_accounts_duplicado SELECT * FROM backup_accounts ORDER BY id_backup;
RENAME TABLE backup_accounts TO duplicate_backup_accounts, backup_accounts_duplicado TO backup_accounts;
/*************************Inconsistencia backup_accounts*************************/

/*************************Inconsistencia backup_automatics*************************/
CREATE TABLE backup_automatics_duplicado LIKE backup_automatics;
ALTER TABLE backup_automatics_duplicado ADD UNIQUE(id_backup, id_operation, id_account, id_category, period, amount, initial_date);
INSERT IGNORE INTO backup_automatics_duplicado SELECT * FROM backup_automatics ORDER BY id_backup;
RENAME TABLE backup_automatics TO duplicate_backup_automatics, backup_automatics_duplicado TO backup_automatics;
/*************************Inconsistencia backup_automatics*************************/

/*************************Inconsistencia backup_budgets*************************/
CREATE TABLE backup_budgets_duplicado LIKE backup_budgets;
ALTER TABLE backup_budgets_duplicado ADD UNIQUE(id_backup, id_account, id_category, period, amount, budget);
INSERT IGNORE INTO backup_budgets_duplicado SELECT * FROM backup_budgets ORDER BY id_backup;
RENAME TABLE backup_budgets TO duplicate_backup_budgets, backup_budgets_duplicado TO backup_budgets;
/*************************Inconsistencia backup_budgets*************************/

/*************************Inconsistencia backup_cardviews*************************/
CREATE TABLE backup_cardviews_duplicado LIKE backup_cardviews;
ALTER TABLE backup_cardviews_duplicado ADD UNIQUE(id_backup, id_card);
INSERT IGNORE INTO backup_cardviews_duplicado SELECT * FROM backup_cardviews ORDER BY id_backup;
RENAME TABLE backup_cardviews TO duplicate_backup_cardviews, backup_cardviews_duplicado TO backup_cardviews;
/*************************Inconsistencia backup_cardviews*************************/

/*************************Inconsistencia backup_categories*************************/
CREATE TABLE backup_categories_duplicado LIKE backup_categories;
ALTER TABLE backup_categories_duplicado ADD UNIQUE(id_backup, id_category, id_account);
INSERT IGNORE INTO backup_categories_duplicado SELECT * FROM backup_categories ORDER BY id_backup;
RENAME TABLE backup_categories TO duplicate_backup_categories, backup_categories_duplicado TO backup_categories;
/*************************Inconsistencia backup_categories*************************/

/*************************Inconsistencia backup_currencies*************************/
CREATE TABLE backup_currencies_duplicado LIKE backup_currencies;
ALTER TABLE backup_currencies_duplicado ADD UNIQUE(id_backup,iso_code);
INSERT IGNORE INTO backup_currencies_duplicado SELECT * FROM backup_currencies ORDER BY id_backup;
RENAME TABLE backup_currencies TO duplicate_backup_currencies, backup_currencies_duplicado TO backup_currencies;
/*************************Inconsistencia backup_currencies*************************/

/*************************Inconsistencia backup_extras*************************/
CREATE TABLE backup_extras_duplicado LIKE backup_extras;
ALTER TABLE backup_extras_duplicado ADD UNIQUE(id_backup, id_extra);
INSERT IGNORE INTO backup_extras_duplicado SELECT * FROM backup_extras ORDER BY id_backup;
RENAME TABLE backup_extras TO duplicate_backup_extras, backup_extras_duplicado TO backup_extras;
/*************************Inconsistencia backup_extras*************************/

/*************************Inconsistencia backup_movements*************************/
CREATE TABLE backup_movements_duplicado LIKE backup_movements;
ALTER TABLE backup_movements_duplicado ADD UNIQUE(id_backup, id_account, id_category, amount, detail, date_idx);
INSERT IGNORE INTO backup_movements_duplicado SELECT * FROM backup_movements ORDER BY id_backup;
RENAME TABLE backup_movements TO duplicate_backup_movements, backup_movements_duplicado TO backup_movements;
/*************************Inconsistencia backup_movements*************************/

/*************************Inconsistencia backup_preferences*************************/
CREATE TABLE backup_preferences_duplicado LIKE backup_preferences;
ALTER TABLE backup_preferences_duplicado ADD UNIQUE(id_backup, key_name);
INSERT IGNORE INTO backup_preferences_duplicado SELECT * FROM backup_preferences ORDER BY id_backup;
RENAME TABLE backup_preferences TO duplicate_backup_preferences, backup_preferences_duplicado TO backup_preferences;
/*************************Inconsistencia backup_preferences*************************/

