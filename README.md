# Exam Importer (Paragin Assignment)

### Prerequisites

- PHP (tested with 8.2)
- SQLite PHP extension
- Composer

## Setup

```bash
git clone "https://github.com/zanbaldwin/paragin.git" "<project-location>"
cd "<project-location>"
composer install
bin/console -n doctrine:migrations:migrate
```

## Importing Exams

```bash
bin/console app:import:exam 'absolute/or/relative/path/to/exam.xlsx'
```

## Viewing Exams
Your own personal choice of local development setup, or:

```bash
php -S localhost:8000 -t public
```

- Visit [http://localhost:8000](http://localhost:8000) to view the app (in the
  popular style of early 90's web design).

## Issues

Calculating the Rit correlation value was harder than expected. The values I
calculated are obviously wrong, but by this point I'm done and was grateful I
don't need to think about mathematical formulas anymore.
