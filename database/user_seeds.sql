USE pharmacrm;

-- Seeds adicionales para usuarios
-- Password para todos: Admin123 ($2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi)

-- Administradores Adicionales
INSERT INTO
    users (
        role_id,
        username,
        email,
        password,
        full_name,
        phone
    )
VALUES (
        1,
        'diana_admin',
        'diana.admin@pharmacrm.local',
        '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi',
        'Diana Marcela Restrepo',
        '3124567890'
    ),
    (
        1,
        'felipe_admin',
        'felipe.admin@pharmacrm.local',
        '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi',
        'Andrés Felipe Rivera',
        '3135678901'
    );

-- Supervisores Adicionales
INSERT INTO
    users (
        role_id,
        username,
        email,
        password,
        full_name,
        phone
    )
VALUES (
        2,
        'jorge_super',
        'jorge.super@pharmacrm.local',
        '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi',
        'Jorge Eliécer Gaitán',
        '3146789012'
    ),
    (
        2,
        'lucia_super',
        'lucia.super@pharmacrm.local',
        '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi',
        'Lucía Elena Torres',
        '3157890123'
    ),
    (
        2,
        'roberto_super',
        'roberto.super@pharmacrm.local',
        '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi',
        'Roberto Gómez Vargas',
        '3168901234'
    );

-- Cajeros Adicionales
INSERT INTO
    users (
        role_id,
        username,
        email,
        password,
        full_name,
        phone
    )
VALUES (
        3,
        'cajero2',
        'cajero2@pharmacrm.local',
        '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi',
        'Valentina Hernández',
        '3179012345'
    ),
    (
        3,
        'cajero3',
        'cajero3@pharmacrm.local',
        '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi',
        'Carlos Eduardo Martínez',
        '3180123456'
    ),
    (
        3,
        'cajero4',
        'cajero4@pharmacrm.local',
        '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi',
        'María Fernanda Pérez',
        '3191234567'
    ),
    (
        3,
        'cajero5',
        'cajero5@pharmacrm.local',
        '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi',
        'Jorge Luis Ramírez',
        '3202345678'
    ),
    (
        3,
        'cajero6',
        'cajero6@pharmacrm.local',
        '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi',
        'Ana María González',
        '3213456789'
    );