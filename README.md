# Panel Pracowniczy Firma KOT 🚌

System zarządzania pracownikami dla firmy transportowej wykorzystujący PHP, HTML, CSS i PostgreSQL.

## 🚀 Funkcjonalności

### System logowania i autoryzacji
- Logowanie z hashowaniem haseł (bcrypt)
- System ról (RBAC): Administrator, Dyspozytor, Kierowca, Zarząd
- Automatyczne wylogowanie po 30 minutach nieaktywności
- Reset hasła przez email

### Panel kierowcy
- Dashboard z dzisiejszym grafikiem pracy
- Pełny kalendarz grafików
- Wypełnianie kart drogowych
- Zgłaszanie awarii i incydentów

### Panel administracyjny
- Zarządzanie użytkownikami i rolami
- CRUD pojazdów (autobusy, tramwaje, metro)
- CRUD linii komunikacyjnych
- Zarządzanie stanowiskami z kontrolą limitów
- Przypisywanie stanowisk użytkownikom
- Dashboard z statystykami

### Funkcje zaawansowane
- Mobile-first responsive design
- Tryb ciemny (dark mode)
- Kontrola limitów stanowisk (triggery PostgreSQL)
- Logowanie aktywności użytkowników
- Logi audytowe
- Walidacja formularzy
- Ochrona CSRF

---

## 📋 Wymagania

- **PHP**: 8.0 lub wyższy
- **PostgreSQL**: 14 lub wyższy
- **Serwer WWW**: Apache/Nginx z włączonym mod_rewrite (dla Apache)
- **Rozszerzenia PHP**: pdo_pgsql, pgsql, session, mbstring

---

## 🔧 Instalacja

### 1. Sklonuj repozytorium

```bash
git clone https://github.com/the-realcar/panel.git
cd panel
```

### 2. Konfiguracja bazy danych

#### Utwórz bazę danych PostgreSQL

```bash
sudo -u postgres psql
```

```sql
CREATE DATABASE panel_firmakot;
CREATE USER panel_user WITH PASSWORD 'your_secure_password';
GRANT ALL PRIVILEGES ON DATABASE panel_firmakot TO panel_user;
\q
```

#### Zaimportuj schemat bazy danych

```bash
psql -U panel_user -d panel_firmakot -f database/schema.sql
```

#### Zaimportuj dane testowe

```bash
psql -U panel_user -d panel_firmakot -f database/seeds.sql
```

### 3. Konfiguracja aplikacji

Stwórz zmienne środowiskowe lub edytuj pliki w katalogu `config/`:

**Opcja A: Zmienne środowiskowe (zalecane)**

```bash
export DB_HOST=localhost
export DB_PORT=5432
export DB_NAME=panel_firmakot
export DB_USER=panel_user
export DB_PASSWORD=your_secure_password
export BASE_URL=http://localhost
export APP_ENV=production
```

**Opcja B: Bezpośrednia edycja plików**

Edytuj `config/database.php` i ustaw odpowiednie wartości dla połączenia z bazą danych.

### 4. Konfiguracja serwera WWW

#### Apache

Przykładowa konfiguracja VirtualHost:

```apache
<VirtualHost *:80>
    ServerName panel.firmakot.local
    DocumentRoot /var/www/panel
    
    <Directory /var/www/panel>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/panel-error.log
    CustomLog ${APACHE_LOG_DIR}/panel-access.log combined
</VirtualHost>
```

Włącz mod_rewrite:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx

Przykładowa konfiguracja:

```nginx
server {
    listen 80;
    server_name panel.firmakot.local;
    root /var/www/panel;
    index index.php;
    
    location / {
        try_files $uri $uri/ /public/index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 5. Uprawnienia plików

```bash
sudo chown -R www-data:www-data /var/www/panel
sudo chmod -R 755 /var/www/panel
```

### 6. Testowanie instalacji

Przejdź do: `http://localhost/public/login.php`

---

## 👤 Dane testowe

System zawiera 3 predefiniowanych użytkowników testowych:

| Użytkownik | Hasło | Rola |
|------------|-------|------|
| `admin` | `password123` | Administrator |
| `kierowca1` | `password123` | Kierowca |
| `dyspozytor1` | `password123` | Dyspozytor |

**⚠️ WAŻNE**: Zmień te hasła przed wdrożeniem produkcyjnym!

---

## 📁 Struktura projektu

```
panel/
├── admin/                      # Panel administracyjny
│   ├── dashboard.php
│   ├── vehicles/              # Zarządzanie pojazdami
│   ├── lines/                 # Zarządzanie liniami
│   ├── positions/             # Zarządzanie stanowiskami
│   └── users/                 # Zarządzanie użytkownikami
├── config/                     # Pliki konfiguracyjne
│   ├── config.php
│   ├── database.php
│   └── session.php
├── core/                       # Klasy podstawowe
│   ├── Auth.php               # Autentykacja
│   ├── Database.php           # Wrapper PDO
│   ├── RBAC.php               # Kontrola dostępu
│   └── Validator.php          # Walidacja formularzy
├── database/                   # Skrypty bazy danych
│   ├── schema.sql             # Schemat tabel
│   └── seeds.sql              # Dane testowe
├── includes/                   # Wspólne pliki
│   ├── functions.php          # Funkcje pomocnicze
│   ├── header.php             # Nagłówek
│   ├── footer.php             # Stopka
│   └── navigation.php         # Nawigacja
└── public/                     # Pliki publiczne
    ├── assets/                # Zasoby statyczne
    │   ├── css/
    │   └── js/
    ├── driver/                # Panel kierowcy
    ├── index.php
    ├── login.php
    └── logout.php
```

---

## 🎨 Interfejs użytkownika

### Mobile-First Design
- Responsywny design dostosowany do urządzeń mobilnych
- Minimalna szerokość przycisków: 44px (touch-friendly)
- Responsywne tabele z data-label dla mobile
- Flexbox i Grid Layout

