# vici-crm

An open-source CRM built on [Laravel](https://laravel.com), intended to integrate with
[VICIdial](https://www.vicidial.org/) call-center infrastructure.

> **Status: early scaffold.** This repository currently contains a clean Laravel 8
> application skeleton and nothing more — no CRM models, controllers, or VICIdial
> integration have been written yet. It is published early and openly so the work can
> happen in public from the first commit. Contributions and ideas are welcome.

## Goals

The intent is to build a CRM that sits alongside a VICIdial dialer, rather than
replacing it:

- Lead and contact management backed by the dialer's list data
- Agent-facing screens for call disposition and follow-up
- Campaign and call-outcome reporting
- A documented HTTP API so other tools can read and write the same data

None of these are implemented yet. See [Roadmap](#roadmap).

## Requirements

- PHP 7.3+ or 8.x (developed against PHP 8.4)
- Composer
- Node.js and npm (for front-end assets)
- A database supported by Laravel (MySQL/MariaDB recommended, to match VICIdial)

## Getting started

```bash
git clone https://github.com/yogi3696/vici-crm.git
cd vici-crm

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database credentials, then:

```bash
php artisan migrate
php artisan serve
```

The application will be available at http://localhost:8000.

To build front-end assets:

```bash
npm run dev     # development build
npm run watch   # rebuild on change
npm run prod    # production build
```

## Running tests

```bash
php artisan test
```

## Roadmap

Rough order of work. Nothing here is locked in — if you want to take something on or
argue for a different shape, open an issue.

- [ ] Define the core domain: leads, contacts, campaigns, dispositions
- [ ] Database migrations for the above
- [ ] VICIdial connection layer (read lists, push dispositions)
- [ ] Authentication and agent/manager roles
- [ ] Agent call screen
- [ ] Reporting views
- [ ] HTTP API + documentation

## Contributing

Contributions are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md). Since the project is
at the scaffold stage, opening an issue to discuss direction is often more useful than a
pull request.

## Security

Please do not open public issues for security vulnerabilities. See
[SECURITY.md](SECURITY.md) for how to report them.

## License

[MIT](LICENSE).

This project is built on the Laravel framework, which is also MIT licensed.
VICIdial is a separate project licensed under the AGPL; this repository does not
include or redistribute VICIdial code.
