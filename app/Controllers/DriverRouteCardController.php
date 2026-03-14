<?php

class DriverRouteCardController extends Controller {
    public function index() {
        requireLogin();

        $rbac = new RBAC();
        if (!$rbac->hasRole('Kierowca') && !$rbac->isAdmin()) {
            setFlashMessage('error', 'Brak dostepu do panelu kierowcy.');
            $this->redirectTo('/index.php');
        }

        $user_id = getCurrentUserId();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('error', 'Blad weryfikacji formularza. Sprobuj ponownie.');
                $this->redirectTo('/driver/route-card.php');
            }

            $validator = new Validator($_POST);
            $validator->required('vehicle_id', 'Pojazd jest wymagany.')
                      ->required('line_id', 'Linia jest wymagana.')
                      ->required('route_date', 'Data trasy jest wymagana.')
                      ->required('start_time', 'Godzina rozpoczecia jest wymagana.')
                      ->required('end_time', 'Godzina zakonczenia jest wymagana.');

            if (!empty($_POST['passengers_count'])) {
                $validator->numeric('passengers_count', 'Liczba pasazerow musi byc liczba.');
            }

            if ($validator->fails()) {
                $errors = $validator->getErrors();
                setFlashMessage('error', 'Popraw bledy w formularzu: ' . implode(', ', $errors));
            } else {
                try {
                    $card_id = RouteCard::create([
                        'user_id'          => $user_id,
                        'vehicle_id'       => $_POST['vehicle_id'],
                        'line_id'          => $_POST['line_id'],
                        'route_date'       => $_POST['route_date'],
                        'start_time'       => $_POST['start_time'],
                        'end_time'         => $_POST['end_time'],
                        'passengers_count' => !empty($_POST['passengers_count']) ? (int)$_POST['passengers_count'] : 0,
                        'notes'            => !empty($_POST['notes']) ? trim($_POST['notes']) : null
                    ]);

                    if ($card_id && !empty($_POST['trips']) && is_array($_POST['trips'])) {
                        RouteCard::createTrips($card_id, $_POST['trips']);
                    }
                    AuditLog::log('route_card.create', 'route_cards', $card_id, null, ['user_id' => $user_id, 'route_date' => $_POST['route_date'], 'line_id' => $_POST['line_id']]);

                    setFlashMessage('success', 'Karta drogowa zostala zapisana pomyslnie.');
                    $this->redirectTo('/driver/route-card.php');
                } catch (Exception $e) {
                    setFlashMessage('error', 'Blad podczas zapisywania karty drogowej: ' . $e->getMessage());
                }
            }
        }

        $vehicles = Vehicle::listNotBroken();
        $lines = Line::listActive();
        $recent_cards = RouteCard::getRecentByUser($user_id, 10);

        // Zaladuj wszystkie aktywne warianty pogrupowane po line_id dla JS
        $all_variants = RouteVariant::listAll(1000, 0, true);
        $variants_by_line = [];
        foreach ($all_variants as $variant) {
            $variants_by_line[$variant['line_id']][] = [
                'id'           => (int)$variant['id'],
                'variant_name' => $variant['variant_name'],
                'direction'    => $variant['direction'],
                'variant_type' => $variant['variant_type'],
            ];
        }

        $this->render('driver/route-card', [
            'page_title'       => 'Karta Drogowa',
            'vehicles'         => $vehicles,
            'lines'            => $lines,
            'recent_cards'     => $recent_cards,
            'variants_by_line' => $variants_by_line
        ]);
    }
}
