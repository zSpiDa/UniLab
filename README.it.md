# UniLab — Piattaforma per la Gestione di Progetti di Ricerca

[🇬🇧 English version](README.md)

**Piattaforma per la gestione di progetti di ricerca sviluppata con Laravel**

![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-10-FF2D20?logo=laravel&logoColor=white)
![REST API](https://img.shields.io/badge/REST-API-blue)
![SQLite](https://img.shields.io/badge/SQLite-Database-003B57?logo=sqlite&logoColor=white)

## Panoramica del progetto

**UniLab** è una piattaforma web progettata per supportare la gestione di progetti accademici e di ricerca.

L'applicazione mette a disposizione strumenti per organizzare progetti, gruppi di ricerca, task, milestone, pubblicazioni e scadenze all'interno di un ambiente centralizzato.

Sono previsti diversi ruoli utente per controllare l'accesso alle operazioni sui progetti, mentre le API REST permettono l'accesso programmatico ad alcune delle risorse dell'applicazione.

Il progetto è stato sviluppato nell'ambito del corso di **Programmazione per il Web** dell'Università degli Studi di Bari.

---

## Funzionalità principali

- Creazione e gestione dei progetti di ricerca
- Gestione dei membri e dei team di progetto
- Controllo degli accessi basato sui ruoli
- Assegnazione e monitoraggio dei task
- Gestione di milestone e scadenze
- Gestione delle pubblicazioni accademiche
- Commenti e allegati
- Tag associati ai progetti
- Sistema di notifiche
- Dashboard personale con informazioni sui progetti
- Esportazione dei dati in formato CSV
- API REST con autenticazione tramite token
- Operazioni di esportazione asincrone

---

## Ruoli utente

UniLab supporta diversi ruoli con responsabilità e permessi differenti:

- **Principal Investigator (PI)** — gestisce i progetti di ricerca e le relative risorse principali
- **Manager** — supporta la gestione e l'organizzazione dei progetti
- **Researcher** — partecipa alle attività di ricerca e ai progetti assegnati
- **Collaborator** — contribuisce alle attività di progetto con permessi limitati

L'accesso alle funzionalità viene limitato in base al ruolo dell'utente e alla sua appartenenza ai progetti.

---

## Tecnologie utilizzate

- **PHP 8.1+**
- **Laravel 10**
- **Laravel Sanctum**
- **Laravel Breeze**
- **Eloquent ORM**
- **Blade**
- **Tailwind CSS**
- **JavaScript**
- **Vite**
- **SQLite**
- **PHPUnit**

---

## Architettura dell'applicazione

Il progetto segue la tradizionale architettura MVC di Laravel:

```text
UniLab/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   ├── Models/
│   ├── Jobs/
│   └── Services/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   └── views/
├── routes/
│   ├── web.php
│   └── api.php
├── tests/
├── composer.json
├── package.json
└── artisan
```

L'applicazione separa modelli di dominio, controller, servizi e job in background, utilizzando il sistema di routing di Laravel sia per l'interfaccia web sia per le API REST.

---

## Principali entità

L'applicazione gestisce diverse entità collegate tra loro, tra cui:

- Utenti
- Progetti
- Task
- Milestone
- Pubblicazioni
- Autori
- Gruppi
- Commenti
- Allegati
- Tag
- Notifiche

Le relazioni tra le entità vengono gestite attraverso l'ORM **Eloquent** di Laravel.

---

## API REST

UniLab include una REST API versionata per accedere alle risorse dell'applicazione.

Le API comprendono operazioni relative a:

- Autenticazione
- Utenti
- Progetti
- Pubblicazioni
- Esportazione dei dati

Gli endpoint protetti utilizzano **Laravel Sanctum** per l'autenticazione tramite token.

---

## Notifiche e operazioni in background

La piattaforma comprende un sistema di notifiche per eventi e scadenze legati ai progetti.

Alcune operazioni, come l'esportazione dei dati, possono inoltre essere gestite in maniera asincrona attraverso i job di Laravel.

---

## Installazione

Clonare il repository:

```bash
git clone https://github.com/zSpiDa/UniLab.git
cd UniLab
```

Installare le dipendenze PHP:

```bash
composer install
```

Installare le dipendenze frontend:

```bash
npm install
```

Creare il file di configurazione dell'ambiente:

```bash
cp .env.example .env
```

Generare la chiave dell'applicazione:

```bash
php artisan key:generate
```

Configurare il database nel file `.env` ed eseguire le migration:

```bash
php artisan migrate
```

Compilare gli asset frontend:

```bash
npm run build
```

Avviare l'applicazione:

```bash
php artisan serve
```

---

## Esecuzione dei test

Il progetto include test automatici relativi alle funzionalità dell'applicazione, all'autenticazione e alle API.

Per eseguire la suite di test:

```bash
php artisan test
```

---

## Contesto accademico

UniLab è stato sviluppato come progetto accademico per il corso di **Programmazione per il Web** del corso di Laurea in Informatica e Comunicazione Digitale presso l'**Università degli Studi di Bari Aldo Moro**.

Il progetto si concentra sulla progettazione e implementazione di un'applicazione web strutturata attraverso Laravel, modellazione relazionale dei dati, autorizzazione basata sui ruoli, API REST e principi di ingegneria del software.

---

## Disclaimer

Il progetto è stato sviluppato a scopo didattico.

Rappresenta una dimostrazione accademica dello sviluppo di applicazioni web e non è progettato per un utilizzo in produzione senza ulteriori interventi relativi a sicurezza, configurazione e infrastruttura.
