# Contributing

Thanks for taking an interest. This project is at an early stage, so there is a lot of
room to shape it.

## Before you write code

The domain model isn't settled yet. For anything beyond a small fix, **open an issue
first** and describe what you want to build. That avoids two people designing the same
tables in different directions.

Good first contributions right now:

- Proposals for the lead/contact/campaign schema
- Documentation of VICIdial tables and APIs we'll need to talk to
- Development tooling (CI, static analysis, formatting)

## Development setup

See [Getting started](README.md#getting-started) in the README.

## Making a change

1. Fork the repository and create a branch off `main`:
   ```bash
   git checkout -b your-feature-name
   ```
2. Make your change.
3. Make sure the test suite passes:
   ```bash
   php artisan test
   ```
4. Commit with a clear message describing *why*, not just *what*.
5. Push and open a pull request against `main`.

## Code style

This project follows the Laravel/PSR-12 conventions that ship with the framework. The
repository includes a `.styleci.yml` configured for the Laravel preset.

Keep new code consistent with what's around it — naming, structure, and comment density.

## Pull requests

- Keep them focused. One concern per PR.
- Explain the reasoning in the description.
- If it changes behaviour, say how you verified it.
- Draft PRs are fine for work in progress.

## Reporting bugs

Open an issue with:

- What you expected to happen
- What actually happened
- Steps to reproduce
- PHP and Laravel versions

For security vulnerabilities, see [SECURITY.md](SECURITY.md) instead — do not open a
public issue.

## Code of conduct

Be decent to each other. Harassment, personal attacks, and bad-faith argument aren't
welcome here. Maintainers may remove comments or block accounts that make the project
worse to participate in.

## License

By contributing, you agree that your contributions will be licensed under the
[MIT License](LICENSE) that covers this project.
