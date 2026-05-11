<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Error de Sistema'; ?> - PharmaCRM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0D9488;
            --danger: #EF4444;
            --dark: #0F172A;
            --glass: rgba(255, 255, 255, 0.9);
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            color: white;
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .error-container {
            background: var(--glass);
            color: var(--dark);
            padding: 3rem;
            border-radius: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
            max-width: 500px;
            width: 90%;
            position: relative;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icon-box {
            width: 100px;
            height: 100px;
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 2rem;
            font-size: 3rem;
        }
        h1 {
            font-size: 2.5rem;
            margin: 0 0 1rem;
            color: var(--dark);
        }
        p {
            font-size: 1.1rem;
            color: #64748B;
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }
        .btn {
            background: var(--primary);
            color: white;
            padding: 1rem 2rem;
            border-radius: 1rem;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 15px -3px rgba(13, 148, 136, 0.4);
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 25px -5px rgba(13, 148, 136, 0.5);
            background: #0F766E;
        }
        .status-code {
            position: absolute;
            top: 2rem;
            right: 2.5rem;
            font-weight: 800;
            font-size: 1.5rem;
            opacity: 0.2;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="status-code"><?php echo $statusCode ?? 500; ?></div>
        <div class="icon-box">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1>Ups, algo salió mal</h1>
        <p>
            <?php echo $errorMsg ?? 'Ha ocurrido un error inesperado en el servidor. Por favor, intenta de nuevo más tarde o contacta al soporte técnico.'; ?>
        </p>
        <a href="<?php echo APP_URL; ?>" class="btn">
            <i class="fas fa-home"></i>
            Volver al Inicio
        </a>
    </div>
</body>
</html>
