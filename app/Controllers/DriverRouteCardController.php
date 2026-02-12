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
                      ->required('end_time', 'Godzina zakonczenia jest wymagana.')
                      ->required('start_km', 'Poczatkowy stan licznika jest wymagany.')
                      ->required('end_km', 'Koncowy stan licznika jest wymagany.')
                      ->numeric('start_km', 'Poczatkowy stan licznika musi byc liczba.')
                      ->numeric('end_km', 'Koncowy stan licznika musi byc liczba.')
                      ->numeric('passengers_count', 'Liczba pasazerow musi byc liczba.');

            if (!empty($_POST['fuel_start'])) {
                $validator->numeric('fuel_start', 'Poczatkowy stan paliwa musi byc liczba.');
            }
            if (!empty($_POST['fuel_end'])) {
                $validator->numeric('fuel_end', 'Koncowy stan paliwa musi byc liczba.');
            }

            if ($validator->fails()) {
                $errors = $validator->getErrors();
                setFlashMessage('error', 'Popraw bledy w formularzu: ' . implode(', ', $errors));
            } else {
                if ($_POST['end_km'] < $_POST['start_km']) {
                    setFlashMessage('error', 'Koncowy stan licznika nie moze byc mniejszy niz poczatkowy.');
                } elseif (!empty($_POST['fuel_start']) && !empty($_POST['fuel_end']) && $_POST['fuel_end'] > $_POST['fuel_start']) {
                    setFlashMessage('error', 'Koncowy stan paliwa nie moze byc wiekszy niz poczatkowy.');
                } else {
                    try {
                        RouteCard::create([
                            'user_id' => $user_id,
                            'vehicle_id' => $_POST['vehicle_id'],
                            'line_id' => $_POST['line_id'],
                            'route_date' => $_POST['route_date'],
                            'start_time' => $_POST['start_time'],
                            'end_time' => $_POST['end_time'],
                            'start_km' => $_POST['start_km'],
                            'end_km' => $_POST['end_km'],
                            'fuel_start' => !empty($_POST['fuel_start']) ? $_POST['fuel_start'] : null,
                            'fuel_end' => !empty($_POST['fuel_end']) ? $_POST['fuel_end'] : null,
                            'passengers_count' => !empty($_POST['passengers_count']) ? $_POST['passengers_count'] : 0,
                            'notes' => !empty($_POST['notes']) ? trim($_POST['notes']) : null
                        ]);

                        setFlashMessage('success', 'Karta drogowa zostala zapisana pomyslnie.');
                        $this->redirectTo('/driver/route-card.php');
                    } catch (Exception $e) {
                        setFlashMessage('error', 'Blad podczas zapisywania karty drogowej: ' . $e->getMessage());
                    }
                }
            }
        }

        $vehicles = Vehicle::listNotBroken();
        $lines = Line::listActive();
        $recent_cards = RouteCard::getRecentByUser($user_id, 10);

        $this->render('driver/route-card', [
            'page_title' => 'Karta Drogowa',
            'vehicles' => $vehicles,
            'lines' => $lines,
            'recent_cards' => $recent_cards
        ]);
    }
}
