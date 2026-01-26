# Dokumentacja Wymagań Systemowych
## System: Panel Pracowniczy Firma KOT

**Wersja dokumentu:** 1.0
**Data:** 2026-01-23
**Klient:** Prywatne Przedsiębiorstwo Usług Transportowych Ostrans (Ostrans)

---

### 1. Cel i Zakres Projektu

Celem projektu jest stworzenie wewnętrznego systemu webowego (panelu pracowniczego) wspierającego zarządzanie wirtualnym przedsiębiorstwem transportowym. System ma służyć do organizacji pracy kierowców, dyspozytorów i pracowników administracyjnych w środowisku symulacyjnym gier transportowych (OMSI 2, Roblox: Nid's Buses & Trams).

System nie będzie zintegrowany technicznie (API) z zewnętrznym Systemem Informacji Liniowej (SIL), jednak musi zachować **pełną spójność wizualną i logiczną** z systemem [sil.kanbeq.me](http://sil.kanbeq.me/).

---

### 2. Aktorzy Systemu (Użytkownicy)

Każda grupa użytkowników posiada odrębny poziom uprawnień:

1.  **Kierowca:** Użytkownik realizujący kursy w grze. Główny odbiorca wersji mobilnej.
2.  **Dyspozytor:** Osoba zarządzająca bieżącym ruchem, przydziałami pojazdów i nagłymi zdarzeniami.
3.  **Pracownik Administracyjny / Kadry:** Obsługa wniosków, ewidencja czasu, dokumentacja.
4.  **Zarząd (Management):** Pełna kontrola nad strukturą transportową (linie, tabor, infrastruktura).
5.  **Administrator IT:** Zarządzanie technicznym aspektem systemu i kontami użytkowników.

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
Moduł ten jest kluczowy dla odwzorowania struktury przewozowej. Umożliwia Dodawanie, Edycję, Usuwanie i Podgląd następujących obiektów:

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

---

**Koniec dokumentu README.md**
