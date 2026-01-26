# Dokumentacja Wymagań Systemowych
## System: Panel Pracowniczy Firma KOT

**Wersja dokumentu:** 1.0
**Data:** 2026-01-23
**Klient:** Prywatne Przedsiębiorstwo Usług Transportowych Ostrans (Ostrans)

---

### 1. Cel i Zakres Projektu

Celem projektu jest stworzenie wewnętrznego systemu webowego (panelu pracowniczego) wspierającego zarządzanie wirtualnym przedsiębiorstwem transportowym. System ma służyć do organizacji pracy, komunikacji oraz zarządzania flotą i infrastrukturą w kontekście gier symulacyjnych (OMSI 2, Roblox: Nid's Buses & Trams).

System nie będzie zintegrowany technicznie (API) z zewnętrznym Systemem Informacji Liniowej (SIL), jednak musi zachować **pełną spójność wizualną i logiczną** z systemem [sil.kanbeq.me](https://sil.kanbeq.me), aby zapewnić użytkownikom realistyczne odczucia (immersję).

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
*   **Kompatybilność danych:** Administratorzy są zobowiązani do ręcznego utrzymywania spójności nazw przystanków i numeracji linii pomiędzy Panelem Ostrans a zewnętrznym SIL-em, aby kierowcy nie mieli dysonansu poznawczego.