### Dark Mode
- Automatyczne wykrywanie preferencji systemowych
- Przełącznik w nagłówku
- Zapisywanie preferencji w localStorage
- Płynne przejścia między motywami

---

## 🔐 Bezpieczeństwo

- **Hashowanie haseł**: bcrypt z kosztami 10
- **Ochrona CSRF**: tokeny w formularzach
- **SQL Injection**: parametryzowane zapytania PDO
- **XSS**: htmlspecialchars() na wszystkich wyjściach
- **Sesje**: bezpieczne ustawienia cookies (httpOnly, sameSite)
- **Timeout sesji**: 30 minut nieaktywności
- **Logi logowania**: śledzenie prób logowania
- **Logi audytowe**: rejestracja ważnych operacji

---

## 🛠️ Rozwój

### Tryb deweloperski

W `config/config.php` ustaw:

```php
define('APP_ENV', 'development');
```

To włączy:
- Wyświetlanie błędów PHP
- Szczegółowe komunikaty błędów
- Logowanie debugowania

### Struktura bazy danych

System używa 15 tabel PostgreSQL:
- `users` - użytkownicy
- `roles` - role (RBAC)
- `departments` - departamenty/działy
- `positions` - stanowiska z limitami
- `user_roles` - przypisania ról
- `user_positions` - przypisania stanowisk
- `vehicles` - pojazdy
- `lines` - linie komunikacyjne
- `schedules` - grafiki pracy
- `route_cards` - karty drogowe
- `incidents` - awarie i incydenty
- `login_logs` - logi logowania
- `audit_logs` - logi audytowe
- `password_resets` - tokeny resetowania haseł
- `sessions` - sesje użytkowników

### Triggery
- `update_updated_at()` - automatyczna aktualizacja timestamp
- `check_position_limit()` - kontrola limitów stanowisk

---

## 📝 API i rozszerzenia

System jest zaprojektowany modułowo i może być rozszerzony o:
- API REST (JSON responses)
- Integrację z zewnętrznymi systemami
- System powiadomień email
- Eksport raportów (PDF, Excel)
- Kalendarz Google
- System czatu
- Moduł płatności

---

## 🐛 Rozwiązywanie problemów

### Błąd połączenia z bazą danych
```
Nie można połączyć się z bazą danych
```
**Rozwiązanie**: Sprawdź dane w `config/database.php` i upewnij się, że PostgreSQL działa.

### Błąd uprawnień do plików
```
Permission denied
```
**Rozwiązanie**: Ustaw właściciela plików na www-data i uprawnienia 755.

### Sesja wygasła zbyt szybko
**Rozwiązanie**: Sprawdź ustawienie `SESSION_TIMEOUT` w `config/config.php` (domyślnie 1800s = 30 min).

### Dark mode nie działa
**Rozwiązanie**: Sprawdź czy `dark-mode.js` jest załadowany i czy localStorage jest dostępny w przeglądarce.

---

## 📄 Licencja

Ten projekt jest własnością Firmy KOT. Wszystkie prawa zastrzeżone.

---

## 👥 Autorzy

