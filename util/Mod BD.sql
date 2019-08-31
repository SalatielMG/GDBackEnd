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



/*
IdUSER:
41
*/