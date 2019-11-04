-- -----------------------------------------------------
-- Table `usuarios`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `tipo` ENUM('admin', 'aux') NOT NULL,
  `cargo` VARCHAR(50) NOT NULL,
  `imagen` VARCHAR(255) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

insert into usuarios value(0, 'encodemx@encodemx.com', '$2y$15$53WMNcZtZSNihJQ4LeUEg.mIc31EFj3iCm773Umt85hyb4s.R9srK', 'admin', 'CEO', '');


-- -----------------------------------------------------
-- Table `permisos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `permisos` (
  `permiso` VARCHAR(50) NOT NULL UNIQUE,
  `descripcion` VARCHAR(255) NULL,
  PRIMARY KEY (`permiso`))
ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `usuarios_permisos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios_permisos` (
  `usuario` INT NOT NULL,
  `permiso` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`usuario`, `permiso`),
    FOREIGN KEY (`usuario`)
    REFERENCES `usuarios` (`id`),
    FOREIGN KEY (`permiso`)
    REFERENCES `permisos` (`permiso`))
ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*
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

insert into usuarios value(0, 'encodemx@encodemx.com', '$2y$15$53WMNcZtZSNihJQ4LeUEg.mIc31EFj3iCm773Umt85hyb4s.R9srK', 1, 1, 1, 1);*/

--
-- Table structure for table `table_currencies`
--
CREATE TABLE IF NOT EXISTS `table_currencies` (
   `_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   `iso_code` char(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
   `symbol` char(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
   `icon` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
   `selected` tinyint(1) NOT NULL DEFAULT 0)
  ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




--
-- Procedimiento almacenado `symbolCurrency`
--
DELIMITER $$
CREATE DEFINER=`root`@`localhost` FUNCTION `symbolCurrency`(`idBackup` INT(10), `isoCode` CHAR(3), `idAccount` SMALLINT(5)) RETURNS CHAR(5) CHARSET utf8
BEGIN

  DECLARE symbol CHAR(5);
  IF (idAccount != 0) then
    SET isoCode = (SELECT iso_code FROM backup_accounts WHERE  id_backup = idBackup AND id_account = idAccount GROUP BY id_backup, id_account HAVING COUNT( * ) >= 1);
    IF (isoCode IS NULL) then
     	SET symbol = '';
      return symbol;
    END IF;
  END IF;

  SET symbol = (SELECT bc.symbol FROM backup_currencies bc WHERE bc.id_backup = idBackup AND bc.iso_code = isoCode GROUP BY bc.id_backup, bc.iso_code HAVING COUNT( * ) >= 1);

  IF (symbol IS NULL) THEN
    SET symbol = (SELECT tc.symbol FROM table_currencies tc WHERE tc.iso_code = isoCode);
    IF (symbol IS NULL) THEN
      SET symbol = '';
    END IF;
  END IF;

  RETURN symbol;

END$$
DELIMITER ;


--
-- Procedimiento almacenado `nameAccount`
--
DELIMITER $$
CREATE DEFINER=`root`@`localhost` FUNCTION `nameAccount`(`idBackup` INT(10), `idAccount` SMALLINT(5)) RETURNS varchar(50) CHARSET utf8
BEGIN
  DECLARE nameAccount VARCHAR(50);
  SET nameAccount = (SELECT name FROM backup_accounts WHERE id_backup = idBackup AND id_account = idAccount GROUP BY id_backup, id_account HAVING COUNT( * ) >= 1);

  if (nameAccount IS NULL) then
  	SET nameAccount = (SELECT account FROM backup_extras WHERE id_extra = idAccount and id_backup = idBackup GROUP BY id_backup, id_extra HAVING COUNT( * ) >= 1);
  	if(nameAccount IS NULL) then

  	  CASE idAccount
        WHEN 10000 THEN
         SET nameAccount = 'Transferencias';
        WHEN 10001 THEN
         SET nameAccount = 'Todas las cuentas';
        WHEN 10002 THEN
         SET nameAccount = 'Todas las categorias';
        ELSE
          BEGIN
            SET nameAccount = 'Cuenta no encontrada';
          END;
      END CASE;

  	END if;
  END if;

  return nameAccount;
END$$
DELIMITER ;

--
-- Procedimiento almacenado `nameCategory`
--
DELIMITER $$
CREATE DEFINER=`root`@`localhost` FUNCTION `nameCategory`(`idBackup` INT(10), `idCategory` SMALLINT(5)) RETURNS varchar(50) CHARSET utf8
    NO SQL
BEGIN
  DECLARE nameCategory VARCHAR(50);
  SET nameCategory = (SELECT name FROM backup_categories WHERE id_backup = idBackup AND id_category = idCategory GROUP BY id_backup, id_category, id_account HAVING COUNT( * ) >= 1);

  if (nameCategory IS NULL) then
  	SET nameCategory = (SELECT category FROM backup_extras WHERE id_extra = idCategory and id_backup = idBackup GROUP BY id_backup, id_extra HAVING COUNT( * ) >= 1);
  	if(nameCategory IS NULL) then

  	  CASE idCategory
        WHEN 10000 THEN
         SET nameCategory = 'Transferencias';
        WHEN 10001 THEN
         SET nameCategory = 'Todas las cuentas';
        WHEN 10002 THEN
         SET nameCategory = 'Todas las categorias';
        ELSE
          BEGIN
            SET nameCategory = 'Categoria no encontrada';
          END;
      END CASE;

  	END if;
  END if;

  return nameCategory;

  END$$
DELIMITER ;



-- PENDIENTE --

--
-- Trigger mntBackup`
-- gastos5
CREATE TRIGGER `automatizarBackups` AFTER INSERT ON `backups` FOR EACH ROW
BEGIN

  DECLARE done INT DEFAULT FALSE;
  DECLARE idP INT;
  DECLARE consulta CURSOR FOR (select tabla.id_backup from ((SELECT @rownum:=@rownum+1 AS pos, b.id_backup FROM (SELECT @rownum:=0) r, `backups` b
	where b.id_user = new.id_user ORDER BY `b`.`id_backup` DESC) as tabla) where tabla.pos > 10);
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  OPEN consulta;

  read_loop: LOOP
    FETCH consulta INTO idP;
    IF done THEN
      LEAVE read_loop;
    END IF;
    delete from backups where id_backup = idP;
  END LOOP;

  CLOSE consulta;


	DELETE FROM backups WHERE id_
	backup in (select tabla.id_backup from ((SELECT @rownum:=@rownum+1 AS pos, b.id_backup FROM (SELECT @rownum:=0) r, `backups` b
	where b.id_user = new.id_user ORDER BY `b`.`id_backup` DESC) as tabla) where tabla.pos > 10);
END

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