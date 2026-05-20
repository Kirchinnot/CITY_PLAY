<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).


1. Cloner le projet
git clone https://github.com/VOTRE-ORG/VOTRE-PROJET.git
2. Entrer dans le dossier
cd CITYPLAY
3. Se déplacer sur la branche develop
git checkout develop
4. Installer les dépendances PHP
composer install
5. Installer les dépendances Node.js
npm install
6. Copier le fichier .env
cp .env.example .env

Sous Windows PowerShell :

copy .env.example .env
7. Générer la clé Laravel
php artisan key:generate
8. Configurer le .env

Modifier :

APP_NAME=CityPlay

APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cityplay
DB_USERNAME=root
DB_PASSWORD=
9. Créer la base de données MySQL

Exemple :

CREATE DATABASE cityplay;
10. Exécuter les migrations
php artisan migrate
11. Exécuter les seeders
php artisan db:seed

OU :

php artisan migrate:fresh --seed
12. Créer le lien de stockage

TRÈS IMPORTANT pour les images.

php artisan storage:link
13. Lancer le serveur Laravel
php artisan serve
14. Lancer Vite

Dans un second terminal :

npm run dev
15. Ouvrir l’application
http://127.0.0.1:8000
Workflow quotidien recommandé
Avant de travailler
git checkout develop
git pull origin develop
Aller sur sa branche
git checkout feature/armel
Après modifications
git add .
git commit -m "feat: ..."
git push origin feature/armel
Si develop a changé
git checkout develop
git pull origin develop

git checkout feature/armel
git merge develop
CONSEIL IMPORTANT POUR VOUS

Ajoutez immédiatement un fichier :

README.md

avec :

installation,
commandes,
stack,
structure projet.

Parce que :

ça évite énormément de problèmes d’équipe.

BONUS — Commande rapide complète

Après clonage :

composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
npm run dev
php artisan serve

C’est votre setup standard CityPlay.