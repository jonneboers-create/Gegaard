# Gegaard · Klant & Specificatie Portaal (gedeeld)

Sleep een Outlook-mail (`.msg`) erin → klant, product/artikel, volume, deadlines en
specificaties worden er automatisch uitgehaald en de bijlagen gesorteerd. Nu **gedeeld**:
iedereen die verbindt, voegt toe aan en ziet **dezelfde** lijst.

## Twee onderdelen

| Onderdeel        | Bestand      | Waar het draait |
|------------------|--------------|-----------------|
| Frontend         | `index.html` | GitHub Pages (de github.io-link) |
| Backend + opslag | `api.php`    | **Jouw eigen PHP-server** (SQLite-database + bijlagen) |

De frontend praat via HTTPS met `api.php`. Alle dossiers en bijlagen leven in één
SQLite-bestand op jouw server (`data/gegaard.sqlite`).

---

## 1) Backend plaatsen (jouw server)

1. Zet **`api.php`** op een server met **PHP 8+** en de **pdo_sqlite**-extensie
   (zelfde soort omgeving als je Licentie-portaal).
2. Open `api.php` en pas de bovenste 3 regels aan:
   - `$ACCESS_CODE` → kies een gedeeld wachtwoord voor je team.
   - `$DB_FILE` → laat staan, of kies een schrijfbare map.
   - `$ALLOW_ORIGIN` → `'*'` werkt (achter de code), of zet exact
     `'https://jonneboers-create.github.io'` voor extra afscherming.
3. Zorg dat de map `data/` **schrijfbaar** is. Bij de eerste aanroep maakt `api.php`
   zelf de database aan én een `.htaccess` die de map afschermt tegen downloaden.
4. **Belangrijk:** de server moet bereikbaar zijn via **HTTPS met een geldig certificaat**.
   GitHub Pages draait op HTTPS en mag alleen naar HTTPS-adressen praten.
5. Voor de PDF-specs (tot ~3 MB): zet in `php.ini` zo nodig
   `post_max_size = 16M` en `upload_max_filesize = 16M`.

Test: `https://<jouwserver>/pad/api.php?action=ping` met de juiste code geeft `{"ok":true}`.

> **Niet publiek bereikbaar / liever intern houden?** Zet dan zowel `index.html` als
> `api.php` op dezelfde (intranet-)server. Dan is er geen CORS of github.io nodig en
> blijft alle klantdata binnen. De github.io-link vervalt dan.

## 2) Frontend op GitHub Pages

1. Repo `Gegaard` → upload **`index.html`** in de root (of gebruik `deploy.sh`).
2. Settings → Pages → branch `main`, map `/ (root)` → Save.
3. Tool staat op: `https://jonneboers-create.github.io/Gegaard/`
4. **Eenmalig per gebruiker:** bij openen vraagt de tool om het **serveradres**
   (`https://<jouwserver>/pad/api.php`) en de **toegangscode**. Daarna onthoudt de
   browser dit. Wil je dat collega's alleen de code hoeven in te vullen? Zet je
   serveradres dan vast in `index.html` bij `const SERVER_URL_DEFAULT = "..."`.

---

## Gebruik

- **Sleep `.msg`-bestanden** naar het vlak → controlescherm → **Opslaan** (zichtbaar voor iedereen).
- **↻ Vernieuwen** haalt de nieuwste lijst van de server.
- **⚙ Verbinding** om serveradres/code te wijzigen.
- Bijlagen **openen/downloaden** per dossier; **Export Excel** (3 tabbladen) en **Backup JSON**.

## Goed om te weten

- **Gedeelde data, gedeelde code.** De toegangscode is één gezamenlijk wachtwoord.
  Voor stevigere beveiliging: achter de bedrijfs-VPN/intranet plaatsen of echte logins toevoegen.
- **Last-write-wins.** Verschillende dossiers botsen niet (eigen id). Bewerkt iemand
  hetzelfde dossier tegelijk, dan wint de laatste opslag. Gebruik ↻ Vernieuwen.
- **Back-up = het bestand `data/gegaard.sqlite`** op je server. Kopieer dat periodiek.
- **AI-verrijking** blijft optioneel en lokaal (Anthropic-key in je browser, niet op de server).

## Vereisten backend

- PHP 8+ met `pdo_sqlite`
- HTTPS (verplicht voor gebruik vanaf github.io)
- Schrijfrechten op de `data/`-map
