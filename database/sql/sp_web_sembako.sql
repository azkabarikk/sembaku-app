USE db_web_sembako;

DROP PROCEDURE IF EXISTS sp_set_margin_aktif;

DELIMITER $$

CREATE PROCEDURE sp_set_margin_aktif(IN p_idmargin INT)
BEGIN
    -- Menonaktifkan semua margin
    UPDATE margin_penjualan
    SET status = 0
    WHERE status = 1;

    -- Mengaktifkan margin yang dipilih
    UPDATE margin_penjualan
    SET status = 1
    WHERE idmargin_penjualan = p_idmargin;
END$$

DELIMITER ;