- Zespół deweloperski Firma KOT
- GitHub: [@the-realcar](https://github.com/the-realcar)

---

## 📞 Kontakt

W razie pytań lub problemów:
- Email: admin@firmakot.pl
- GitHub Issues: [https://github.com/the-realcar/panel/issues](https://github.com/the-realcar/panel/issues)

---

---

# Poniżej znajduje się oryginalna dokumentacja wymagań systemowych

---

# Dokumentacja Wymagań Systemowych
## System: Panel Pracowniczy Firma KOT

**Wersja dokumentu:** 1.0
**Data:** 2026-01-23
**Klient:** Firma KOT

---

### 1. Cel i Zakres Projektu

Celem projektu jest stworzenie wewnętrznego systemu webowego (panelu pracowniczego) wspierającego zarządzanie wirtualnym przedsiębiorstwem transportowym. System ma służyć do organizacji pracy kierowców, dyspozytorów i pracowników administracyjnych w środowisku symulacyjnym gier transportowych (OMSI 2, Roblox: Nid's Buses & Trams).

System nie będzie zintegrowany technicznie (API) z zewnętrznym Systemem Informacji Liniowej (SIL), jednak musi zachować **pełną spójność wizualną i logiczną** z systemem [sil.kanbeq.me](http://sil.kanbeq.me/).

---

### 2. Aktorzy Systemu (Użytkownicy)

Szczegółowa hierarchia stanowisk w Firmie KOT oraz Spółkach:

#### A. Firma KOT

##### I. Zarząd
1. **Zarząd KOT** (limit: 3 osoby)
2. **Główny Inspektor** (limit: 1 osoba)
3. **Dyspozytor Główny** (limit: 1 osoba)

##### II. Administracja (KOT)
1. **Główny Administrator** (limit: 1 osoba)
2. **Zastępca Głównego Administratora** (limit: 2 osoby)
3. **Starszy Administrator** (limit: 5 osób)

##### III. Nadzór Ruchu
1. **Koordynator rozkładów jazdy** (limit: 3 osoby)
2. **Planer rozkładów jazdy** (limit: 8 osób)
3. **Planer tras linii** (limit: 7 osób)
4. **Kierownik Ruchu** (limit: 1 osoba)
5. **Zastępca Kierownika Ruchu** (limit: 2 osoby)
6. **Doświadczony Nadzorca Ruchu** (limit: 5 osób)
7. **Nadzorca Ruchu** (bez limitu)
8. **Młodszy Nadzorca Ruchu** (limit: 10 osób)

#### B. Spółki

##### I. Zarząd Spółki
1. **Dyrektor Spółki** (limit: 1 osoba)
2. **Zastępca Dyrektora Spółki** (limit: 2 osoby)

##### II. Administracja (Spółki)
1. **Główny Administrator** (limit: 1 osoba)
2. **Zastępca Głównego Administratora** (limit: 2 osoby)
3. **Starszy Administrator** (limit: 5 osób)
4. **Doświadczony Administrator** (limit: 10 osób)
5. **Administrator** (bez limitu)
6. **Moderator** (limit: 15 osób)
7. **Młodszy Moderator** (limit: 10 osób)

##### III. Kontrole
1. **Główny Inspektor** (limit: 1 osoba)
2. **Specjalista ds. Kontroli** (limit: 1 osoba)
3. **Zastępca Specjalisty ds. Kontroli** (limit: 2 osoby)

##### IV. Dyspozytornia
1. **Dyspozytor Główny** (limit: 1 osoba)
2. **Zastępca Dyspozytora Głównego** (limit: 3 osoby)
3. **Starszy Dyspozytor** (limit: 5 osób)
4. **Dyspozytor** (limit: 10 osób)
5. **Młodszy Dyspozytor** (limit: 5 osób)

##### V. Transport
1. **Koordynator Przewozów** (limit: 1 osoba)
2. **Zastępca Koordynatora Przewozów** (limit: 2 osoby)
3. **Egzaminator** (limit: 5 osób)
4. **Kontroler biletów** (bez limitu)
5. **Starszy Kierowca/Motorniczy** (limit: 10 osób)
6. **Kierowca/Motorniczy** (bez limitu)
7. **Młodszy Kierowca/Motorniczy** (limit: 15 osób)

##### VI. Zajezdnia
1. **Kierownik Zajezdni** (limit: 1 osoba)
2. **Zastępca Kierownika Zajezdni** (limit: 1 osoba)
3. **Mechanik** (bez limitu)
4. **Elektromechanik** (bez limitu)
5. **Lakiernik** (bez limitu)
6. **Blacharz** (bez limitu)
7. **Pracownik obsługi technicznej** (bez limitu)

#### Uprawnienia i Mapowanie Ról w Systemie

Grupowanie stanowisk w role systemowe (RBAC):
1. **Zarząd** – Zarząd KOT, Główny Inspektor, Dyspozytor Główny, Dyrektor Spółki, itp.
2. **Administrator IT** – wszyscy administratorzy i moderatorzy
3. **Nadzór Ruchu** – wszyscy planiści i nadzorcy
4. **Dyspozytor** – wszyscy dyspozytorzy
5. **Kontrole** – inspektorzy i kontrolerzy
6. **Kadry** – (do zdefiniowania)
7. **Transport** – kierowcy, motorniczowie, egzaminatorzy
8. **Zajezdnia** – personel techniczny

**Uwagi techniczne:**
- W PostgreSQL: tabele `positions` (stanowiska), `roles` (role systemowe), `role_position_mapping` (mapowanie)
- Użytkownicy przypisani do **stanowisk**, stanowiska mapowane do **ról** (RBAC)
- Limity egzekwowane w PHP podczas dodawania użytkowników

---

### 3. Wymagania Funkcjonalne

#### 3.1. Moduł Uwierzytelniania i Bezpieczeństwa
*   Logowanie za pomocą loginu i hasła.
*   Mechanizm przypominania/resetowania hasła.
*   Automatyczne wylogowanie po określonym czasie bezczynności (sesja).
*   System ról i uprawnień (RBAC) – blokowanie dostępu do modułów nieprzypisanych do danej roli.

#### 3.2. Panel Kierowcy (Priorytet Mobile)
*   **Grafik Pracy:** Przejrzysty widok przydzielonych służb (data, godziny, linia, brygada).
*   **Karta Drogowa:** Cyfrowy odpowiednik karty drogowej – możliwość wpisania stanu licznika, wybrania pojazdu.
*   **Raportowanie:** Formularz zgłaszania awarii pojazdu lub zdarzeń drogowych (wypadki w symulacji).
*   **Dokumentacja:** Dostęp "read-only" do regulaminów i instrukcji w PDF/tekście.

#### 3.3. Panel Dyspozytora
*   **Zarządzanie Służbami:** Przydzielanie kierowców do brygad i pojazdów.
*   **Status Floty:** Podgląd, które pojazdy są w ruchu, a które na zajezdni lub w serwisie.
*   **Dyspozycje:** Możliwość wysyłania komunikatów do kierowców (np. "Zmiana trasy").

#### 3.4. Panel Kadrowo-Administracyjny
*   **Ewidencja Czasu Pracy (ECP):** Podgląd i edycja godzin wyjeżdżonych przez kierowców.
*   **Zarządzanie Personelem:** Dodawanie pracowników, edycja danych, archiwizacja kont.
*   **Raporty:** Generowanie zestawień miesięcznych (ilość kilometrów, spalanie – symulacyjne).

#### 3.5. Panel Zarządu – Zarządzanie Strukturą Transportową (CRUD)

Moduł kluczowy dla odwzorowania struktury przewozowej. Dostęp do poszczególnych funkcji jest uzależniony od stanowiska:

**Uprawnienia według stanowisk:**
- **Zarząd KOT, Dyrektor Spółki:** Pełny dostęp do wszystkich funkcji CRUD.
- **Koordynator rozkładów jazdy:** Dostęp do zarządzania liniami, brygadami, rozkładami jazdy.
- **Planer tras linii:** Dostęp TYLKO do definiowania tras i wariantów (sekcje F, G). Brak dostępu do pojazdów, brygad.
- **Planer rozkładów jazdy:** Dostęp do rozkładów jazdy i powiązania ich z brygadami. Brak dostępu do pojazdów.
- **Kierownik Zajezdni:** Dostęp do zarządzania pojazdami (CRUD) w przydzielonej zajezdni.

**Funkcje modułu:**

**A. Pojazdy (Tabor)**
*   Numer taborowy (unikalny).
*   Typ pojazdu (Autobus / Tramwaj).
*   Marka i Model (np. Solaris Urbino 12 – istotne dla mapowania modelu w OMSI/Roblox).
*   Rok produkcji / Malowanie (Livery).
*   Status: Aktywny, Wycofany, Serwis, Rezerwa.

**B. Przystanki (Fizyczne)**
*   Nazwa przystanku.
*   Unikalny identyfikator (ID zgodne z logiką SIL).
*   Lokalizacja opisowa (np. "Przy dworcu").

**C. Stanowiska (Słupki)**
*   Numer stanowiska (np. 01, 02).
*   Powiązanie z Przystankiem fizycznym.
*   Typ: Przystanek przelotowy, pętla, techniczny, "na żądanie".

**D. Linie**
*   Numer linii (np. 105, N12).
*   Typ linii: Dzienna, Nocna, Podmiejska, Zastępcza.
*   Kolorystyka oznaczenia linii (zgodna z SIL).

**E. Brygady**
*   Numer brygady (np. 105/1, 105/02).
*   Powiązanie z Linią.
*   Domyślny typ taboru (np. przegubowy).

**F. Kierunki i Trasy (Warianty)**
*   Definicja wariantu trasy (Kierunek A -> B, Kierunek B -> A, Zjazdy do zajezdni).
*   Nazwa kierunku (wyświetlana na tablicach).

**G. Sekwencja Przystanków (Trasa)**
*   Lista uporządkowana przystanków dla danego wariantu trasy.
*   Przypisywanie konkretnego stanowiska (słupka) do przystanku na trasie.
*   Czas przelotu między przystankami (dla celów rozkładowych).

#### 3.6. Zarządzanie Stanowiskami i Limitami

Panel dla Zarządu i Administratorów IT umożliwiający:

*   **Definiowanie Stanowisk:** Dodawanie nowych stanowisk z przypisaniem do wydziału/departamentu.
*   **Limity Stanowisk:** Określenie maksymalnej liczby osób na danym stanowisku (lub brak limitu).
*   **Kontrola Limitów:** Automatyczna walidacja przy dodawaniu użytkowników – system uniemożliwia przekroczenie limitu.
*   **Mapowanie na Role:** Przypisywanie stanowisk do ról systemowych (RBAC) w celu kontroli dostępu.
*   **Audyt:** Historia zmian w strukturze stanowisk (kto, kiedy, co zmienił).

---

### 4. Wymagania Niefunkcjonalne (Jakość i Design)

#### 4.1. Interfejs Użytkownika (UI) i Responsywność (RWD)
*   **Mobile First:** Panel musi być w pełni funkcjonalny na smartfonach.
    *   Menu nawigacyjne w wersji mobilnej zgodne z dostarczoną makietą (np. dolny pasek nawigacyjny lub wysuwany sidebar "hamburger").
    *   Przyciski i pola formularzy muszą być łatwe do obsługi kciukiem (wysokość min. 44px).
*   **Stylistyka:**
    *   Design "Industrialny / Transportowy".
    *   Wysoki kontrast, czytelność w jasnym i ciemnym trybie (Dark Mode zalecany dla kierowców jeżdżących w nocy).
    *   Inspiracja wizualna systemem SIL (podobne fonty, układ tabel), ale bez bezpośredniego połączenia.

#### 4.2. Kontekst Symulacyjny (Gaming)
*   System musi obsługiwać specyfikę gier:
    *   **OMSI 2:** Możliwość wpisywania numerów bocznych i linii zgodnych z HOF file.
    *   **Roblox (Nid's Buses & Trams):** Pola formularzy dostosowane do nazw przystanków występujących w grze.
*   Dane w systemie są fikcyjne, ale struktura bazy danych powinna być profesjonalna (relacyjna), aby budować realizm (Roleplay).

#### 4.3. Dostępność i Wydajność
*   Dostępność 24/7.
*   Czas ładowania strony poniżej 2 sekund.
*   Obsługa do 100 zalogowanych użytkowników jednocześnie (skalowalność pod eventy w grze).

---

### 5. Wymagania Techniczne

*   **Platforma:** Przeglądarka internetowa (Chrome, Firefox, Edge, Safari – mobile).
*   **Backend:** Preferowane technologie webowe (np. PHP, Node.js, Python).
*   **Baza Danych:** MySQL lub PostgreSQL (relacyjna struktura dla linii/brygad).
*   **Hosting:** Serwer z obsługą SSL (kłódka bezpieczeństwa – wymagana dla realizmu i bezpieczeństwa haseł).

---

### 6. Integracje (Logiczne)

*   **Brak API do SIL:** System działa jako niezależna wyspa danych.
*   **Kompatybilność danych:** Administratorzy są zobowiązani do ręcznego utrzymywania spójności nazw przystanków i numeracji linii pomiędzy Panelem Ostrans a zewnętrznym SIL-em, aby kierowcy mogli się płynnie poruszać między systemami.

---

### 7. Historyjki Użytkownika (User Stories)

#### 7.1. Moduł Uwierzytelniania i Bezpieczeństwa

**US-001: Logowanie do systemu**
> Jako **użytkownik systemu** (dowolna rola),  
> chcę **zalogować się za pomocą loginu i hasła**,  
> aby **uzyskać dostęp do funkcji przypisanych mojej roli**.
>
> **Kryteria akceptacji:**
> - Formularz logowania zawiera pola: login, hasło
> - System weryfikuje poprawność danych z bazą PostgreSQL
> - Po prawidłowym logowaniu użytkownik jest przekierowywany do panelu odpowiedniego dla jego roli
> - Nieprawidłowe dane wyświetlają komunikat błędu
> - Responsywny design (RWD) – formularz działa na mobile i desktop

**US-002: Resetowanie hasła**
> Jako **użytkownik systemu**,  
> chcę **zresetować zapomniane hasło**,  
> aby **odzyskać dostęp do konta bez pomocy administratora**.
>
> **Kryteria akceptacji:**
> - Link "Zapomniałeś hasła?" widoczny na stronie logowania
> - Formularz z polem adres email/login
> - System wysyła link resetujący na email (lub alternatywnie: kod do przepisania)
> - Link jest ważny przez określony czas (np. 24h)
> - Po użyciu linku użytkownik może ustawić nowe hasło

**US-003: Automatyczne wylogowanie**
> Jako **administrator IT**,  
> chcę **aby system automatycznie wylogowywał użytkowników po okresie bezczynności**,  
> aby **zwiększyć bezpieczeństwo w przypadku pozostawienia zalogowanej sesji**.
>
> **Kryteria akceptacji:**
> - Sesja wygasa po 30 minutach bezczynności
> - System wyświetla ostrzeżenie 2 minuty przed wylogowaniem
> - Po wylogowaniu użytkownik jest przekierowywany do strony logowania
> - Czas bezczynności jest konfigurowalny przez administratora

**US-004: System ról i uprawnień (RBAC)**
> Jako **administrator IT**,  
> chcę **przypisywać użytkowników do ról z określonymi uprawnieniami**,  
> aby **kontrolować dostęp do poszczególnych modułów systemu**.
>
> **Kryteria akceptacji:**
> - System rozpoznaje 5 ról: Kierowca, Dyspozytor, Kadry, Zarząd, Admin IT
> - Każda rola ma dostęp tylko do przypisanych modułów
> - Próba dostępu do nieautoryzowanego modułu wyświetla błąd 403
> - W bazie PostgreSQL istnieje tabela `roles` i `user_roles`

---

#### 7.2. Panel Kierowcy

**US-005: Przeglądanie grafiku pracy**
> Jako **kierowca**,  
> chcę **zobaczyć mój grafik pracy w przejrzystej formie**,  
> aby **wiedzieć, kiedy i na jakiej linii mam jechać**.
>
> **Kryteria akceptacji:**
> - Widok kalendarza/listy z przydzielonymi służbami (data, godziny, linia, brygada)
> - Możliwość filtrowania po dacie (dzisiaj, tydzień, miesiąc)
> - Design mobile-first – łatwa nawigacja na smartfonie
> - Widok zgodny ze stylistyką SIL (kolory linii, fonty transportowe)

**US-006: Wypełnianie karty drogowej**
> Jako **kierowca**,  
> chcę **cyfrowo wypełnić kartę drogową przed rozpoczęciem służby**,  
> aby **zarejestrować stan licznika i przypisany pojazd**.
>
> **Kryteria akceptacji:**
> - Formularz zawiera: wybór pojazdu, stan licznika początkowy, data i godzina
> - Po zakończeniu służby: stan licznika końcowy
> - System oblicza przejechane kilometry
> - Dane zapisywane w bazie PostgreSQL (tabela `route_cards`)
> - Responsywny formularz, duże przyciski (min. 44px wysokości)

**US-007: Zgłaszanie awarii pojazdu**
> Jako **kierowca**,  
> chcę **zgłosić awarię pojazdu lub zdarzenie drogowe**,  
> aby **dyspozytor i zarząd byli natychmiast poinformowani**.
>
> **Kryteria akceptacji:**
> - Formularz z polami: typ zdarzenia (awaria/wypadek), opis, numer pojazdu, lokalizacja
> - Możliwość dodania zrzutu ekranu (opcjonalnie)
> - Zgłoszenie zapisywane w tabeli `incidents`
> - Powiadomienie dla dyspozytora (opcjonalnie email/push)

**US-008: Dostęp do dokumentacji**
> Jako **kierowca**,  
> chcę **mieć dostęp do regulaminów i instrukcji**,  
> aby **szybko sprawdzić zasady podczas gry**.
>
> **Kryteria akceptacji:**
> - Sekcja "Dokumentacja" w menu kierowcy
> - Lista plików PDF lub artykułów tekstowych (read-only)
> - Podgląd w przeglądarce bez konieczności pobierania
> - Responsywny widok dla mobile

---

#### 7.3. Panel Dyspozytora

**US-009: Przydzielanie kierowców do brygad**
> Jako **dyspozytor**,  
> chcę **przydzielić kierowcę do konkretnej brygady i pojazdu**,  
> aby **zarządzać bieżącym ruchem**.
>
> **Kryteria akceptacji:**
> - Widok listy dostępnych kierowców i brygad
> - Możliwość przeciągnięcia kierowcy do brygady (drag & drop) lub wyboru z listy rozwijanej
> - System zapisuje przypisanie w tabeli `assignments`
> - Zmiany widoczne natychmiast dla kierowcy w jego grafiku

**US-010: Podgląd statusu floty**
> Jako **dyspozytor**,  
> chcę **zobaczyć, które pojazdy są w ruchu, a które na zajezdni**,  
> aby **szybko reagować na potrzeby organizacyjne**.
>
> **Kryteria akceptacji:**
> - Tabela/mapa z listą pojazdów i ich statusem (w ruchu / zajezdnia / serwis / rezerwa)
> - Możliwość filtrowania po statusie
> - Kolory oznaczające status (np. zielony = w ruchu, pomarańczowy = serwis)
> - Aktualizacja statusu w czasie rzeczywistym lub po odświeżeniu

**US-011: Wysyłanie dyspozycji do kierowców**
> Jako **dyspozytor**,  
> chcę **wysłać wiadomość do kierowcy**,  
> aby **poinformować go o zmianie trasy lub innej pilnej sprawie**.
>
> **Kryteria akceptacji:**
> - Formularz: wybór kierowcy, treść wiadomości
> - Kierowca widzi powiadomienie w swoim panelu
> - Historia wysłanych dyspozycji (tabela `dispatches`)
> - Responsywny interfejs

---

#### 7.4. Panel Kadrowo-Administracyjny

**US-012: Ewidencja czasu pracy (ECP)**
> Jako **pracownik kadr**,  
> chcę **przeglądać i edytować godziny pracy kierowców**,  
> aby **prowadzić prawidłową ewidencję czasu**.
>
> **Kryteria akceptacji:**
> - Widok tabeli z listą kierowców i sumą godzin w miesiącu
> - Możliwość ręcznej korekty godzin (z logiem zmian)
> - Eksport danych do CSV
> - Baza PostgreSQL: tabela `work_hours`

**US-013: Zarządzanie personelem**
> Jako **pracownik kadr**,  
> chcę **dodawać nowych pracowników i edytować ich dane**,  
> aby **utrzymać aktualną bazę personelu**.
>
> **Kryteria akceptacji:**
> - Formularz dodawania: imię, nazwisko, email, rola, data zatrudnienia
> - Możliwość edycji i archiwizacji konta (nie usuwanie, aby zachować historię)
> - Tabela `users` w PostgreSQL z polem `archived`

**US-014: Generowanie raportów miesięcznych**
> Jako **pracownik kadr**,  
> chcę **wygenerować raport miesięczny dla kierowcy**,  
> aby **zobaczyć ilość kilometrów i inne statystyki**.
>
> **Kryteria akceptacji:**
> - Wybór kierowcy i miesiąca
> - Raport zawiera: suma km, liczba służb, średnie spalanie (symulacyjne)
> - Eksport do PDF
> - Responsywny widok

---

#### 7.5. Panel Zarządu – Zarządzanie Strukturą Transportową

**US-015: Zarządzanie pojazdami (CRUD)**
> Jako **członek zarządu**,  
> chcę **dodawać, edytować i usuwać pojazdy z taboru**,  
> aby **utrzymać aktualną bazę floty**.
>
> **Kryteria akceptacji:**
> - Formularz z polami: numer taborowy, typ (autobus/tramwaj), marka, model, rok, livery, status
> - Numer taborowy jest unikalny (walidacja w PHP + PostgreSQL UNIQUE)
> - Lista pojazdów z możliwością filtrowania po statusie
> - Tabela `vehicles` w PostgreSQL

**US-016: Zarządzanie przystankami i stanowiskami**
> Jako **członek zarządu**,  
> chcę **dodawać przystanki i przypisywać do nich stanowiska (słupki)**,  
> aby **odzwierciedlić fizyczną strukturę komunikacyjną**.
>
> **Kryteria akceptacji:**
> - Tabela `stops` (przystanki fizyczne): nazwa, ID, lokalizacja
> - Tabela `platforms` (stanowiska): numer, ID przystanku (klucz obcy), typ
> - Formularz dodawania z relacją jeden-do-wielu (przystanek -> stanowiska)
> - Możliwość edycji i usuwania (z ostrzeżeniem, jeśli jest używane w trasach)

**US-017: Zarządzanie liniami**
> Jako **członek zarządu**,  
> chcę **tworzyć i edytować linie komunikacyjne**,  
> aby **zorganizować siatkę połączeń**.
>
> **Kryteria akceptacji:**
> - Formularz: numer linii, typ (dzienna/nocna/podmiejska/zastępcza), kolor
> - Tabela `lines` w PostgreSQL
> - Walidacja unikalności numeru linii
> - Widok listy linii z kolorowym oznaczeniem (zgodnie ze stylistyką SIL)

**US-018: Zarządzanie brygadami**
> Jako **członek zarządu**,  
> chcę **przypisywać brygady do linii**,  
> aby **określić konkretne kursy do realizacji**.
>
> **Kryteria akceptacji:**
> - Formularz: numer brygady, ID linii (klucz obcy), domyślny typ taboru
> - Tabela `brigades` z relacją do `lines`
> - Lista brygad pogrupowana według linii

**US-019: Definiowanie tras i wariantów**
> Jako **członek zarządu**,  
> chcę **zdefiniować warianty tras dla linii (np. kierunek A->B, zjazd)**,  
> aby **kierowcy wiedzieli, którędy jechać**.
>
> **Kryteria akceptacji:**
> - Tabela `route_variants`: ID linii, nazwa kierunku, typ (normalny/zjazd)
> - Formularz wyboru linii i dodawania wariantów
> - Możliwość edycji nazwy kierunku (wyświetlanej na tablicach)

**US-020: Budowanie sekwencji przystanków na trasie**
> Jako **członek zarządu**,  
> chcę **określić kolejność przystanków na wariancie trasy**,  
> aby **system wiedział, jaką trasę pokonuje kierowca**.
>
> **Kryteria akceptacji:**
> - Interfejs drag & drop lub numerowana lista
> - Tabela `route_stops`: ID wariantu, ID stanowiska, kolejność, czas przelotu
> - Możliwość podglądu trasy na mapie lub liście (mobile-friendly)
> - Walidacja: każde stanowisko może być dodane tylko raz w sekwencji

---

#### 7.6. Panel Administratora IT

**US-021: Zarządzanie kontami użytkowników**
> Jako **administrator IT**,  
> chcę **tworzyć, edytować i blokować konta użytkowników**,  
> aby **kontrolować dostęp do systemu**.
>
> **Kryteria akceptacji:**
> - Panel z listą użytkowników (tabela `users`)
> - Możliwość dodania nowego użytkownika (generowanie hasła lub wysyłka linku aktywacyjnego)
> - Możliwość blokowania/odblokowywania konta (pole `active`)
> - Podgląd logów logowania (tabela `login_logs`)

**US-022: Konfiguracja parametrów systemu**
> Jako **administrator IT**,  
> chcę **zmieniać parametry systemowe (np. czas sesji, logo, nazwa firmy)**,  
> aby **dostosować system do potrzeb organizacji**.
>
> **Kryteria akceptacji:**
> - Panel ustawień z konfigurowalnymi wartościami
> - Tabela `settings` (klucz-wartość) w PostgreSQL
> - Zmiany widoczne natychmiast po zapisaniu

**US-023: Podgląd logów systemowych**
> Jako **administrator IT**,  
> chcę **przeglądać logi systemowe (logowania, błędy, zmiany krytyczne)**,  
> aby **diagnozować problemy i monitorować bezpieczeństwo**.
>
> **Kryteria akceptacji:**
> - Widok tabeli logów z filtrowaniem po dacie, użytkowniku, typie zdarzenia
> - Tabele: `login_logs`, `error_logs`, `audit_logs`
> - Możliwość eksportu do CSV

---

#### 7.8. Zarządzanie Stanowiskami i Limitami

**US-029: Zarządzanie stanowiskami (CRUD)**
> Jako **Administrator IT lub Członek Zarządu**,  
> chcę **dodawać, edytować i usuwać stanowiska w systemie**,  
> aby **odzwierciedlić strukturę organizacyjną Firmy KOT i Spółek**.
>
> **Kryteria akceptacji:**
> - Formularz dodawania stanowiska: nazwa, wydział/departament, maksymalna liczba osób (lub checkbox "bez limitu"), opis
> - Lista stanowisk z możliwością filtrowania po wydziale
> - Tabela `positions` w PostgreSQL: `id`, `name`, `department`, `max_count` (NULL = bez limitu), `description`
> - Walidacja: nie można usunąć stanowiska, do którego są przypisani użytkownicy (lub wyświetlenie ostrzeżenia)
> - Responsywny interfejs (HTML, CSS, PHP)

**US-030: Kontrola limitów stanowisk**
> Jako **Administrator IT**,  
> chcę **aby system automatycznie blokował przypisanie użytkownika do stanowiska, jeśli przekroczono limit**,  
> aby **utrzymać zgodność ze strukturą organizacyjną**.
>
> **Kryteria akceptacji:**
> - Podczas przypisywania użytkownika do stanowiska, system sprawdza liczbę już przypisanych osób
> - Jeśli `COUNT(user_positions WHERE position_id = X) >= positions.max_count`, wyświetlany jest błąd
> - Komunikat błędu: "Limit stanowisk został wyczerpany. Maksymalna liczba osób: [max_count]"
> - Walidacja w PHP przed zapisem do PostgreSQL
> - Walidacja po stronie bazy danych (trigger lub constraint)

**US-031: Przypisywanie użytkowników do stanowisk**
> Jako **Administrator IT lub Pracownik Kadr**,  
> chcę **przypisać użytkownika do konkretnego stanowiska**,  
> aby **określić jego rolę i uprawnienia w systemie**.
>
> **Kryteria akceptacji:**
> - Formularz edycji użytkownika zawiera pole "Stanowisko" (dropdown z listą dostępnych stanowisk)
> - System sprawdza limit przed zapisem (integracja z US-030)
> - Tabela `user_positions`: `user_id`, `position_id`, `assigned_date`, `assigned_by`
> - Po zapisie, system automatycznie przypisuje rolę systemową (RBAC) na podstawie `role_position_mapping`
> - Możliwość zmiany stanowiska (z logiem w tabeli `audit_logs`)

**US-032: Podgląd struktury organizacyjnej**
> Jako **Członek Zarządu**,  
> chcę **zobaczyć pełną strukturę organizacyjną z liczbą osób na każdym stanowisku**,  
> aby **monitorować wykorzystanie zasobów ludzkich**.
>
> **Kryteria akceptacji:**
> - Widok drzewa organizacyjnego (hierarchia wydziałów i stanowisk)
> - Dla każdego stanowiska: nazwa, liczba przypisanych osób / limit (np. "3 / 5" lub "15 / ∞")
> - Kolory: zielony (poniżej limitu), żółty (80-99% limitu), czerwony (limit osiągnięty)
> - Możliwość kliknięcia stanowiska, aby zobaczyć listę przypisanych użytkowników
> - Export do PDF
> - Responsywny interfejs (HTML, CSS)

---

#### 7.7. Wymagania Niefunkcjonalne

**US-024: Responsywność (Mobile First)**
> Jako **kierowca grający na smartfonie**,  
> chcę **korzystać z panelu na małym ekranie bez problemów**,  
> aby **nie musieć przełączać się na komputer**.
>
> **Kryteria akceptacji:**
> - Design mobile-first (CSS: media queries)
> - Menu nawigacyjne: dolny pasek lub hamburger menu
> - Przyciski min. 44px wysokości (łatwe do trafienia kciukiem)
> - Testy na urządzeniach: iPhone, Android, różne rozdzielczości

**US-025: Dark Mode**
> Jako **kierowca grający w nocy**,  
> chcę **włączyć ciemny motyw interfejsu**,  
> aby **nie męczyć oczu jasnym światłem ekranu**.
>
> **Kryteria akceptacji:**
> - Przełącznik Light/Dark Mode w menu użytkownika
> - Zapisanie preferencji w sesji lub ciasteczku
> - Wysoki kontrast w obu trybach (WCAG AA)

**US-026: Zgodność wizualna z SIL**
> Jako **użytkownik zaznajomiony z SIL**,  
> chcę **widzieć podobny styl (fonty, kolory linii, układ tabel)**,  
> aby **szybko się odnaleźć w systemie**.
>
> **Kryteria akceptacji:**
> - Użycie podobnych fontów (np. Roboto, Open Sans)
> - Tabele z podobnymi nagłówkami i kolorystyką
> - Design "industrialny/transportowy" (ikony autobusów, tramwajów)

**US-027: Wydajność systemu**
> Jako **użytkownik systemu**,  
> chcę **aby strony ładowały się w mniej niż 2 sekundy**,  
> aby **sprawnie pracować w systemie**.
>
> **Kryteria akceptacji:**
> - Czas ładowania < 2s (mierzony Google Lighthouse)
> - Optymalizacja zapytań PostgreSQL (indeksy, cache)
> - Minimalizacja CSS/JS (np. przez build tool)

**US-028: Dostępność 24/7**
> Jako **kierowca grający o różnych porach**,  
> chcę **mieć dostęp do systemu o każdej porze dnia i nocy**,  
> aby **wypełnić kartę drogową przed rozpoczęciem służby**.
>
> **Kryteria akceptacji:**
> - Hosting z gwarantem uptime 99.9%
> - SSL (HTTPS)
> - Monitoring serwera (np. UptimeRobot, alertowanie przy przestoju)

---

### 8. Priorytetyzacja

**Iteracja 1 (MVP):**
- US-001, US-002, US-003, US-004 (uwierzytelnianie i RBAC)
- US-005, US-006, US-007 (podstawowy panel kierowcy)
- US-015, US-017 (zarządzanie pojazdami i liniami)
- US-024 (responsywność mobile)

**Iteracja 2:**
- US-009, US-010, US-011 (panel dyspozytora)
- US-012, US-013 (panel kadr – podstawy)
- US-016, US-018, US-019, US-020 (rozbudowa struktury transportowej)

**Iteracja 3:**
- US-008, US-014 (dokumentacja, raporty)
- US-021, US-022, US-023 (panel admina IT)
- US-025, US-026 (dark mode, spójność wizualna)
- US-027, US-028 (optymalizacja wydajności i dostępność)

**Iteracja 4 (Zarządzanie stanowiskami):**
- US-029, US-030, US-031, US-032 (zarządzanie stanowiskami i limitami)
- Implementacja tabel PostgreSQL: `positions`, `user_positions`, `role_position_mapping`
- Implementacja walidacji limitów w PHP i PostgreSQL (trigger)
- Panel administracyjny do zarządzania strukturą organizacyjną

---

### 9. Schemat Bazy Danych

#### 9.1. Tabele związane ze stanowiskami i rolami

**Tabela: `positions` (Stanowiska)**
```sql
CREATE TABLE positions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    department VARCHAR(100) NOT NULL, -- np. "Zarząd KOT", "Transport - Spółka A"
    max_count INT DEFAULT NULL, -- NULL = bez limitu
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indeks dla szybkiego wyszukiwania po wydziale
CREATE INDEX idx_positions_department ON positions(department);
```

**Tabela: `user_positions` (Przypisanie użytkowników do stanowisk)**
```sql
CREATE TABLE user_positions (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    position_id INT NOT NULL REFERENCES positions(id) ON DELETE RESTRICT,
    assigned_date DATE DEFAULT CURRENT_DATE,
    assigned_by INT REFERENCES users(id), -- kto przypisał
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, position_id) -- jeden użytkownik może mieć wiele stanowisk, ale nie duplikatów
);

-- Indeksy
CREATE INDEX idx_user_positions_user ON user_positions(user_id);
CREATE INDEX idx_user_positions_position ON user_positions(position_id);
```

**Tabela: `roles` (Role systemowe - RBAC)**
```sql
CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE, -- np. "Zarząd", "Dyspozytor", "Kierowca"
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Wstawienie domyślnych ról
INSERT INTO roles (name, description) VALUES
('Zarząd', 'Pełny dostęp do zarządzania strukturą transportową'),
('Administrator IT', 'Zarządzanie systemem i użytkownikami'),
('Nadzór Ruchu', 'Planowanie tras i nadzór nad ruchem'),
('Dyspozytor', 'Zarządzanie bieżącym ruchem i przydziałami'),
('Kontrole', 'Inspekcja i kontrola jakości'),
('Kadry', 'Zarządzanie personelem i ewidencją czasu'),
('Transport', 'Realizacja kursów i obsługa linii'),
('Zajezdnia', 'Obsługa techniczna i konserwacja pojazdów');
```

**Tabela: `role_position_mapping` (Mapowanie stanowisk na role)**
```sql
CREATE TABLE role_position_mapping (
    id SERIAL PRIMARY KEY,
    role_id INT NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    position_id INT NOT NULL REFERENCES positions(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(role_id, position_id)
);

-- Indeksy
CREATE INDEX idx_rpm_role ON role_position_mapping(role_id);
CREATE INDEX idx_rpm_position ON role_position_mapping(position_id);
```

#### 9.2. Funkcja PostgreSQL: Kontrola limitu stanowisk

```sql
-- Funkcja sprawdzająca limit stanowisk (trigger)
CREATE OR REPLACE FUNCTION check_position_limit()
RETURNS TRIGGER AS $$
DECLARE
    current_count INT;
    max_allowed INT;
BEGIN
    -- Pobierz limit stanowiska
    SELECT max_count INTO max_allowed
    FROM positions
    WHERE id = NEW.position_id;

    -- Jeśli NULL, brak limitu
    IF max_allowed IS NULL THEN
        RETURN NEW;
    END IF;

    -- Policz aktualną liczbę przypisanych użytkowników
    SELECT COUNT(*) INTO current_count
    FROM user_positions
    WHERE position_id = NEW.position_id;

    -- Sprawdź, czy limit został przekroczony
    IF current_count >= max_allowed THEN
        RAISE EXCEPTION 'Limit stanowisk został wyczerpany. Maksymalna liczba osób: %', max_allowed;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger uruchamiający funkcję przed wstawieniem
CREATE TRIGGER trigger_check_position_limit
BEFORE INSERT ON user_positions
FOR EACH ROW
EXECUTE FUNCTION check_position_limit();
```

#### 9.3. Widok: Lista użytkowników z rolami

```sql
CREATE VIEW user_roles_view AS
SELECT
    u.id AS user_id,
    u.username,
    u.email,
    p.name AS position_name,
    p.department,
    r.name AS role_name
FROM users u
LEFT JOIN user_positions up ON u.id = up.user_id
LEFT JOIN positions p ON up.position_id = p.id
LEFT JOIN role_position_mapping rpm ON p.id = rpm.position_id
LEFT JOIN roles r ON rpm.role_id = r.id;
```

---

**Koniec dokumentu README.md**
