-- -----------------------------------------------------
-- Table `usuarios`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `tipo` ENUM('superAdmin', 'admin', 'aux') NOT NULL,
  `cargo` VARCHAR(50) NOT NULL,
  `imagen` VARCHAR(255) NULL default 'anonymus.png',
  `codigo` VARCHAR(5) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

insert into usuarios value(1, 'encodemx@encodemx.com', '$2y$15$53WMNcZtZSNihJQ4LeUEg.mIc31EFj3iCm773Umt85hyb4s.R9srK', 'superAdmin', 'CEO', 'anonymus.png', null);


-- -----------------------------------------------------
-- Table `permisos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `permisos` (
  `id` SMALLINT NOT NULL AUTO_INCREMENT,
  `permiso` VARCHAR(50) NOT NULL UNIQUE,
  `descripcion` VARCHAR(255) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT INTO `permisos` (`id`, `permiso`, `descripcion`) VALUES
	(1, 'Insert', 'Agregar registros'),
	(2, 'Update', 'Actualizar registros'),
	(3, 'Delete', 'Eliminar registos'),
	(4, 'Export', 'Exportar Backups a ficheros SQLITE y XLSX'),
	(5, 'Mnt Backups', 'Permite ajustar los backups a una cantidad minina según la indicada'),
	(6, 'Mnt inconsistencia', 'Permite corregir los problemas de inconsistencia en la Base de Datos');

-- -----------------------------------------------------
-- Table `usuarios_permisos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuarios_permisos` (
  `usuario` SMALLINT NOT NULL,
  `permiso` SMALLINT NOT NULL,
  PRIMARY KEY (`usuario`, `permiso`),
    FOREIGN KEY (`usuario`)
    REFERENCES `usuarios` (`id`),
    FOREIGN KEY (`permiso`)
    REFERENCES `permisos` (`id`))
ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT INTO `usuarios_permisos` (`usuario`, `permiso`) VALUES
	(1, 1),
	(1, 2),
	(1, 3),
	(1, 4),
	(1, 5),
	(1, 6);

--
-- Table structure for table `table_currencies`
--
CREATE TABLE IF NOT EXISTS `table_currencies` (
   `_id` SMALLINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
   `iso_code` char(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
   `symbol` char(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
   `icon` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
   `selected` tinyint(1) NOT NULL DEFAULT 0)
  ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO table_currencies (_id, iso_code, symbol, icon, selected) VALUES (1, 'AED', 'د.إ ', 'flag_aed', 0),(2, 'AKZ', 'Kz', 'flag_akz', 0),(3, 'AFS', 'Af', 'flag_afs', 0),(4, 'ARS', '$', 'flag_ars', 0),(5, 'AUD', '$', 'flag_aud', 0),(6, 'AZN', 'ман.', 'flag_azn', 0),(7, 'BAM', 'KM', 'flag_bam', 0),(8, 'BDT', '৳', 'flag_bdt', 0),(9, 'BGN', 'лв.', 'flag_bgn', 0),(10, 'BHD', '.د.ب', 'flag_bhd', 0),(11, 'BOB', 'Bs.', 'flag_bob', 0),(12, 'BTC', 'Ƀ', 'flag_btc', 0),(13, 'BRL', 'R$', 'flag_brl', 0),(14, 'CAD', 'C$', 'flag_cad', 0),(15, 'CHF', 'Fr.', 'flag_chf', 0),(16, 'CLP', '$', 'flag_clp', 0),(17, 'CNY', '¥', 'flag_cny', 0),(18, 'COP', '$', 'flag_cop', 0),(19, 'CRC', '₡', 'flag_crc', 0),(20, 'CZK', 'Kč', 'flag_czk', 0),(21, 'DKK', 'kr', 'flag_dkk', 0),(22, 'DOP', 'RD$', 'flag_dop', 0),(23, 'EGP', 'ج.م', 'flag_egp', 0),(24, 'EUR', '€', 'flag_eur', 0),(25, 'GBP', '£', 'flag_gbp', 0),(26, 'GTQ', 'Q', 'flag_gtq', 0),(27, 'HKD', '$', 'flag_hkd', 0),(28, 'HNL', 'L', 'flag_huf', 0),(29, 'HRK', 'kn', 'flag_hrk', 0),(30, 'HUF', 'Ft', 'flag_huf', 0),(31, 'IDR', 'Rp', 'flag_idr', 0),(32, 'ILS', '₪', 'flag_ils', 0),(33, 'INR', '₹', 'flag_inr', 0),(34, 'JPY', '¥', 'flag_jpy', 0),(35, 'KES', 'KSh', 'flag_kes', 0),(36, 'KRW', '₩', 'flag_krw', 0),(37, 'KWD', 'د.ك', 'flag_kwd', 0),(38, 'KZT', 'T', 'flag_kzt', 0),(39, 'MAD', 'درهم', 'flag_mad', 0),(40, 'LBP', 'ل.ل.', 'flag_lbp', 0),(41, 'LKR', 'Rs', 'flag_lkr', 0),(42, 'MXN', '$', 'flag_mxn', 0),(43, 'MYR', 'RM', 'flag_myr', 0),(44, 'NGN', '₦', 'flag_ngn', 0),(45, 'NIO', 'C$', 'flag_nio', 0),(46, 'NOK', 'kr', 'flag_nok', 0),(47, 'NZD', '$', 'flag_nzd', 0),(48, 'OMR', 'ر.ع.', 'flag_omr', 0),(49, 'PAB', '$', 'flag_pab', 0),(50, 'PEN', 'S/.', 'flag_pen', 0),(51, 'PHP', '₱', 'flag_php', 0),(52, 'PKR', 'Rs', 'flag_pkr', 0),(53, 'PLN', 'zł', 'flag_pln', 0),(54, 'PYG', 'Gs.', 'flag_pyg', 0),(55, 'RON', 'lei', 'flag_ron', 0),(56, 'RUB', '₽', 'flag_rub', 0),(57, 'RWF', 'R₣', 'flag_rwf', 0),(58, 'SAR', 'ر.س', 'flag_sar', 0),(59, 'SEK', 'kr', 'flag_sek', 0),(60, 'SGD', '$', 'flag_sgd', 0),(61, 'SVC', '₡', 'flag_svd', 0),(62, 'THB', '฿', 'flag_thb', 0),(63, 'TND', 'د.ت', 'flag_tnd', 0),(64, 'TRY', '₺', 'flag_try', 0),(65, 'TWD', '$', 'flag_twd', 0),(66, 'TZS', 'Tsh', 'flag_tzs', 0),(67, 'UAH', '₴', 'flag_uah', 0),(68, 'USD', '$', 'flag_usd', 0),(69, 'UYU', '$U', 'flag_uyu', 0),(70, 'VEF', 'Bs.', 'flag_vef', 0),(71, 'VND', '₫', 'flag_vnd', 0),(72, 'XOF', 'CFA', 'flag_xof', 0),(73, 'ZAR', 'R', 'flag_zar', 0);



--
-- Procedimiento almacenado `symbolCurrency`
--
DELIMITER $$
CREATE DEFINER=`cpses_gay7tk23r0`@`localhost` FUNCTION `symbolCurrency`(`idBackup` INT(10), `isoCode` CHAR(3), `idAccount` SMALLINT(5)) RETURNS CHAR(5) CHARSET utf8 COLLATE utf8_unicode_ci
LANGUAGE SQL
DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
BEGIN

  DECLARE symbol CHAR(5) CHARSET utf8 COLLATE utf8_unicode_ci;
  IF (idAccount != 0) then
    SET isoCode = (SELECT iso_code  FROM backup_accounts WHERE  id_backup = idBackup AND id_account = idAccount GROUP BY id_backup, id_account HAVING COUNT( * ) >= 1);
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
CREATE DEFINER=`cpses_gay7tk23r0`@`localhost` FUNCTION `nameAccount`(`idBackup` INT(10), `idAccount` SMALLINT(5)) RETURNS varchar(50) CHARSET utf8 COLLATE utf8_unicode_ci
LANGUAGE SQL
DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
BEGIN
  DECLARE nameAccount VARCHAR(50) CHARSET utf8 COLLATE utf8_unicode_ci;
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
CREATE DEFINER=`cpses_gay7tk23r0`@`localhost` FUNCTION `nameCategory`(`idBackup` INT(10), `idCategory` SMALLINT(5)) RETURNS varchar(50) CHARSET utf8 COLLATE utf8_unicode_ci
LANGUAGE SQL
DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
BEGIN
  DECLARE nameCategory VARCHAR(50) CHARSET utf8 COLLATE utf8_unicode_ci;
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