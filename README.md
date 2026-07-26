# Savannah Health System

Hospital management information system for Tanzanian health facilities.

**Brand:** Savannah Health System · **Accent:** Mint green (`#98E8C8`) with acacia trees & savannah snake motif

## Stack

- Laravel 12 (PHP) + MariaDB/MySQL (local) or PostgreSQL (Render)
- Blade + Tailwind CSS
- English + Kiswahili

## Live cloud

- **GitHub:** https://github.com/Dmmkuliku/savannah-health-system
- **Render (backend):** https://savannah-health-system.onrender.com
- **Vercel (public entry):** set after deploy — proxies automatically to Render via `RENDER_BACKEND_URL`

[![Deploy to Render](https://render.com/images/deploy-to-render-button.svg)](https://render.com/deploy?repo=https://github.com/Dmmkuliku/savannah-health-system)

## First login (cloud & local)

Only the administrator is created at install:

| Email | Password |
|---|---|
| `admin@savannah.health` | `Savannah@Admin1` |

After login, open **Staff Users** to register doctors, nurses, cashiers, lab, pharmacy, and other hospital workers.

## Local hospital PC install (multi-device LAN)

### Windows (XAMPP)

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache + MySQL**
2. Copy this project onto the hospital server PC
3. Double-click `install-hospital.bat`
4. Start the system:

```bat
php artisan serve --host=0.0.0.0 --port=8000
```

Other ward computers open: `http://SERVER-IP:8000`

Or point Apache `DocumentRoot` to the `public` folder for permanent LAN use.

### Linux / macOS

```bash
chmod +x install-hospital.sh
./install-hospital.sh
php artisan serve --host=0.0.0.0 --port=8000
```

### Manual

```bash
composer install
cp .env.example .env
php artisan key:generate
# set DB_* in .env to savannah_health
php artisan migrate --seed
npm install && npm run build
php artisan serve --host=0.0.0.0 --port=8000
```

## Modules

Patients/EMR, OPD, IPD, RCH, Maternity, Theatre, Blood Bank, Lab, Radiology, Pharmacy, Billing (TZS), NHIF verify/claims stubs, GePG stub, Auto exemptions (Msamaha), MTUHA reports, role-based staff.

## Deploy notes

- `render.yaml` — Docker web service + Postgres
- `vercel/proxy.js` — Vercel entry proxies all traffic to Render so one Vercel link runs the full system
- Set Vercel env `RENDER_BACKEND_URL` to the Render service URL after first deploy
