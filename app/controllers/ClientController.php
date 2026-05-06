<?php
class ClientController {
    private $clientModel;

    public function __construct() {
        $this->clientModel = new Client();
    }

    public function index() {
        if (!Auth::hasPermission('view_clients')) {
            setFlash('error', 'No tienes permiso para ver los clientes.');
            redirect('?route=dashboard');
        }
        $search = sanitize($_GET['search'] ?? '');
        $page = max(1, intval($_GET['page'] ?? 1));
        $data = [
            'clients' => $this->clientModel->getAll($page, ITEMS_PER_PAGE, $search),
            'totalClients' => $this->clientModel->count($search),
            'search' => $search,
            'page' => $page,
            'totalPages' => ceil($this->clientModel->count($search) / ITEMS_PER_PAGE),
        ];
        $pageTitle = 'Clientes';
        $currentRoute = 'clients';
        $content = APP_ROOT . '/views/clients/index.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function create() {
        if (!Auth::hasPermission('manage_clients')) {
            setFlash('error', 'No tienes permiso para gestionar clientes.');
            redirect('?route=clients');
        }
        $pageTitle = 'Nuevo Cliente';
        $currentRoute = 'clients';
        $content = APP_ROOT . '/views/clients/create.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function store() {
        if (!Auth::hasPermission('manage_clients')) redirect('?route=clients');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('?route=clients');
        $v = new Validator($_POST);
        $v->required('first_name', 'Nombre')->required('last_name', 'Apellido')
          ->required('document_number', 'Documento');
        if ($v->fails()) {
            setFlash('error', $v->firstError());
            redirect('?route=clients&action=create');
        }
        try {
            $this->clientModel->create($_POST);
            setFlash('success', 'Cliente creado exitosamente.');
            redirect('?route=clients');
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
            redirect('?route=clients&action=create');
        }
    }

    public function profile() {
        if (!Auth::hasPermission('view_clients')) {
            setFlash('error', 'No tienes permiso para ver perfiles de clientes.');
            redirect('?route=dashboard');
        }
        $id = intval($_GET['id'] ?? 0);
        $client = $this->clientModel->findById($id);
        if (!$client) { setFlash('error', 'Cliente no encontrado.'); redirect('?route=clients'); }

        $data = [
            'client' => $client,
            'purchases' => $this->clientModel->getClientPurchaseHistory($id),
            'medications' => $this->clientModel->getFrequentMedications($id),
            'loyaltyTransactions' => (new LoyaltyProgram())->getTransactions($id),
        ];
        $pageTitle = $client['first_name'] . ' ' . $client['last_name'];
        $currentRoute = 'clients';
        $content = APP_ROOT . '/views/clients/profile.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function edit() {
        if (!Auth::hasPermission('manage_clients')) {
            setFlash('error', 'No tienes permiso para gestionar clientes.');
            redirect('?route=clients');
        }
        $id = intval($_GET['id'] ?? 0);
        $client = $this->clientModel->findById($id);
        if (!$client) { setFlash('error', 'Cliente no encontrado.'); redirect('?route=clients'); }
        $data = ['client' => $client];
        $pageTitle = 'Editar Cliente';
        $currentRoute = 'clients';
        $content = APP_ROOT . '/views/clients/create.php';
        include APP_ROOT . '/views/layouts/main.php';
    }

    public function update() {
        if (!Auth::hasPermission('manage_clients')) redirect('?route=clients');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('?route=clients');
        $id = intval($_POST['id'] ?? 0);
        try {
            $this->clientModel->update($id, $_POST);
            setFlash('success', 'Cliente actualizado.');
        } catch (Exception $e) {
            setFlash('error', 'Error: ' . $e->getMessage());
        }
        redirect('?route=clients');
    }

    public function delete() {
        if (!Auth::hasPermission('delete_clients')) {
            setFlash('error', 'No tienes permiso para eliminar clientes.');
            redirect('?route=clients');
        }
        $id = intval($_GET['id'] ?? 0);
        if ($id) { $this->clientModel->delete($id); setFlash('success', 'Cliente eliminado.'); }
        redirect('?route=clients');
    }

    public function search() {
        $q = sanitize($_GET['q'] ?? '');
        jsonResponse($this->clientModel->search($q));
    }
}

