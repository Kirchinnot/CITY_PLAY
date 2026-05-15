📝 Rapport d'Avancement Quotidien – CityPlay
Date : 15/05/2026

Phase : Session 1 – Fondations & Architecture

Statut global : 🟢 En ligne avec les objectifs de la session

🎯 Objectifs Atteints de la Session
1. Modélisation et Structuration de la Base de Données
Réalisation : Écriture et exécution réussie des migrations globales pour Laravel 12.

2. Création des Modèles Eloquent (Backend)
Réalisation : Génération de l'ensemble des fichiers de modèles PHP nécessaires à l'architecture.

3. Développement et Injection des Seeders
Réalisation : Création du script DatabaseSeeder permettant de peupler instantanément la base de données locale avec un jeu de données de test cohérent (fausses mairies, parcours types, points GPS et énigmes).

4. Alignement Fonctionnel : Parcours Utilisateur & Logique Métier
Réalisation : Session de travail collaborative pour clarifier et verrouiller le périmètre du MVP.

Points clés validés :

Validation du flux d'inscription avec amorce pour le 2FA.

Spécification du comportement du geofencing (calcul de distance en arrière-plan et tolérance fixée).

Mécanique des équipes (limite stricte à 10 membres gérée par la table pivot).

Validation de la logique de purge automatique des données pour la conformité RGPD.

🛠️ État des Lieux de la Stack Technique 
Framework : Laravel v12.59.0 (Opérationnel)

Auth / Frontend : Base Laravel Breeze (Vue 3 / Inertia) configurée.

Base de données : Migrée et initialisée (php artisan migrate --seed OK).