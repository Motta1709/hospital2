USE pharmacrm;

-- Clientes Adicionales
INSERT INTO
    clients (
        document_type,
        document_number,
        first_name,
        last_name,
        email,
        phone,
        city,
        date_of_birth,
        gender,
        loyalty_points
    )
VALUES (
        'CC',
        '1001112223',
        'Diana',
        'Restrepo',
        'diana.restrepo@example.com',
        '3104445566',
        'Florencia',
        '1992-04-15',
        'F',
        150
    ),
    (
        'CC',
        '1002223334',
        'Andrés',
        'Felipe Rivera',
        'andres.rivera@example.com',
        '3117778899',
        'Morelia',
        '1988-12-10',
        'M',
        80
    ),
    (
        'CE',
        '900888777',
        'John',
        'Smith',
        'john.smith@example.com',
        '3201112233',
        'Florencia',
        '1980-05-20',
        'M',
        200
    );

-- Productos Adicionales
INSERT INTO
    products (
        category_id,
        supplier_id,
        barcode,
        name,
        generic_name,
        presentation,
        concentration,
        lot_number,
        expiration_date,
        purchase_price,
        sale_price,
        stock,
        min_stock
    )
VALUES (
        6,
        1,
        '7701234567890',
        'Centrum Silver',
        'Multivitamínico',
        'Frasco x 60 tabletas',
        'N/A',
        'LOT-999',
        '2027-12-31',
        35000.00,
        52000.00,
        20,
        5
    ),
    (
        11,
        2,
        '7709876543210',
        'Alcohol Antiséptico',
        'Alcohol Etílico',
        'Frasco x 500ml',
        '70%',
        'LOT-888',
        '2028-06-30',
        4500.00,
        7500.00,
        50,
        10
    ),
    (
        1,
        3,
        '7705554443332',
        'Dolex Avanzado',
        'Acetaminofén',
        'Caja x 24 tabletas',
        '500mg',
        'LOT-777',
        '2027-01-15',
        12000.00,
        18000.00,
        40,
        10
    );

-- Ventas de prueba (simuladas)
INSERT INTO
    sales (
        client_id,
        user_id,
        invoice_number,
        subtotal,
        discount_amount,
        tax_amount,
        total,
        payment_method,
        status,
        created_at
    )
VALUES (
        8,
        3,
        'FV-2026-0010',
        52000.00,
        0,
        0,
        52000.00,
        'card',
        'completed',
        NOW()
    ),
    (
        9,
        3,
        'FV-2026-0011',
        7500.00,
        0,
        0,
        7500.00,
        'cash',
        'completed',
        NOW()
    );

-- Items de venta
INSERT INTO
    sale_items (
        sale_id,
        product_id,
        quantity,
        unit_price,
        subtotal
    )
VALUES (7, 20, 1, 52000.00, 52000.00),
    (8, 21, 1, 7500.00, 7500.00);

##