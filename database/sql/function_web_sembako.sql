-- Function harga jual SembaKu. Jalankan setelah ddl_web_sembako.sql.
USE db_web_sembako;

DROP FUNCTION IF EXISTS fn_harga_jual;

DELIMITER $$

CREATE FUNCTION fn_harga_jual(
    p_harga_dasar DECIMAL(14,2),
    p_persen_margin DECIMAL(9,4)
)
RETURNS BIGINT
DETERMINISTIC
NO SQL
BEGIN
    RETURN ROUND(p_harga_dasar * (1 + p_persen_margin / 100), 0);
END$$

DELIMITER ;

-- Uji: harga dasar 10000 dengan margin 12% menghasilkan 11200.
SELECT fn_harga_jual(10000, 12) AS harga_jual;
