# Gegaard · Klant & Specificatie Portaal

Intern hulpmiddel van Luiten Food. Sleep een Outlook-mail (`.msg`) erin en het portaal
haalt automatisch **klant, product/artikel, volume, deadlines en productspecificaties**
eruit en sorteert de bijlagen. Antwoord op één blik: *welke klant gebruikt welk product
en hoeveel volume.*

Alles draait **client-side in de browser** — geen server, geen frameworks. Eén bestand.

---

## Delen via GitHub Pages

1. Maak een repository aan (bv. `gegaard-portaal`).
2. Upload **`index.html`** in de hoofdmap (zie `deploy.sh` voor de git-route).
3. Repo → **Settings → Pages** → Source: `Deploy from a branch` → branch `main`, map `/ (root)` → **Save**.
4. Na ~1 minuut staat de tool op:
   `https://<jouw-gebruikersnaam>.github.io/gegaard-portaal/`
   Die link deel je met collega's.

> Privé houden? Gebruik een **private repo** met **GitHub Pages via een Organization/Team**,
> of host hem op het intranet. De code is alleen frontend en bevat geen klantdata.

---

## Hoe gebruik je het

- **Sleep één of meer `.msg`-bestanden** naar het vlak (of klik om te kiezen).
- Er opent een **controlescherm** met alle velden vóór-ingevuld. Pas aan waar nodig en klik **Opslaan in portaal**.
- In het **overzicht** sorteer/zoek je op klant, product, volume of aantal specs.
- Per dossier kun je elke **bijlage openen of downloaden** (PDF-specs, bidsheets, labels).
- **Export Excel** geeft 3 tabbladen (Overzicht / Volume per regio / Bijlagen).
  **Backup JSON** maakt een herstelbestand; **Import JSON** leest het terug.

## Belangrijk om te weten

- **Data is lokaal.** Verwerkte mails en bijlagen leven in de IndexedDB van *jouw* browser.
  Andere collega's zien jouw lijst niet — ze hebben hun eigen lokale opslag.
  Wil je een gedeelde dataset voor het hele team? Dan is een backend nodig (los traject).
- **AI-verrijking** is optioneel. Binnen Claude werkt het zonder sleutel. Op GitHub Pages
  vul je in de instellingen een Anthropic API-key in; die wordt **alleen lokaal**
  (localStorage) bewaard en staat **niet** in de repo. Zet 'm nooit hard in de code.

## Ondersteund

- Outlook `.msg` (volledig client-side geparset)
- Bijlagen: PDF, XLSX/XLS, afbeeldingen — opgeslagen en terug te openen
- EU-getalnotatie bij volume (`1.500` = 1500, `1,5` = 1,5; ton → kg automatisch)
- Meertalige detectie (NL / EN / DE / FR / IT) voor product, volume en deadlines
