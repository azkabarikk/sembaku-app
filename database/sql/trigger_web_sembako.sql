USE db_web_sembako;

DROP TRIGGER IF EXISTS trg_validasi_kartu_stok;

DELIMITER $$

CREATE TRIGGER trg_validasi_kartu_stok
BEFORE INSERT ON kartu_stok
FOR EACH ROW
BEGIN
    DECLARE v_saldo BIGINT;

    -- Mengambil saldo stok terakhir berdasarkan riwayat mutasi
    SELECT COALESCE(SUM(masuk - keluar), 0)
    INTO v_saldo
    FROM kartu_stok
    WHERE idbarang = NEW.idbarang;

    -- Menghitung saldo stok setelah transaksi baru
    SET NEW.stock = v_saldo + NEW.masuk - NEW.keluar;
END$$

DELIMITER ;
