<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h2 class="h5 mb-0 fw-bold">Registrar Nueva Sucursal</h2>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info border-0 shadow-sm mb-4">
                    <div class="d-flex">
                        <i class="fas fa-magic fa-lg me-3 mt-1"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Automatización Activada</h6>
                            <p class="small mb-0">Al crear la sucursal, el sistema generará automáticamente un usuario administrador con el prefijo <code>admin_</code> y el nombre de la sucursal.</p>
                        </div>
                    </div>
                </div>

                <form action="<?= APP_URL ?>?route=branches&action=store" method="POST">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Nombre de la Sucursal *</label>
                            <input type="text" name="name" class="form-control form-control-lg" placeholder="Ej: Sede Norte Florencia" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">NIT / Identificación</label>
                            <input type="text" name="nit" class="form-control" placeholder="900.000.000-1">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ciudad</label>
                            <input type="text" name="city" class="form-control" placeholder="Florencia">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Dirección Completa</label>
                            <input type="text" name="address" class="form-control" placeholder="Carrera 10 #12-34 Barrio Centro">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Teléfono de Contacto</label>
                            <input type="text" name="phone" class="form-control" placeholder="310 123 4567">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" placeholder="sede_norte@pharmacrm.com">
                        </div>
                    </div>

                    <div class="mt-5 d-flex gap-3">
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="fas fa-save me-2"></i> Crear Sucursal
                        </button>
                        <a href="<?= APP_URL ?>?route=branches" class="btn btn-outline-secondary btn-lg px-4">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

