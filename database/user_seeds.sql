SET NAMES utf8mb4;
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
        X'416E6472C3A9732046656C69706520526976657261',
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
        X'4A6F72676520456C69C3A96365722047616974C3A16E',
        '3146789012'
    ),
    (
        2,
        'lucia_super',
        'lucia.super@pharmacrm.local',
        '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi',
        X'4C7563C3AD6120456C656E6120546F72726573',
        '3157890123'
    ),
    (
        2,
        'roberto_super',
        'roberto.super@pharmacrm.local',
        '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi',
        X'526F626572746F2047C3B36D657A20566172676173',
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
        X'56616C656E74696E61204865726EC3A16E64657A',
        '3179012345'
    ),
    (
        3,
        'cajero3',
        'cajero3@pharmacrm.local',
        '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi',
        X'4361726C6F73204564756172646F204D617274C3AD6E657A',
        '3180123456'
    ),
    (
        3,
        'cajero4',
        'cajero4@pharmacrm.local',
        '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi',
        X'4D6172C3AD61204665726E616E64612050C3A972657A',
        '3191234567'
    ),
    (
        3,
        'cajero5',
        'cajero5@pharmacrm.local',
        '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi',
        X'4A6F726765204C7569732052616DC3AD72657A',
        '3202345678'
    ),
    (
        3,
        'cajero6',
        'cajero6@pharmacrm.local',
        '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi',
        X'416E61204D6172C3AD6120476F6E7AC3A16C657A',
        '3213456789'
    );

##