<?php

class AdminDictionariesController extends Controller {
    private function ensureAdmin(): void {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak uprawnien do zarzadzania slownikami.');
            $this->redirectTo('/index.php');
        }
    }

    private function dictionaryConfigs(): array {
        return [
            'vehicle_depots' => [
                'title' => 'Zajezdnie',
                'singular' => 'zajezdnię',
                'key' => 'dict_vehicle_depots',
                'route' => '/admin/depots/index.php',
                'fallback' => ['KM', 'KW', 'MC']
            ],
            'vehicle_capacities' => [
                'title' => 'Pojemności pojazdów',
                'singular' => 'pojemność',
                'key' => 'dict_vehicle_capacities',
                'route' => '/admin/vehicle-capacities/index.php',
                'fallback' => ['MINI', 'MIDI', 'MAXI', 'MAXI+', 'MEGA', 'MEGA+', 'GIGA']
            ],
            'vehicle_types' => [
                'title' => 'Typy pojazdów',
                'singular' => 'typ pojazdu',
                'key' => 'dict_vehicle_types',
                'route' => '/admin/vehicle-types/index.php',
                'fallback' => ['bus', 'tbus', 'tram', 'metro']
            ],
            'vehicle_drive_types' => [
                'title' => 'Typy napędów',
                'singular' => 'typ napędu',
                'key' => 'dict_vehicle_drive_types',
                'route' => '/admin/drive-types/index.php',
                'fallback' => ['Diesel', 'CNG', 'Hybrydowy', 'Elektryczny', 'Wodorowy']
            ]
        ];
    }

    private function renderDictionaryPage(string $config_key): void {
        $this->ensureAdmin();

        $config = $this->dictionaryConfigs()[$config_key] ?? null;
        if ($config === null) {
            setFlashMessage('error', 'Nieznany słownik.');
            $this->redirectTo('/admin/dashboard.php');
        }

        $errors = [];
        $editing_value = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Nieprawidlowy token CSRF.');
                $this->redirectTo($config['route']);
            }

            $action = $_POST['dictionary_action'] ?? 'create';
            $value = trim((string)($_POST['value'] ?? ''));
            $original_value = trim((string)($_POST['original_value'] ?? ''));
            $values = DictionarySetting::getValues($config['key'], $config['fallback']);

            if (in_array($action, ['create', 'update'], true) && $value === '') {
                $errors['value'] = 'Podaj wartość.';
                $editing_value = $value;
            }

            if (empty($errors)) {
                if ($action === 'create') {
                    if (in_array($value, $values, true)) {
                        $errors['value'] = 'Taka wartość już istnieje.';
                    } else {
                        $values[] = $value;
                    }
                } elseif ($action === 'update') {
                    $index = array_search($original_value, $values, true);
                    if ($index === false) {
                        $errors['value'] = 'Edytowana wartość nie została znaleziona.';
                    } elseif ($value !== $original_value && in_array($value, $values, true)) {
                        $errors['value'] = 'Taka wartość już istnieje.';
                    } else {
                        $values[$index] = $value;
                    }
                    $editing_value = $original_value;
                } elseif ($action === 'delete') {
                    $values = array_values(array_filter($values, static function ($item) use ($original_value) {
                        return $item !== $original_value;
                    }));
                }
            }

            if (empty($errors)) {
                DictionarySetting::saveValues($config['key'], $values, isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null);
                setFlashMessage('success', 'Zaktualizowano słownik: ' . mb_strtolower($config['title']) . '.');
                $this->redirectTo($config['route']);
            }
        }

        if (isset($_GET['edit'])) {
            $editing_value = (string)$_GET['edit'];
        }

        $values = DictionarySetting::getValues($config['key'], $config['fallback']);
        $items = [];
        foreach ($values as $index => $value) {
            $items[] = [
                'id' => $index + 1,
                'value' => $value
            ];
        }

        $this->render('admin/dictionaries/index', [
            'page_title' => $config['title'],
            'config' => $config,
            'items' => $items,
            'errors' => $errors,
            'editing_value' => $editing_value
        ]);
    }

    public function depots(): void {
        $this->renderDictionaryPage('vehicle_depots');
    }

    public function vehicleCapacities(): void {
        $this->renderDictionaryPage('vehicle_capacities');
    }

    public function vehicleTypes(): void {
        $this->renderDictionaryPage('vehicle_types');
    }

    public function driveTypes(): void {
        $this->renderDictionaryPage('vehicle_drive_types');
    }
}