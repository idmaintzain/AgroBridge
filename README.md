# AgroBridge

A Lead City University postgraduate diploma project by **Olatunji Ayodeji Peter** (LCU/PG/0010452).

Smallholder farmers list a crop, a quantity and a price. A buyer pays from a demo wallet. AgroBridge holds that payment. The farmer is paid only after the buyer confirms collection, or after the farmer enters a collection code only the buyer can see. There is no control that pays the farmer merely because they say the produce was delivered.

The academic write-up is in `project-document`.

## Run the demo

MySQL 9 is expected on `127.0.0.1:3307`, database `agrobridge`, user `root`, empty password. A project-local server is included:

```bash
sh scripts/start-mysql.sh
php artisan migrate --seed
php artisan serve
```

Open http://127.0.0.1:8000. The sign-in page has buttons for the defence accounts, and the password for all of them is `password`:

| Email | Password | Level |
| --- | --- | --- |
| farmer@agrobridge.test | password | Farmer only. Wallet ₦20,000.00. Lists maize, cassava, tomato, rice, and okra |
| buyer@agrobridge.test | password | Buyer only. Wallet ₦500,000.00 |
| ada@agrobridge.test | password | Farmer and buyer. Wallet ₦150,000.00. Lists yam, pepper, plantain, groundnut, onion, and cocoa |
| admin@agrobridge.test | password | Administrator. Disputes, fees, and settlement |

`php artisan orders:settle` cancels unpaid reservations, refunds collections that never happen, and releases payments whose dispute window has closed. The same job runs every five minutes when the scheduler is on, and the admin screen has a button for the defence.

## Tests

```bash
php artisan test
```

The tests cover the money rules: a farmer cannot self-release, a buyer cannot overspend, an unpaid order returns reserved stock, and a fee change does not rewrite an order that was already placed.
