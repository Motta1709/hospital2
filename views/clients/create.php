<?php
/**
 * Vista: Formulario de Creación/Edición de Clientes
 * -----------------------------------------------
 * Esta vista permite capturar o actualizar la información de un cliente/paciente.
 */
$client = $data['client'] ?? null;
$isEdit = !empty($client);
?>

<div class="card" style="max-width: 700px; margin: 0 auto;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-<?= $isEdit ? 'edit' : 'user-plus' ?>"></i> 
            <?= $isEdit ? 'Editar' : 'Nuevo' ?> Cliente
        </h3>
    </div>

    <form action="<?= APP_URL ?>?route=clients&action=<?= $isEdit ? 'update' : 'store' ?>" method="POST" class="p-4">
        
        <?php if ($isEdit): ?>
            <!-- Campo oculto para el ID en caso de edición -->
            <input type="hidden" name="id" value="<?= $client['id'] ?>">
        <?php endif; ?>

        <!-- Sección: Nombre y Apellido -->
        <div class="form-row mb-3">
            <div class="form-group col-md-6">
                <label for="first_name">Nombre *</label>
                <input type="text" 
                       id="first_name" 
                       name="first_name" 
                       class="form-control" 
                       value="<?= htmlspecialchars($client['first_name'] ?? '') ?>" 
                       required>
            </div>
            <div class="form-group col-md-6">
                <label for="last_name">Apellido *</label>
                <input type="text" 
                       id="last_name" 
                       name="last_name" 
                       class="form-control" 
                       value="<?= htmlspecialchars($client['last_name'] ?? '') ?>" 
                       required>
            </div>
        </div>

        <!-- Sección: Identificación -->
        <div class="form-row mb-3">
            <div class="form-group col-md-6">
                <label for="document_type">Tipo de Documento</label>
                <select id="document_type" name="document_type" class="form-control">
                    <?php 
                    $docTypes = [
                        'CC'  => 'Cédula de Ciudadanía',
                        'TI'  => 'Tarjeta de Identidad',
                        'CE'  => 'Cédula de Extranjería',
                        'PA'  => 'Pasaporte',
                        'NIT' => 'NIT'
                    ];
                    foreach ($docTypes as $key => $label): 
                    ?>
                        <option value="<?= $key ?>" <?= ($client['document_type'] ?? 'CC') == $key ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="document_number">Número de Documento *</label>
                <input type="text" 
                       id="document_number" 
                       name="document_number" 
                       class="form-control" 
                       value="<?= htmlspecialchars($client['document_number'] ?? '') ?>" 
                       required>
            </div>
        </div>

        <!-- Sección: Contacto -->
        <div class="form-row mb-3">
            <div class="form-group col-md-6">
                <label for="phone">Teléfono</label>
                <input type="text" 
                       id="phone" 
                       name="phone" 
                       class="form-control" 
                       value="<?= htmlspecialchars($client['phone'] ?? '') ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="email">Email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-control" 
                       value="<?= htmlspecialchars($client['email'] ?? '') ?>">
            </div>
        </div>

        <!-- Sección: Ubicación y Nacimiento -->
        <div class="form-row mb-3">
            <div class="form-group col-md-6">
                <label for="city">Ciudad</label>
                <input type="text" 
                       id="city" 
                       name="city" 
                       class="form-control" 
                       value="<?= htmlspecialchars($client['city'] ?? '') ?>">
            </div>
            <div class="form-group col-md-6">
                <label for="date_of_birth">Fecha de Nacimiento</label>
                <input type="date" 
                       id="date_of_birth" 
                       name="date_of_birth" 
                       class="form-control" 
                       value="<?= $client['date_of_birth'] ?? '' ?>">
            </div>
        </div>

        <!-- Sección: Información Personal -->
        <div class="form-row mb-3">
            <div class="form-group col-md-6">
                <label for="gender">Género</label>
                <select id="gender" name="gender" class="form-control">
                    <option value="">— Seleccione —</option>
                    <option value="M" <?= ($client['gender'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                    <option value="F" <?= ($client['gender'] ?? '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                    <option value="O" <?= ($client['gender'] ?? '') === 'O' ? 'selected' : '' ?>>Otro</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="address">Dirección</label>
                <input type="text" 
                       id="address" 
                       name="address" 
                       class="form-control" 
                       value="<?= htmlspecialchars($client['address'] ?? '') ?>">
            </div>
        </div>

        <!-- Sección: Datos Médicos -->
        <div class="form-group mb-3">
            <label for="allergies">Alergias Conocidas</label>
            <textarea id="allergies" 
                      name="allergies" 
                      class="form-control" 
                      rows="2"
                      placeholder="Ej: Penicilina, Sulfonamidas..."><?= htmlspecialchars($client['allergies'] ?? '') ?></textarea>
        </div>

        <div class="form-group mb-4">
            <label for="medical_notes">Notas Médicas</label>
            <textarea id="medical_notes" 
                      name="medical_notes" 
                      class="form-control" 
                      rows="3"><?= htmlspecialchars($client['medical_notes'] ?? '') ?></textarea>
        </div>

        <!-- Botones de Acción -->
        <div class="d-flex gap-2 border-top pt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?= $isEdit ? 'Actualizar' : 'Guardar' ?> Cliente
            </button>
            <a href="<?= APP_URL ?>?route=clients" class="btn btn-outline-secondary">
                Cancelar
            </a>
        </div>

    </form>
</div>
