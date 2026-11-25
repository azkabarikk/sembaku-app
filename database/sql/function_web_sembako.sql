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
    DECLARE v_harga_jual BIGINT;

    -- Menghitung harga jual berdasarkan margin
    SET v_harga_jual = ROUND(
        p_harga_dasar + (p_harga_dasar * p_persen_margin / 100),
        0
    );

    -- Mengembalikan hasil harga jual
    RETURN v_harga_jual;
END$$

DELIMITER ;


-- Membuat function untuk menghitung subtotal barang
DELIMITER $$
CREATE FUNCTION fn_hitung_subtotal(
    p_jumlah INT,
    p_harga_satuan INT
)
RETURNS BIGINT
DETERMINISTIC
NO SQL
BEGIN
    -- Mengalikan jumlah barang dengan harga satuan
    RETURN p_jumlah * p_harga_satuan;
END$$

DELIMITER ;


-- Membuat function untuk menghitung PPN tetap sebesar 11%
DELIMITER $$
CREATE FUNCTION fn_hitung_ppn(
    p_subtotal BIGINT
)
RETURNS BIGINT
DETERMINISTIC
NO SQL
BEGIN
    -- Menghitung PPN sebesar 11% dari subtotal
    RETURN ROUND(p_subtotal * 11 / 100, 0);
END$$
DELIMITER ;
